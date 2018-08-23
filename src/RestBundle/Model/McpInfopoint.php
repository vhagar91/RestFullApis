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

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use RestBundle\Helpers\BackendModuleName;
use RestBundle\Helpers\Date;
use RestBundle\Helpers\Operations;
use RestBundle\Helpers\Utils;
use RestBundle\Exception\InvalidFormException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class McpInfopoint extends Mcp
{
    /**
     * @param $request
     * @return mixed
     */
    public function loginInfopoint($request)
    {
        $user = $request->request->get('user');
        $encrypt_password = self::encryptPassword($request->request->get('password'));
        $query = "SELECT user_id,user_name,user_user_name,user_last_name
FROM user INNER JOIN userstaffmanager ON (user.user_id = userstaffmanager.user_staff_manager_user)
WHERE  user_name=:user_name AND user_password=:user_password; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('user_name', $user);
        $stmt->bindValue('user_password', $encrypt_password);
        $stmt->execute();
        $po = $stmt->fetch();

        if (isset($po['user_id'])) {
            $destinations = $this->getDestinationByUserStaffManager($po['user_id']);
            $po['user_destinations'] = $destinations;
            $po['nomencladores'] = $this->getNomencladores();
            return $po;
        } else {
            return $this->view->create(array('success' => false, 'message' => 'Bad credentials'), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @param $request
     * @return mixed
     * @throws \Exception
     */
    public function ownerships($request){
        //$tiempo_inicio = $this->microtime_float();

        $ownerships = $this->ownershipsByDest($request->query->get('des_id'));

        //$tiempo_fin = $this->microtime_float();
        //$tiempo = $tiempo_fin - $tiempo_inicio;

        return $ownerships;
    }

    /**
     * @param $request
     * @return mixed
     * @throws \Exception
     */
    public function rooms($request){
        $user = $request->query->get('user');
        $encrypt_password = self::encryptPassword($request->query->get('password'));
        $query = "SELECT user_id,user_name,user_user_name,user_last_name
FROM user INNER JOIN userstaffmanager ON (user.user_id = userstaffmanager.user_staff_manager_user)
WHERE  user_name=:user_name AND user_password=:user_password; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('user_name', $user);
        $stmt->bindValue('user_password', $encrypt_password);
        $stmt->execute();
        $po = $stmt->fetch();

        if (isset($po['user_id'])) {
            $own_id = $request->query->get('own_id');
            $from_date = $request->query->get('start');
            $to_date = $request->query->get('end');

            $xrooms = $this->roomsByOwnership($own_id);
            $rooms = array();

            foreach ($xrooms as $room){
                $udetails = $this->getUDetailsByRoom($room['room_id'], $from_date, $to_date);
                $reservations = $this->getReservationsByRoom($room['room_id'], $from_date, $to_date);
                $room['room_udetails'] = $udetails;
                $room['room_reservations'] = $reservations;

                $rooms[] = $room;
            }

            return $rooms;
        } else {
            return $this->view->create(array('success' => false, 'message' => 'Bad credentials'), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @param todos $request
     * @param código $code
     * @return array
     */
    public function updateCalendarRoom($request) {
        $user = $request->request->get('user');
        $encrypt_password = self::encryptPassword($request->request->get('password'));

        $queryUser = "SELECT user.user_id FROM user
  INNER JOIN userstaffmanager ON (user.user_id = userstaffmanager.user_staff_manager_user)
  INNER JOIN userstaffmanager_destination ON (userstaffmanager.user_staff_manager_id = userstaffmanager_destination.user_staff_manager)
  INNER JOIN destination ON (userstaffmanager_destination.destination = destination.des_id)
  INNER JOIN ownership ON (destination.des_id = ownership.own_destination)
  INNER JOIN room ON (ownership.own_id = room.room_ownership)
WHERE user_name=:user_name AND user_password=:user_password AND room.room_id = :room_id";

        $availability = $request->request->get('availability');
        $availabilityRooms = $this->parseAvailability($availability);
        $ownership = ['room' => array()];
        foreach ($availabilityRooms as $availabilityRoom) {
            $roomId = $availabilityRoom['room'];
            $availabilities = $availabilityRoom['availabilities'];
            $start = $availabilityRoom['start'];
            $end = $availabilityRoom['end'];

            $stmtUser = $this->conn->prepare($queryUser);
            $stmtUser->bindValue('user_name', $user);
            $stmtUser->bindValue('user_password', $encrypt_password);
            $stmtUser->bindValue('room_id', $roomId);
            $stmtUser->execute();
            $resUser = $stmtUser->fetch();
            if($resUser != null && $resUser != false && isset($resUser)) {
                $this->addavailableroombyrange($roomId, $start, $end, $availabilities, "Por App InfoPoint", $resUser['user_id']);

                $room = array();
                $start = new \DateTime();
                $end = (new \DateTime())->modify('1 years');
                $unavailability = self::getUnavailability($start->format('Y-m-d'), $end->format('Y-m-d'), $roomId);
                $reservations = self::getOwnerShipReservation($roomId, $start->format('Y-m-d'), $end->format('Y-m-d'));
                $room['room_id'] = $roomId;
                $room['room_udetails'] = $unavailability;
                $room['room_reservations'] = $reservations;
                $ownership['own_rooms'][] = $room;
            }
        }

        return $this->view->create($ownership, Response::HTTP_OK);
    }

    /**
     * @param $request
     * @return mixed
     * @throws \Exception
     */
    public function getAll($request){
        $user = $request->query->get('user');
        $encrypt_password = self::encryptPassword($request->query->get('password'));

        $query = "SELECT user_id,user_name,user_user_name,user_last_name
FROM user INNER JOIN userstaffmanager ON (user.user_id = userstaffmanager.user_staff_manager_user)
WHERE  user_name=:user_name AND user_password=:user_password; ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('user_name', $user);
        $stmt->bindValue('user_password', $encrypt_password);
        $stmt->execute();
        $po = $stmt->fetch();

        if (isset($po['user_id'])) {
            $from_date = $request->query->get('start');
            $to_date = $request->query->get('end');

            $des_id = $request->query->get('des_id');
            $ownerships = $this->getUDetailsReservationsByDest($des_id, $from_date, $to_date);

            return $ownerships;
        } else {
            return $this->view->create(array('success' => false, 'message' => 'Bad credentials'), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**************************Metodos auxiliares************************************************/

    /**
     * @param $userStaffManagerId
     * @return
     * @throws \Exception
     */
    public function getDestinationByUserStaffManager($userStaffManagerId){
        try {
            $queryDestOwnership = "SELECT destination.des_id,destination.des_name
FROM userstaffmanager
INNER JOIN userstaffmanager_destination ON (userstaffmanager.user_staff_manager_id = userstaffmanager_destination.user_staff_manager)
INNER JOIN destination ON (userstaffmanager_destination.destination = destination.des_id)
WHERE userstaffmanager.user_staff_manager_user = :user_staff_manager_user
ORDER BY destination.des_id";

            $stmtDestOwnership = $this->conn->prepare($queryDestOwnership);
            $stmtDestOwnership->bindValue('user_staff_manager_user', $userStaffManagerId);
            $stmtDestOwnership->execute();
            $resDestOwnership = $stmtDestOwnership->fetchAll();

            return $resDestOwnership;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $des_id
     * @return
     * @throws \Exception
     */
    public function ownershipsByDest($des_id){
        try {
            $queryOwnerships = "SELECT ownership.own_id,ownership.own_name,ownership.own_mcp_code,ownership.own_status
FROM ownership
WHERE ownership.own_destination = :des_id";

            $stmtOwnerships = $this->conn->prepare($queryOwnerships);
            $stmtOwnerships->bindValue('des_id', $des_id);
            $stmtOwnerships->execute();

            return $stmtOwnerships->fetchAll();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $own_id
     * @return
     * @throws \Exception
     */
    public function roomsByOwnership($own_id){
        try {
            $queryOwnershipsRooms = "SELECT room.room_id,room.room_num FROM room WHERE room.room_ownership = :own_id";

            $stmtOwnershipsRooms = $this->conn->prepare($queryOwnershipsRooms);
            $stmtOwnershipsRooms->bindValue('own_id', $own_id);
            $stmtOwnershipsRooms->execute();
            $resOwnershipsRooms = $stmtOwnershipsRooms->fetchAll();

            return $resOwnershipsRooms;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /*************************** ****************************************************/
    /*************************** ****************************************************/
    /*************************** ****************************************************/
    /*************************** ****************************************************/

    public function getUDetailsReservationsByDest($des_id, $from_date, $to_date){


        $ownershipsRooms = $this->getOwnershipsAndRoomsByDest($des_id);
        $ownerships = [];
        $ownershipLast = null;

        foreach ($ownershipsRooms as $ownershipRoom){
            $objets = $this->extractObjets($ownershipRoom);
            $ownership = $objets['ownership'];
            $room = $objets['room'];

            if($from_date && $to_date){
                $udetails = $this->getUDetailsByRoom($room['room_id'], $from_date, $to_date);
                $reservations = $this->getReservationsByRoom($room['room_id'], $from_date, $to_date);
                $room['room_udetails'] = $udetails;
                $room['room_reservations'] = $reservations;
            }

            if($ownershipLast == null || $ownershipLast['own_id'] != $ownership['own_id']){
                $ownership['own_rooms'] = array();
                $ownership['own_rooms'][] = $room;
                $ownerships[] = $ownership;
            }
            else{
                $ownershipx = &$ownerships[count($ownerships) - 1];
                $ownershipx['own_rooms'][] = $room;
            }
            $ownershipLast = $ownership;
        }

        return $ownerships;
    }

    public function getOwnershipsAndRoomsByDest($des_id){
        try {
            $queryOwnershipsRooms = "SELECT room.room_id,room.room_num,
ownership.own_id,ownership.own_id,ownership.own_name,ownership.own_mcp_code,ownership.own_status
FROM room INNER JOIN ownership ON (room.room_ownership = ownership.own_id)
WHERE ownership.own_destination = :des_id
ORDER BY ownership.own_id, room.room_id";

            $stmtOwnershipsRooms = $this->conn->prepare($queryOwnershipsRooms);
            $stmtOwnershipsRooms->bindValue('des_id', $des_id);
            $stmtOwnershipsRooms->execute();
            $resOwnershipsRooms = $stmtOwnershipsRooms->fetchAll();

            return $resOwnershipsRooms;
        } catch (\Exception $e) {
            throw $e;
        }
    }



    /**
     * @param $request
     * @return mixed
     * @throws \Exception
     */
    public function getNomencladores(){
        $nomencladores = [
            'provinces'=>$this->listProvinces(),
            'municipalities'=>$this->listMunicipality(),
            //'destinations'=>$this->listDestinations(),
            'owns_categories'=>$this->listOwnsCategories(),
            'owns_types'=>$this->listOwnsTypes(),
            'room_types'=>$this->listRoomTypes(),
            'reservation_status'=>$this->listReservationStatus(),
            'climates'=>$this->listClimate(),
            'audiovisuals'=>$this->listAudiovisual(),
            'bathroom_types'=>$this->listBathroomTypes(),
            'languages'=>$this->listLanguages(),
            'owns_status'=>$this->listOwnsStatus()
        ];

        return $nomencladores;
    }

    /**
     * Crear nueva propiedad.
     * @param $accommodation
     * @param $user
     * @param $pass
     * @return array
     */
    public function createOwnership($accommodation, $user, $pass)
    {
        $encrypt_password = self::encryptPassword($pass);
        $desId = $accommodation->destination;
        $valid = $this->isValidUser($user, $encrypt_password, $desId);
        if(!$valid){
            return $this->view->create(array('success' => false, 'code' => Response::HTTP_UNAUTHORIZED, 'message' => 'No tiene permisos para adicionar casas en este destino'), Response::HTTP_UNAUTHORIZED);
        }

        $array_temp = array();
        ($accommodation->name != "") ? $array_temp['own_name'] = $accommodation->name : $array_temp;
        ($accommodation->owner_name_one != "") ? $array_temp['own_homeowner_1'] = $accommodation->owner_name_one : $array_temp;
        ($accommodation->owner_name_two != "") ? $array_temp['own_homeowner_2'] = $accommodation->owner_name_two : $array_temp;
        ($accommodation->email_one != "") ? $array_temp['own_email_1'] = $accommodation->email_one : $array_temp;
        ($accommodation->email_two != "") ? $array_temp['own_email_2'] = $accommodation->email_two : $array_temp;
        (isset($accommodation->mobile) && $accommodation->mobile != "") ? $array_temp['own_mobile_number'] = $accommodation->mobile : $array_temp;
        (isset($accommodation->phone) && $accommodation->phone != "") ? $array_temp['own_phone_number'] = $accommodation->phone : $array_temp;
        ($accommodation->licence != "") ? $array_temp['own_licence_number'] = $accommodation->licence : $array_temp;
        ($accommodation->number != "") ? $array_temp['own_address_number'] = $accommodation->number : $array_temp;
        ($accommodation->street != "") ? $array_temp['own_address_street'] = $accommodation->street : $array_temp;
        ($accommodation->between_street_one != "") ? $array_temp['own_address_between_street_1'] = $accommodation->between_street_one : $array_temp;
        ($accommodation->between_street_two != "") ? $array_temp['own_address_between_street_2'] = $accommodation->between_street_two : $array_temp;
        ($accommodation->province != "") ? $array_temp['own_address_province'] = $accommodation->province : $array_temp;
        ($accommodation->municipality != "") ? $array_temp['own_address_municipality'] = $accommodation->municipality : $array_temp;
        ($accommodation->destination != "") ? $array_temp['own_destination'] = $accommodation->destination : $array_temp;
        ($accommodation->languages != "") ? $array_temp['own_langs'] = $accommodation->languages : $array_temp;
        ($accommodation->category != "") ? $array_temp['own_category'] = $accommodation->category : $array_temp;
        ($accommodation->type != "") ? $array_temp['own_type'] = $accommodation->type : $array_temp;
        ($accommodation->status != "") ? $array_temp['own_status'] = $accommodation->status : $array_temp;
        ($accommodation->breakfast != "") ? $array_temp['own_facilities_breakfast'] = $accommodation->breakfast : $array_temp;
        (isset($accommodation->priceBreakfast) && $accommodation->priceBreakfast != "") ? $array_temp['own_facilities_breakfast_price'] = $accommodation->priceBreakfast : $array_temp;
        ($accommodation->dinner != "") ? $array_temp['own_facilities_dinner'] = $accommodation->dinner : $array_temp;
        (isset($accommodation->price_from_dinner) && $accommodation->price_from_dinner != "") ? $array_temp['own_facilities_dinner_price_from'] = $accommodation->price_from_dinner : $array_temp;
        (isset($accommodation->price_to_dinner) && $accommodation->price_to_dinner != "") ? $array_temp['own_facilities_dinner_price_to'] = $accommodation->price_to_dinner : $array_temp;
        ($accommodation->parking != "") ? $array_temp['own_facilities_parking'] = $accommodation->parking : $array_temp;
        (isset($accommodation->priceParking) && $accommodation->priceParking != "") ? $array_temp['own_facilities_parking_price'] = $accommodation->priceParking : $array_temp;
        ($accommodation->parking_cycles != "") ? $array_temp['own_description_bicycle_parking'] = $accommodation->parking_cycles : $array_temp;
        ($accommodation->mascot != "") ? $array_temp['own_description_pets'] = $accommodation->mascot : $array_temp;
        ($accommodation->laundry != "") ? $array_temp['own_description_laundry'] = $accommodation->laundry : $array_temp;
        ($accommodation->internet != "") ? $array_temp['own_description_internet'] = $accommodation->internet : $array_temp;
        ($accommodation->jacuzee != "") ? $array_temp['own_water_jacuzee'] = $accommodation->jacuzee : $array_temp;
        ($accommodation->sauna != "") ? $array_temp['own_water_sauna'] = $accommodation->sauna : $array_temp;
        ($accommodation->pool != "") ? $array_temp['own_water_piscina'] = $accommodation->pool : $array_temp;
        ($accommodation->coupon != "") ? $array_temp['own_cubacoupon'] = $accommodation->coupon : $array_temp;
//la longitud esta mal puesta(se hizo asi porque desde el principio estubo mal) debe ser: longitud = x, tatitud:y
        (isset($accommodation->longitud) && $accommodation->longitud != "") ? $array_temp['own_geolocate_y'] = $accommodation->longitud : $array_temp;
        (isset($accommodation->latitud) && $accommodation->latitud != "") ? $array_temp['own_geolocate_x'] = $accommodation->latitud : $array_temp;
        (isset($accommodation->commission_percent) && $accommodation->commission_percent != "") ? $array_temp['own_commission_percent'] = $accommodation->commission_percent : $array_temp;
        $create = (new \DateTime())->format('Y-m-d');
        $array_temp['own_creation_date'] = $create;

        $code = '';
        if (count($array_temp)) {
            if (array_key_exists('own_address_province', $array_temp)) {
                $provCode = self::getTableById('province', 'prov_id', $array_temp['own_address_province'], 'fetch');
                if ($provCode != false) {
                    $code = $provCode['prov_own_code'];
                    $query = "SELECT MAX(SUBSTRING(ownership.own_mcp_code, 3)*1) AS code FROM ownership WHERE ownership.own_mcp_code LIKE :mycode";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindValue('mycode', "%" . $code . "%");
                    $stmt->execute();
                    $codeMCP = $stmt->fetch();
                    if ($codeMCP != false) {
                        $str_number = $codeMCP['code'];
                        $number = (int)$str_number;
                        $number++;

                        $str_number = ''.$number;
                        if($number < 100){
                            $str_number = str_pad($str_number, 3, "0", STR_PAD_LEFT);
                        }

                        $code = $code . $str_number;
                        $array_temp['own_mcp_code'] = $code;
                    }
                }
            }
        }

        $this->conn->insert('ownership', $array_temp);
        $id = $this->conn->lastInsertId();
        $rooms = (isset($accommodation->rooms) && $accommodation->rooms != "") ? $accommodation->rooms : [];
        $this->addRoomsToOwnership($rooms, $id);

        $date = new \DateTime();
        $m = 'Casa con código '.$array_temp['own_mcp_code'].' adicionada desde la app movil por usuario:'.$valid['user_name'].' el dia:'.$date->format('Y-m-d H:i:s.u e');
        $this->insertLog(BackendModuleName::MODULE_OWNERSHIP, Operations::SAVE_AND_NEW, 'ownership', $m, $valid['user_id']);

        return array('success' => true, 'own_mcp_code' => $code, 'own_id' => $id);
    }

    public function saveImages($user, $pass, $code, $images_base64, $container, $host){

        $desId = $this->getDestinationIdByOwnershipCode($code);
        if($desId == false){
            return array("success"=>false);
        }

        $encrypt_password = self::encryptPassword($pass);
        $valid = $this->isValidUser($user, $encrypt_password, null);
        if(!$valid){
            return array('success' => false, 'code' => Response::HTTP_UNAUTHORIZED, 'message' => 'No tiene permisos para adicionar imagenes');
        }

        $path = $container->getParameter('ownership.dir.photos.originals');
        $subPath = strtolower($code);

        $fullPath = $path.$subPath;;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        if(isset($images_base64)){
            $order = 0;
            foreach ($images_base64 as $key=>$image_base64) {
                $name = "image_".$key."_";
                $path_complete = $fullPath."/".$name;
                $base64     = $image_base64;

                $image      = imagecreatefromstring(base64_decode($base64));
                try {
                    imagepng($image, $path_complete);
                    $webDirPhotoFull = $host.$this->resizeAndWatermark($name, $container, $subPath);

                    $this->saveImagesInDb($code, $webDirPhotoFull, $order);

                    $order++;
                }
                catch(Exception $e){
                    throw $e;
                }
            }
        }

        return array("success"=>true);
    }

    /**
     * Adicionar habitación a una propiedad.
     * @param $rooms
     * @param $ownId
     * @return array
     */
    public function addRoomsToOwnership($rooms, $ownId)
    {
        foreach ($rooms as $room) {
            $array_room = array();
            (isset($room->number) && $room->number != "") ? $array_room['room_num'] = $room->number : $array_room;
            ($room->room_type != "") ? $array_room['room_type'] = $room->room_type : $array_room;
            ($room->active != "") ? $array_room['room_active'] = $room->active : $array_room;
            (isset($room->beds_number) && $room->beds_number != "") ? $array_room['room_beds'] = $room->beds_number : $array_room;
            (isset($room->price_high_season) && $room->price_high_season != "") ? $array_room['room_price_up_to'] = $room->price_high_season : $array_room;
            (isset($room->price_low_season) && $room->price_low_season != "") ? $array_room['room_price_down_to'] = $room->price_low_season : $array_room;
            ($room->climate != "") ? $array_room['room_climate'] = $room->climate : $array_room;
            ($room->audiovisual != "") ? $array_room['room_audiovisual'] = $room->audiovisual : $array_room;
            ($room->smoker != "") ? $array_room['room_smoker'] = $room->smoker : $array_room;
            ($room->safe != "") ? $array_room['room_safe'] = $room->safe : $array_room;
            ($room->facilities_bb != "") ? $array_room['room_baby'] = $room->facilities_bb : $array_room;
            ($room->bathroom_type != "") ? $array_room['room_bathroom'] = $room->bathroom_type : $array_room;
            ($room->terrace != "") ? $array_room['room_terrace'] = $room->terrace : $array_room;
            ($room->yard != "") ? $array_room['room_yard'] = $room->yard : $array_room;
            (isset($room->window) && $room->window != "") ? $array_room['room_windows'] = $room->window : $array_room;
            (isset($room->balcony) && $room->balcony != "") ? $array_room['room_balcony'] = $room->balcony : $array_room;
            $array_room['room_ownership'] = $ownId;

            $this->conn->insert('room', $array_room);
        }

        return array('success' => true);
    }

    /**
     * Adicionar la No Disponibilidad de una habitación por rango
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function addavailableroombyrangex($request, $code){
        $start = $request->request->get('start');
        $end = $request->request->get('end');

        /*Obtener la no disponibilidad que el start cae dentro de la no disponibilidad*/
        $query = "SELECT unavailabilitydetails.ud_id, unavailabilitydetails.ud_to_date, unavailabilitydetails.ud_sync_st,  unavailabilitydetails.ud_reason FROM unavailabilitydetails";
        $whereAndLimit = " WHERE room_id = :room_id AND ud_from_date < :start AND ud_to_date >= :start LIMIT 1";
        $stmt = $this->conn->prepare($query.$whereAndLimit);
        $stmt->bindValue('room_id', $code);
        $stmt->bindValue('start', $start);
        $stmt->execute();
        $unavailability = $stmt->fetch();

        if($unavailability != false){
            $ud_to_date_new = (new \DateTime($start))->modify('-1 day')->format('Y-m-d');
            $query = "UPDATE unavailabilitydetails SET  ud_to_date = :ud_to_date";
            $stmt = $this->conn->prepare($query.$whereAndLimit);
            $stmt->bindValue('ud_to_date', $ud_to_date_new);
            $stmt->bindValue('room_id', $code);
            $stmt->bindValue('start', $start);
            $stmt->execute();

            if((new \DateTime($unavailability['ud_to_date'])) > (new \DateTime($end))){
                $ud_from_date = (new \DateTime($end))->modify('+1 day')->format('Y-m-d');

                $query = "INSERT INTO unavailabilitydetails (room_id, ud_sync_st, ud_from_date, ud_to_date, ud_reason) VALUE (:room_id, :ud_sync_st, :ud_from_date, :ud_to_date, :ud_reason)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue('room_id', $code);
                $stmt->bindValue('ud_sync_st', $unavailability['ud_sync_st']);
                $stmt->bindValue('ud_from_date', $ud_from_date);
                $stmt->bindValue('ud_to_date', $unavailability['ud_to_date']);
                $stmt->bindValue('ud_reason', $unavailability['ud_reason']);
                $stmt->execute();
            }
        }

        /*Actualizar la no disponibilidades que el end cae dentro de la no disponibilidad*/
        $ud_from_date = (new \DateTime($end))->modify('+1 day')->format('Y-m-d');

        $query = "UPDATE unavailabilitydetails SET ud_from_date = :ud_from_date WHERE room_id = :room_id AND ud_from_date <= :end AND ud_to_date > :end LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('ud_from_date', $ud_from_date);
        $stmt->bindValue('room_id', $code);
        $stmt->bindValue('end', $end);
        $stmt->execute();

        /*elimino las que estan en el rango*/
        $query = "DELETE FROM unavailabilitydetails WHERE room_id = :room_id AND ud_from_date >= :start AND ud_from_date <= :end AND ud_to_date >= :start AND ud_to_date <= :end ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('room_id', $code);
        $stmt->bindValue('start', $start);
        $stmt->bindValue('end', $end);
        $stmt->execute();

        /*incerto las no disponibilidades new*/
        $unavailabilities=json_decode($request->request->get('date_range'));
        $reason = $request->request->get('reason');
        $reason = isset($reason) ? $reason : '';
        foreach ($unavailabilities as $unavailability) {
            $this->conn->insert('unavailabilitydetails', array('room_id' => $code, 'ud_sync_st' => 0, 'ud_from_date' => $unavailability->start, 'ud_to_date' => $unavailability->end, 'ud_reason' => $reason));
        }

        return $this->view->create(array('success' => true), 200);
    }

    private function extractObjets($ownershipRoom){
        $ownership = array();
        $room = array();

        foreach ($ownershipRoom as $key => $value) {
            $continue = true;
            $index = strpos($key, 'own_');
            if($index === 0){
                $ownership[$key] = $value;
                $continue = false;
            }

            if($continue){
                $index = strpos($key, 'room_');
                if($index === 0){
                    $room[$key] = $value;
                }
            }
        }

        return array('ownership'=>$ownership, 'room'=>$room);
    }

    /**
     * Funcion que retorna las no disponibilidades de una room.
     * @param $room_id
     * @param $from_date
     * @param $to_date
     * @return mixed
     * @throws \Exception
     */
    public function getUDetailsByRoom($room_id, $from_date, $to_date)
    {
        try {
            $queryRooms = "SELECT unavailabilitydetails.ud_id,unavailabilitydetails.ud_from_date,unavailabilitydetails.ud_to_date
FROM unavailabilitydetails
WHERE
  unavailabilitydetails.room_id = :room_id AND
  (unavailabilitydetails.ud_from_date >= :from_date AND unavailabilitydetails.ud_from_date <= :to_date OR
  unavailabilitydetails.ud_to_date >= :from_date AND unavailabilitydetails.ud_to_date <= :to_date)";

            $stmtRooms = $this->conn->prepare($queryRooms);
            $stmtRooms->bindValue('room_id', $room_id);
            $stmtRooms->bindValue('from_date', $from_date);
            $stmtRooms->bindValue('to_date', $to_date);
            $stmtRooms->execute();
            $resRooms = $stmtRooms->fetchAll();

            return $resRooms;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Funcion que retorna las reservas de una room.
     * @param $room_id
     * @param $from_date
     * @param $to_date
     * @param int $status_cancel
     * @param int $status_reserv
     * @return mixed
     * @throws \Exception
     */
    public function getReservationsByRoom($room_id, $from_date, $to_date, $status_cancel = 4, $status_reserv = 5)
    {
        try {
            $queryReservations = "SELECT ownershipreservation.own_res_id,ownershipreservation.own_res_status,ownershipreservation.own_res_reservation_from_date,
  ownershipreservation.own_res_reservation_to_date FROM ownershipreservation
WHERE
  ownershipreservation.own_res_selected_room_id = :room_id AND ownershipreservation.own_res_status=:own_res_status_reserva AND
  (ownershipreservation.own_res_reservation_from_date >= :from_date AND
  ownershipreservation.own_res_reservation_from_date <= :to_date OR
  ownershipreservation.own_res_reservation_to_date >= :from_date AND
  ownershipreservation.own_res_reservation_to_date <= :to_date)";

            $stmtReservations = $this->conn->prepare($queryReservations);
            $stmtReservations->bindValue('room_id', $room_id);
            //$stmtReservations->bindValue('own_res_status_cancel', $status_cancel);
            $stmtReservations->bindValue('own_res_status_reserva', $status_reserv);
            $stmtReservations->bindValue('from_date', $from_date);
            $stmtReservations->bindValue('to_date', $to_date);
            $stmtReservations->execute();
            $resReservations = $stmtReservations->fetchAll();

            foreach ($resReservations as &$reservation) {
                $date = new \DateTime($reservation['own_res_reservation_to_date']);
                $date->modify('-1 day');
                $reservation['own_res_reservation_to_date'] = $date->format('Y-m-d');

                $reservation['own_res_status'] = ($reservation['own_res_status'] == 5) ? 'Reservada' : 'Cancelada';
            }

            return $resReservations;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function isValidUser($user, $encrypt_password, $desId){
        /*$user = $request->request->get('user');
        $encrypt_password = self::encryptPassword($request->request->get('password'));*/

        $queryUser = "SELECT user.user_id, user.user_name FROM user
  INNER JOIN userstaffmanager ON (user.user_id = userstaffmanager.user_staff_manager_user)
  INNER JOIN userstaffmanager_destination ON (userstaffmanager.user_staff_manager_id = userstaffmanager_destination.user_staff_manager)
  INNER JOIN destination ON (userstaffmanager_destination.destination = destination.des_id)
WHERE user_name=:user_name AND user_password=:user_password ";

        if(isset($desId)){
            $queryUser .= "AND destination.des_id = :des_id";
        }

        $stmtUser = $this->conn->prepare($queryUser);
        $stmtUser->bindValue('user_name', $user);
        $stmtUser->bindValue('user_password', $encrypt_password);
        if(isset($desId)){
            $stmtUser->bindValue('des_id', $desId);
        }
        $stmtUser->execute();
        $resUser = $stmtUser->fetch();

        return ($resUser != false) ? $resUser : false;
    }

    public function getDestinationIdByOwnershipCode($code){
        try {
            $query= "SELECT
  ownership.own_id,
  destination.des_id,
  destination.des_name
FROM
  destination
  INNER JOIN ownership ON (destination.des_id = ownership.own_destination)
WHERE
  ownership.own_mcp_code = :code";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('code', $code);

            $stmt->execute();
            $po = $stmt->fetch();

            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function resizeAndWatermark($fileName, $container, $subPath = "") {
        $watermark_full_path = $container->getParameter('watermark.mycp.full.path');
        $dirPhotosOriginals = $container->getParameter('ownership.dir.photos.originals');
        $dirPhotos = $container->getParameter('cbs.dir.web').$container->getParameter('ownership.dir.photos');
        $new_height = $container->getParameter('ownership.height.photos');

        $dirPhotoOriginal = $dirPhotosOriginals.$subPath;
        $dirPhoto = $dirPhotos.$subPath;

        $dirPhotoOriginalFull = $dirPhotoOriginal."/".$fileName;
        $fileName .= '.jpeg';
        $dirPhotoFull = $dirPhoto."/".$fileName;

        $imagine = new Imagine();

        $this->createDirectoryIfNotExist($dirPhoto);
        $imagine->open($dirPhotoOriginalFull)->save($dirPhotoFull, array('format' => 'jpeg','quality' => 100));

        $new_width = $this->resize($dirPhotoFull, $new_height);

        $watermark = $imagine->open($watermark_full_path);
        $wSize = $watermark->getSize();
        if ($wSize->getWidth() > $new_width || ($new_width - $wSize->getWidth()) < 10 ) {
            $watermark_width = $wSize->getWidth() - 20;
            $watermark = $watermark->resize(new Box($watermark_width, ($wSize->getHeight() - 10)));
            $wSize = $watermark->getSize();
        }

        $point = new Point(($new_width - $wSize->getWidth() - 10), 10);

        $imagine->open($dirPhotoOriginalFull)->paste($watermark, $point)->save($dirPhotoFull, array('format' => 'jpeg','quality' => 100));

        return $container->getParameter('ownership.dir.photos').$subPath."/".$fileName;
    }

    public function saveImagesInDb($code, $webDirPhotoFull, $order){
        $array_photo = array();
        $array_photo['pho_name'] = $webDirPhotoFull;
        $array_photo['pho_order'] = $order;
        $array_photo['pho_notes'] = "upload from app movile";

        $this->conn->insert('photo', $array_photo);
        $id = $this->conn->lastInsertId();

        $query= "SELECT ownership.own_id FROM ownership WHERE ownership.own_mcp_code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('code', $code);
        $stmt->execute();
        $ownership = $stmt->fetch();

        $array_photo = array();
        $array_photo['own_pho_pho_id'] = $id;
        $array_photo['own_pho_own_id'] = $ownership['own_id'];
        $this->conn->insert('ownershipphoto', $array_photo);
    }

    private function microtime_float()
    {
        list($useg, $seg) = explode(" ", microtime());
        return ((float)$useg + (float)$seg);
    }




}