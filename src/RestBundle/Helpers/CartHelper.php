<?php

/**
 * Description of Utils
 */

namespace RestBundle\Helpers;

use RestBundle\Helpers\BookingModality;
use RestBundle\Helpers\Season;

class CartHelper
{
    /**
     * @param $idUser
     * @param $dateFrom
     * @param $dateTo
     * @param $idRoom
     * @return bool
     */
    public static function existsCartItems($conn, $idUser, $dateFrom, $dateTo, $idRoom){
        $query = "SELECT c.cart_id
                  FROM cart c
                  JOIN room r on c.cart_room = r.room_id
                  JOIN user u on c.cart_user = u.user_id
                  WHERE c.cart_user = :user_id
                  AND r.room_id >= :room
                  AND c.cart_date_from >= :dateFrom
                  AND c.cart_date_to <= :dateTo";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'user_id', $idUser );
        $stmt->bindValue( 'room', $idRoom );
        $stmt->bindValue( 'dateFrom', $dateFrom);
        $stmt->bindValue( 'dateTo', $dateTo);
        $stmt->execute();
        $po = $stmt->fetch();

        return isset( $po['cart_id'] );

    }

    /**
     * @param $idUser
     * @return mixed
     */
    public static function getCartItems($conn, $idUser ) {
        $query = "SELECT c.*
                  FROM cart c
                  WHERE c.cart_user = :user_id";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'user_id', $idUser );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return mixed
     */
    public static function hasCompleteReservation($conn, $idOwnership){
        $query = "SELECT abm.accommodation
                  FROM accommodation_booking_modality abm
                  JOIN booking_modality bm on abm.bookingModality = id
                  WHERE bm.name= :completeReservation
                   AND abm.accommodation = :accommodation";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'completeReservation', BookingModality::COMPLETE_RESERVATION_BOOKING );
        $stmt->bindValue( 'accommodation', $idOwnership );
        $stmt->execute();

        $po = $stmt->fetch();

        return isset( $po['accommodation'] );
    }

    /**
     * @return mixed
     */
    public static function getOwnershipData($conn, $idOwnership){
        $query = "SELECT o.*
                  FROM ownership o
                  WHERE o.own_id = :ownership";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'ownership', $idOwnership );
        $stmt->execute();

        $po = $stmt->fetch();

        return $po;
    }

    /**
     * @return mixed
     */
    public static function getOwnershipIdFromRoom($conn, $idRoom){
        $query = "SELECT r.room_ownership
                  FROM room r

                  WHERE r.room_id = :room";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'room', $idRoom );
        $stmt->execute();

        $po = $stmt->fetch();

        return $po["room_ownership"];
    }

    /**
     * @return mixed
     */
    public static function getCurrentServiceFeeId($conn){
        $query = "SELECT s.id
                  FROM servicefee s
                  WHERE s.current = 1";
        $stmt  = $conn->prepare( $query );
        $stmt->execute();

        $po = $stmt->fetch();

        return $po["id"];
    }

    /**
     * @param $idUser
     * @return mixed
     */
    public static function getOwnShipReserByUser($conn, $idUser){
        $day = date("Y-m-d");

        $query = "SELECT owres.*
                  FROM ownershipreservation owres
                  JOIN generalreservation gres on owres.own_res_gen_res_id = gres.gen_res_id
                  WHERE gres.gen_res_user_id = :user_id
                  AND owres.own_res_reservation_from_date >= :dateToday
                  AND owres.own_res_status <> :status";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'user_id', $idUser );
        $stmt->bindValue( 'dateToday', $day );
        $stmt->bindValue( 'status', GeneralReservationStatus::STATUS_CANCELLED );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getTripleRoomCharged($conn, $item){
        $isRoomTriple = CartHelper::isRoomTriple($conn, $item["cart_room"]);

        return $isRoomTriple &&
        ($item["cart_count_adults"] + $item["cart_count_children"] >= 3) && $item["complete_reservation_mode"] == "0";
    }

    public static function getRoomById ($conn, $idRoom){
        $query = "SELECT r.*
                  FROM room r
                  WHERE r.room_id = :room";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'room', $idRoom );
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function getBookingModalityByAccommodation ($conn, $idAccommodation){
        $query = "SELECT abm.price, bm.name
                  FROM accommodation_booking_modality abm
                  JOIN booking_modality bm on bm.id = abm.bookingModality
                  WHERE abm.accommodation = :idAccommodation";
        $stmt  = $conn->prepare( $query );
        $stmt->bindValue( 'idAccommodation', $idAccommodation );
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function isRoomTriple($conn, $idRoom)
    {
        $room = CartHelper::getRoomById($conn, $idRoom);
        $bookingModalityData = CartHelper::getBookingModalityByAccommodation($conn, $room["room_ownership"]);

        $completeReservationPrice = -1;
        if($bookingModalityData != null and $bookingModalityData["name"] == BookingModality::COMPLETE_RESERVATION_BOOKING)
            $completeReservationPrice = $bookingModalityData["price"];

        return ($room["room_type"] == "Habitación Triple" || $room["room_type"] == "Habitación doble" || $room["room_type"] == "Habitación doble (Dos camas)") && $completeReservationPrice == -1;
    }

    public static function getSeasons($conn, $checkin_date, $checkeout_date, $id_destination = null)
    {
        $query_string = "SELECT s.* FROM season s
                         WHERE ((s.season_startdate <= :checkin AND s.season_enddate >= :checkout)
                            OR (s.season_startdate >= :checkin AND s.season_enddate <= :checkout)
                            OR (s.season_startdate <= :checkin AND s.season_enddate <= :checkout)
                            OR (s.season_startdate >= :checkin AND s.season_enddate >= :checkout))";

        if(isset($id_destination))
            $query_string .= " AND (s.season_destination = :destination_id OR s.season_destination IS NULL)";
        else
            $query_string .= " AND s.season_destination IS NULL ";

        $query_string .= " ORDER BY s.season_startdate ASC ";

        $stmt  = $conn->prepare( $query_string );
        $stmt->bindValue( 'checkin', $checkin_date );
        $stmt->bindValue( 'checkout', $checkeout_date );

        if(isset($id_destination))
            $stmt->bindValue( 'destination_id', $id_destination );

        $stmt->execute();

        return $stmt->fetchAll();

    }

    public static function getPriceBySeasonType($conn, $idRoom, $seasonType)
    {
        $room = CartHelper::getRoomById($conn, $idRoom);
        $bookingModality = CartHelper::getBookingModalityByAccommodation($conn, $room["room_ownership"]);


        if($bookingModality != null && $bookingModality["name"] == BookingModality::COMPLETE_RESERVATION_BOOKING)
        {
            return $bookingModality["price"];
        }
        else{
            switch($seasonType)
            {
                case Season::SEASON_TYPE_HIGH: return $room["room_price_up_to"];
                case Season::SEASON_TYPE_SPECIAL: return ($room["room_price_special"] != null && $room["room_price_special"] > 0) ? $room["room_price_special"]: $room["room_price_up_to"];
                default: return $room["room_price_down_to"];
            }
        }
    }

    public static function getOwnershipReservations($conn, $generalReservationId)
    {
        $query_string = "SELECT owres.* FROM ownershipreservation owres
                         WHERE owres.own_res_gen_res_id = :genResId";

        $stmt  = $conn->prepare( $query_string );
        $stmt->bindValue( 'genResId', $generalReservationId );

        $stmt->execute();

        return $stmt->fetchAll();

    }

    public static function updateDates($conn, $generalReservationId) {
        $ownReservations = CartHelper::getOwnershipReservations($conn, $generalReservationId);

        if(count($ownReservations) > 0) {
            $min_date = null;
            $max_date = null;
            $min_date_timestamp = null;
            $max_date_timestamp = null;
            $nights = 0;

            foreach ($ownReservations as $item) {

                $nights += $item["own_res_nights"];

                if($min_date == null)
                {
                    $min_date = $item["own_res_reservation_from_date"];
                    $min_date_timestamp =  Timer::getTimestamp($min_date);
                }
                else{
                    $current_min_date_timestamp = Timer::getTimestamp($item["own_res_reservation_from_date"]);
                    if($current_min_date_timestamp < $min_date_timestamp)
                    {
                        $min_date = $item["own_res_reservation_from_date"];
                        $min_date_timestamp =  Timer::getTimestamp($min_date);
                    }
                }


                if($max_date == null)
                {
                    $max_date = $item["own_res_reservation_to_date"];
                    $max_date_timestamp =  Timer::getTimestamp($max_date);
                }
                else
                {
                    $current_max_date_timestamp = Timer::getTimestamp($item["own_res_reservation_to_date"]);
                    if($current_max_date_timestamp > $max_date_timestamp)
                    {
                        $max_date = $item["own_res_reservation_to_date"];
                        $max_date_timestamp =  Timer::getTimestamp($max_date);
                    }
                }

            }

            return array(
                "gen_res_from_date" => $min_date,
                "gen_res_to_date" => $max_date,
                "gen_res_nights" => $nights
            );
        }
    }
}

?>
