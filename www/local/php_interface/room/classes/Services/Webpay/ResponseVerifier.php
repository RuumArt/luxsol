<?php

namespace Room\Services\Webpay;

/**
 * GP webpay HTTP API, section 5.2: verify the received bytes in protocol order.
 * The merchant's private key is not needed to verify a gateway response.
 */
class ResponseVerifier
{
    public const SIGNED_FIELDS = [
        'OPERATION', 'ORDERNUMBER', 'MERORDERNUM', 'MD', 'PRCODE', 'SRCODE',
        'RESULTTEXT', 'USERPARAM1', 'ADDINFO', 'TOKEN', 'EXPIRY', 'ACSRES',
        'ACCODE', 'PANPATTERN', 'DAYTOCAPTURE', 'TOKENREGSTATUS', 'ACRC',
        'RRN', 'PAR', 'TRACEID',
    ];

    public static function verify(array $params, string $merchantNumber, string $publicKeyPath): void
    {
        foreach (['OPERATION', 'ORDERNUMBER', 'MERORDERNUM', 'PRCODE', 'SRCODE', 'DIGEST', 'DIGEST1'] as $field) {
            if (!isset($params[$field]) || !is_string($params[$field]) || $params[$field] === '') {
                throw new \RuntimeException('Missing or invalid GP webpay field: ' . $field);
            }
        }

        if ($params['OPERATION'] !== 'CREATE_ORDER') {
            throw new \RuntimeException('Unexpected GP webpay operation.');
        }

        foreach (['ORDERNUMBER' => 15, 'MERORDERNUM' => 30, 'PRCODE' => 10, 'SRCODE' => 10] as $field => $length) {
            if (!preg_match('/^[0-9]{1,' . $length . '}$/D', $params[$field])) {
                throw new \RuntimeException('Invalid GP webpay numeric field: ' . $field);
            }
        }

        if ($merchantNumber === '' || !is_file($publicKeyPath) || !is_readable($publicKeyPath)) {
            throw new \RuntimeException('GP webpay verification configuration is missing.');
        }

        $values = [];
        foreach (self::SIGNED_FIELDS as $field) {
            // Absent optional fields are omitted; present empty fields contribute "||".
            if (array_key_exists($field, $params)) {
                if (!is_string($params[$field])) {
                    throw new \RuntimeException('Invalid GP webpay field: ' . $field);
                }
                $values[] = $params[$field];
            }
        }

        $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));
        if ($publicKey === false) {
            throw new \RuntimeException('Invalid GP webpay public key.');
        }

        $message = implode('|', $values);
        foreach (['DIGEST' => $message, 'DIGEST1' => $message . '|' . $merchantNumber] as $field => $signedMessage) {
            $signature = strlen($params[$field]) <= 2000 ? base64_decode($params[$field], true) : false;
            if ($signature === false || $signature === '' ||
                openssl_verify($signedMessage, $signature, $publicKey, OPENSSL_ALGO_SHA1) !== 1) {
                throw new \RuntimeException('Invalid GP webpay signature: ' . $field);
            }
        }
    }
}
