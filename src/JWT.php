<?php

namespace Joalvm\Utils;

use Firebase\JWT\JWT as BaseJWT;
use Firebase\JWT\Key;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class JWT
{
    public const HS512 = 'HS512';
    public const HS256 = 'HS256';

    public static function decode(string $token): \stdClass
    {
        return BaseJWT::decode(
            $token,
            new Key(Config::get('app.key'), self::HS512),
        );
    }

    public static function decodeHS256(string $token): \stdClass
    {
        return BaseJWT::decode(
            $token,
            new Key(Config::get('app.key'), self::HS256),
        );
    }

    /**
     * Crea un JWT token pudiendo personalizar la fecha de expiración,
     * el valor retornado es un array[string $token, Carbon $expire].
     *
     * @return array<int,\Illuminate\Support\Carbon|string>
     */
    public static function encode(array $payload, int $expireMinutes = 4320): array
    {
        $now = Carbon::now();
        $expire = Carbon::now()->addMinutes($expireMinutes);

        $token = BaseJWT::encode(
            array_merge($payload, [
                'iat' => $now->unix(),
                'exp' => $expire->unix(),
            ]),
            Config::get('app.key'),
            self::HS512
        );

        return [$token, $expire];
    }
}
