<?php

namespace App\Support;

final class IdentityInput
{
    public const TYPES = ['nis', 'nip', 'nik', 'lainnya'];

    public static function normalizePhone(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $digits = (string) preg_replace('/[^0-9+]/', '', $value);
        if (str_starts_with($digits, '+62')) {
            $digits = '0'.substr($digits, 3);
        } elseif (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    public static function identityPattern(string $type): string
    {
        return match ($type) {
            'nis' => '/^[0-9]{4,12}$/',
            'nip' => '/^[0-9]{18}$/',
            'nik' => '/^[0-9]{16}$/',
            'lainnya' => '/^[A-Za-z0-9._-]{4,20}$/',
            default => '/^(?!)$/',
        };
    }

    public static function identityHint(string $type): string
    {
        return match ($type) {
            'nis' => 'NIS berisi 4–12 angka.',
            'nip' => 'NIP harus tepat 18 angka.',
            'nik' => 'NIK harus tepat 16 angka.',
            'lainnya' => 'ID lainnya berisi 4–20 huruf, angka, titik, garis bawah, atau tanda hubung.',
            default => 'Pilih jenis identitas terlebih dahulu.',
        };
    }
}
