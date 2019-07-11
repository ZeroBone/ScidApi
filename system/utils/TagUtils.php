<?php

namespace Api\Utils;


class TagUtils {

    const TAG_CHARS = '0289PYLQGRJCUV';

    public static function idToTag($high, $low) {

        $id = ($low << 8) + $high;

        $tag = [];

        while ($id > 0) {

            $i = $id % 14;

            $id = floor($id / 14);

            $tag[] = self::TAG_CHARS[$i];

        }

        return implode('', array_reverse($tag));

    }

}