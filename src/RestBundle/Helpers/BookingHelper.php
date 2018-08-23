<?php

/**
 * Description of Utils
 */

namespace RestBundle\Helpers;

use RestBundle\Helpers\BookingModality;
use RestBundle\Helpers\Season;

class BookingHelper
{

    public static function getReservations($conn, $idReservationArray){
        $query = "SELECT o.own_commission_percent as commission, owres.own_res_total_in_site as total, owres.own_res_reservation_from_date as fromDate,
                  owres.own_res_reservation_to_date as toDate, owres.own_res_night_price as nightPrice, gres.service_fee, gres.gen_res_id
                  FROM ownershipreservation owres
                  JOIN generalreservation gres on gres.gen_res_id = owres.own_res_gen_res_id
                  JOIN ownership o on o.own_id = gres.gen_res_own_id
                  WHERE owres.own_res_id IN (:idsReservation)
                  ORDER BY owres.own_res_gen_res_id ASC";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'idsReservation', $idReservationArray );
        $stmt->execute();
        $po = $stmt->fetch();

        return $stmt->fetchAll();

    }

    public static function getBookingPrepayments($conn, $idReservationArray,$idCurrency){
        $reservations = BookingHelper::getReservations($conn, $idReservationArray);
        $totalToPay = 0;
        $totalPayAtService = 0;
        $generalReservationsArray = array();
        $currentGenResId = 0;

        foreach ($reservations as $reservation) {
            $commission = $reservation["commission"];
            $total = $reservation["total"];
            $nightPrice = $reservation["nightPrice"];
            $fromDate = $reservation["fromDate"];
            $toDate = $reservation["toDate"];
            $payAtAccommodation = 0;

            if($nightPrice > 0){
                $fromDateTimestamp = Timer::getTimestamp($fromDate);
                $toDateTimestamp = Timer::getTimestamp($toDate);
                $nights = Timer::nights($fromDateTimestamp, $toDateTimestamp);
                $payAtAccommodation += $nightPrice * $nights;
            }
            else
                $payAtAccommodation += $total;


            $totalToPay += $payAtAccommodation * $commission / 100;
            $totalPayAtService += $payAtAccommodation * (1 - $commission / 100);

            if($currentGenResId != $reservation["gen_res_id"]){
                $currentGenResId = $reservation["gen_res_id"];
                $generalReservationsArray[] = $currentGenResId;
            }
        }

        $serviceFee = BookingHelper::getCurrentServiceFee($conn);
        $totalToPay += $serviceFee["fixedFee"];

        $currency = BookingHelper::getCurrency($conn, $idCurrency);
        $currencyExchangeTax = $currency["curr_cuc_change"];
        $totalToPay = $totalToPay * $currencyExchangeTax;

        //TODO: Calcular la tarifa turista y sumarla al totalToPay tambien

        return array(
            "reservationsCost" => $totalToPay,
            "payAtService" =>  $totalPayAtService,
            "touristTax" => 0,
            "currencyCode" => $currency["curr_code"],
            "generalReservationIds" => $generalReservationsArray
        );
    }


    public static function getServiceFee ($conn, $idServiceFee){
        $query = "SELECT s.*
                  FROM servicefee s
                  WHERE s.id = :id";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'id', $idServiceFee );
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function getCurrentServiceFee ($conn){
        $query = "SELECT s.*
                  FROM servicefee s
                  WHERE s.current = 1";
        $stmt  = $conn->prepare( $query );
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function getCurrency ($conn, $idCurrency){
        $query = "SELECT s.*
                  FROM currency s
                  WHERE s.curr_id = :id";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'id', $idCurrency );
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function getReservationsObj ($conn, $idsReservationsArray){
        $query = "SELECT s.*
                  FROM ownershipreservation s
                  WHERE s.own_res_ud IN (:ids)";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'ids', $idsReservationsArray );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function SkrillExtraDescriptions($generalReservationsIds){

        $finalDetails = array();
        $finalDetailsString = '';
        $num = 2;
        $maxReached = false;

        foreach ($generalReservationsIds as $generalReservationId) {

            if ($num > self::MAX_SKRILL_NUM_DETAILS) {
                $maxReached = true;
                break;
            }

            $detailString ="CAS.".$generalReservationId;

            if (strlen($finalDetailsString . ', ' . $detailString) > self::MAX_SKRILL_DETAIL_STRING_LENGTH) {
                $detail = BookingHelper::getSkrillDetail($num, $finalDetailsString);
                $finalDetails = array_merge($finalDetails, $detail);
                $finalDetailsString = $detailString;
                $num++;
            } else {
                $detailString = (empty($finalDetailsString) ? '' : ', ') . $detailString;
                $finalDetailsString .= $detailString;
            }
        }

        if (!$maxReached && !empty($finalDetailsString)) {
            $detail = BookingHelper::getSkrillDetail($num, $finalDetailsString);
            $finalDetails = array_merge($finalDetails, $detail);
        }

        return $finalDetails;
    }

    public static function getSkrillDetail($num, $text)
    {
        $detail = array(
            "detail{$num}_description" => 'Reservation IDs: ',
            "detail{$num}_text" => $text
        );

        return $detail;
    }


}

?>
