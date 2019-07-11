<?php

namespace Api\Security;


class XorEncryption {

    public static function encrypt($data, $key) {

        $stringLength = strlen($data);

        $keyLength = strlen($key);

        for ($i = 0; $i < $stringLength; $i++) {

            $data[$i] = ($data[$i] ^ $key[$i % $keyLength]);

        }

        return $data;

    }

    public static function decrypt($data, $key) {

        return self::encrypt($data, $key);

    }

}