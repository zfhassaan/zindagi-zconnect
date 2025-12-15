<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Helpers;

class ValidationHelper
{
    /**
     * Validate CNIC format (Pakistani CNIC).
     */
    public static function validateCnic(string $cnic): bool
    {
        // Remove dashes and spaces
        $cnic = preg_replace('/[-\s]/', '', $cnic);
        
        // Pakistani CNIC format: 13 digits
        return preg_match('/^\d{13}$/', $cnic) === 1;
    }

    /**
     * Validate mobile number (Pakistani format).
     */
    public static function validateMobileNumber(string $mobile): bool
    {
        // Remove spaces, dashes, and plus signs
        $mobile = preg_replace('/[\s\-+]/', '', $mobile);
        
        // Pakistani mobile format: 10 digits starting with 03
        return preg_match('/^03\d{9}$/', $mobile) === 1;
    }

    /**
     * Format CNIC with dashes.
     */
    public static function formatCnic(string $cnic): string
    {
        $cnic = preg_replace('/[-\s]/', '', $cnic);
        
        if (strlen($cnic) === 13) {
            return substr($cnic, 0, 5) . '-' . substr($cnic, 5, 7) . '-' . substr($cnic, 12, 1);
        }
        
        return $cnic;
    }

    /**
     * Format mobile number.
     */
    public static function formatMobileNumber(string $mobile): string
    {
        $mobile = preg_replace('/[\s\-+]/', '', $mobile);
        
        if (strlen($mobile) === 10 && str_starts_with($mobile, '03')) {
            return '+92' . substr($mobile, 1);
        }
        
        return $mobile;
    }

    /**
     * Sanitize sensitive data.
     */
    public static function sanitizeData(array $data, array $sensitiveFields = []): array
    {
        $defaultSensitiveFields = [
            'password',
            'pin',
            'cvv',
            'card_number',
            'account_number',
            'cnic',
            'mobile_number',
        ];

        $sensitiveFields = array_merge($defaultSensitiveFields, $sensitiveFields);

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $data[$key] = self::maskValue($value);
            } elseif (is_array($value)) {
                $data[$key] = self::sanitizeData($value, $sensitiveFields);
            }
        }

        return $data;
    }

    /**
     * Mask a sensitive value.
     */
    protected static function maskValue($value): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        $length = strlen($value);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
    }
}

