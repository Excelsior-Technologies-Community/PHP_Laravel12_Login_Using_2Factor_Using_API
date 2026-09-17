<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_2fa_enabled',
        'google_2fa_secret',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_2fa_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google_2fa_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    /**
     * Generate 8 unique emergency recovery codes (e.g. ABCD-1234)
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        $plainCodes = [];

        for ($i = 0; $i < 8; $i++) {
            $part1 = strtoupper(bin2hex(random_bytes(2)));
            $part2 = strtoupper(bin2hex(random_bytes(2)));
            $code = "{$part1}-{$part2}";

            $codes[] = [
                'code' => $code,
                'used_at' => null,
            ];
            $plainCodes[] = $code;
        }

        $this->two_factor_recovery_codes = $codes;
        $this->save();

        return $plainCodes;
    }

    /**
     * Verify and mark a recovery code as used
     */
    public function verifyAndConsumeRecoveryCode(string $code): bool
    {
        $cleanedCode = strtoupper(trim(str_replace(' ', '', $code)));
        $codes = $this->two_factor_recovery_codes ?? [];

        foreach ($codes as $index => $item) {
            $existingCode = strtoupper(trim($item['code'] ?? ''));
            // Check formatted or unhyphenated match
            if (($existingCode === $cleanedCode || str_replace('-', '', $existingCode) === str_replace('-', '', $cleanedCode)) && empty($item['used_at'])) {
                $codes[$index]['used_at'] = now()->toDateTimeString();
                $this->two_factor_recovery_codes = $codes;
                $this->save();
                return true;
            }
        }

        return false;
    }

    /**
     * Get list of recovery codes with status
     */
    public function getRecoveryCodesList(): array
    {
        return $this->two_factor_recovery_codes ?? [];
    }
}
