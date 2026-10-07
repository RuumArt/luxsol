<?php

namespace Room\Tools;

use Bitrix\Main\Application;
use Room\Services;

class Order
{
    const DPD_CLIENT_KEY = \DPD_CLIENT_KEY;
    const DPD_EMAIL = \DPD_EMAIL;
    const DPD_DELIS_ID = \DPD_DELIS_ID;
    const DPD_PICKUP_ADDRESS_ID = \DPD_PICKUP_ADDRESS_ID;

    /**
     * Код свойства заказа, куда пишется номер отправления.
     * Если такого свойства в заказе нет, шаг просто пропускается
     */
    const DPD_MPS_PROPERTY_CODE = 'DPD_MPS_ID';

    /**
     * Службы доставки, заказы которых отправляются в DPD.
     * #18 - Kuriérska služba DPD. Packeta, Toptrans и самовывоз
     * в DPD уходить не должны
     */
    const DPD_DELIVERY_IDS = [18];

    /**
     * Доставляет ли заказ курьер DPD
     *
     * @param \Bitrix\Sale\Order $order
     * @return bool
     */
    public static function isDpdOrder($order): bool
    {
        $deliveryIds = array_map('intval', (array)$order->getDeliveryIdList());

        return !empty(array_intersect($deliveryIds, self::DPD_DELIVERY_IDS));
    }

    /**
     * Создание отправления DPD по заказу
     *
     * Раньше и результат, и ошибка уходили в error_log и терялись: менеджер
     * переводил заказ в "Готово к отправке", видел успех, а отправления не было.
     * Теперь итог пишется в журнал событий Битрикса и в комментарий к заказу
     *
     * @param \Bitrix\Sale\Order $order
     * @return array ['success' => bool, 'parcels' => array, 'error' => string]
     */
    public static function createDpd($order): array
    {
        $orderId = (int)$order->getId();

        try {
            // Нулевой вес не мешает создать отправление: DpdService подставит
            // 1 кг. Но это почти всегда незаполненный вес товара в каталоге,
            // и тариф с габаритами тогда считаются неверно - помечаем в журнале
            $weightWarning = ((float)$order->getBasket()->getWeight() <= 0)
                ? ' ВНИМАНИЕ: вес заказа нулевой, отправлен 1 кг по умолчанию -'
                    . ' проверьте вес товаров в каталоге.'
                : '';

            $result = self::getDpdService()->createShipmentFromOrder($order);
            $parcels = self::extractParcelNumbers($result);

            $message = 'DPD: отправление создано'
                . (!empty($parcels)
                    ? ', посылки: ' . implode(', ', $parcels)
                    : '. Номера посылок в ответе не найдены, ответ: ' . self::shortJson($result));

            $message .= $weightWarning;

            self::logDpd($orderId, $message, $weightWarning !== '');
            self::saveDpdResult($orderId, $message, $parcels);

            return [
                'success' => true,
                'parcels' => $parcels,
                'error' => '',
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            $message = 'DPD: отправление НЕ создано. ' . $e->getMessage();

            self::logDpd($orderId, $message, true);
            self::saveDpdResult($orderId, $message, []);

            return [
                'success' => false,
                'parcels' => [],
                'error' => $e->getMessage(),
                'result' => [],
            ];
        }
    }

    /**
     * @return Services\DpdService
     */
    public static function getDpdService(): Services\DpdService
    {
        return new Services\DpdService(
            self::DPD_CLIENT_KEY,
            self::DPD_EMAIL,
            self::DPD_DELIS_ID,
            self::DPD_PICKUP_ADDRESS_ID
        );
    }

    /**
     * Номера посылок из ответа DPD
     *
     * Структура ответа отличается для одной и нескольких посылок,
     * поэтому просто собираем все подходящие ключи на любой глубине
     *
     * @param mixed $result
     * @return array
     */
    private static function extractParcelNumbers($result): array
    {
        if (empty($result) || !is_array($result)) {
            return [];
        }

        $numbers = [];

        array_walk_recursive($result, static function ($value, $key) use (&$numbers) {
            $key = strtolower((string)$key);

            if (in_array($key, ['parcelno', 'mpsid'], true) && (string)$value !== '') {
                $numbers[] = (string)$value;
            }
        });

        return array_values(array_unique($numbers));
    }

    /**
     * Ответ DPD в лог одной строкой, обрезанный до разумной длины
     *
     * @param mixed $value
     * @param int $limit
     * @return string
     */
    private static function shortJson($value, int $limit = 700): string
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return '(не удалось сериализовать)';
        }

        return mb_strlen($json) > $limit ? mb_substr($json, 0, $limit) . '…' : $json;
    }

    /**
     * Запись в журнал: и в файл, и в журнал событий Битрикса,
     * который виден в админке без доступа к серверу
     *
     * @param int $orderId
     * @param string $message
     * @param bool $isError
     * @return void
     */
    private static function logDpd(int $orderId, string $message, bool $isError): void
    {
        $text = 'Заказ #' . $orderId . '. ' . $message;

        AddMessage2Log($text);

        if (class_exists('\CEventLog')) {
            \CEventLog::Add([
                'SEVERITY' => $isError ? 'ERROR' : 'INFO',
                'AUDIT_TYPE_ID' => $isError ? 'ROOM_DPD_ERROR' : 'ROOM_DPD_SUCCESS',
                'MODULE_ID' => 'sale',
                'ITEM_ID' => $orderId,
                'DESCRIPTION' => $text,
            ]);
        }
    }

    /**
     * Дописывает итог вызова в комментарий к заказу и, если заведено
     * свойство DPD_MPS_ID, сохраняет туда номера посылок
     *
     * Выполняется в фоновом задании: метод вызывается из обработчика смены
     * статуса, то есть в момент, когда заказ ещё сохраняется. Сохранять его
     * повторно изнутри этого же сохранения нельзя
     *
     * @param int $orderId
     * @param string $message
     * @param array $parcels
     * @return void
     */
    private static function saveDpdResult(int $orderId, string $message, array $parcels): void
    {
        Application::getInstance()->addBackgroundJob(
            static function () use ($orderId, $message, $parcels) {
                try {
                    $order = \Bitrix\Sale\Order::load($orderId);

                    if (!$order) {
                        return;
                    }

                    $comments = (string)$order->getField('COMMENTS');
                    $stamp = date('d.m.Y H:i');

                    $order->setField(
                        'COMMENTS',
                        trim($comments . "\n" . $stamp . ' ' . $message)
                    );

                    if (!empty($parcels)) {
                        $property = \Room\Helpers\PropertyHelper::getPropertyByCode(
                            $order->getPropertyCollection(),
                            self::DPD_MPS_PROPERTY_CODE
                        );

                        if ($property) {
                            $property->setValue(implode(', ', $parcels));
                        }
                    }

                    $order->save();
                } catch (\Throwable $e) {
                    AddMessage2Log(
                        'Заказ #' . $orderId . '. Не удалось записать итог DPD: ' . $e->getMessage()
                    );
                }
            }
        );
    }
}
