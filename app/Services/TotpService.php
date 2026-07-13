<?php

namespace App\Services;

/**
 * Minimal RFC 6238 TOTP implementation (sha1, 6 digits, 30s periods)
 * so the template has no extra composer dependency.
 */
class TotpService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $length = 24): string
    {
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::ALPHABET[random_int(0, 31)];
        }

        return $secret;
    }

    public static function provisioningUri(string $secret, string $label, string $issuer = 'CLIuno'): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer),
            rawurlencode($label),
            $secret,
            rawurlencode($issuer),
        );
    }

    public static function code(string $secret, ?int $timestamp = null): string
    {
        $counter = intdiv($timestamp ?? time(), 30);
        $binary = pack('N*', 0).pack('N*', $counter);
        $hash = hash_hmac('sha1', $binary, self::base32Decode($secret), true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $value = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }

    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $now = time();
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::code($secret, $now + ($i * 30)), $code)) {
                return true;
            }
        }

        return false;
    }

    private static function base32Decode(string $secret): string
    {
        $bits = 0;
        $value = 0;
        $output = '';
        foreach (str_split(strtoupper(rtrim($secret, '='))) as $char) {
            $index = strpos(self::ALPHABET, $char);
            if ($index === false) {
                continue;
            }
            $value = ($value << 5) | $index;
            $bits += 5;
            if ($bits >= 8) {
                $output .= chr(($value >> ($bits - 8)) & 0xFF);
                $bits -= 8;
            }
        }

        return $output;
    }
}
