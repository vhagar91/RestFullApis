<?php
/*
 * This file is part of the API-REST CBS v1.0.0.0 alfa.
 *
 * (c) Development team HDS <correo>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace RestBundle\Model;

use Doctrine\DBAL\Portability\Connection;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use RestBundle\Controller\McprestController;
use RestBundle\Helpers\BackendModuleName;
use RestBundle\Helpers\BookingHelper;
use RestBundle\Helpers\BookingModality;
use RestBundle\Helpers\Date;
use RestBundle\Helpers\GeneralReservationStatus;
use RestBundle\Helpers\Operations;
use RestBundle\Helpers\OwnershipReservationStatus;
use RestBundle\Helpers\Timer;
use RestBundle\Helpers\Utils;
use RestBundle\Exception\InvalidFormException;
use RestBundle\Service\MyCpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use UserBundle\Entity\User;
use RestBundle\Helpers\CartHelper;

class McpApp extends Base {
    const OWNERSHIP_STATUS_ACTIVE = 1;
    const OWNERSHIP_STATUS_RESERVED = 5;
    const COMPLETE_RESERVATION_BOOKING = "Propiedad Completa";
    const COMPLETE_RESERVATION_BOOKING_TRANS = "Propiedad completa";
    const UD_SYNC_DELETED = 2;

    const SEASON_TYPE_LOW = 0;
    const SEASON_TYPE_HIGH = 1;
    const SEASON_TYPE_SPECIAL = 2;

    const MAX_SKRILL_NUM_DETAILS = 5;
    const MAX_SKRILL_DETAIL_STRING_LENGTH = 240;

    private $container = null;

    /**
     * @param $request
     *
     * @return mixed
     */
    public function login( $request ) {
        $user     = $request->request->get( 'user' );
        $password = $request->request->get( 'password' );

        $encrypt_password = self::encryptPassword( $password );

        $query = "SELECT
u.user_id, u.user_name, u.user_user_name, u.user_last_name, u.user_email, u.user_id, u.user_address, u.user_city,
l.lang_code,u.user_phone,
ut.user_tourist_postal_code postal_code,
c.co_name AS co_code,
p.pho_name
FROM user u
INNER JOIN usertourist ut ON (u.user_id = ut.user_tourist_user)
INNER JOIN lang l ON (ut.user_tourist_language = l.lang_id)
LEFT OUTER JOIN country c ON (u.user_country = c.co_id)
LEFT OUTER JOIN photo p ON (u.user_photo = p.pho_id)
WHERE  (u.user_name=:user_name OR u.user_email=:user_name) AND u.user_password=:user_password; ";
        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'user_name', $user );
        $stmt->bindValue( 'user_password', $encrypt_password );
        $stmt->execute();
        $po = $stmt->fetch();

        /*$pathToCont = "xxxxxxx.txt";
        $file = fopen($pathToCont, "a");
        fwrite($file, '----------------' . PHP_EOL);
        fwrite($file, ' -->  user_name: ' . $user . ' --> user_password: ' . $encrypt_password . PHP_EOL);
        fwrite($file, '--------------' . PHP_EOL);
        fclose($file);*/

        if ( isset( $po['user_id'] ) ) {
            $date   = $request->request->get( 'start' );
            $userId = $po['user_id'];

            $token       = $this->updateSessionUser( $userId, $user, $password, $date );
            $po['token'] = $token;

            $currency       = $this->getCurrency( $userId );
            $po['currency'] = $currency;

            /*$own = $this->getReservationsByDate($date, $po['user_id']);
            $po['reservations'] = $own;*/

            return $this->view->create( $po, Response::HTTP_OK );
        } else {
            return $this->view->create( array( 'success' => false, 'code' => Response::HTTP_UNAUTHORIZED, 'message' => 'Bad credentials' ), Response::HTTP_UNAUTHORIZED );
        }
    }

    /**
     * @throws \Exception
     * @internal param $userStaffManagerId
     */
    public function getDestinations() {
        try {
            $queryDestinations = "SELECT destination.des_id,destination.des_name
FROM destination
ORDER BY destination.des_id";

            $stmtDestOwnership = $this->conn->prepare( $queryDestinations );
            $stmtDestOwnership->execute();
            $destinations = $stmtDestOwnership->fetchAll();

            return $this->view->create( $destinations, Response::HTTP_OK );
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }

    public function getAccomodationsTop( $request ) {
        $ownStatus = self::OWNERSHIP_STATUS_ACTIVE;
        $user_id   = ( $request->query->get( 'user_id' ) != '' ) ? $request->query->get( 'user_id' ) : null;;
        $locale     = ( $request->query->get( 'language' ) != '' ) ? $request->query->get( 'language' ) : "EN";
        $session_id = null;
        $start      = ( $request->query->get( 'start' ) != '' ) ? $request->query->get( 'start' ) : "0";
        $limit      = 10;
        $currency   = ( $request->query->get( 'currency' ) != '' ) ? $request->query->get( 'currency' ) : "EUR";


        try {
            $owns_id      = "0";
            $reservations = $this->ownNotAvailable();
            foreach ( $reservations as $res ) {
                $owns_id .= "," . $res["own_id"];
            }

            /*
            prov.prov_name as prov_name,
            o.own_inmediate_booking as OwnInmediateBooking,
            (SELECT min(d.odl_brief_description) FROM lang l INNER JOIN ownershipdescriptionlang d ON (l.lang_id = d.odl_id_lang) WHERE d.odl_id_ownership = o.own_id AND l.lang_code = '$locale') as description,
            (SELECT min(a.second_icon_or_class_name) FROM accommodation_award aw INNER JOIN award a ON (aw.award = a.id) WHERE aw.accommodation = o.own_id ORDER BY aw.year DESC, a.ranking_value DESC) as award,
            (SELECT count(fav.favorite_id) FROM favorite fav WHERE " . (($user_id != null) ? " fav.favorite_user = $user_id " : " fav.favorite_user is null") . " AND " . (($session_id != null) ? " fav.favorite_session_id = '$session_id' " : " fav.favorite_session_id is null") . " AND fav.favorite_ownership=o.own_id) as is_in_favorites

            (SELECT count(fav.favorite_id) FROM favorite fav WHERE " . (($user_id != null) ? " fav.favorite_user = $user_id " : " fav.favorite_user is null") . " AND fav.favorite_ownership=o.own_id) as is_in_favorites
            **/
            $query = "SELECT o.own_id as own_id,
                         o.own_name as own_name,
                         o.own_mcp_code as mcp_code,
                         o.own_comments_total as comments_total,
                         o.own_inmediate_booking_2 as inmediate_booking,
                         o.own_maximun_number_guests as maximun_number_guests,
                         o.own_rating as stars,
                         des.des_name as destination,
                         pho.pho_name as photo,
                         data.reservedRooms as count_reservations,
                         (IF(abMod.price IS NOT NULL AND bMod.name LIKE '%completa%', abMod.price,o.own_minimum_price) * cur.curr_cuc_change) as min_price,
                         " . ( ( $user_id != null ) ? "(SELECT count(fav.favorite_id) FROM favorite fav WHERE fav.favorite_user = $user_id AND fav.favorite_ownership=o.own_id)" : "0" ) . " as in_favorite
                         FROM ownership o
                         INNER JOIN province prov ON (o.own_address_province = prov.prov_id)
                         INNER JOIN destination des ON (o.own_destination = des.des_id)
                         INNER JOIN ownershipdata data ON (o.own_id = data.accommodation)
                         LEFT OUTER JOIN ownershipphoto op ON (data.principalPhoto = op.own_pho_id)
                         LEFT OUTER JOIN photo pho ON (op.own_pho_pho_id = pho.pho_id)
                         LEFT OUTER JOIN accommodation_booking_modality abMod ON (o.own_id = abMod.accommodation)
                         LEFT OUTER JOIN booking_modality bMod ON (abMod.bookingModality = bMod.id),
                         currency cur
                         WHERE  cur.curr_code = '$currency' AND o.own_inmediate_booking_2=1 AND o.own_id NOT IN ($owns_id)
                         AND o.own_status = $ownStatus
                         ORDER BY o.own_ranking DESC, o.own_comments_total DESC, count_reservations DESC LIMIT $limit OFFSET $start";

            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            $result = $stmt->fetchAll();

            return $this->view->create( $result, Response::HTTP_OK );
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }

    public function getAccomodations( $request ) {
        $ownStatus  = self::OWNERSHIP_STATUS_ACTIVE;
        $user_id    = ( $request->query->get( 'user_id' ) != '' ) ? $request->query->get( 'user_id' ) : null;
        $locale     = ( $request->query->get( 'language' ) != '' ) ? $request->query->get( 'language' ) : "EN";
        $session_id = null;
        $start      = ( $request->query->get( 'start' ) != '' ) ? $request->query->get( 'start' ) : "0";
        $limit      = 10;
        $currency   = ( $request->query->get( 'currency' ) != '' ) ? $request->query->get( 'currency' ) : "EUR";

        $destination = ( $request->query->get( 'destination_id' ) != '' ) ? $request->query->get( 'destination_id' ) : null;
        $guests      = ( $request->query->get( 'guests' ) != '' ) ? $request->query->get( 'guests' ) : null;
        $rooms       = ( $request->query->get( 'rooms' ) != '' ) ? $request->query->get( 'rooms' ) : null;
        $from        = ( $request->query->get( 'from' ) != '' ) ? $request->query->get( 'from' ) : null;
        $to          = ( $request->query->get( 'to' ) != '' ) ? $request->query->get( 'to' ) : null;
        $favorite     = ( $request->query->get( 'favorite' ) != '' && $request->query->get( 'favorite' ) == '1') ? true : false;


        try {
            $owns_id = "0";

            if ( $from != null && $to != null ) {
                $reservations = $this->ownNotAvailable( $from, $to );
                foreach ( $reservations as $res ) {
                    $owns_id .= "," . $res["own_id"];
                }
            }

            $customInnerJoins = "";
            $where            = " AND ";
            $having           = " HAVING ";
            $groupBy          = " GROUP BY ";

            if ( $destination != null ) {
                $where .= " des.des_id = :des_id ";
            }
            if ( $guests != null ) {
                $s = ( $where != " AND " ) ? " AND " : "";
                $where .= " $s o.own_maximun_number_guests >= :guests ";
            }
            if ( $rooms != null ) {
                $customInnerJoins .= " INNER JOIN room ON ( o.own_id = room.room_ownership) ";
                $groupBy .= " o.own_id ";
                $having .= " COUNT(room.room_id) > :rooms ";
            }

            if ( $where == " AND " ) {
                $where = "";
            }
            if ( $groupBy == " GROUP BY " ) {
                $groupBy = "";
            }
            if ( $having == " HAVING " ) {
                $having = "";
            }

            $favoriteb = ( ( $user_id != null ) ? "(SELECT count(fav.favorite_id) FROM favorite fav WHERE fav.favorite_user = $user_id AND fav.favorite_ownership=o.own_id)" : "0" );
            $favotiteSelect =  $favoriteb. " as in_favorite ";
            $favotiteWhere = ( ( $user_id != null && $favorite) ? $favoriteb." = 1 AND" : "" );

            $query = "SELECT o.own_id as own_id,
                         o.own_name as own_name,
                         o.own_mcp_code as mcp_code,
                         o.own_comments_total as comments_total,
                         o.own_inmediate_booking_2 as inmediate_booking,
                         o.own_maximun_number_guests as maximun_number_guests,
                         o.own_rating as stars,
                         des.des_name as destination,
                         pho.pho_name as photo,
                         data.reservedRooms as count_reservations,
                         (IF(abMod.price IS NOT NULL AND bMod.name LIKE '%completa%', abMod.price,o.own_minimum_price) * cur.curr_cuc_change) as min_price,
                         $favotiteSelect
                         FROM ownership o
                         $customInnerJoins
                         INNER JOIN province prov ON (o.own_address_province = prov.prov_id)
                         INNER JOIN destination des ON (o.own_destination = des.des_id)
                         INNER JOIN ownershipdata data ON (o.own_id = data.accommodation)
                         LEFT OUTER JOIN ownershipphoto op ON (data.principalPhoto = op.own_pho_id)
                         LEFT OUTER JOIN photo pho ON (op.own_pho_pho_id = pho.pho_id)
                         LEFT OUTER JOIN accommodation_booking_modality abMod ON (o.own_id = abMod.accommodation)
                         LEFT OUTER JOIN booking_modality bMod ON (abMod.bookingModality = bMod.id),
                         currency cur
                         WHERE $favotiteWhere cur.curr_code = '$currency' AND o.own_status = $ownStatus $where AND o.own_id NOT IN ($owns_id)
                         $groupBy
                         $having
                         ORDER BY o.own_ranking DESC, o.own_comments_total DESC, count_reservations DESC
                         LIMIT $limit OFFSET $start";

            $stmt = $this->conn->prepare( $query );

            if ( $destination != null ) {
                $stmt->bindValue( 'des_id', $destination );
            }
            if ( $guests != null ) {
                $stmt->bindValue( 'guests', $guests );
            }
            if ( $rooms != null ) {
                $stmt->bindValue( 'rooms', $rooms );
            }

            $stmt->execute();
            $result = $stmt->fetchAll();

            return $this->view->create( $result, Response::HTTP_OK );
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }

    public function getAccomodation( $request ) {
        $user_id = ( $request->query->get( 'user_id' ) != '' ) ? $request->query->get( 'user_id' ) : null;;
        $locale     = ( $request->query->get( 'language' ) != '' ) ? $request->query->get( 'language' ) : "EN";
        $session_id = null;
        $currency   = ( $request->query->get( 'currency' ) != '' ) ? $request->query->get( 'currency' ) : "EUR";
        $own_id     = $request->query->get( 'own_id' );

        try {
            /*
            prov.prov_name as prov_name,
            o.own_inmediate_booking as OwnInmediateBooking,

            (SELECT min(a.second_icon_or_class_name) FROM accommodation_award aw INNER JOIN award a ON (aw.award = a.id) WHERE aw.accommodation = o.own_id ORDER BY aw.year DESC, a.ranking_value DESC) as award,
            (SELECT count(fav.favorite_id) FROM favorite fav WHERE " . (($user_id != null) ? " fav.favorite_user = $user_id " : " fav.favorite_user is null") . " AND " . (($session_id != null) ? " fav.favorite_session_id = '$session_id' " : " fav.favorite_session_id is null") . " AND fav.favorite_ownership=o.own_id) as is_in_favorites

            (SELECT count(fav.favorite_id) FROM favorite fav WHERE " . (($user_id != null) ? " fav.favorite_user = $user_id " : " fav.favorite_user is null") . " AND fav.favorite_ownership=o.own_id) as is_in_favorites
            **/
            $query = "SELECT o.own_id as own_id,
                         o.own_name as own_name,
                         o.own_mcp_code as mcp_code,
                         o.own_inmediate_booking_2 as inmediate_booking,
                         o.own_maximun_number_guests as maximun_number_guests,
                         o.own_rating as stars,
                         o.own_geolocate_x as lat,
                         o.own_geolocate_y as lon,
                         o.own_homeowner_1 as owner_name,
                         o.own_category as category,
                         o.own_type as type,
                         o.own_langs as languages,
                         o.own_facilities_breakfast as breakfast,
                         (o.own_facilities_breakfast_price * cur.curr_cuc_change) as breakfast_price,
                         o.own_facilities_dinner as dinner,
                         (o.own_facilities_dinner_price_from * cur.curr_cuc_change) as dinner_price_from,
                         (o.own_facilities_dinner_price_to * cur.curr_cuc_change) as dinner_price_to,
                         o.own_facilities_parking as parking,
                         des.des_name as destination,
                         phoown.pho_name as photo_own,
                         pho.pho_name as photo,
                         cur.curr_cuc_change as cuc_change,
                         (IF(abMod.price IS NOT NULL AND bMod.name LIKE '%completa%', abMod.price,o.own_minimum_price) * cur.curr_cuc_change) as min_price,
                         (SELECT min(d.odl_brief_description) FROM lang l INNER JOIN ownershipdescriptionlang d ON (l.lang_id = d.odl_id_lang) WHERE d.odl_id_ownership = o.own_id AND l.lang_code = '$locale') as description,
                         " . ( ( $user_id != null ) ? "(SELECT count(fav.favorite_id) FROM favorite fav WHERE fav.favorite_user = $user_id AND fav.favorite_ownership=o.own_id)" : "0" ) . " as in_favorite
                         FROM ownership o
                         INNER JOIN province prov ON (o.own_address_province = prov.prov_id)
                         INNER JOIN destination des ON (o.own_destination = des.des_id)
                         INNER JOIN ownershipdata data ON (o.own_id = data.accommodation)
                         LEFT OUTER JOIN photo phoown ON (o.own_owner_photo = phoown.pho_id)
                         LEFT OUTER JOIN ownershipphoto op ON (data.principalPhoto = op.own_pho_id)
                         LEFT OUTER JOIN photo pho ON (op.own_pho_pho_id = pho.pho_id)
                         LEFT OUTER JOIN accommodation_booking_modality abMod ON (o.own_id = abMod.accommodation)
                         LEFT OUTER JOIN booking_modality bMod ON (abMod.bookingModality = bMod.id),
                         currency cur
                         WHERE  cur.curr_code = '$currency' AND o.own_id = $own_id LIMIT 1";

            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            $result = $stmt->fetch();

            $result['rooms']  = $this->getRoomsByAccomodation( $result['own_id'], $result['cuc_change'] );
            $result['photos'] = $this->getPhotosByAccomodation( $result['own_id'] );
            $result['comments']=$this->getCommentsByAccomodation($result['own_id']);
            return $this->view->create( $result, Response::HTTP_OK );
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }

    public function favorite( $request ) {
        $userId      = $request->request->get( 'user_id' );
        $token       = $request->request->get( 'token' );
        $ownershipId = $request->request->get( 'ownership_id' );
        $action      = $request->request->get( 'action' );
        $now         = ( new \DateTime() )->format( 'Y-m-d' );

        if ( $this->isValidToken( $userId, $token ) ) {
            if ( $action == '1' ) {
                $query = "SELECT COUNT(favorite_id) AS f FROM favorite WHERE favorite.favorite_user = :user_id AND favorite.favorite_ownership = :ownership_id LIMIT 1";
                $stmt  = $this->conn->prepare( $query );
                $stmt->bindValue( 'user_id', $userId );
                $stmt->bindValue( 'ownership_id', $ownershipId );
                $stmt->execute();
                $r = $stmt->fetch();
                if ( ( $r['f'] * 1 ) > 0 ) {//ya esta
                    return $this->view->create( array( 'success' => 1, 'msg' => 'Esta casa ya esta en los favoritos de este usuario' ), Response::HTTP_OK );
                } else {
                    $query = "INSERT INTO favorite (favorite_user,favorite_ownership,favorite_creation_date)
VALUE (:favorite_user,:favorite_ownership,:favorite_creation_date)";
                    $stmt  = $this->conn->prepare( $query );
                    $stmt->bindValue( 'favorite_user', $userId );
                    $stmt->bindValue( 'favorite_ownership', $ownershipId );
                    $stmt->bindValue( 'favorite_creation_date', $now );
                    $stmt->execute();

                    return $this->view->create( array( 'success' => 1, 'msg' => 'Adicionado correctamente' ), Response::HTTP_OK );
                }
            } else {
                $query = "DELETE FROM favorite WHERE favorite.favorite_user = :favorite_user AND favorite.favorite_ownership = :favorite_ownership";
                $stmt  = $this->conn->prepare( $query );
                $stmt->bindValue( 'favorite_user', $userId );
                $stmt->bindValue( 'favorite_ownership', $ownershipId );
                $stmt->execute();

                return $this->view->create( array( 'success' => 1, 'msg' => 'Eliminado correctamente' ), Response::HTTP_OK );
            }
        } else {
            return $this->view->create( 'Token no valido', Response::HTTP_UNAUTHORIZED );
        }
    }

    public function getAvailableRooms( $request ) {
        $currency = ( $request->query->get( 'currency' ) != '' ) ? $request->query->get( 'currency' ) : "EUR";
        $ownId    = ( $request->query->get( 'own_id' ) != '' ) ? $request->query->get( 'own_id' ) : null;
        $from     = ( $request->query->get( 'from' ) != '' ) ? $request->query->get( 'from' ) : null;
        $to       = ( $request->query->get( 'to' ) != '' ) ? $request->query->get( 'to' ) : null;

        $fromDate = \DateTime::createFromFormat( 'Y-m-d', $from )->setTime( 0, 0, 0 );
        $toDate   = \DateTime::createFromFormat( 'Y-m-d', $to )->setTime( 0, 0, 0 );

        $fromTimestamp = $fromDate->getTimestamp();
        $toTimestamp   = $toDate->getTimestamp();

        $mycpUtils = $this->getContainer()->get( 'mycp.utils' );

        $nights = $mycpUtils->nights( $fromTimestamp, $toTimestamp );
        $toDate->modify( '-1 days' );

        $ownership                  = $this->getOwnership( $ownId, $currency );
        $ownership['room_recharge'] = MyCpUtils::CONFIG_TRIPLE_ROOM_CHARGE * $ownership['cuc_change'];
        $bookingModality            = $ownership['modality'];
        $ownership['service_fee']   = $this->getServiceFeeCurrent();
        $ownership['nights']        = $nights;

        $completeReservationPrice = 0;
        if ( $bookingModality != null && $bookingModality == McpApp::COMPLETE_RESERVATION_BOOKING ) {
            $completeReservationPrice       = $ownership['modality_price'];
            $ownership['modality_complete'] = 1;
        } else {
            $ownership['modality_complete'] = 0;
        }

        $roomsAux = $this->getRoomsByAccomodation( $ownId, $ownership['cuc_change'] );
        $rooms    = ( $completeReservationPrice > 0 ) ? array( $roomsAux[0] ) : $roomsAux;

        $array_dates  = $mycpUtils->datesBetween( $fromTimestamp, $toTimestamp );
        $desId        = $ownership['des_id'];
        $seasons      = $this->getDestinationSeasons( $fromDate, $toDate, $desId );
        $seasons_type = array();
        for ( $a = 0; $a < count( $array_dates ) - 1; $a ++ ) {
            $season_type    = $this->seasonTypeByDate( $seasons, $array_dates[ $a ] );
            $seasons_type[] = $season_type;
        }

        $roomsAux = array();
        foreach ( $rooms as $room ) {
            $unavailable_room = $this->getRoomUnavailabilitydetails( $room['room_id'], $fromDate, $toDate );
            if ( $unavailable_room != false ) {
                $room['unavailability'] = 1;
            } else {
                $reservations = $this->getRoomReservations( $room['room_id'], $fromDate, $toDate );
                if ( $reservations != false ) {
                    $room['unavailability'] = 1;
                } else {
                    $room['unavailability'] = 0;
                }
            }

            if ( $room['unavailability'] == 0 ) {
                //hacer la sumatoria por cada una de las temporadas.
                //$total_price_room  = 0;
                $prices_dates_temp = array();
                foreach ( $seasons_type as $season_type ) {
                    $roomPrice = ( $completeReservationPrice > 0 ) ? $completeReservationPrice : $this->getPriceRoomBySeasonType( $season_type, $ownership, $room );
                    //$total_price_room += $roomPrice;
                    $prices_dates_temp[] = $roomPrice * 1;
                }
                //$room['total_price'] = $total_price_room;
                $room['prices'] = $prices_dates_temp;
                $roomsAux[]     = $room;
            }
        }

        $ownership['rooms'] = $roomsAux;

        return $this->view->create( $ownership, Response::HTTP_OK );
    }

    //TO YANETMORALESR: en este metodo vas a meter los cascos......jajajajaja
    public function addToCart( $request ) {
        $userId      = $request->request->get( 'user_id' );
        $sessionId      = $request->request->get( 'session_id' );
        $token       = $request->request->get( 'token' );
        $check_dispo = $request->request->get( 'check_dispo' );
        $from_date   = $request->request->get( 'from_date' );
        $start_timestamp = \DateTime::createFromFormat("Y-m-d", $from_date)->getTimestamp();
        $to_date     = $request->request->get( 'to_date' );
        $end_timestamp = \DateTime::createFromFormat("Y-m-d", $to_date)->getTimestamp();
        $ids_rooms   = $request->request->get( 'ids_rooms' );
        $adults      = $request->request->get( 'adults' );
        $kids        = $request->request->get( 'kids' );
        $hasCompleteReservation      = $request->request->get( 'hasCompleteReservation' );
        $kidsAge_1   = $request->request->get( 'kidsAge_1' );
        $kidsAge_2   = $request->request->get( 'kidsAge_2' );
        $kidsAge_3   = $request->request->get( 'kidsAge_3' );
        $currency    = $this->getCurrency( $userId );
        $returnedArray = array();

        if ( $this->isValidToken( $userId, $token )) {
            //TO YANETMORALESR: Aqui metes lo tuyo
            $array_ids_rooms = explode('&', $ids_rooms);
            //array_shift($array_ids_rooms);
            $array_count_guests = explode('&', $adults);
            //array_shift($array_count_guests);
            $array_count_kids = explode('&', $kids);
            //array_shift($array_count_kids);

            $array_count_kidsAge_1 = ($kidsAge_1 != "") ? explode('&', $kidsAge_1): array();
            //array_shift($array_count_kidsAge_1);

            $array_count_kidsAge_2 = ($kidsAge_1 != "") ? explode('&', $kidsAge_2): array();
            //array_shift($array_count_kidsAge_2);

            $array_count_kidsAge_3 = ($kidsAge_1 != "") ? explode('&', $kidsAge_3): array();
            //array_shift($array_count_kidsAge_3);

            $cartItems = CartHelper::getCartItems($this->conn, $userId);

            if(isset($check_dispo) && $check_dispo!='' && ($check_dispo==1 || $check_dispo==2 ) ){
                $ownerShip= CartHelper::getOwnShipReserByUser($this->conn, $userId);
            }

            $showError = false;
            $showErrorOwnExist = false;
            $showErrorItem='';
            $arrayIdCart=array();
            for ($a = 0; $a < count($array_ids_rooms); $a++) {

                $insert = 1;
                foreach ($cartItems as $item) {
                    $cartDateFrom = $item['cart_date_from'];
                    // $array_cartDateFrom = explode(' ', $cartDateFrom);
                    //$cartDateFrom = $array_cartDateFrom[0];
                    //die(\DateTime::createFromFormat("Y-m-d", $cartDateFrom)->getTimestamp());
                    $cartDateFrom_timestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $cartDateFrom)->getTimestamp();
                    $cartDateTo = $item['cart_date_to'];
                    $cartDateTo_timestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $cartDateTo)->getTimestamp();
                    $cartRoom = $item['cart_room'];

                    if (isset($array_count_guests[$a]) && isset($array_count_kids[$a]) &&
                        (($cartDateFrom_timestamp <= $start_timestamp && $cartDateTo_timestamp >= $start_timestamp) ||
                            ($cartDateFrom_timestamp <= $end_timestamp && $cartDateTo_timestamp >= $end_timestamp) || $cartDateFrom_timestamp==$start_timestamp && $cartDateTo_timestamp==$end_timestamp) &&
                        $cartRoom == $array_ids_rooms[$a]) {
                        $insert = 0;
                        $showError = true;
                        $showErrorItem=$item;
                    }
                }
                if(isset($check_dispo) && $check_dispo!='' && ($check_dispo==1 || $check_dispo==2 ) ){
                    if(count($ownerShip)){
                        foreach ($ownerShip as $item){
                            $ownDateFrom = $item["own_res_reservation_from_date"];
                            $ownDateFrom_timestamp = \DateTime::createFromFormat("Y-m-d", $ownDateFrom)->getTimestamp();
                            $ownDateTo = $item["own_res_reservation_to_date"];
                            $ownDateTo_timestamp = \DateTime::createFromFormat("Y-m-d", $ownDateTo)->getTimestamp();

                            if ((($ownDateFrom_timestamp <= $start_timestamp && $ownDateTo_timestamp >= $start_timestamp) ||
                                    ($ownDateFrom_timestamp <= $end_timestamp && $ownDateTo_timestamp >= $end_timestamp) || $ownDateFrom_timestamp==$start_timestamp && $ownDateTo_timestamp==$end_timestamp) &&
                                $item["own_res_selected_room_id"] == $array_ids_rooms[$a] ) {
                                $insert = 0;
                                $showError = true;
                                $showErrorOwnExist = true;
                            }
                        }
                    }
                }
                if ($insert == 1) {
                    $room = $array_ids_rooms[$a];
                    if($room != null) {
                        $serviceFee = CartHelper::getCurrentServiceFeeId($this->conn);
                        $cart = array();

                        $existsCartItem = CartHelper::existsCartItems($this->conn, $userId, $from_date, $to_date, $room);

                        if ($start_timestamp < $end_timestamp && !$existsCartItem) {
                            $cart["cart_date_from"] = $from_date;
                            $cart["cart_date_to"] = $to_date;
                            $cart["complete_reservation_mode"] = $hasCompleteReservation;
                            $cart["cart_room"] = $room;
                            $cart["service_fee"] = $serviceFee;
                            $cart["cart_count_adults"] = (isset($array_count_guests[$a])) ? $array_count_guests[$a]: 1;
                            $cart["cart_count_children"] = (isset($array_count_kids[$a])) ? $array_count_kids[$a]: 0;

                            if (isset($check_dispo) && $check_dispo != '' && $check_dispo == 1 && $userId == null) {
                                $cart["check_available"] = 1;
                            }
                            if (isset($check_dispo) && $check_dispo != '' && $check_dispo == 2 && $userId == null) {
                                $cart["inmediate_booking"] = 1;
                            }

                            $kidsAge = array();

                            if (isset($array_count_kidsAge_1[$a]) && $array_count_kidsAge_1[$a] != -1)
                                $kidsAge["FirstKidAge"] = $array_count_kidsAge_1[$a];

                            if (isset($array_count_kidsAge_2[$a]) && $array_count_kidsAge_2[$a] != -1)
                                $kidsAge["SecondKidAge"] = $array_count_kidsAge_2[$a];

                            if (isset($array_count_kidsAge_3[$a]) && $array_count_kidsAge_3[$a] != -1)
                                $kidsAge["ThirdKidAge"] = $array_count_kidsAge_3[$a];

                            if (count($kidsAge))
                                $cart["childrenAges"] = serialize($kidsAge);

                            $cart["cart_created_date"]=date('Y-m-d');
                            if ($userId != null) {
                                $cart["cart_user"] = $userId;
                            } else if ($sessionId != null)
                                $cart["cart_session_id"] = $sessionId;

                            $this->conn->insert('cart', $cart);
                            $arrayIdCart[] = $this->conn->lastInsertId();

                            if ($userId != null || $sessionId != null) {
                                // TROPA: Aqui se debe guardar un job, pero como? Lo obviamos?
                            }
                        }
                    }
                    else{
                        $insert = 0;
                        $showError = true;
                    }
                }
            }

            if($showError)
            {
                //TROPA: Aqui ya esa existe una de las habitaciones en el carrito. Devuelve el error que mejor creas
                return $this->view->create(array('success'=>1, 'msg'=>'Es posible que la habitacion seleccionada ya este en el carrito del usuario'), Response::HTTP_OK);
            }
            else {
                if ($userId != null) {
                    if (isset($check_dispo) && $check_dispo != '' && $check_dispo == "1" && !$showErrorOwnExist) {
                        //Es que el usuario mando a consultar la disponibilidad
                        $this->checkDispo($arrayIdCart, $userId, $sessionId, false);
                    } elseif (isset($check_dispo) && $check_dispo != '' && $check_dispo == "2" && !$showErrorOwnExist) {
                        //Es que el usuario mando a hacer una reserva
                        $returnedArray = $this->checkDispo($arrayIdCart, $userId, $sessionId, true);
                    } else {
                        return $this->view->create('Error', Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }

            $change = $currency['curr_cuc_change'];
            for($i = 0; $i < count($returnedArray); $i++){
                $rid = $returnedArray[$i]['own_res_selected_room_id'];
                $returnedArray[$i]['own_res_selected_room_id'] = $this->getRoomById( $rid , $change);

                $returnedArray[$i]['own_res_total_in_site'] = $returnedArray[$i]['own_res_total_in_site'] * $currency['curr_cuc_change'];

                //como no utulizo estos indices los hago nulos para optimizar el envio
                $returnedArray[$i]['own_res_room_price_down'] = null;
                $returnedArray[$i]['own_res_room_price_up'] = null;
                $returnedArray[$i]['own_res_room_type'] = null;

            }
            return $this->view->create( array( 'success' => 1, 'msg' => 'Adicionado correctamente', "reservations" => $returnedArray ), Response::HTTP_OK );
        } else {
            return $this->view->create( 'Error', Response::HTTP_UNAUTHORIZED );
        }
    }

    public function payReservations( $request ) {
        $reservations_list     = $request->request->get( 'reservations_list' );
        $token = $request->request->get( 'token' );
        $userName = $request->request->get( 'user_name' );
        $userLastName = $request->request->get( 'user_last_name' );
        $userEmail = $request->request->get( 'user_email' );
        $userId = $request->request->get( 'user_id' );
        $currencyId = $request->request->get( 'currency_id' );
        $currency = ( $request->request->get( 'currency' ) != '' ) ? $request->request->get( 'currency' ) : "EUR";
        $amount = $request->request->get( 'amount' ) * 1;
        $returnUrl = 'https://www.mycasaparticular.com/en/payment/skrill-return/';//$request->request->get("return_url");
        $returnUrlText = 'Back to MyCasaParticular';//$request->request->get("return_url_text");
        $cancelUrl = 'https://www.mycasaparticular.com/en/payment/skrill-cancel';//$request->request->get("cancel_url");
        $statusUrl = 'https://www.mycasaparticular.com/en/skrill/status';//$request->request->get("status_url");
        $languageCode = $request->request->get("language_code");
        $confirmationNote = 'MyCasaParticular whishes you a very nice stay in Cuba';//$request->request->get("confirmation_note");
        $skrillSubmitButtonText = $request->request->get("skrill_submit_button");
        $userAddress = $request->request->get("user_address");
        $userZipCode = $request->request->get("user_zip_code");
        $userCity = $request->request->get("user_city");
        $userCountryCode = $request->request->get("user_country_code");

        if (!$this->isValidToken( $userId, $token )) {
            return $this->view->create( 'Error', Response::HTTP_UNAUTHORIZED );
        }

        $user = $this->getUserbyId($userId);
        if($user != null){
            $userEmail = $user['user_email'];
            $userName = $user['user_user_name'];
            $userLastName = $user['user_last_name'];
            $userAddress = $user['user_address'];
            $userZipCode = $user['user_tourist_postal_code'];
            $userCity = $user['user_city'];
            $userCountryCode = $user['co_code'];
        }


        if($currency == 'CUC'){
            $currency = 'USD';
            $c = $this->getChange($currency);
            $change = $c['cuc_change'] * 1;
            $amount *=  $change;
        }

        $reservations = explode(",", $reservations_list);

        $prices = BookingHelper::getBookingPrepayments($this->conn, $reservations_list, $currencyId);

        $booking = array(
            "booking_cancel_protection" => 0,
            "booking_currency" => $currencyId,
            "booking_user_id" => $userId,
            "booking_user_dates" => $userName.", ".$userEmail,
            "booking_prepay" => $prices["reservationsCost"],
            "payAtService" => $prices["payAtService"],
            "tax_for_service" => $prices["touristTax"]
        );

        $this->conn->insert('booking', $booking);
        $bookingId = $this->conn->lastInsertId();

        foreach($reservations as $reservationId){
            $reservationArray = array("own_res_reservation_booking" => $bookingId);
            $this->conn->update('ownershipreservation', $reservationArray, array('own_res_id' => $reservationId));
        }

        $recipient = "MyCasaParticular.com";
        //$amount = $prices["reservationsCost"];
        //$currencyCode = $prices["currencyCode"];
        $paymentMethods = "ACC,DID,SFT";
        $detail1_description = "Booking ID:";

        $generalReservationsId = $prices["generalReservationIds"];
        $extraDescriptions = BookingHelper::SkrillExtraDescriptions($generalReservationsId);
        $extra_description = "";

        foreach($extraDescriptions as $extra){
            foreach($extra  as $key => $value)
            {
                $extra_description = $extra_description . (($extra_description != "") ? "&": "").$key."=".$value;
            }
        }

        $detail1_description .= $bookingId;
        $returnUrl .= $bookingId;
        $url = "https://www.moneybookers.com/app/payment.pl?pay_to_email=accounting@mycasaparticular.com&recipient_description=$recipient&transaction_id=$bookingId&return_url=$returnUrl&return_url_text=$returnUrlText&return_url_target=1&cancel_url=$cancelUrl&cancel_url_target=1&status_url=$statusUrl&status_url2=booking@mycasaparticular.com&dynamic_descriptor=Descriptor&language=$languageCode&confirmation_note=$confirmationNote&pay_from_email=$userEmail&logo_url=https://www.mycasaparticular.com/bundles/frontend/img/mycp.png&first_name=$userName&last_name=$userLastName&address=$userAddress&postal_code=$userZipCode&city=$userCity&country=$userCountryCode&amount=$amount&currency=$currency&detail1_description=$detail1_description&detail1_text=$bookingId&payment_methods=$paymentMethods&hide_login=1";

        if (true ) {
            return $this->view->create( array( 'success' => 1, 'msg' => 'Adicionado correctamente', "url" => $url ), Response::HTTP_OK );
        } else {
            return $this->view->create( 'Error', Response::HTTP_INTERNAL_SERVER_ERROR );
        }
    }

    /*
     * TROPA: ES ESTE TROPA
     */
    public function touristReservationList( $request )
    {
        $userId = $request->query->get('user_id');
        $date = $request->query->get('date');
        $token = $request->query->get( 'token' );
        $currency = ( $request->query->get( 'currency' ) != '' ) ? $request->query->get( 'currency' ) : "EUR";
        $count = $request->query->get( 'count' );

        if (!true/*$this->isValidToken( $userId, $token )*/) {
            return $this->view->create( 'Error', Response::HTTP_UNAUTHORIZED );
        }

        $status = OwnershipReservationStatus::STATUS_AVAILABLE;

        $select = "owr.own_res_gen_res_id,
owr.own_res_id,
ow.own_id,
owr.own_res_status,
owr.own_res_total_in_site,
owr.own_res_count_adults,
owr.own_res_count_childrens,
owr.own_res_reservation_from_date,
owr.own_res_reservation_to_date,
owr.own_res_selected_room_id,
owr.own_res_room_type";

        if(isset($count)){
            $select = "COUNT(owr.own_res_id) AS l";
        }

        $query = "SELECT $select
        FROM ownershipreservation owr
        JOIN generalreservation gr on gr.gen_res_id = owr.own_res_gen_res_id
        JOIN ownership ow ON ow.own_id = gr.gen_res_own_id
        WHERE gr.gen_res_user_id = :user_id AND owr.own_res_status = $status ";

        if($date != null && $date != ""){
            //$date   = Date::createFromString( $date );
            $query .= " AND owr.own_res_reservation_from_date >= :date";
        }

        $query .= " ORDER BY gr.gen_res_id ASC, owr.own_res_reservation_from_date ASC";

        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'user_id', $userId);

        if ($date != null && $date != "") {
            $date   = Date::createFromString( $date );
            $stmt->bindValue( 'date', date_format( $date, 'Y-m-d' ) );
        }

        if(isset($count)){
            $stmt->execute();
            $r = $stmt->fetch();
            return $this->view->create( array( 'success' => 1, 'msg' => $r['l']), Response::HTTP_OK );
        }

        $stmt->execute();
        $result = $stmt->fetchAll();

        $ownerships = array();
        $restFinal = array();
        $servicefee   = $this->getServiceFeeCurrent();
        foreach($result as $reservationDetail){
            $reservationDetail['own_res_selected_room_id'] = array('room_id'=>$reservationDetail['own_res_selected_room_id'], 'type'=>$reservationDetail['own_res_room_type']);
            $reservationDetail['own_res_room_type'] = null;

            $genResId = $reservationDetail['own_res_gen_res_id'];
            $own = $reservationDetail['own_id'];
            $key = $genResId.'-'.$own;

            if(!isset($ownerships[$key])){
                $fromDate = \DateTime::createFromFormat( 'Y-m-d', $reservationDetail['own_res_reservation_from_date'] )->setTime( 0, 0, 0 );
                $toDate   = \DateTime::createFromFormat( 'Y-m-d', $reservationDetail['own_res_reservation_to_date'] )->setTime( 0, 0, 0 );
                $fromTimestamp = $fromDate->getTimestamp();
                $toTimestamp   = $toDate->getTimestamp();
                $mycpUtils = $this->getContainer()->get( 'mycp.utils' );
                $nights = $mycpUtils->nights( $fromTimestamp, $toTimestamp );

                $ownership = $this->getOwnership( $own, $currency );
                $ownership['nights'] = $nights;
                $ownership['service_fee'] = $servicefee;
                $ownerships[$key] = $ownership;
            }
            else{
                $ownership = $ownerships[$key];
            }

            $reservationDetail['own_id'] = null;
            $reservationDetail['gen_res_nights'] = null;

            $this->incertInGeneralReservation($genResId, $restFinal, $reservationDetail, $ownership);
        }

        return $restFinal;
    }
    public function touristReservationListBooked( $request )
    {
        $userId = $request->query->get('user_id');
        $date = $request->query->get('date');
        $status = $request->query->get('status');
        $token = $request->query->get( 'token' );
        $currency = ( $request->query->get( 'currency' ) != '' ) ? $request->query->get( 'currency' ) : "EUR";
        $count = $request->query->get( 'count' );

        if (!true/*$this->isValidToken( $userId, $token )*/) {
            return $this->view->create( 'Error', Response::HTTP_UNAUTHORIZED );
        }
        
        

        $select = "owr.own_res_gen_res_id,
owr.own_res_id,
ow.own_id,
owr.own_res_status,
owr.own_res_total_in_site,
owr.own_res_count_adults,
owr.own_res_count_childrens,
owr.own_res_reservation_from_date,
owr.own_res_reservation_to_date,
owr.own_res_selected_room_id,
owr.own_res_room_type";

        if(isset($count)){
            $select = "COUNT(owr.own_res_id) AS l";
        }

        $query = "SELECT $select
        FROM ownershipreservation owr
        JOIN generalreservation gr on gr.gen_res_id = owr.own_res_gen_res_id
        JOIN ownership ow ON ow.own_id = gr.gen_res_own_id
        WHERE gr.gen_res_user_id = :user_id AND owr.own_res_status = $status ";

        if($date != null && $date != ""){
            //$date   = Date::createFromString( $date );
            $query .= " AND owr.own_res_reservation_from_date >= :date";
        }

        $query .= " ORDER BY gr.gen_res_id ASC, owr.own_res_reservation_from_date ASC";

        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'user_id', $userId);

        if ($date != null && $date != "") {
            $date   = Date::createFromString( $date );
            $stmt->bindValue( 'date', date_format( $date, 'Y-m-d' ) );
        }

        if(isset($count)){
            $stmt->execute();
            $r = $stmt->fetch();
            return $this->view->create( array( 'success' => 1, 'msg' => $r['l']), Response::HTTP_OK );
        }

        $stmt->execute();
        $result = $stmt->fetchAll();

        $ownerships = array();
        $restFinal = array();
        $servicefee   = $this->getServiceFeeCurrent();
        foreach($result as $reservationDetail){
            $reservationDetail['own_res_selected_room_id'] = array('room_id'=>$reservationDetail['own_res_selected_room_id'], 'type'=>$reservationDetail['own_res_room_type']);
            $reservationDetail['own_res_room_type'] = null;

            $genResId = $reservationDetail['own_res_gen_res_id'];
            $own = $reservationDetail['own_id'];
            $key = $genResId.'-'.$own;

            if(!isset($ownerships[$key])){
                $fromDate = \DateTime::createFromFormat( 'Y-m-d', $reservationDetail['own_res_reservation_from_date'] )->setTime( 0, 0, 0 );
                $toDate   = \DateTime::createFromFormat( 'Y-m-d', $reservationDetail['own_res_reservation_to_date'] )->setTime( 0, 0, 0 );
                $fromTimestamp = $fromDate->getTimestamp();
                $toTimestamp   = $toDate->getTimestamp();

                $mycpUtils = $this->getContainer()->get( 'mycp.utils' );
                $nights = $mycpUtils->nights( $fromTimestamp, $toTimestamp );

                $ownership = $this->getOwnership( $own, $currency );
                $ownership['nights'] = $nights;
                $ownership['service_fee'] = $servicefee;
                $ownerships[$key] = $ownership;
            }
            else{
                $ownership = $ownerships[$key];
            }

            $reservationDetail['own_id'] = null;
            $reservationDetail['gen_res_nights'] = null;

            $this->incertInGeneralReservation($genResId, $restFinal, $reservationDetail, $ownership);
        }

        return $restFinal;
    }
    private function incertInGeneralReservation($genResId, &$generalReservations, $reservationDetail, $accommodation){
        for($i = 0; $i < count($generalReservations); $i++){
            if($generalReservations[$i]['gen_res_id'] == $genResId){
                $generalReservations[$i]['details'][] = $reservationDetail;
                return;
            }
        }
        $generalReservations[] = array('gen_res_id'=>$genResId, 'accommodation'=>$accommodation,'details'=>array($reservationDetail));
    }

    public function registerUser( $request ) {
        $name      = $request->request->get( 'name' );
        $last_name      = $request->request->get( 'last_name' );
        $password      = $request->request->get( 'password' );
        $email = $request->request->get( 'email' );
        $country_id = $request->request->get( 'country_id' );
        $currency_id = $request->request->get( 'currency_id' );
        $language_id = $request->request->get( 'language_id' );

        $userArray = array(
            "user_name" => $email,
            "user_email" => $email,
            "user_role" => "ROLE_CLIENT_TOURIST",
            "user_password" => self::encryptPassword( $password ),
            "user_user_name" => $name,
            "user_last_name" => $last_name,
            "user_country" => $country_id,
            "user_enabled" => 1,
            "locked" => 0
        );

        $this->conn->insert('user', $userArray);
        $userId  = $this->conn->lastInsertId();

        $userturisArray = array(
            "user_tourist_language" => 20,
            "user_tourist_user" => $userId,
            "user_tourist_currency" => 1
        );
        $this->conn->insert('usertourist', $userturisArray);

        $userArray["user_id"] = $userId;

        if (true ) {
            return $this->view->create( array( 'success' => 1, 'msg' => 'Adicionado correctamente', "user" => $userArray ), Response::HTTP_OK );
        } else {
            return $this->view->create( 'Error', Response::HTTP_INTERNAL_SERVER_ERROR );
        }
    }

    /**
     * @throws \Exception
     * @internal param $userStaffManagerId
     */
    public function getCounties() {
        try {
            $query = "SELECT co_id, co_name FROM country";

            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            $results = $stmt->fetchAll();

            return $this->view->create( $results, Response::HTTP_OK );
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }

    /**
     * @throws \Exception
     * @internal param $userStaffManagerId
     */
    public function getCurrencies() {
        try {
            $query = "SELECT currency.curr_id,currency.curr_name,currency.curr_code,currency.curr_cuc_change,currency.curr_symbol,currency.curr_site_price_in
  FROM currency";

            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            $results = $stmt->fetchAll();

            return $this->view->create( $results, Response::HTTP_OK );
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }

    public function changeCurrency( $request ) {
        $userId      = $request->request->get( 'user_id' );
        $token       = $request->request->get( 'token' );
        $currencyId = $request->request->get( 'currency_id' );

        if ( $this->isValidToken( $userId, $token ) ) {
            $data["user_tourist_currency"] = $currencyId;
            $this->conn->update('usertourist', $data, array('user_tourist_user' => $userId));
            return $this->view->create( array( 'success' => 1, 'msg' => 'Cambiado correctamente' ), Response::HTTP_OK );
        } else {
            return $this->view->create( 'Token no valido', Response::HTTP_UNAUTHORIZED );
        }
    }

    public function changePass( $request ) {
        $userId      = $request->request->get( 'user_id' );
        $token       = $request->request->get( 'token' );
        $password = $request->request->get( 'password' );
        $password = self::encryptPassword( $password );

        if ( $this->isValidToken( $userId, $token ) ) {
            $data["user_password"] = $password;
            $this->conn->update('user', $data, array('user_id' => $userId));
            return $this->view->create( array( 'success' => 1, 'msg' => 'Cambiada correctamente' ), Response::HTTP_OK );
        } else {
            return $this->view->create( 'Token no valido', Response::HTTP_UNAUTHORIZED );
        }
    }

    /**
     * @throws \Exception
     * @internal param $userStaffManagerId
     */
    public function getLanguages() {
        try {
            $query = "SELECT l.lang_id, l.lang_name, l.lang_code FROM lang l WHERE l.lang_active = 1";

            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            $results = $stmt->fetchAll();

            return $this->view->create( $results, Response::HTTP_OK );
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }

    public function changeLanguage( $request ) {
        $userId      = $request->request->get( 'user_id' );
        $token       = $request->request->get( 'token' );
        $languageId = $request->request->get( 'language_id' );

        if ( $this->isValidToken( $userId, $token ) ) {
            $data["user_language"] = $languageId;
            $this->conn->update(' user', $data, array('user_id' => $userId));
            return $this->view->create( array( 'success' => 1, 'msg' => 'Cambiado correctamente' ), Response::HTTP_OK );
        } else {
            return $this->view->create( 'Token no valido', Response::HTTP_UNAUTHORIZED );
        }
    }


    /********* Auxiliar functions **************/

    public function checkDispo($cartItems, $userId, $sessionId,$inmediatily_booking){

        $reservations = array();
        $own_ids = array();
        $min_date = null;
        $max_date = null;
        $generalReservations = array();
        $returnedReservations =array();

        if (count($cartItems) > 0) {
            $res_array = array();
            $own_visited = array();
            foreach ($cartItems as $itemId) {

                $item = $this->getCartItem($itemId);

                if ($min_date == null)
                    $min_date = $item["cart_date_from"];
                else if ($item["cart_date_from"] < $min_date)
                    $min_date = $item["cart_date_from"];

                if ($max_date == null)
                    $max_date = $item["cart_date_to"];
                else if ($item["cart_date_to"] > $max_date)
                    $max_date = $item["cart_date_to"];

                $res_own_id = CartHelper::getOwnershipIdFromRoom($this->conn, $item["cart_room"]);

                $array_group_by_own_id = array();
                $flag = 1;
                foreach ($own_visited as $own) {
                    if ($own == $res_own_id) {
                        $flag = 0;
                    }
                }
                if ($flag == 1)
                    foreach ($cartItems as $itemId) {
                        $item = $this->getCartItem($itemId);
                        $current_own_id = CartHelper::getOwnershipIdFromRoom($this->conn, $item["cart_room"]);

                        if ($res_own_id == $current_own_id) {
                            array_push($array_group_by_own_id, $item);
                        }
                    }
                array_push($res_array, $array_group_by_own_id);
                array_push($own_visited, $res_own_id);
            }
            $nigths = array();
            foreach ($res_array as $cartItemArray) {
                if (isset($cartItemArray[0])) {
                    $cartItem = $cartItemArray[0];
                    $resByOwn = CartHelper::getOwnershipIdFromRoom($this->conn, $cartItem["cart_room"]);
                    $ownershipData = CartHelper::getOwnershipData($this->conn, $resByOwn);
                    $hasCompleteReservation = CartHelper::hasCompleteReservation($this->conn, $resByOwn);

                    $serviceFee = CartHelper::getCurrentServiceFeeId($this->conn);
                    $general_reservation = array();
                    $general_reservation["gen_res_user_id"] = $userId;
                    $general_reservation["gen_res_date"] = date('Y-m-d H:i:s');
                    $general_reservation["gen_res_status_date"] = date('Y-m-d H:i:s');
                    $general_reservation["gen_res_hour"] = date('G');
                    $general_reservation["complete_reservation_mode"] = $hasCompleteReservation;
                    if ($inmediatily_booking)
                        $general_reservation["gen_res_status"] = GeneralReservationStatus::STATUS_AVAILABLE;
                    else
                        $general_reservation["gen_res_status"] = GeneralReservationStatus::STATUS_PENDING;

                    $general_reservation["gen_res_from_date"] = $min_date;
                    $general_reservation["gen_res_to_date"] = $max_date;
                    $general_reservation["gen_res_saved"] = 0;
                    $general_reservation["gen_res_own_id"] = $resByOwn;
                    $general_reservation["gen_res_date_hour"] = date('H:i:s');
                    $general_reservation["service_fee"] = $serviceFee;

                    $total_price = 0;
                    $partial_total_price = array();
                    $destination_id = ($ownershipData["own_destination"] != null) ? $ownershipData["own_destination"] : null;
                    foreach ($cartItemArray as $item) {
                        $isTripleRoom = CartHelper::getTripleRoomCharged($this->conn, $item);
                        $triple_room_recharge = ($isTripleRoom && !$hasCompleteReservation) ? Utils::CONFIGURATION_TRIPLE_ROOM_CHARGE : 0;
                        $array_dates = Timer::datesBetween(Timer::getTimestamp($item["cart_date_from"], "Y-m-d H:i:s"), Timer::getTimestamp($item["cart_date_to"], "Y-m-d H:i:s"));
                        $temp_price = 0;
                        $seasons = CartHelper::getSeasons($this->conn, Timer::getTimestamp($item["cart_date_from"], "Y-m-d H:i:s"), Timer::getTimestamp($item["cart_date_to"], "Y-m-d H:i:s"), $destination_id);

                        for ($a = 0; $a < count($array_dates) - 1; $a++) {
                            if ($hasCompleteReservation) {
                                $bookingModalityData = CartHelper::getBookingModalityByAccommodation($this->conn, $resByOwn);

                                if ($bookingModalityData != null && $bookingModalityData["name"] == BookingModality::COMPLETE_RESERVATION_BOOKING) {
                                    $roomPrice = $bookingModalityData["price"];
                                } else {
                                    $seasonType = Timer::seasonTypeByDate($seasons, $array_dates[$a]);
                                    $roomPrice = CartHelper::getPriceBySeasonType($this->conn, $item["cart_room"], $seasonType);
                                }
                            } else {
                                $seasonType = Timer::seasonTypeByDate($seasons, $array_dates[$a]);
                                $roomPrice = CartHelper::getPriceBySeasonType($this->conn, $item["cart_room"], $seasonType);
                            }
                            $total_price += $roomPrice + $triple_room_recharge;
                            $temp_price += $roomPrice + $triple_room_recharge;

                        }
                        array_push($partial_total_price, $temp_price);
                    }
                    $general_reservation["gen_res_total_in_site"] = $total_price;
                    $this->conn->insert('generalreservation', $general_reservation);
                    $generalReservationId = $this->conn->lastInsertId();

                    $arrayKidsAge = array();

                    $flag_1 = 0;
                    foreach ($cartItemArray as $item) {
                        $room = CartHelper::getRoomById($this->conn, $item["cart_room"]);
                        $ownership_reservation = $this->createReservation($item, $room, $generalReservationId, $partial_total_price[$flag_1]);
                        if ($inmediatily_booking)
                            $ownership_reservation["own_res_status"] = OwnershipReservationStatus::STATUS_AVAILABLE;

                        array_push($reservations, $ownership_reservation);

//                        $ownership_photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($ownership_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
//                        array_push($array_photos, $ownership_photo);
//
//                        $nightsCount = $service_time->nights($ownership_reservation->getOwnResReservationFromDate()->getTimestamp(), $ownership_reservation->getOwnResReservationToDate()->getTimestamp());
//                        array_push($nigths, $nightsCount);

                        if ($item["childrenAges"] != null) {
                            $roomNum = $room["room_num"];
                            $arrayKidsAge[$roomNum] = $item["childrenAges"];
                        }

                        $this->conn->insert('ownershipreservation', $ownership_reservation);
                        $ownershipReservationId = $this->conn->lastInsertId();

                        array_push($own_ids, $ownershipReservationId);
                        $returnedReservations[] = array_merge(array("own_res_id" => $ownershipReservationId), $ownership_reservation);
                        $flag_1++;
                    }
                    $general_reservation["childrenAges"] = serialize($arrayKidsAge);
                    $update = CartHelper::updateDates($this->conn, $generalReservationId);
                    $general_reservation["gen_res_from_date"] = $update["gen_res_from_date"];
                    $general_reservation["gen_res_to_date"] = $update["gen_res_to_date"];
                    $general_reservation["gen_res_nights"] = $update["gen_res_nights"];

                    $this->conn->update('generalreservation', $general_reservation, array('gen_res_id' => $generalReservationId));
                    array_push($generalReservations, $generalReservationId);

                    //Tropa: Aqui se manda un SMS al propietario si es de reserva inmediata
//                    if($general_reservation->getGenResOwnId()->getOwnInmediateBooking()){
//                        $smsService = $this->get("mycp.notification.service");
//                        $smsService->sendInmediateBookingSMSNotification($general_reservation);
//                    }
                }
            }
        } else {
            return false;
        }
        //$locale = $this->get('translator')->getLocale();
        // Enviando mail al cliente
        if(!$inmediatily_booking){
            //TROPA: Aqui hay que mandar un correito
            $this->addTaskForSendEmail(2, implode(",", $reservations));

            /*$body = $this->render('FrontEndBundle:mails:email_check_available.html.twig', array(
                'user' => $user,
                'reservations' => $reservations,
                'ids' => $own_ids,
                'nigths' => $nigths,
                'photos' => $array_photos,
                'user_locale' => $locale
            ));

            if($user != null) {
                $locale = $this->get('translator');
                $subject = $locale->trans('REQUEST_SENT');
                $service_email = $this->get('Email');
                $service_email->sendEmail(
                    $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
                );
            }*/
        }

        if(!$inmediatily_booking){
//            //Enviando mail al reservation team
//            //TROPA: Aqui hay que mandar unos correitos
            foreach($generalReservations as $genResId){
//
//                //Enviando correo a solicitud@mycasaparticular.com
                $this->addTaskForSendEmail(3, $genResId);
                //\MyCp\FrontEndBundle\Helpers\ReservationHelper::sendingEmailToReservationTeam($genResId, $em, $this, $service_email, $service_time, $request, 'solicitud@mycasaparticular.com', 'no-reply@mycasaparticular.com');
            }
        }
        foreach($cartItems as $temp){
            $this->conn->delete('cart', array('cart_id' => $temp));
        }

        if(!$inmediatily_booking) //esta consultando la disponibilidad
            return true;
        else                      //esta haciendo una reserva, antes se devolvian los ids solamente
            return $returnedReservations;
    }

    public function createReservation($cartItem, $room, $generalReservationId, $calculatedPrice = 0, $calculateTotalPrice = false) {

        $ownership_reservation =array();
        $ownership_reservation["own_res_count_adults"] = $cartItem["cart_count_adults"];
        $ownership_reservation["own_res_count_childrens"] = $cartItem["cart_count_children"];
        $ownership_reservation["own_res_night_price"] = 0;
        $ownership_reservation["own_res_status"] = OwnershipReservationStatus::STATUS_PENDING;
        $ownership_reservation["own_res_reservation_from_date"] = $cartItem["cart_date_from"];
        $ownership_reservation["own_res_reservation_to_date"] = $cartItem["cart_date_to"];
        $ownership_reservation["own_res_selected_room_id"] = $cartItem["cart_room"];
        $ownership_reservation["own_res_room_price_down"] = $room["room_price_down_to"];
        $ownership_reservation["own_res_room_price_up"] = $room["room_price_up_to"];
        $ownership_reservation["own_res_room_price_special"] = $room["room_price_special"];
        $ownership_reservation["own_res_gen_res_id"] = $generalReservationId;
        $ownership_reservation["own_res_room_type"] = $room["room_type"];

        $modality = CartHelper::getBookingModalityByAccommodation($this->conn, $room["room_ownership"]);

        if($modality != null && $modality["name"] == BookingModality::COMPLETE_RESERVATION_BOOKING && $modality["price"] > 0) {
            $ownership_reservation["own_res_complete_reservation_price"] = $modality["price"];
            $ownership_reservation["own_res_room_type"] = BookingModality::COMPLETE_RESERVATION_BOOKING_TRANS;
        }


        if ($calculateTotalPrice)
            $ownership_reservation["own_res_total_in_site"] = 0; //TODO: Calcular segun los cambios de estaciones
        else
            $ownership_reservation["own_res_total_in_site"] = $calculatedPrice;

        return $ownership_reservation;
    }

    public function addTaskForSendEmail($type, $data){
        $creation_date = new \DateTime();
        $creation_date = $creation_date->format('Y-m-d');

        $this->conn->insert('task_renta', array('type' => $type, 'status' => 0, 'data' => $data, 'creation_date' => $creation_date));
        return true;
    }

    /** Actualizar la  Session*
     *
     * @param $idUser
     * @param $user
     * @param $password
     * @param $date
     *
     * @return string
     */
    public function updateSessionUser( $idUser, $user, $password, $date ) {
        $query = "SELECT user_session.user_id FROM user_session WHERE user_session.user_id = :user_id";
        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'user_id', $idUser );
        $stmt->execute();
        $po = $stmt->fetch();

        $update = isset( $po['user_id'] );

        $expiryDate = \DateTime::createFromFormat( 'Y-m-d', $date );
        $token      = self::encryptPassword( $expiryDate->format( 'Y-m-d H:i:s' ) . $user . $password );
        $expiryDate->modify( '+6 month' );

        if ( $update ) {
            $query = "UPDATE user_session SET token = :token, expiry_date = :expiry_date WHERE user_id = :user_id";
        } else {
            $query = "INSERT INTO user_session(user_id,token,expiry_date) VALUE (:user_id,:token,:expiry_date)";
        }

        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue( 'user_id', $idUser );
        $stmt->bindValue( 'token', $token );
        $stmt->bindValue( 'expiry_date', $expiryDate->format( 'Y-m-d' ) );
        $stmt->execute();

        return $token;
    }

    public function isValidToken( $idUser, $token ) {
        $query = "SELECT user_session.user_id FROM user_session WHERE user_session.user_id = :user_id OR user_session.token = :token";
        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'user_id', $idUser );
        $stmt->bindValue( 'token', $token );
        $stmt->execute();
        $po = $stmt->fetch();

        return isset( $po['user_id'] );
    }

    public function getCurrency( $idUser ) {
        $query = "SELECT currency.curr_id,currency.curr_name,currency.curr_code,currency.curr_cuc_change,currency.curr_symbol,currency.curr_site_price_in
  FROM usertourist
  INNER JOIN currency ON (usertourist.user_tourist_currency = currency.curr_id)
  INNER JOIN user ON (usertourist.user_tourist_user = user.user_id)
  WHERE user.user_id = :user_id";
        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'user_id', $idUser );
        $stmt->execute();

        $r = $stmt->fetch();
        if($r === false){
            $query = "SELECT currency.curr_id,currency.curr_name,currency.curr_code,currency.curr_cuc_change,currency.curr_symbol,currency.curr_site_price_in
  FROM currency
  WHERE currency.curr_id = 1";
            $stmt  = $this->conn->prepare( $query );
            $stmt->execute();
            $r = $stmt->fetch();
        }

        return $r;
    }

    public function getCartItem( $idCart ) {
        $query = "SELECT c.*
                  FROM cart c
                  WHERE c.cart_id = :cart_id";
        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'cart_id', $idCart );
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obtener listado de reservas dada una fecha de inicio.
     *
     * @param date $date Fecha de inicio
     * @param null $iduser
     *
     * @return array
     */
    public function getReservationsByDate( $date, $iduser = null ) {
        $date   = Date::createFromString( $date );
        $select = "DISTINCT gr.gen_res_id,gr.gen_res_from_date,gr.gen_res_to_date,gr.gen_res_own_id,owr.*,ow.*,p.prov_phone_code,p.prov_id,m.mun_id ";
        //InnerJoin
        $inner = "";
        $inner .= " INNER JOIN ownershipreservation owr ON gr.gen_res_id = owr.own_res_gen_res_id ";
        $inner .= " INNER JOIN ownership ow ON ow.own_id = gr.gen_res_own_id ";
        $inner .= "INNER JOIN province p ON ow.own_address_province = p.prov_id ";
        $inner .= "INNER JOIN municipality m ON ow.own_address_municipality = m.mun_id";
        $where = "gr.gen_res_to_date>=:date_from AND gr.gen_res_status=:gen_res_status";
        if ( $iduser != null ) {
            $inner .= " INNER JOIN user u ON u.user_id = gr.gen_res_user_id ";
            $select .= ",u.*";
            $where .= " AND u.user_id =:client_id";
        }
        $query = "SELECT " . $select . " FROM  generalreservation gr " . $inner . " WHERE " . $where . " ; ";
        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'date_from', date_format( $date, 'Y-m-d' ) );
        $stmt->bindValue( 'gen_res_status', 2 );
        if ( $iduser != null ) {
            $stmt->bindValue( 'client_id', (int) $iduser );
        }
        $stmt->execute();
        $po = $stmt->fetchAll();
        //return $po;
        $reservations = array();
        $array_aux    = array();
        foreach ( $po as $item ) {
            if ( ! in_array( $item['gen_res_id'], $array_aux ) ) {
                $array_aux[]              = $item['gen_res_id'];
                $aux['gen_res_id']        = $item['gen_res_id'];
                $aux['gen_res_from_date'] = $item['gen_res_from_date'];
                $aux['gen_res_to_date']   = $item['gen_res_to_date'];
                $aux['accommodation']     = array(
                    'own_id'            => $item['own_id'],
                    'own_mcp_code'      => $item['own_mcp_code'],
                    'own_name'          => $item['own_name'],
                    'address'           => array(
                        'own_destination'              => $item['own_destination'],
                        'prov_id'                      => $item['prov_id'],
                        'mun_id'                       => $item['mun_id'],
                        'own_address_street'           => $item['own_address_street'],
                        'own_address_number'           => $item['own_address_number'],
                        'own_address_between_street_1' => $item['own_address_between_street_1'],
                        'own_address_between_street_2' => $item['own_address_between_street_2'],
                        'own_address_province'         => $item['own_address_province'],
                        'own_address_municipality'     => $item['own_address_province']
                    ),
                    'own_mobile_number' => $item['own_mobile_number'],
                    'own_phone_number'  => $item['prov_phone_code'] . ' ' . $item['own_phone_number'],
                    'own_email_1'       => $item['own_email_1'],
                    'own_email_2'       => $item['own_email_2'],
                    'own_geolocate_y'   => $item['own_geolocate_y'],
                    'own_geolocate_x'   => $item['own_geolocate_x']
                );
                $aux['details']           = self::getDetailsReservations( $item['gen_res_id'] );

                $reservations[] = $aux;
            }

        }

        return $reservations;
    }

    public function getDetailsReservations( $gen_res_id ) {
        $res   = array();
        $inner = "";
        //$temp = self::getTableById('ownershipreservation', 'own_res_gen_res_id', $gen_res_id, 'fetchAll');
        $select = "owr.*,r.*";
        $where  = " owr.own_res_gen_res_id =:own_res_gen_res_id";

        $inner .= " INNER JOIN room r ON owr.own_res_selected_room_id = r.room_id";
        $query = "SELECT " . $select . " FROM   ownershipreservation owr " . $inner . " WHERE " . $where . " ; ";
        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'own_res_gen_res_id', $gen_res_id );
        $stmt->execute();
        $temp = $stmt->fetchAll();
        if ( count( $temp ) ) {
            foreach ( $temp as $item ) {
                $aux['own_res_id']                    = $item['own_res_id'];
                $aux['own_res_count_childrens']       = $item['own_res_count_childrens'];
                $aux['own_res_count_adults']          = $item['own_res_count_adults'];
                $aux['own_res_reservation_from_date'] = $item['own_res_reservation_from_date'];
                $aux['own_res_reservation_to_date']   = $item['own_res_reservation_to_date'];
                $aux['room']                          = array( 'room_type' => $item['own_res_room_type'], 'room_id' => $item['room_id'], 'room_num' => $item['room_num'] );
                $res[]                                = $aux;
            }
        }

        return $res;

    }

    public function getRoomsByAccomodation( $idOwn, $change ) {
        try {
            $query = "SELECT
room.room_id as room_id,
room.room_num,
room.room_type as type,
room.room_beds as beds,
(room.room_price_up_from * $change) as price_up_from,
(room.room_price_up_to * $change) as price_up_to,
(room.room_price_down_from * $change) as price_down_from,
(room.room_price_down_to * $change) as price_down_to,
(room.room_price_special * $change) as price_special,
room.room_climate as climate,
room.room_audiovisual as audiovisual,
room.room_smoker as smoker,
room.room_safe as safe,
room.room_baby as baby,
room.room_bathroom as bathroom,
room.room_stereo as stereo,
room.room_windows as windows,
room.room_balcony as balcony,
room.room_terrace as terrace,
room.room_yard as yard
FROM room WHERE room.room_active = 1 AND room.room_ownership = :id_own";

            $stmt = $this->conn->prepare( $query );
            $stmt->bindValue( 'id_own', $idOwn );
            $stmt->execute();
            $r = $stmt->fetchAll();

            return $r;
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }

    public function getRoomById( $idRoom , $change) {
        try {
            $query = "SELECT
room.room_id as room_id,
room.room_num,
room.room_type as type,
(room.room_price_up_from * $change) as price_up_from,
(room.room_price_up_to * $change) as price_up_to,
(room.room_price_down_from * $change) as price_down_from,
(room.room_price_down_to * $change) as price_down_to,
(room.room_price_special * $change) as price_special,
room.room_beds as beds,
room.room_climate as climate,
room.room_audiovisual as audiovisual,
room.room_smoker as smoker,
room.room_safe as safe,
room.room_baby as baby,
room.room_bathroom as bathroom,
room.room_stereo as stereo,
room.room_windows as windows,
room.room_balcony as balcony,
room.room_terrace as terrace,
room.room_yard as yard
FROM room WHERE room.room_active = 1 AND room.room_id = :room_id";

            $stmt = $this->conn->prepare( $query );
            $stmt->bindValue( 'room_id', $idRoom );
            $stmt->execute();
            $r = $stmt->fetch();

            return $r;
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }

    public function getChange($currency){
        $query = "SELECT cur.curr_cuc_change as cuc_change FROM currency cur
                         WHERE  cur.curr_code = '$currency' LIMIT 1";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        return $stmt->fetch();
    }
    public function getCommentsByAccomodation( $idOwn ) {
        try {
            $query = "SELECT user.user_name as user,comment.com_comments as comment FROM comment INNER JOIN user ON (user.user_id = comment.com_user)
WHERE comment.com_ownership = :id_own and comment.com_public=1";

            $stmt = $this->conn->prepare( $query );
            $stmt->bindValue( 'id_own', $idOwn );
            $stmt->execute();
            $r = $stmt->fetchAll();

            return $r;
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }
    public function getPhotosByAccomodation( $idOwn ) {
        try {
            $query = "SELECT photo.pho_name as url FROM ownershipphoto INNER JOIN photo ON (ownershipphoto.own_pho_pho_id = photo.pho_id)
WHERE ownershipphoto.own_pho_own_id = :id_own";

            $stmt = $this->conn->prepare( $query );
            $stmt->bindValue( 'id_own', $idOwn );
            $stmt->execute();
            $r = $stmt->fetchAll( \PDO::FETCH_COLUMN, 0 );

            return $r;
        }
        catch ( \Exception $e ) {
            throw $e;
        }
    }

    public function ownNotAvailable( $arrivalDate = null, $leavingDate = null ) {
        $arrival   = "";
        $departure = "";
        if ( $arrivalDate == null || $arrivalDate == "" ) {
            $arrival = new \DateTime();
            $arrival = $arrival->format( "Y-m-d" );
        } else {
            $arrival = $arrivalDate;
        }

        if ( $leavingDate == null || $leavingDate == "" ) {
            $date      = new \DateTime();
            $departure = $date->modify( '+2 days' )->format( "Y-m-d" );
        } else {
            $departure = $leavingDate;
        }

        $status_reserved = self::OWNERSHIP_STATUS_RESERVED;
        $sql             = 'SELECT DISTINCT o.own_id
                FROM  ownership o
                WHERE (SELECT count(two.own_id)
                  FROM (
                        (SELECT DISTINCT r2.room_id,o2.own_id FROM ownershipreservation owr1
                                INNER JOIN room r2 ON r2.room_id = owr1.own_res_selected_room_id
                                INNER JOIN generalreservation g2 ON g2.gen_res_id = owr1.own_res_gen_res_id
                                INNER JOIN ownership o2 ON o2.own_id = g2.gen_res_own_id
                                WHERE owr1.own_res_status = ' . $status_reserved . ' AND
                                ((owr1.own_res_reservation_from_date <= "' . $arrival . '" AND owr1.own_res_reservation_to_date > "' . $arrival . '") OR (owr1.own_res_reservation_from_date <= "' . $departure . '" AND owr1.own_res_reservation_to_date > "' . $departure . '"))
                        )
                        UNION
                            (SELECT DISTINCT r3.room_id,o3.own_id from unavailabilitydetails ud
                                    INNER JOIN room r3 ON r3.room_id = ud.room_id
                                    INNER JOIN ownership o3 ON o3.own_id = r3.room_ownership
                                    WHERE (( ud.ud_from_date<="' . $arrival . '"  AND ud.ud_to_date >="' . $arrival . '" ) OR ( ud.ud_from_date <="' . $departure . '"  AND  ud.ud_to_date >="' . $departure . '"))
                        )
                       ) as two WHERE two.own_id=o.own_id
                    ) >= o.own_rooms_total ';

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return null
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * @param null $container
     */
    public function setContainer( $container ) {
        $this->container = $container;
    }

    public function getOwnership( $ownId, $currency ) {
        $query = "SELECT
  o.own_id,
  o.own_name as own_name,
  o.own_mcp_code as mcp_code,
  o.own_commission_percent as commission_percent,
  booking_modality.name AS modality,
  accommodation_booking_modality.price * cur.curr_cuc_change AS modality_price,
  o.own_inmediate_booking_2 as inmediate_booking,
  cur.curr_cuc_change AS cuc_change,
  des.des_id,
  des.des_name as destination
FROM
  ownership o
  INNER JOIN province prov ON (o.own_address_province = prov.prov_id)
  INNER JOIN destination des ON (o.own_destination = des.des_id)
  LEFT OUTER JOIN accommodation_booking_modality ON (o.own_id = accommodation_booking_modality.accommodation)
  LEFT OUTER JOIN booking_modality ON (accommodation_booking_modality.bookingModality = booking_modality.id),
  currency cur
WHERE
  cur.curr_code = :curr_code AND o.own_id = :own_id";
        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'own_id', $ownId );
        $stmt->bindValue( 'curr_code', $currency, \PDO::PARAM_STR );
        $stmt->execute();

        return $stmt->fetch();
    }

    private function getRoomUnavailabilitydetails( $id_room, $fromDate, $toDate ) {
        $delete = McpApp::UD_SYNC_DELETED;
        $query  = "SELECT o.ud_id
                        FROM unavailabilitydetails o
                        WHERE o.ud_sync_st<> $delete
                        AND o.room_id = :id_room
                        AND ((o.ud_from_date >= :start AND o.ud_to_date <= :end) OR
                             (o.ud_to_date >= :start AND o.ud_to_date <= :end) OR
                             (o.ud_from_date <= :end AND o.ud_from_date >= :start) OR
                             (o.ud_from_date <= :start AND o.ud_to_date >= :start))
                        ORDER BY o.ud_from_date DESC";

        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue( 'id_room', $id_room );
        $stmt->bindValue( 'start', $fromDate->format( 'Y-m-d' ), \PDO::PARAM_STR );
        $stmt->bindValue( 'end', $toDate->format( 'Y-m-d' ), \PDO::PARAM_STR );
        $stmt->execute();

        return $stmt->fetch();
    }

    private function getRoomReservations( $id_room, $fromDate, $toDate ) {
        $status = self::OWNERSHIP_STATUS_RESERVED;
        $query  = "SELECT ore.own_res_id
            FROM ownershipreservation ore
        WHERE (ore.own_res_status = $status)
        AND ((ore.own_res_reservation_from_date >= :start AND ore.own_res_reservation_to_date < :end) OR
             (ore.own_res_reservation_to_date > :start AND ore.own_res_reservation_to_date < :end) OR
             (ore.own_res_reservation_from_date <= :end AND ore.own_res_reservation_from_date >= :start) OR
             (ore.own_res_reservation_from_date <= :start AND ore.own_res_reservation_to_date > :start))
        AND ore.own_res_selected_room_id = :room_id
        ORDER BY ore.own_res_reservation_from_date ASC";

        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue( 'room_id', $id_room );
        $stmt->bindValue( 'start', $fromDate->format( 'Y-m-d' ), \PDO::PARAM_STR );
        $stmt->bindValue( 'end', $toDate->format( 'Y-m-d' ), \PDO::PARAM_STR );
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getDestinationSeasons( $checkin_date, $checkeout_date, $id_destination = null ) {
        $query = "SELECT s.season_id AS id, s.season_type, s.season_startdate AS startdate, s.season_enddate AS enddate
FROM season s
                         WHERE ((s.season_startdate <= :checkin AND s.season_enddate >= :checkout)
                            OR (s.season_startdate >= :checkin AND s.season_enddate <= :checkout)
                            OR (s.season_startdate <= :checkin AND s.season_enddate <= :checkout)
                            OR (s.season_startdate >= :checkin AND s.season_enddate >= :checkout))";

        if ( isset( $id_destination ) ) {
            $query .= " AND (s.season_destination = $id_destination OR s.season_destination IS NULL)";
        } else {
            $query .= " AND s.season_destination IS NULL ";
        }
        $query .= " ORDER BY s.season_startdate ASC ";

        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue( 'checkin', $checkin_date->format( 'Y-m-d' ), \PDO::PARAM_STR );
        $stmt->bindValue( 'checkout', $checkeout_date->format( 'Y-m-d' ), \PDO::PARAM_STR );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function seasonTypeByDate( $seasons, $date_timestamp ) {
        $season_type = self::SEASON_TYPE_LOW;
        foreach ( $seasons as $season ) {
            $startdate = \DateTime::createFromFormat( 'Y-m-d H:i:s', $season['startdate'] );
            $enddate   = \DateTime::createFromFormat( 'Y-m-d H:i:s', $season['enddate'] );
            if ( $startdate->getTimestamp() <= $date_timestamp && $enddate->getTimestamp() >= $date_timestamp ) {
                if ( $season_type == self::SEASON_TYPE_LOW || ( $season_type == self::SEASON_TYPE_HIGH && $season->getSeasonType() == self::SEASON_TYPE_SPECIAL ) ) {
                    $season_type = $season['season_type'];
                }
            }
        }

        return $season_type;
    }

    public function getPriceRoomBySeasonType( $seasonType, $ownership, $room ) {
        $bookingModality = $ownership['modality'];
        if ( $bookingModality != null && $bookingModality == McpApp::COMPLETE_RESERVATION_BOOKING ) {
            return $ownership['modality_price'];
        } else {
            switch ( $seasonType ) {
                case self::SEASON_TYPE_HIGH:
                    return $room['price_up_to'];
                case self::SEASON_TYPE_SPECIAL:
                    return ( $room['price_special'] != null && $room['price_special'] > 0 ) ? $room['price_special'] : $room['price_up_to'];
                default:
                    return $room['price_down_to'];
            }
        }

    }

    public function getServiceFeeCurrent() {
        $query = "SELECT
  servicefee.id,
  servicefee.fixedFee,
  servicefee.one_nr_until_20_percent,
  servicefee.one_nr_from_20_to_25_percent,
  servicefee.one_nr_from_more_25_percent,
  servicefee.one_night_several_rooms_percent,
  servicefee.one_2_nights_percent,
  servicefee.one_3_nights_percent,
  servicefee.one_4_nights_percent,
  servicefee.one_5_nights_percent
FROM servicefee
WHERE servicefee.current = 1
ORDER BY servicefee.`date` DESC LIMIT 1";
        $stmt  = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getUserbyId( $userId ) {

        $query = "SELECT
u.user_id, u.user_name, u.user_user_name, u.user_last_name, u.user_email, u.user_id, u.user_address, u.user_city, ut.user_tourist_postal_code, c.co_code
FROM user u
INNER JOIN usertourist ut ON (u.user_id = ut.user_tourist_user)
LEFT OUTER JOIN country c ON (u.user_country = c.co_id)
WHERE  u.user_id=:user_id ";

        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'user_id', $userId );
        $stmt->execute();
        $po = $stmt->fetch();

        /*$pathToCont = "xxxxxxx.txt";
        $file = fopen($pathToCont, "a");
        fwrite($file, '----------------' . PHP_EOL);
        fwrite($file, ' -->  user_name: ' . $user . ' --> user_password: ' . $encrypt_password . PHP_EOL);
        fwrite($file, '--------------' . PHP_EOL);
        fclose($file);*/

        if ( isset( $po['user_id'] ) ) {
            return $po;
        } else {
            return null;
        }
    }
    public function getCountrybyCode( $code ) {

        $query = "SELECT
c.co_id
FROM country c

WHERE  c.co_name=:code ";

        $stmt  = $this->conn->prepare( $query );
        $stmt->bindValue( 'code', $code );
        $stmt->execute();
        $po = $stmt->fetch();

        /*$pathToCont = "xxxxxxx.txt";
        $file = fopen($pathToCont, "a");
        fwrite($file, '----------------' . PHP_EOL);
        fwrite($file, ' -->  user_name: ' . $user . ' --> user_password: ' . $encrypt_password . PHP_EOL);
        fwrite($file, '--------------' . PHP_EOL);
        fclose($file);*/

        if ( $po) {
            return $po;
        } else {
            return null;
        }
    }
    public function SaveProfileUser( $request ) {
        $user_id      = $request->request->get( 'user_id' );
        $phone     = $request->request->get( 'phone' );
        $email = $request->request->get( 'email' );
        $country_code = $request->request->get( 'country_code' );
        $city = $request->request->get( 'city' );
        $user = $this->getUserbyId($user_id);
        $country_id=$this->getCountrybyCode($country_code)['co_id'];
        if($user != null){
            $userArray = array(

                "user_email" => $email,
                "user_phone" =>$phone,
                "user_city" =>$city,
                "user_country" => $country_id,

            );
            $this->conn->update('user', $userArray, array('user_id' => $user_id));
        }



        if (true ) {
            return $this->view->create( array( 'success' => 1, 'msg' => 'Modificado correctamente', "user" => $userArray ), Response::HTTP_OK );
        } else {
            return $this->view->create( 'Error', Response::HTTP_INTERNAL_SERVER_ERROR );
        }
    }
    /**
     * Obtener listado de reservas de un cliente.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $userid identificador del usuario
     * @return array
     */
    public function getReservationclient($request, $userid) {

        $select = "u.user_id,c.co_name AS co_code,p.pho_name,u.user_city,u.user_phone,l.lang_code,u.user_name,u.user_user_name,u.user_last_name,u.user_email,u.user_enabled";
        $where = "u.user_id =:client_id";

        $query = "SELECT " . $select . " FROM  user u INNER JOIN usertourist ut ON (u.user_id = ut.user_tourist_user) INNER JOIN lang l ON (ut.user_tourist_language = l.lang_id) LEFT OUTER JOIN country c ON (u.user_country = c.co_id)LEFT OUTER JOIN photo p ON (u.user_photo = p.pho_id)" . " WHERE " . $where . " ; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('client_id', (int)$userid);
        $stmt->execute();
        $po = $stmt->fetchAll();
        $i = 0;
        $res['user_id'] = $po[$i]['user_id'];
        $res['user_name'] = $po[$i]['user_name'];
        $res['lang_code'] = $po[$i]['lang_code'];
        $res['co_code'] = $po[$i]['co_code'];
        $res['user_phone'] = $po[$i]['user_phone'];
        $res['pho_name'] = $po[$i]['pho_name'];
        $res['user_city'] = $po[$i]['user_city'];
        $res['user_user_name'] = $po[$i]['user_user_name'];
        $res['user_last_name'] = $po[$i]['user_last_name'];
        $res['user_email'] = $po[$i]['user_email'];
        $res['user_enabled'] = $po[$i]['user_enabled'];
        $currency       = $this->getCurrency( $userid );

        $res['currency'] = $currency;
        $res['reservations'] = self::getReservationabydate($request->query->get('start'), $po[$i]['user_id']);
        return $this->view->create($res, 200);
    }
    /**
     * Obtener listado de reservas dada una fecha de inicio.
     * @param date $date Fecha de inicio
     * @return array
     */
    public function getReservationabydate($date, $iduser = null) {

        $select = "DISTINCT gr.gen_res_id,ow.own_inmediate_booking_2, ow.own_commission_percent,owr.own_res_total_in_site,des.des_name,gr.gen_res_status,gr.gen_res_from_date,gr.gen_res_to_date,gr.gen_res_own_id,owr.*,ow.*,p.prov_phone_code,p.prov_id,m.mun_id ";
        //InnerJoin
        $inner = "";
        $inner .= " INNER JOIN ownershipreservation owr ON gr.gen_res_id = owr.own_res_gen_res_id ";
        $inner .= " INNER JOIN ownership ow ON ow.own_id = gr.gen_res_own_id ";
        $inner .= "INNER JOIN province p ON ow.own_address_province = p.prov_id ";
        $inner .= "INNER JOIN municipality m ON ow.own_address_municipality = m.mun_id ";
        $inner .= "INNER JOIN destination des ON ow.own_destination = des.des_id ";
        $where = "";
        if($iduser != null) {
            $inner .= " INNER JOIN user u ON u.user_id = gr.gen_res_user_id ";

            $select .= ",u.*";
            $where .= "u.user_id =:client_id";

        }
        $query = "SELECT " . $select . " FROM  generalreservation gr " . $inner . " WHERE " . $where . " ; ";
        $stmt = $this->conn->prepare($query);


        if($iduser != null)
            $stmt->bindValue('client_id', (int)$iduser);
        $stmt->execute();
        $po = $stmt->fetchAll();
        //return $po;
        $reservations = array();
        $array_aux = array();
        $servicefee   = $this->getServiceFeeCurrent();
        $currency       = $this->getCurrency( $iduser );

        foreach ($po as $item) {

            if(!in_array($item['gen_res_id'], $array_aux)) {
                $fromDate = \DateTime::createFromFormat( 'Y-m-d', $item['gen_res_from_date'] )->setTime( 0, 0, 0 );
                $toDate   = \DateTime::createFromFormat( 'Y-m-d', $item['gen_res_to_date'] )->setTime( 0, 0, 0 );
                $fromTimestamp = $fromDate->getTimestamp();
                $toTimestamp   = $toDate->getTimestamp();
                $dates = MyCpUtils::datesBetween($fromTimestamp, $toTimestamp, null);

                $nights = count($dates) - 1;
                $array_aux[] = $item['gen_res_id'];
                $aux['gen_res_id'] = $item['gen_res_id'];
                $aux['booking'] = self::getBookings($item['gen_res_id']);
                $aux['own_res_status'] = $item['own_res_status'];
                $aux['own_res_total_in_site'] = $item['own_res_total_in_site'];
                $aux['gen_res_from_date'] = $item['gen_res_from_date'];
                $aux['gen_res_to_date'] = $item['gen_res_to_date'];
                $aux['accommodation'] = array('own_id' => $item['own_id'],'lon' => $item['own_geolocate_y'], 'lat' => $item['own_geolocate_x'],'service_fee' => $servicefee,'cuc_change' => $currency['curr_cuc_change'],'nights' => $nights,'own_name' => $item['own_name'],'mcp_code' => $item['own_mcp_code'],'commission_percent' => $item['own_commission_percent'],'inmediate_booking' => $item['own_inmediate_booking_2'],'destination' => $item['des_name'], 'address' => array('own_destination' => $item['own_destination'], 'prov_id' => $item['prov_id'], 'mun_id' => $item['mun_id'], 'own_address_street' => $item['own_address_street'], 'own_address_number' => $item['own_address_number'], 'own_address_between_street_1' => $item['own_address_between_street_1'], 'own_address_between_street_2' => $item['own_address_between_street_2'], 'own_address_province' => $item['own_address_province'], 'own_address_municipality' => $item['own_address_province']), 'own_mobile_number' => $item['own_mobile_number'], 'own_phone_number' => $item['prov_phone_code'] . ' ' . $item['own_phone_number'], 'own_email_1' => $item['own_email_1'], 'own_email_2' => $item['own_email_2'], 'own_geolocate_y' => $item['own_geolocate_y'], 'own_geolocate_x' => $item['own_geolocate_x']);
                $aux['details'] = self::getDetailsreserv($item['gen_res_id']);

                $reservations[] = $aux;
            }

        }
        return $reservations;
    }
    public function getBookings($id_reservation) {
           $res = "";
           $select = " DISTINCT book.booking_id";
           $inner = "";
           $where = " owr.own_res_gen_res_id =:own_res_gen_res_id LIMIT 1";
           $inner .= " INNER JOIN booking book ON book.booking_id = owr.own_res_reservation_booking";
           $query = "SELECT " . $select . " FROM   ownershipreservation owr " . $inner . " WHERE " . $where . ";";

           $stmt = $this->conn->prepare($query);
           $stmt->bindValue('own_res_gen_res_id', $id_reservation);
           $stmt->execute();
           $temp = $stmt->fetchAll();
           foreach ($temp as $item) {
                         $res=$item['booking_id'];
           }
           return $res;

    }
    public function getDetailsreserv($gen_res_id) {
        $res = array();
        $inner = "";
        //$temp = self::getTableById('ownershipreservation', 'own_res_gen_res_id', $gen_res_id, 'fetchAll');
        $select = "owr.*,r.*";
        $where = " owr.own_res_gen_res_id =:own_res_gen_res_id";

        $inner .= " INNER JOIN room r ON owr.own_res_selected_room_id = r.room_id";
        $query = "SELECT " . $select . " FROM   ownershipreservation owr " . $inner . " WHERE " . $where . " ; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_res_gen_res_id', $gen_res_id);
        $stmt->execute();
        $temp = $stmt->fetchAll();
        if(count($temp)) {
            foreach ($temp as $item) {
                $aux['own_res_gen_res_id'] = $item['own_res_gen_res_id'];
                $aux['own_res_id'] = $item['own_res_id'];
                $aux['own_res_status'] = $item['own_res_status'];
                $aux['own_res_total_in_site'] = $item['own_res_total_in_site'];
                $aux['own_res_count_childrens'] = $item['own_res_count_childrens'];
                $aux['own_res_count_adults'] = $item['own_res_count_adults'];
                $aux['own_res_reservation_from_date'] = $item['own_res_reservation_from_date'];
                $aux['own_res_reservation_to_date'] = $item['own_res_reservation_to_date'];
                $aux['own_res_selected_room_id'] = array('type' => $item['own_res_room_type'], 'room_id' => $item['room_id'], 'room_num' => $item['room_num']);
                $res[] = $aux;
            }
        }
        return $res;

    }

}