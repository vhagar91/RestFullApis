<?php

/**
 * Description of Utils
 */

namespace RestBundle\Helpers;

class Utils
{
    const CONFIGURATION_TRIPLE_ROOM_CHARGE = 10;

    /**
     * @param $needle
     * @param $array
     */
    public static function searchInArray($needle, $array, $field)
    {
        $i = 0;
        foreach ($array as $key => $value) {
            if ($value[$field] == $needle)
                return $i;
            $i++;
        }
        return false;
    }

    public static function generateCode($lon = 6) {
        $code = '';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUWYZ';
        $max = strlen($pattern)-1;
        for($i=0;$i < $lon;$i++) $code .= $pattern{mt_rand(0,$max)};
        return $code;
    }
}

?>
