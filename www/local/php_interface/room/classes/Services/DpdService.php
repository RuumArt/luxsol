<?php

namespace Room\Services;

use Bitrix\Sale\Order;
use Room\Helpers\PropertyHelper;
use Exception;

/**
 * Класс для работы с DPD API
 * Документация: https://capi.dpd.sk/shipment/json
 */
class DpdService
{
    /**
     * URL API для создания отправлений
     */
    // const API_URL = 'https://capi.dpd.sk/shipment/json';
    const API_URL = 'https://api.dpd.sk/shipment/json';

    /**
     * URL API для поиска пунктов выдачи
     */
    const API_PARCELSHOP_URL = 'https://api.dpd.sk/parcelshop/json';

    /**
     * Максимальный вес одной посылки, кг. Стандарт DPD SK - 31.5 кг,
     * заказ тяжелее раскладывается на несколько коробок
     */
    // Предел веса одного мешка. Не паспортные 31,5 кг: на сайте виден вес
    // сетки без коробки и упаковки, плюс погрешность весов.
    // Значение по умолчанию: рабочее берётся из настроек службы DPD
    // методом maxParcelWeight(), чтобы накладная делилась ровно так же,
    // как посчитана цена доставки
    const MAX_PARCEL_WEIGHT = 30.0;

    /**
     * Вес по умолчанию, кг. Подставляется, если в заказе вес не посчитан
     */
    const DEFAULT_PARCEL_WEIGHT = 1.0;

    /**
     * @var string ClientKey для аутентификации
     */
    private $clientKey;

    /**
     * @var string Email для аутентификации
     */
    private $email;

    /**
     * @var bool Использовать тестовое окружение
     */
    private $isTestMode = false;

    /**
     * @var string Delis ID для идентификации клиента
     */
    private $delisId;

    /**
     * @var int ID адреса забора (Pickup address ID)
     */
    private $pickupAddressId;

    /**
     * DpdService constructor.
     *
     * @param string $clientKey API ключ клиента
     * @param string $email Email для аутентификации
     * @param string|null $delisId Delis ID (опционально)
     * @param int|null $pickupAddressId ID адреса забора (опционально)
     * @param bool $isTestMode Тестовый режим
     */
    public function __construct(
        string $clientKey,
        string $email,
        ?string $delisId = null,
        ?int $pickupAddressId = null,
        bool $isTestMode = false
    ) {
        $this->clientKey = $clientKey;
        $this->email = $email;
        $this->delisId = $delisId;
        $this->pickupAddressId = $pickupAddressId;
        $this->isTestMode = $isTestMode;
    }

    /**
     * Создание отправления на основе заказа Bitrix
     *
     * @param Order $order Заказ Bitrix
     * @return array|false Результат создания отправления или false в случае ошибки
     * @throws Exception
     */
    public function createShipmentFromOrder(Order $order)
    {
        $shipmentData = $this->prepareShipmentData($order);

        $errors = $this->getValidationErrors($shipmentData);

        if (!empty($errors)) {
            throw new Exception('Заказ не готов к отправке в DPD: ' . implode('; ', $errors));
        }

        // Исключение намеренно не глушим: вызывающий код пишет его
        // в журнал и в комментарий к заказу, иначе сбой остаётся незамеченным
        return $this->createShipment($shipmentData);
    }

    /**
     * Создание отправления
     *
     * @param array $shipmentData Данные отправления
     * @return array Результат создания отправления
     * @throws Exception
     */
    public function createShipment(array $shipmentData): array
    {
        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'create',
            'params' => [
                'DPDSecurity' => [
                    'SecurityToken' => [
                        'ClientKey' => $this->clientKey,
                        'Email' => $this->email
                    ]
                ],
                'shipment' => $shipmentData
            ],
            'id' => 'null'
        ];

        $response = $this->makeRequest(self::API_URL, $requestData);

        if (isset($response['error'])) {
            throw new Exception('DPD API Error: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        return $response['result']['result'] ?? [];
    }

    /**
     * Удаление отправления
     *
     * @param string|array $mpsId MPS ID отправления или массив MPS ID
     * @return array Результат удаления
     * @throws Exception
     */
    public function deleteShipment($mpsId): array
    {
        if (is_string($mpsId)) {
            $mpsId = [$mpsId];
        }

        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'deleteShipments',
            'params' => [
                'DPDSecurity' => [
                    'SecurityToken' => [
                        'ClientKey' => $this->clientKey,
                        'Email' => $this->email
                    ]
                ],
                'mpsId' => $mpsId
            ],
            'id' => 'null'
        ];

        $response = $this->makeRequest(self::API_URL, $requestData);

        if (isset($response['error'])) {
            throw new Exception('DPD API Error: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        return $response['result']['result'] ?? [];
    }

    /**
     * Печать этикеток
     *
     * @param array $parcelNumbers Массив номеров посылок
     * @param string $pageSize Размер страницы (A4, A6)
     * @param string $position Позиция на странице (1, 2, 4)
     * @return array Результат с URL этикетки
     * @throws Exception
     */
    public function printLabels(array $parcelNumbers, string $pageSize = 'A4', string $position = '1'): array
    {
        $parcels = [];
        foreach ($parcelNumbers as $parcelNo) {
            $parcels[] = ['parcelno' => $parcelNo];
        }

        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'printLabels',
            'params' => [
                'DPDSecurity' => [
                    'SecurityToken' => [
                        'ClientKey' => $this->clientKey,
                        'Email' => $this->email
                    ]
                ],
                'label' => [
                    'parcels' => [
                        'parcel' => $parcels
                    ],
                    'pageSize' => $pageSize,
                    'position' => $position
                ]
            ],
            'id' => 'null'
        ];

        $response = $this->makeRequest(self::API_URL, $requestData);

        if (isset($response['error'])) {
            throw new Exception('DPD API Error: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        return $response['result'] ?? [];
    }

    /**
     * Получить все доступные пункты выдачи
     *
     * @return array Список пунктов выдачи
     * @throws Exception
     */
    public function getAllParcelShops(): array
    {
        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'getAll',
            'id' => 'null'
        ];

        $response = $this->makeRequest(self::API_PARCELSHOP_URL, $requestData);

        if (isset($response['error'])) {
            throw new Exception('DPD API Error: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        return $response['result']['parcelshops']['parcelshop'] ?? [];
    }

    /**
     * Получить пункты выдачи по адресу
     *
     * @param string $city Город
     * @param string $zip Почтовый индекс
     * @param string|null $street Улица (опционально)
     * @param int $radius Радиус поиска в км (по умолчанию 10)
     * @param string $country Код страны (ISO alpha-2, по умолчанию 'SK')
     * @return array Список пунктов выдачи
     * @throws Exception
     */
    public function getParcelShopsByAddress(
        string $city,
        string $zip,
        ?string $street = null,
        int $radius = 10,
        string $country = 'SK'
    ): array {
        $params = [
            'city' => $city,
            'zip' => $zip,
            'radius' => $radius,
            'country' => $country
        ];

        if ($street !== null) {
            $params['street'] = $street;
        }

        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'getByAddress',
            'params' => $params,
            'id' => 'null'
        ];

        $response = $this->makeRequest(self::API_PARCELSHOP_URL, $requestData);

        if (isset($response['error'])) {
            throw new Exception('DPD API Error: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        return $response['result']['parcelshops']['parcelshop'] ?? [];
    }

    /**
     * Выполнение HTTP запроса к API
     *
     * @param string $url URL API
     * @param array $data Данные для отправки
     * @return array Ответ API
     * @throws Exception
     */
    private function makeRequest(string $url, array $data): array
    {
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($jsonData === false) {
            throw new Exception('Ошибка кодирования JSON: ' . json_last_error_msg());
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);

        curl_close($curl);

        if ($response === false) {
            throw new Exception('Ошибка CURL: ' . $curlError);
        }

        if ($httpCode !== 200) {
            throw new Exception('HTTP Error: ' . $httpCode . ' Response: ' . $response);
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Ошибка декодирования JSON: ' . json_last_error_msg() . ' Response: ' . $response);
        }

        return $decodedResponse;
    }

    /**
     * Формирование данных отправления для стандартного заказа
     *
     * @param array $params Параметры заказа
     * @return array Данные отправления
     */
    public static function buildShipmentData(array $params): array
    {
        $shipment = [
            'reference' => $params['reference'] ?? '',
            'delisId' => $params['delisId'] ?? '',
            'product' => $params['product'] ?? 1, // 1 - Classic, 10 - CityService
            'note' => $params['note'] ?? ''
        ];

        // Данные отправителя
        if (isset($params['addressSender']['id'])) {
            $shipment['addressSender'] = [
                'id' => $params['addressSender']['id']
            ];
        } elseif (isset($params['addressSender'])) {
            $shipment['addressSender'] = [
                'type' => $params['addressSender']['type'] ?? 'b2b',
                'name' => $params['addressSender']['name'] ?? '',
                'street' => $params['addressSender']['street'] ?? '',
                'houseNumber' => $params['addressSender']['houseNumber'] ?? '',
                'zip' => $params['addressSender']['zip'] ?? '',
                'country' => $params['addressSender']['country'] ?? 703, // 703 - Slovakia
                'city' => $params['addressSender']['city'] ?? '',
                'phone' => $params['addressSender']['phone'] ?? '',
                'email' => $params['addressSender']['email'] ?? '',
                'note' => $params['addressSender']['note'] ?? ''
            ];
        }

        // Данные получателя
        $shipment['addressRecipient'] = [
            'type' => $params['addressRecipient']['type'] ?? 'b2b',
            'name' => $params['addressRecipient']['name'] ?? '',
            'street' => $params['addressRecipient']['street'] ?? '',
            'houseNumber' => $params['addressRecipient']['houseNumber'] ?? '',
            'zip' => $params['addressRecipient']['zip'] ?? '',
            'country' => $params['addressRecipient']['country'] ?? 703,
            'city' => $params['addressRecipient']['city'] ?? '',
            'phone' => $params['addressRecipient']['phone'] ?? '',
            'email' => $params['addressRecipient']['email'] ?? '',
            'note' => $params['addressRecipient']['note'] ?? ''
        ];

        // Параметры забора
        if (isset($params['pickup'])) {
            $shipment['pickup'] = [
                'date' => $params['pickup']['date'] ?? date('Ymd'),
                'timeWindow' => [
                    'beginning' => $params['pickup']['timeWindow']['beginning'] ?? '1000'
                ]
            ];
        }

        // Посылки
        $parcels = [];
        if (isset($params['parcels']) && is_array($params['parcels'])) {
            foreach ($params['parcels'] as $parcel) {
                $parcels[] = [
                    'weight' => $parcel['weight'] ?? 1,
                    'height' => $parcel['height'] ?? 1,
                    'width' => $parcel['width'] ?? 1,
                    'depth' => $parcel['depth'] ?? 1
                ];
            }
        } else {
            // По умолчанию одна посылка
            $parcels[] = [
                'weight' => $params['weight'] ?? 1,
                'height' => $params['height'] ?? 1,
                'width' => $params['width'] ?? 1,
                'depth' => $params['depth'] ?? 1
            ];
        }

        if (count($parcels) === 1) {
            $shipment['parcels'] = [
                'parcel' => $parcels[0]
            ];
        } else {
            $shipment['parcels'] = [
                'parcel' => $parcels
            ];
        }

        // Дополнительные услуги
        if (isset($params['services']) && is_array($params['services'])) {
            $shipment['services'] = $params['services'];
        }

        return $shipment;
    }

    /**
     * Подготовка данных отправления из заказа Bitrix
     *
     * Метод публичный: им пользуется диагностика local/tools/check_dpd_order.php,
     * чтобы показать посылку без отправки в DPD
     *
     * @param Order $order Заказ Bitrix
     * @return array Данные отправления
     */
    public function prepareShipmentData(Order $order): array
    {
        $propertyCollection = $order->getPropertyCollection();
        $basket = $order->getBasket();

        $orderId = $order->getId();
        $orderPrice = $order->getPrice();

        // Получение данных получателя (основные)
        $mainName = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'FIO');
        $mainLastName = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'LAST_NAME');
        $mainEmail = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'EMAIL');
        $mainPhone = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'PHONE');
        $mainAddress = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'Adresa');
        $mainCity = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'Mesto');
        $mainZip = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'PSС');

        // Получение данных получателя (дополнительные - для доставки)
        $name = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'FIO_2');
        $lastName = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'LAST_NAME_2');
        $phone = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'PHONE_2');
        $address = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'Adresa_2');
        $city = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'Mesto_2');
        $zip = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'PSС_2');

        // Выбор данных получателя (приоритет у дополнительных)
        $recipientName = !empty($name) ? $name : $mainName;
        $recipientLastName = !empty($lastName) ? $lastName : $mainLastName;
        $recipientPhone = !empty($phone) ? $phone : $mainPhone;
        $recipientEmail = $mainEmail;
        $recipientAddress = !empty($address) ? $address : $mainAddress;
        $recipientCity = !empty($city) ? $city : $mainCity;
        $recipientZip = !empty($zip) ? $zip : $mainZip;

        // Вес посылки (в кг)
        $weight = $basket->getWeight();
        $normalWeight = !empty($weight)
            ? round($weight / 1000, 2)
            : self::DEFAULT_PARCEL_WEIGHT;

        // Формирование полного имени получателя
        $fullName = trim($recipientName . ' ' . $recipientLastName);

        // Парсинг адреса (улица и номер дома)
        $addressParts = $this->parseAddress($recipientAddress);
        $street = $addressParts['street'];
        $houseNumber = $addressParts['houseNumber'];

        // Данные получателя
        $addressRecipient = [
            'type' => 'b2c',
            'name' => $fullName,
            'street' => $street,
            'houseNumber' => $houseNumber,
            // Индекс отправляем только цифрами: в заказах он часто записан
            // как "831 02", и DPD такой формат не принимает
            'zip' => self::normalizeZip($recipientZip),
            'country' => 703, // 703 - Slovakia
            'city' => $recipientCity,
            'phone' => $recipientPhone,
            'email' => $recipientEmail
        ];

        // Данные отправителя
        // Если указан Pickup address ID, используем его вместо полных данных
        if ($this->pickupAddressId) {
            $addressSender = [
                'id' => $this->pickupAddressId
            ];
        } else {
            // Fallback на полные данные, если ID не указан
            $addressSender = [
                'type' => 'b2b',
                'name' => 'Luxsol s.r.o.',
                'street' => 'Račianska',
                'houseNumber' => '66',
                'zip' => '83102',
                'country' => 703, // 703 - Slovakia
                'city' => 'Bratislava',
                'phone' => '+421905123456',
                'email' => 'office@luxsol.sk'
            ];
        }

        // Параметры забора (сегодня)
        $pickup = [
            'date' => date('Ymd'),
            'timeWindow' => [
                'beginning' => '1000'
            ]
        ];

        // Посылки: вес тяжелее лимита раскладывается на несколько коробок
        $parcels = $this->buildParcels($basket, $normalWeight);

        $services = [];

        $paymentCollection = $order->getPaymentCollection();

        $psIDs = [];

        foreach ($paymentCollection as $payment):
            $psID = $payment->getPaymentSystemId();
            $psIDs[] = (int)$psID;
        endforeach;

        if (in_array(6, $psIDs)) {
            $services['cod'] = [
                'amount' => (string)$orderPrice,
                'currency' => "EUR",
                'variableSymbol' => $orderId,
                'paymentMethod' => 0,
                'bankAccount' => [
                    'id' => 3,
                ],
            ];
        }

        if (!empty($recipientEmail)) {
            $services['notification'] = [
                'type' => 3,
                'destination' => $recipientPhone,
                'rule' => 1,
            ];
        }

        // Формирование данных отправления
        $shipmentData = self::buildShipmentData([
            'reference' => (string)$orderId,
            'delisId' => $this->delisId ?? '',
            'product' => 9, // 1 - Classic
            'note' => 'Order #' . $orderId,
            'addressSender' => $addressSender,
            'addressRecipient' => $addressRecipient,
            'pickup' => $pickup,
            'parcels' => $parcels,
            'services' => $services
        ]);

        return $shipmentData;
    }

    /**
     * Индекс только цифрами: "831 02" -> "83102"
     *
     * @param string $zip
     * @return string
     */
    public static function normalizeZip(string $zip): string
    {
        return (string)preg_replace('/\D/', '', $zip);
    }

    /**
     * Корректен ли словацкий индекс: ровно 5 цифр
     *
     * @param string $zip
     * @return bool
     */
    public static function isValidZip(string $zip): bool
    {
        return (bool)preg_match('/^\d{5}$/', self::normalizeZip($zip));
    }

    /**
     * Предел веса посылки из настроек службы доставки DPD
     *
     * @return float
     */
    public static function maxParcelWeight(): float
    {
        return \Room\Delivery\DpdHandler::maxBagWeight();
    }

    /**
     * Сколько посылок нужно на такой вес
     *
     * @param float $weightKg
     * @return int
     */
    public static function getParcelCount(float $weightKg): int
    {
        return max(1, (int)ceil(max($weightKg, 0.1) / self::maxParcelWeight()));
    }

    /**
     * Раскладывает вес заказа по посылкам
     *
     * DPD не принимает посылку тяжелее лимита, поэтому заказ на 33 кг
     * уходит двумя коробками. Вес делится поровну, остаток - в последнюю,
     * чтобы сумма сошлась с весом заказа
     *
     * @param float $weightKg Вес заказа в килограммах
     * @return array Массив посылок для buildShipmentData()
     */
    public function buildParcels($basket, float $weightKg): array
    {
        $weightKg = max($weightKg, 0.1);

        // Раскладка ровно та же, по которой посчитана цена доставки:
        // куски сеток и штучные товары разложены по мешкам до 30 кг
        $bags = [];

        if ($basket) {
            $packed = \Room\Delivery\Packer::pack(
                \Room\Delivery\Packer::piecesFromBasket($basket),
                self::maxParcelWeight(),
                ['\Room\Delivery\DpdHandler', 'priceForBag']
            );

            if (!empty($packed['bags'])) {
                foreach ($packed['bags'] as $bag) {
                    $bags[] = array_sum($bag);
                }
            }
        }

        // Вес в каталоге не заполнен или корзину разложить не удалось -
        // отправляем одной посылкой, чтобы вызов не сорвался
        if (empty($bags)) {
            $bags = [$weightKg];
        }

        $parcels = [];

        foreach ($bags as $bagWeight) {
            $bagWeight = round(max($bagWeight, 0.1), 2);
            $dimensions = $this->getParcelDimensionsByWeight($bagWeight * 1000);

            $parcels[] = [
                'weight' => (string)$bagWeight,
                'height' => $dimensions['height'],
                'width' => $dimensions['width'],
                'depth' => $dimensions['depth'],
            ];
        }

        return $parcels;
    }

    /**
     * Что именно мешает отправить заказ в DPD
     *
     * Возвращает список проблем человеческим текстом: он уходит в журнал
     * и в комментарий к заказу, чтобы менеджер понимал, что исправлять
     *
     * @param array $shipmentData Данные отправления
     * @return array Список ошибок, пустой массив - всё в порядке
     */
    public function getValidationErrors(array $shipmentData): array
    {
        $errors = [];

        $recipient = $shipmentData['addressRecipient'] ?? [];

        if (empty($recipient['name'])) {
            $errors[] = 'не указано имя получателя';
        }

        if (empty($recipient['street'])) {
            $errors[] = 'не указана улица получателя';
        }

        if (empty($recipient['city'])) {
            $errors[] = 'не указан город получателя';
        }

        $zip = (string)($recipient['zip'] ?? '');

        if ($zip === '') {
            $errors[] = 'не указан индекс получателя';
        } elseif (!self::isValidZip($zip)) {
            $errors[] = 'индекс "' . $zip . '" не похож на словацкий PSC, нужно 5 цифр';
        }

        $sender = $shipmentData['addressSender'] ?? [];

        if (isset($sender['id'])) {
            if (empty($sender['id'])) {
                $errors[] = 'не указан адрес забора отправителя';
            }
        } elseif (empty($sender['name']) || empty($sender['street'])
            || empty($sender['zip']) || empty($sender['city'])) {
            $errors[] = 'не заполнены данные отправителя';
        }

        $parcels = $shipmentData['parcels']['parcel'] ?? [];

        if (empty($parcels)) {
            $errors[] = 'не сформирована ни одна посылка';
        } else {
            // одна посылка приходит объектом, несколько - массивом объектов
            $list = isset($parcels['weight']) ? [$parcels] : $parcels;

            foreach ($list as $index => $parcel) {
                $parcelWeight = (float)($parcel['weight'] ?? 0);

                if ($parcelWeight > self::maxParcelWeight()) {
                    $errors[] = sprintf(
                        'посылка %d весит %s кг при лимите %s кг',
                        $index + 1,
                        $parcelWeight,
                        self::maxParcelWeight()
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * Определение габаритов посылки в зависимости от веса
     *
     * @param float $weightInGrams Вес в граммах
     * @return array Массив с размерами ['height' => int, 'width' => int, 'depth' => int]
     */
    private function getParcelDimensionsByWeight(float $weightInGrams): array
    {
        // Конфигурация размеров посылок по весу (в граммах)
        $dimensionsMap = [
            3000 => ['height' => 10, 'width' => 20, 'depth' => 30],   // до 3кг
            5000 => ['height' => 15, 'width' => 25, 'depth' => 35],   // 3-5 кг
            10000 => ['height' => 30, 'width' => 30, 'depth' => 30], // 5-10 кг
            20000 => ['height' => 50, 'width' => 50, 'depth' => 70], // 10-20 кг
            30000 => ['height' => 50, 'width' => 60, 'depth' => 80], // 20-30 кг
        ];

        // Поиск подходящего диапазона веса
        foreach ($dimensionsMap as $maxWeight => $dimensions) {
            if ($weightInGrams < $maxWeight) {
                return $dimensions;
            }
        }

        // Для веса больше 30кг используем максимальные размеры
        return ['height' => 50, 'width' => 60, 'depth' => 80];
    }

    /**
     * Парсинг адреса на улицу и номер дома
     *
     * @param string $address Полный адрес
     * @return array Массив с 'street' и 'houseNumber'
     */
    private function parseAddress(string $address): array
    {
        $address = trim($address);
        
        // Попытка найти номер дома в конце строки
        if (preg_match('/^(.+?)\s+(\d+[a-zA-Z]?)$/', $address, $matches)) {
            return [
                'street' => trim($matches[1]),
                'houseNumber' => trim($matches[2])
            ];
        }

        // Если номер дома не найден, возвращаем весь адрес как улицу
        return [
            'street' => $address,
            'houseNumber' => ''
        ];
    }

    /**
     * Валидация данных отправления перед отправкой
     *
     * @param array $shipmentData Данные отправления
     * @return bool true если все обязательные поля заполнены
     */
    public function validateShipmentData(array $shipmentData): bool
    {
        return empty($this->getValidationErrors($shipmentData));
    }

}


