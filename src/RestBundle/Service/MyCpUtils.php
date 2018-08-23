<?php


namespace RestBundle\Service;


class MyCpUtils {
	const CONFIG_TRIPLE_ROOM_CHARGE = 10;

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

	public function nights($startdate, $enddate, $format = null)
	{
		$dates = MyCpUtils::datesBetween($startdate, $enddate, $format);
		return count($dates) - 1;
	}
}