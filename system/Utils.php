<?php

namespace Api\Utils;


class Utils {

    const DEFAULT_ENCODING = 'utf-8';

    public static function getStringLength($line) {

        return mb_strlen($line, self::DEFAULT_ENCODING);
    }

    public static function stringStartsWith($haystack, $needle) {

        return (
            mb_substr($haystack, 0, self::getStringLength($needle), self::DEFAULT_ENCODING) === $needle
        );
    }

    public static function getStringSubString($line, $start=0, $length=null) {

        return mb_substr($line, $start, $length, self::DEFAULT_ENCODING);
    }

    public static function stringHasSubstring($line, $subString) {

        return mb_strpos($line, $subString, 0, self::DEFAULT_ENCODING) !== false;

    }

    public static function stringSplit($str, $len = 1) {

        $fragments = [];

        $strLen = self::getStringLength($str);


        for ($i = 0; $i < $strLen; $i++) {

            $fragments[] = self::getStringSubString($str, $i, $len);

        }


        return $fragments;

    }

    public static function isValidEmail($email) {

        return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $email);

    }

}