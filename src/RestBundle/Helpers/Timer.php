<?php


namespace RestBundle\Helpers;
use RestBundle\Helpers\Season;


class Timer {

    public static function nights($startdate, $enddate, $format = null)
    {
        $dates = Time::datesBetween($startdate, $enddate, $format);
        return count($dates) - 1;
    }

    public static function datesBetween($startdate, $enddate, $format = null) {

        (is_int($startdate)) ? 1 : $startdate = strtotime($startdate);
        (is_int($enddate)) ? 1 : $enddate = strtotime($enddate);

        if ($startdate > $enddate) {
            return false; //The end date is before start date
        }

        while ($startdate <= $enddate) {
            $arr[] = ($format) ? date($format, $startdate) : $startdate;
            $startdate = strtotime("+1 day", $startdate);
        }
        return $arr;
    }

    public static function getTimestamp($date, $format = "Y-m-d"){
        return  \DateTime::createFromFormat($format, $date)->getTimestamp();
    }

    public static function seasonTypeByDate($seasons, $date_timestamp) {
        $season_type = Season::SEASON_TYPE_LOW;
        foreach ($seasons as $season) {
            $season_startdate = Timer::getTimestamp($season["season_startdate"], "Y-m-d H:i:s");
            $season_enddate = Timer::getTimestamp($season["season_enddate"], "Y-m-d H:i:s");
            if ($season_startdate <= $date_timestamp && $season_enddate >= $date_timestamp) {
                if ($season_type == Season::SEASON_TYPE_LOW ||
                    ($season_type == Season::SEASON_TYPE_HIGH && $season["season_type"] == Season::SEASON_TYPE_SPECIAL))
                    $season_type = $season["season_type"];
            }
        }
        return $season_type;
    }

    public static function seasonByDate($seasons, $date_timestamp) {
        $season_type = Timer::seasonTypeByDate($seasons, $date_timestamp);
        switch ($season_type) {
            case Season::SEASON_TYPE_HIGH: return "top";
            case Season::SEASON_TYPE_SPECIAL: return "special";
            default: return "down";
        }
    }
} 