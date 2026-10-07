<?php

/**
 * Run in the PHP container: php /var/www/scripts/tests/gpwebpay-response.php
 * Uses ephemeral RSA keys and Bitrix doubles; no database, HTTP calls or real payments.
 */
namespace Bitrix\Main {
    class Error { public function __construct(public string $message) {} }
    class Request {
        public function __construct(private array $values) {}
        public function getRaw($name) { return $this->values[$name] ?? null; }
        public function get($name) { return $this->getRaw($name); }
    }
}

namespace Bitrix\Main\Localization {
    class Loc {
        public static function loadMessages($file) {}
        public static function getMessage($name) { return $name; }
    }
}

namespace Bitrix\Main\Type { class DateTime extends \DateTime {} }

namespace Bitrix\Sale {
    class Payment {
        public array $fields = ['PS_INVOICE_ID' => '1234567890', 'CURRENCY' => 'EUR'];
        public bool $paid = false;
        public int $saves = 0;
        public function getId() { return 42; }
        public function getOrderId() { return 7; }
        public function getSum() { return 50.0; }
        public function getField($name) { return $this->fields[$name] ?? null; }
        public function setField($name, $value) { $this->fields[$name] = $value; }
        public function save() { $this->saves++; }
        public function isPaid() { return $this->paid; }
    }
    class Order {
        public static function load($id) { return new self(); }
        public function getPropertyCollection() { return $this; }
        public function getUserEmail() { return $this; }
        public function getPayerName() { return $this; }
        public function getValue() { return 'buyer@example.test'; }
    }
}

namespace Bitrix\Sale\PaySystem {
    class ServiceHandler {
        public array $config = [];
        public array $extra = [];
        public $service;
        public function __construct() {
            $this->service = new class { public function getField($name) { return 7; } };
        }
        public function getBusinessValue($payment, $name) { return $this->config[$name] ?? ''; }
        public function setExtraParams($params) { $this->extra = $params; }
        public function showTemplate($payment, $name) { return new ServiceResult(); }
    }
    class ServiceResult {
        public const MONEY_COMING = 'money_coming';
        private array $errors = [];
        private array $psData = [];
        private $operation = null;
        public function addError($error) { $this->errors[] = $error->message; }
        public function isSuccess() { return !$this->errors; }
        public function getErrorMessages() { return $this->errors; }
        public function setOperationType($value) { $this->operation = $value; }
        public function getOperationType() { return $this->operation; }
        public function setPsData($value) { $this->psData = $value; }
        public function getPsData() { return $this->psData; }
        public function setData($value) {}
    }
    class ErrorLog { public static function add($entry) {} }
}

namespace {
    if (PHP_SAPI !== 'cli') {
        http_response_code(403);
        exit('CLI only');
    }

    $siteRoot = dirname(__DIR__, 2);
    $_SERVER['DOCUMENT_ROOT'] = $siteRoot . '/www';
    require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/room/autoload.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/sale_payment/gpweb/handler.php';

    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    if ($key === false || !openssl_pkey_export($key, $privatePem)) {
        throw new \RuntimeException('Could not create test RSA key.');
    }
    $publicPem = openssl_pkey_get_details($key)['key'];
    $publicPath = tempnam(sys_get_temp_dir(), 'gpwebpay-public-');
    $privatePath = tempnam(sys_get_temp_dir(), 'gpwebpay-private-');
    file_put_contents($publicPath, $publicPem);
    file_put_contents($privatePath, $privatePem);

    $handler = new \Sale\Handlers\PaySystem\GpWebHandler();
    $handler->config = [
        'GPWEB_MERCHANT_NUMBER' => '123456789',
        'GPWEB_PUBLIC_KEY_PATH' => $publicPath,
        'GPWEB_PRIVATE_KEY_PATH' => $privatePath,
        'PS_CHANGE_STATUS_PAY' => 'Y',
    ];
    $base = [
        'OPERATION' => 'CREATE_ORDER', 'ORDERNUMBER' => '1234567890',
        'MERORDERNUM' => '42', 'PRCODE' => '0', 'SRCODE' => '0',
    ];

    // Fixtures are explicitly ordered per protocol, independent of the verifier's field list.
    $sign = static function (array $fields, string $merchant = '123456789') use ($key): array {
        $message = implode('|', $fields);
        openssl_sign($message, $digest, $key, OPENSSL_ALGO_SHA1);
        openssl_sign($message . '|' . $merchant, $digest1, $key, OPENSSL_ALGO_SHA1);
        return $fields + ['DIGEST' => base64_encode($digest), 'DIGEST1' => base64_encode($digest1)];
    };
    $passed = 0;
    $check = static function (string $name, array $params, bool $accepted, ?\Bitrix\Sale\Payment $payment = null) use ($handler, &$passed): void {
        $payment = $payment ?? new \Bitrix\Sale\Payment();
        $before = $payment->fields;
        $result = $handler->processRequest($payment, new \Bitrix\Main\Request($params));
        $moneyComing = $result->isSuccess() &&
            $result->getOperationType() === \Bitrix\Sale\PaySystem\ServiceResult::MONEY_COMING;
        if ($moneyComing !== $accepted || $payment->fields !== $before || $payment->saves !== 0 ||
            (!$accepted && ($result->isSuccess() || ($result->getPsData()['PS_STATUS'] ?? '') === 'Y'))) {
            throw new \RuntimeException('FAIL: ' . $name);
        }
        $passed++;
        echo 'PASS: ', $name, PHP_EOL;
    };

    try {
        $valid = $sign($base);
        $check('signed successful payment', $valid, true);
        $check('request parameter order does not affect signature', array_reverse($valid, true), true);
        $check('unsigned success is rejected', $base, false);
        foreach (['DIGEST', 'DIGEST1', 'PRCODE', 'SRCODE', 'OPERATION', 'ORDERNUMBER', 'MERORDERNUM'] as $field) {
            $params = $valid;
            unset($params[$field]);
            $check('missing ' . $field, $params, false);
        }
        foreach (['DIGEST', 'DIGEST1'] as $field) {
            $check('malformed ' . $field, array_replace($valid, [$field => 'not-base64!']), false);
            $check('tampered ' . $field, array_replace($valid, [$field => base64_encode(str_repeat('x', 256))]), false);
        }
        $check('changed order number', array_replace($valid, ['ORDERNUMBER' => '1234567891']), false);
        $check('changed payment ID', array_replace($valid, ['MERORDERNUM' => '43']), false);
        $check('other merchant signature', $sign($base, '987654321'), false);
        $check('signed response for another payment', $sign(array_replace($base, ['MERORDERNUM' => '43'])), false);
        $check('signed response for another attempt', $sign(array_replace($base, ['ORDERNUMBER' => '1234567891'])), false);
        $check('signed error response', $sign(array_replace($base, ['PRCODE' => '14', 'SRCODE' => '1'])), false);
        $check('nonzero secondary code cannot confirm payment', $sign(array_replace($base, ['SRCODE' => '1'])), false);
        $check('array input is rejected without a fatal error', array_replace($valid, ['PRCODE' => ['0']]), false);
        $check('empty status is rejected', array_replace($valid, ['PRCODE' => '']), false);
        $check('nonnumeric status is rejected', array_replace($valid, ['PRCODE' => 'success']), false);
        $check('card verification is not a payment', $sign(array_replace($base, ['OPERATION' => 'CARD_VERIFICATION'])), false);

        $optional = [
            'OPERATION' => 'CREATE_ORDER', 'ORDERNUMBER' => '1234567890', 'MERORDERNUM' => '42',
            'MD' => '', 'PRCODE' => '0', 'SRCODE' => '0', 'RESULTTEXT' => '',
            'ADDINFO' => '<info>paid</info>', 'TOKEN' => 'token', 'EXPIRY' => '2812',
            'ACSRES' => 'F', 'ACCODE' => '123456', 'PANPATTERN' => '411111******1111',
            'DAYTOCAPTURE' => '', 'TOKENREGSTATUS' => '', 'ACRC' => '00',
            'RRN' => '123456789', 'PAR' => 'account', 'TRACEID' => 'trace',
        ];
        $check('optional and empty fields remain in signature', $sign($optional), true);
        $check('changed optional data invalidates signature', array_replace($sign($optional), ['ADDINFO' => '<info>changed</info>']), false);
        unset($optional['MD']);
        $check('absent optional field is omitted', $sign($optional), true);
        $check('leading zeros in payment ID are preserved for signature', $sign(array_replace($base, ['MERORDERNUM' => '00042'])), true);

        $legacy = new \Bitrix\Sale\Payment();
        $legacy->fields['PS_INVOICE_ID'] = '';
        $legacy->fields['PS_STATUS_MESSAGE'] = $base['ORDERNUMBER'];
        $check('attempt stored by old handler', $valid, true, $legacy);
        $legacy->fields['PS_STATUS_MESSAGE'] = '';
        $check('missing saved attempt is rejected', $valid, false, $legacy);

        $handler->config['GPWEB_PUBLIC_KEY_PATH'] = $publicPath . '.missing';
        $check('missing certificate rejects payment', $valid, false);
        $handler->config['GPWEB_PUBLIC_KEY_PATH'] = $publicPath;
        $handler->config['PS_CHANGE_STATUS_PAY'] = 'N';
        $check('automatic payment status disabled', $valid, false);
        $handler->config['PS_CHANGE_STATUS_PAY'] = 'Y';
        $paid = new \Bitrix\Sale\Payment();
        $paid->paid = true;
        $check('already paid payment is not confirmed twice', $valid, false, $paid);

        $payment = new \Bitrix\Sale\Payment();
        $result = $handler->initiatePay($payment);
        parse_str(parse_url($handler->extra['URL'] ?? '', PHP_URL_QUERY) ?? '', $outgoing);
        if (!$result->isSuccess() || ($outgoing['MERORDERNUM'] ?? '') !== '42' ||
            ($outgoing['ORDERNUMBER'] ?? '') !== $payment->fields['PS_INVOICE_ID']) {
            throw new \RuntimeException('FAIL: outgoing payment ID and attempt persistence');
        }
        $passed++;
        echo 'PASS: outgoing payment ID and attempt persistence', PHP_EOL;

        echo $passed, ' checks passed; no real payments or database writes.', PHP_EOL;
    } finally {
        unlink($publicPath);
        unlink($privatePath);
    }
}
