<?php

/**
 * Description of Utils
 */

namespace RestBundle\Helpers;

class Date
{
    /**
     * @param $date_string
     * @param string $date_separator
     * @param int $month_position
     * @return \DateTime
     */
    public static function createFromString($date_string, $date_separator = '-', $month_position = 0)
    {
        try {
            $date_array = explode($date_separator, $date_string);
            $date = new \DateTime($date_array[0] . '-' . $date_array[1] . '-' . $date_array[2]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $date;
    }

    /**
     * @param $date_string
     * @param $format_string
     * @return null|string
     */
    public static function createForQuery($date_string, $format_string)
    {
        $date = \DateTime::createFromFormat($format_string, $date_string);

        return ($date != null) ? $date->format("Y-m-d") : null;
    }

}

?>
