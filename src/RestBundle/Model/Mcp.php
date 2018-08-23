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
use RestBundle\Helpers\Date;
use RestBundle\Helpers\Operations;
use RestBundle\Helpers\Utils;
use RestBundle\Exception\InvalidFormException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use UserBundle\Entity\User;

class Mcp extends Base implements McpInterface {
    const STATUS_RESERVED = 2;      //status de la reserva

    /**
     * Función que devuelve los datos de una tabla
     * @param $table        Tabla que se quiere consultar
     * @param $field        Nombre del campo por el que se qiere filtrar
     * @param $value_field  Valor del campo
     * @param string        Operación que se va a realizar
     * //fetch all results in associative array format
     * $results = $statement->fetchAll();
     *
     * //fetch single row
     * $result = $statement->fetch();
     *
     * //total row count
     * $result = $statement->rowCount();
     * @return mixed
     */
    public function getTableById($table, $field, $value_field, $operation) {
        $query = "SELECT * FROM " . $table . " WHERE " . $field . "=:code; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('code', $value_field);
        $stmt->execute();
        if($operation == 'fetchAll')
            $po = $stmt->fetchAll();
        if($operation == 'fetch')
            $po = $stmt->fetch();
        if($operation == 'rowCount')
            $po = $stmt->rowCount();
        return $po;
    }

    /**
     * Para obtener los datos de una casa
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getAccommodation($request, $code) {
        if($request->query->get('user_casa') != 'null') {
            $query = "SELECT * FROM usercasa WHERE  user_casa_user=:user_casa_user; ";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('user_casa_user', $request->query->get('user_casa'));
            $stmt->execute();
            $po = $stmt->fetch();
            $code = $po['user_casa_ownership'];
        }

        $select = "o.own_id,";
        $select_room = "";
        $innerJoin = "";
        $rooms = array();
        ($request->query->get('code_own') == 'true' || $request->query->get('code_own') == '') ? $select .= 'o.own_mcp_code,' : $select;
        ($request->query->get('name') == 'true' || $request->query->get('name') == '') ? $select .= 'o.own_name,' : $select;
        ($request->query->get('address_street') == 'true' || $request->query->get('address_street') == '') ? $select .= 'o.own_address_street,' : $select;
        ($request->query->get('address_number') == 'true' || $request->query->get('address_number') == '') ? $select .= 'o.own_address_number,' : $select;
        ($request->query->get('address_between_street_1') == 'true' || $request->query->get('address_between_street_1') == '') ? $select .= 'o.own_address_between_street_1,' : $select;
        ($request->query->get('address_between_street_2') == 'true' || $request->query->get('address_between_street_2') == '') ? $select .= 'o.own_address_between_street_2,' : $select;
        if($request->query->get('address_province') == 'true' || $request->query->get('address_province') == '') {
            $select .= 'p.prov_name,';
            $innerJoin .= "INNER JOIN province p ON o.own_address_province = p.prov_id ";
        }
        else $select;
        if($request->query->get('address_municipality') == 'true' || $request->query->get('address_municipality') == '') {
            $select .= 'm.mun_name,';
            $innerJoin .= "INNER JOIN municipality m ON o.own_address_municipality = m.mun_id ";
        }
        else $select;
        ($request->query->get('mobile_number') == 'true' || $request->query->get('mobile_number') == '') ? $select .= 'o.own_mobile_number,' : $select;
        ($request->query->get('phone_number') == 'true' || $request->query->get('phone_number') == '') ? $select .= 'o.own_phone_number,' : $select;
        ($request->query->get('email_1') == 'true' || $request->query->get('email_1') == '') ? $select .= 'o.own_email_1,' : $select;
        ($request->query->get('email_2') == 'true' || $request->query->get('email_2') == '') ? $select .= 'o.own_email_2,' : $select;
        if($request->query->get('list_room') == 'true' || $request->query->get('list_room') == '') { //Si mando a buscar la lista de habitaciones
            $select_room .= 'r.room_id,';
            $select_room .= 'r.room_type,';
            $select_room .= 'r.room_num,';
            ($request->query->get('price_up_to') == 'true' || $request->query->get('price_up_to') == '') ? $select_room .= 'r.room_price_up_to,' : $select_room;
            ($request->query->get('price_down_to') == 'true' || $request->query->get('price_down_to') == '') ? $select_room .= 'r.room_price_down_to,' : $select_room;
            ($request->query->get('climate') == 'true' || $request->query->get('climate') == '') ? $select_room .= 'r.room_climate,' : $select_room;
            ($request->query->get('audiovisual') == 'true' || $request->query->get('audiovisual') == '') ? $select_room .= 'r.room_audiovisual,' : $select_room;
            ($request->query->get('smoker') == 'true' || $request->query->get('smoker') == '') ? $select_room .= 'r.room_smoker,' : $select_room;
            ($request->query->get('safe') == 'true' || $request->query->get('safe') == '') ? $select_room .= 'r.room_safe,' : $select_room;
            ($request->query->get('baby_facility') == 'true' || $request->query->get('baby_facility') == '') ? $select_room .= 'r.room_baby,' : $select_room;
            ($request->query->get('bathroom_type') == 'true' || $request->query->get('bathroom_type') == '') ? $select_room .= 'r.room_bathroom,' : $select_room;
            ($request->query->get('stereo') == 'true' || $request->query->get('stereo') == '') ? $select_room .= 'r.room_stereo,' : $select_room;
            ($request->query->get('windows') == 'true' || $request->query->get('windows') == '') ? $select_room .= 'r.room_windows,' : $select_room;
            ($request->query->get('balcony') == 'true' || $request->query->get('balcony') == '') ? $select_room .= 'r.room_balcony,' : $select_room;
            ($request->query->get('terrace') == 'true' || $request->query->get('terrace') == '') ? $select_room .= 'r.room_terrace,' : $select_room;
            ($request->query->get('yard') == 'true' || $request->query->get('yard') == '') ? $select_room .= 'r.room_yard,' : $select_room;
        }
        if($request->query->get('user_casa') != 'null')
            $query = "SELECT " . substr($select, 0, -1) . " FROM ownership o " . $innerJoin . " WHERE o.own_id=:code; ";
        else
            $query = "SELECT " . substr($select, 0, -1) . " FROM ownership o " . $innerJoin . " WHERE o.own_mcp_code=:code; ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('code', $code);
        $stmt->execute();
        $ship = $stmt->fetchAll();
        if(count($ship)) {
            if($select_room != '') {
                $query = "SELECT " . substr($select_room, 0, -1) . " FROM room r WHERE r.room_ownership=:code; ";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue('code', $ship[0]['own_id']);
                $stmt->execute();
                $rooms = $stmt->fetchAll();
                $ship[0]['room'] = $rooms;
            }
            return $ship[0];
        }
        else
            throw new InvalidFormException('The ownership no exist.');
    }

    /**
     * Editar datos de contacto de una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function putContactaccommodation($request, $code) {
        $array_temp = array();
        ($request->request->get('mobile') != "") ? $array_temp['own_mobile_number'] = $request->request->get('mobile') : $array_temp;
        ($request->request->get('phone') != "") ? $array_temp['own_phone_number'] = $request->request->get('phone') : $array_temp;
        ($request->request->get('email_1') != "") ? $array_temp['own_email_1'] = $request->request->get('email_1') : $array_temp;
        ($request->request->get('email_2') != "") ? $array_temp['own_email_2'] = $request->request->get('email_2') : $array_temp;
        if(count($array_temp))
            $this->conn->update('ownership', $array_temp, array('own_mcp_code' => $code));
        return array('success' => true);
    }

    /**
     * Editar datos de una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function editOwnership($request, $code) {
        $array_temp = array();
        ($request->request->get('owner2') != "") ? $array_temp['own_homeowner_2'] = $request->request->get('owner2') : $array_temp;
        ($request->request->get('email_1') != "") ? $array_temp['own_email_1'] = $request->request->get('email_1') : $array_temp;
        ($request->request->get('email_2') != "") ? $array_temp['own_email_2'] = $request->request->get('email_2') : $array_temp;
        ($request->request->get('mobile') != "") ? $array_temp['own_mobile_number'] = $request->request->get('mobile') : $array_temp;
        ($request->request->get('phone') != "") ? $array_temp['own_phone_number'] = $request->request->get('phone') : $array_temp;
        ($request->request->get('languages') != "") ? $array_temp['own_langs'] = $request->request->get('languages') : $array_temp;
        ($request->request->get('category') != "") ? $array_temp['own_category'] = $request->request->get('category') : $array_temp;
        ($request->request->get('own_type') != "") ? $array_temp['own_type'] = $request->request->get('own_type') : $array_temp;
        ($request->request->get('breakfast') != "") ? $array_temp['own_facilities_breakfast'] = $request->request->get('breakfast') : $array_temp;
        ($request->request->get('breakfastPrice') != "") ? $array_temp['own_facilities_breakfast_price'] = $request->request->get('breakfastPrice') : $array_temp;
        ($request->request->get('dinner') != "") ? $array_temp['own_facilities_dinner'] = $request->request->get('breakfast') : $array_temp;
        ($request->request->get('dinnerMinPrice') != "") ? $array_temp['own_facilities_dinner_price_from'] = $request->request->get('dinnerMinPrice') : $array_temp;
        ($request->request->get('dinnerMaxPrice') != "") ? $array_temp['own_facilities_dinner_price_to'] = $request->request->get('dinnerMaxPrice') : $array_temp;
        ($request->request->get('parking') != "") ? $array_temp['own_facilities_parking'] = $request->request->get('parking') : $array_temp;
        ($request->request->get('parkingPrice') != "") ? $array_temp['own_facilities_parking_price'] = $request->request->get('parkingPrice') : $array_temp;
        ($request->request->get('parkingCycle') != "") ? $array_temp['own_description_bicycle_parking'] = $request->request->get('parkingCycle') : $array_temp;
        ($request->request->get('pets') != "") ? $array_temp['own_description_pets'] = $request->request->get('pets') : $array_temp;
        ($request->request->get('laundry') != "") ? $array_temp['own_description_laundry'] = $request->request->get('laundry') : $array_temp;
        ($request->request->get('internetService') != "") ? $array_temp['own_description_internet'] = $request->request->get('internetService') : $array_temp;
        ($request->request->get('otherServices') != "") ? $array_temp['own_facilities_notes'] = $request->request->get('otherServices') : $array_temp;
        ($request->request->get('jacuzzi') != "") ? $array_temp['own_water_jacuzee'] = $request->request->get('jacuzzi') : $array_temp;
        ($request->request->get('sauna') != "") ? $array_temp['own_water_sauna'] = $request->request->get('sauna') : $array_temp;
        ($request->request->get('pool') != "") ? $array_temp['own_water_piscina'] = $request->request->get('pool') : $array_temp;
        if(count($array_temp)) {
            $this->conn->update('ownership', $array_temp, array('own_mcp_code' => $code));
            return array('success' => true, 'code' => $code);
        }


        return array('success' => true, 'message' => "Nothing to update");
    }

    /**
     * Eliminar una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function deleteOwnership($request, $code) {
        $password = $request->request->get('password');
        try {
            $ownership = self::getTableById('ownership', 'own_mcp_code', $code, 'fetch');
            $userCasa = self::getTableById('usercasa', 'user_casa_ownership', $ownership['own_id'], 'fetch');

            $user = self::getTableById('user', 'user_id', $userCasa['user_casa_user'], 'fetch');
            if($user['user_password'] != $password)
                return array('success' => false, 'message' => 'Invalid data');
            $this->conn->update('ownership', array('own_status' => 4), array('own_mcp_code' => $code));
            return array('success' => true);
        }
        catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }

    }

    /**
     * Registrar propietario de una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function registerOwner($request, $code) {
        //todo

    }

    /**
     * Cambiar estado de una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function setOwnershipStatus($request, $code) {
        $array_temp = array();
        ($request->request->get('status') != "") ? $array_temp['own_status'] = $request->request->get('status') : $array_temp;

        try {
            $this->conn->update('ownership', $array_temp, array('own_mcp_code' => $code));
            return array('success' => true);
        }
        catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }

    }

    /**
     * Cambiar estado de una habitación.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function setOwnershipRoomStatus($request, $code) {
        $array_temp = array();
        $array_set = array();
        ($request->request->get('roomNumber') != "") ? $array_temp['room_num'] = $request->request->get('roomNumber') : $array_temp;
        ($request->request->get('status') != "") ? $array_temp['room_active'] = $request->request->get('status') : $array_temp;
        try {
            $ownership = self::getTableById('ownership', 'own_mcp_code', $code, 'fetch');
            if($ownership != false) {
                $array_set['room_ownership'] = $ownership['own_id'];
                ($request->request->get('roomNumber') != "") ? $array_set['room_num'] = $request->request->get('roomNumber') : '';

            }
            else {
                return array('success' => false, 'message' => 'Ownership code not found');
            }
            $this->conn->update('room', $array_temp, $array_set);
            return array('success' => true);
        }
        catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }

    }

    /**
     * Cambiar estado de una habitación.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function getOwnershipRooms($request, $code) {
        $array_set = array();
        try {
            $ownership = self::getTableById('ownership', 'own_mcp_code', $code, 'fetch');
            if($ownership != false) {
                $array_set['room_ownership'] = $ownership['own_id'];
                $rooms = self::getTableById('room', 'room_ownership', $array_set['room_ownership'], 'fetchAll');
                return $this->view->create($rooms, 200);
            }
            else {
                return array('success' => false, 'message' => 'Ownership code not found');
            }

        }
        catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }

    }

    /**
     * Enviar solicitud de cambio para una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function postSolicitude($fields, $code) {
    }

    /**
     * Adicionar la No Disponibilidad de una habitación
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function postAddavailableroom($request, $code) {
        $fields = array();
        $temp = self::getTableById('room', 'room_id', $code, 'fetchAll');
        if(count($temp)) {
            if($request->request->get('from_date') != '') {
                $fields['from_date'] = $request->request->get('from_date');
            }
            else {
                throw new InvalidFormException();
            }
            if($request->request->get('to_date') != '') {
                $fields['to_date'] = $request->request->get('to_date');
            }
            else {
                throw new InvalidFormException();
            }
            if($request->request->get('reason') != '') {
                $fields['reason'] = $request->request->get('reason');
            }

            $date_from = Date::createFromString($fields['from_date']);
            $date_to = Date::createFromString($fields['to_date']);
            if($date_from > $date_to)
                throw new InvalidFormException('From date must be less than or equal to the To date.');
            /* if($date_from<new \DateTime())
                 throw new InvalidFormException('Start date must be greater than the current date.');*/
            else {
                //Find avaible room by code
                $temp = self::getTableById('unavailabilitydetails', 'room_id', $code, 'fetchAll');
                if(count($temp)) {
                    foreach ($temp as $item) {
                        //Caso 1 son iguales edito la razón
                        if((Date::createFromString($item['ud_from_date'], '-', 1) == $date_from) && (Date::createFromString($item['ud_to_date'], '-', 1) == $date_to))
                            $this->conn->update('unavailabilitydetails', array('ud_reason' => (isset($fields['reason'])) ? $fields['reason'] : $item['ud_reason']), array('ud_id' => $item['ud_id']));
                        //Caso 2 la fecha de inicio esta dentro del rango de fecha inicio y fecha fin del las guardadas en BD y la fecha fin guardada es menor que la entrada en este caso actualizo la razón y la fecha fin
                        else if((Date::createFromString($item['ud_from_date'], '-', 1) <= $date_from) && (Date::createFromString($item['ud_to_date'], '-', 1) >= $date_from) && (Date::createFromString($item['ud_to_date'], '-', 1) <= $date_to))
                            $this->conn->update('unavailabilitydetails', array('ud_reason' => (isset($fields['reason'])) ? $fields['reason'] : $item['ud_reason'], 'ud_to_date' => date_format($date_to, 'Y-m-d')), array('ud_id' => $item['ud_id']));
                        //Caso 3 la fecha de fin esta dentro del rango de fecha inicio y fecha fin del las guardadas en BD y la fecha de inicio guardada es mayor que la fecha de inicio en este caso actualizo la razón y la fecha inicio
                        else if((Date::createFromString($item['ud_from_date'], '-', 1) <= $date_to) && (Date::createFromString($item['ud_to_date'], '-', 1) >= $date_to) && (Date::createFromString($item['ud_from_date'], '-', 1) >= $date_from))
                            $this->conn->update('unavailabilitydetails', array('ud_reason' => (isset($fields['reason'])) ? $fields['reason'] : $item['ud_reason'], 'ud_from_date' => date_format($date_from, 'Y-m-d')), array('ud_id' => $item['ud_id']));
                        else
                            $this->conn->insert('unavailabilitydetails', array('room_id' => $code, 'ud_sync_st' => 0, 'ud_from_date' => date_format($date_from, 'Y-m-d'), 'ud_to_date' => date_format($date_to, 'Y-m-d'), 'ud_reason' => (isset($fields['reason'])) ? $fields['reason'] : ''));
                    }
                }
                else
                    $this->conn->insert('unavailabilitydetails', array('room_id' => $code, 'ud_sync_st' => 0, 'ud_from_date' => date_format($date_from, 'Y-m-d'), 'ud_to_date' => date_format($date_to, 'Y-m-d'), 'ud_reason' => (isset($fields['reason'])) ? $fields['reason'] : ''));
            }
            return $this->view->create(array('success' => true), 200);
        }
        else
            throw new InvalidFormException('The room no exist.');
    }

    /**
     * Eliminar, la No Disponibilidad de una habitación.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return void
     */
    public function deleteDelavailableroom($request, $code) {
        $fields = array();
        if($request->request->get('from_date') != '') {
            $fields['from_date'] = $request->request->get('from_date');
        }
        else {
            throw new InvalidFormException();
        }
        if($request->request->get('to_date') != '') {
            $fields['to_date'] = $request->request->get('to_date');
        }
        else {
            throw new InvalidFormException();
        }
        if($request->request->get('ud_id') != '') {
            $fields['ud_id'] = $request->request->get('ud_id');
        }

        $date_from = Date::createFromString($fields['from_date']);
        $date_to = Date::createFromString($fields['to_date']);
        if($date_from > $date_to)
            throw new InvalidFormException('From date must be less than or equal to the To date.');
        else {
            //Find avaible room by code
            $temp = self::getTableById('unavailabilitydetails', 'room_id', $code, 'fetchAll');
            if(count($temp)) {
                foreach ($temp as $item) {
                    //Busco la no disponibilidad
                    if((Date::createFromString($item['ud_from_date'], '-', 1) == $date_from) && (Date::createFromString($item['ud_to_date'], '-', 1) == $date_to))
                        $this->conn->delete('unavailabilitydetails', array('ud_id' => $item['ud_id']));
                }
            }
            else
                throw new InvalidFormException('There is no non availability in the date range indicated.');
        }
        return $this->view->create(array(), 200);
    }

    /**
     * Consultar, la No Disponibilidad de una habitación.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return void
     */
    public function getAvailable($request, $code) {
        $fields = array();
        ($request->query->get('from_date') != '') ? $fields['from_date'] = $request->query->get('from_date') : $fields;
        ($request->query->get('to_date') != '') ? $fields['to_date'] = $request->query->get('to_date') : $fields;
        $temp = self::getTableById('room', 'room_id', $code, 'fetchAll');
        if(count($temp)) {
            if(count($fields)) {
                $date_from = (isset($fields['from_date'])) ? Date::createFromString($fields['from_date']) : "";
                $date_to = (isset($fields['to_date'])) ? Date::createFromString($fields['to_date']) : "";
                $po = "";
                //Se devuelven todas las no disponibilidades cuyas fechas sea mayor que la fecha de inicio
                if($date_from != '' && $date_to == '') {
                    $query = "SELECT * FROM unavailabilitydetails WHERE ud_from_date>=:from_date AND room_id=:room_id; ";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindValue('from_date', date_format($date_from, 'Y-m-d'));
                    $stmt->bindValue('room_id', $code);
                    $stmt->execute();
                    $po = $stmt->fetchAll();
                }
                //Se devuelven todas las no disponibilidades cuyas fechas sea menores que la fecha de fin
                if($date_from == '' && $date_to != '') {
                    $query = "SELECT * FROM unavailabilitydetails WHERE ud_to_date<=:to_date AND room_id=:room_id; ";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindValue('to_date', date_format($date_to, 'Y-m-d'));
                    $stmt->bindValue('room_id', $code);
                    $stmt->execute();
                    $po = $stmt->fetchAll();
                }
                //Se devuelven todas las no disponibilidades que esten dentro del rango solicitado
                if($date_from != '' && $date_to != '') {
                    $query = "SELECT * FROM unavailabilitydetails WHERE  ud_from_date>=:from_date AND ud_to_date<=:to_date AND room_id=:room_id; ";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindValue('from_date', date_format($date_from, 'Y-m-d'));
                    $stmt->bindValue('to_date', date_format($date_to, 'Y-m-d'));
                    $stmt->bindValue('room_id', $code);
                    $stmt->execute();
                    $po = $stmt->fetchAll();
                }
                return $this->view->create($po, 200);
            }
            else {
                //Find avaible room by code
                $temp = self::getTableById('unavailabilitydetails', 'room_id', $code, 'fetchAll');
                if(count($temp)) {
                    return $this->view->create($temp, 200);
                }
                else
                    throw new InvalidFormException('There is no non availability in the date range indicated.');
            }
            return $this->view->create(array(), 200);
        }
        else
            throw new InvalidFormException('The room no exist.');
    }

    /**
     * Obtener listado de reservas de una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getReservationaccommodation($fields, $code) {
        $query = "SELECT
                  gr.gen_res_id,
                  gr.gen_res_date,
                  gr.gen_res_from_date,
                  gr.gen_res_to_date,
                  gr.gen_res_status,
                  gr.gen_res_nights,
                  gr.gen_res_own_id,
                  owr.own_res_id,
                  owr.own_res_count_adults,
                  owr.own_res_count_childrens,
                  owr.own_res_nights,
                  owr.own_res_night_price,
                  owr.own_res_selected_room_id,
                  u.user_user_name,
                  u.user_last_name,
                  u.user_email,
                  u.user_city,
                  ct.co_name,
                  curr.curr_code,
                  owr.own_res_id,
                  owr.own_res_count_adults,
                  owr.own_res_count_childrens,
                  owr.own_res_nights,
                  owr.own_res_night_price,
                  owr.own_res_selected_room_id
                  FROM generalreservation gr
                  INNER JOIN ownershipreservation owr ON gr.gen_res_id = owr.own_res_gen_res_id
                  INNER JOIN ownership ON ownership.own_id = gr.gen_res_own_id
                  INNER JOIN user u ON u.user_id = gr.gen_res_user_id
                  INNER JOIN usertourist ut ON u.user_id = ut.user_tourist_user
                  INNER JOIN country ct ON ct.co_id = u.user_country
                  INNER JOIN currency curr ON curr.curr_id = ut.user_tourist_currency
                  WHERE ownership.own_mcp_code=:own_id;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_id', $code);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $this->view->create($po, 200);
    }

    public function getReservation($fields, $code) {
    }

    /**
     * Obtener detalles de una reserva.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code identificador de la reserva
     * @return array
     */
    public function getDetailsreservation($fields, $code) {
        $query = "SELECT
                  gr.gen_res_id,
                  gr.gen_res_date,
                  gr.gen_res_from_date,
                  gr.gen_res_to_date,
                  gr.gen_res_status,
                  gr.gen_res_nights,
                  gr.gen_res_own_id,
                  u.user_user_name,
                  u.user_last_name,
                  u.user_email,
                  u.user_city,
                  ct.co_name,
                  curr.curr_code,
                  owr.own_res_id,
                  owr.own_res_count_adults,
                  owr.own_res_count_childrens,
                  owr.own_res_nights,
                  owr.own_res_night_price,
                  owr.own_res_selected_room_id
                  FROM generalreservation gr
                  INNER JOIN ownershipreservation owr ON gr.gen_res_id = owr.own_res_gen_res_id
                  INNER JOIN user u ON u.user_id = gr.gen_res_user_id
                  INNER JOIN usertourist ut ON u.user_id = ut.user_tourist_user
                  INNER JOIN country ct ON ct.co_id = u.user_country
                  INNER JOIN currency curr ON curr.curr_id = ut.user_tourist_currency
                  WHERE gr.gen_res_id=:reservation_id;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('reservation_id', $code);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $this->view->create($po, 200);
    }

    /**
     * Obtener listado de clientes de una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getListclientaccommodation($fields, $code) {
        $query = "SELECT
                u.user_name,
                u.user_last_name,
                u.user_email,
                ct.co_name,
                lang.lang_name,
                (SELECT COUNT(ownershipreservation.own_res_id) FROM ownershipreservation INNER JOIN generalreservation ON generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id WHERE generalreservation.gen_res_user_id=u.user_id AND ownershipreservation.own_res_status=5) AS reservas
                FROM
                ownershipreservation owr
                INNER JOIN generalreservation gr ON gr.gen_res_id = owr.own_res_gen_res_id
                INNER JOIN user u ON u.user_id = gr.gen_res_user_id
                INNER JOIN usertourist ut ON u.user_id = ut.user_tourist_user
                INNER JOIN ownership own ON own.own_id = gr.gen_res_own_id
                INNER JOIN country ct ON ct.co_id = u.user_country
                INNER JOIN lang lang ON lang.lang_id = ut.user_tourist_language
                WHERE own.own_mcp_code=:own_code AND  (SELECT COUNT(ownershipreservation.own_res_id) FROM ownershipreservation INNER JOIN generalreservation ON generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id WHERE generalreservation.gen_res_user_id=u.user_id AND ownershipreservation.own_res_status=5) >0;
                ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_code', $code);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $this->view->create($po, 200);
    }

    /**
     * Buscar clientes de una propiedad
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getClientaccommodation($request, $code) {
        $fields = array();
        ($request->query->get('client_name') != '') ? $fields['client_name'] = $request->query->get('client_name') : $fields;
        ($request->query->get('client_email') != '') ? $fields['client_email'] = $request->query->get('client_email') : $fields;
        ($request->query->get('client_country') != '') ? $fields['client_country'] = $request->query->get('client_country') : $fields;
        //Busco el codigo de la propiedad
        $accommodation = self::getTableById('ownership', 'own_mcp_code', $code, 'fetch');
        if($accommodation != false) {
            if(count($accommodation)) {
                $where = "g.gen_res_own_id<=:gen_res_own_id AND g.gen_res_status=:gen_res_status ";
                //InnerJoin
                $inner = "INNER JOIN user u ON g.gen_res_user_id=u.user_id ";
                $select = "g.gen_res_user_id AS id_user, u.user_user_name,u.user_last_name,u.user_email";
                (isset($fields['client_name'])) ? $where = $where . " AND u.user_user_name like :client_name " : $where;
                (isset($fields['client_email'])) ? $where = $where . " AND u.user_email like :client_email " : $where;
                if(isset($fields['client_country'])) {
                    $select .= ",c.co_name";
                    $where = $where . " AND c.co_name like :client_country ";
                    $inner .= "INNER JOIN country c ON u.user_country=c.co_id";
                };
                //Busco todos los clientes con reservaciones realizadas
                $query = "SELECT " . $select . " FROM  generalreservation g " . $inner . " WHERE " . $where . " ; ";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue('gen_res_own_id', $accommodation['own_id']);
                $stmt->bindValue('gen_res_status', self::STATUS_RESERVED);
                if(isset($fields['client_name']))
                    $stmt->bindValue('client_name', "%" . $fields['client_name'] . "%");
                if(isset($fields['client_email']))
                    $stmt->bindValue('client_email', "%" . $fields['client_email'] . "%");
                if(isset($fields['client_country']))
                    $stmt->bindValue('client_country', "%" . $fields['client_country'] . "%");
                $stmt->execute();
                $po = $stmt->fetchAll();

                $rest = array();
                $rest_temp = array();
                $total = sizeof($po);
                for ($i = 0; $i < $total; $i++) {
                    $valor = Utils::searchInArray($po[$i]['id_user'], $rest_temp, 'id_user');
                    if(is_int($valor))
                        $rest[$valor]['cant_reserva'] = $rest[$valor]['cant_reserva'] + 1;
                    else {
                        $temp['id_user'] = $po[$i]['id_user'];
                        $temp['user_user_name'] = $po[$i]['user_user_name'];
                        $temp['user_last_name'] = $po[$i]['user_last_name'];
                        $temp['user_email'] = $po[$i]['user_email'];
                        $rest_temp[] = $temp;
                        $aux['user_user_name'] = $po[$i]['user_user_name'];
                        $aux['user_last_name'] = $po[$i]['user_last_name'];
                        $aux['user_email'] = $po[$i]['user_email'];
                        $aux['cant_reserva'] = 1;
                        $rest[] = $aux;
                    }
                }
                return $this->view->create($rest, 200);
            }
        }
        else
            throw new InvalidFormException('The ownership no exist.');
    }

    /**
     * Obtener listado de reservas de un cliente en una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getReservationclientbyproperty($request, $code) {
        $fields = array();
        ($request->query->get('client_name') != '') ? $fields['client_name'] = $request->query->get('client_name') : $fields;
        ($request->query->get('client_email') != '') ? $fields['client_email'] = $request->query->get('client_email') : $fields;
        ($request->query->get('client_country') != '') ? $fields['client_country'] = $request->query->get('client_country') : $fields;
        ($request->query->get('client_id') != '') ? $fields['client_id'] = $request->query->get('client_id') : $fields;
        $where = "own.own_mcp_code=:own_id AND gr.gen_res_status=:gen_res_status ";
        //InnerJoin
        $inner = "INNER JOIN ownership own ON own.own_id = gr.gen_res_own_id
                INNER JOIN ownershipreservation owr ON gr.gen_res_id = owr.own_res_gen_res_id
                INNER JOIN user u ON u.user_id = gr.gen_res_user_id ";
        $select = "gr.*, u.*, owr.* ";
        (isset($fields['client_name'])) ? $where = $where . " AND u.user_user_name like :client_name " : $where;
        (isset($fields['client_email'])) ? $where = $where . " AND u.user_email like :client_email " : $where;
        (isset($fields['client_id'])) ? $where = $where . " AND u.user_id =:client_id " : $where;
        if(isset($fields['client_country'])) {
            $select .= ",c.co_name";
            $where = $where . " AND c.co_name like :client_country ";
            $inner .= "INNER JOIN country c ON u.user_country=c.co_id";
        }
        $query = "SELECT " . $select . " FROM  generalreservation gr " . $inner . " WHERE " . $where . " ; ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_id', $code);
        $stmt->bindValue('gen_res_status', self::STATUS_RESERVED);
        if(isset($fields['client_name']))
            $stmt->bindValue('client_name', "%" . $fields['client_name'] . "%");
        if(isset($fields['client_email']))
            $stmt->bindValue('client_email', "%" . $fields['client_email'] . "%");
        if(isset($fields['client_country']))
            $stmt->bindValue('client_country', "%" . $fields['client_country'] . "%");
        if(isset($fields['client_id']))
            $stmt->bindValue('client_id', (int)$fields['client_id']);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $this->view->create($po, 200);
    }

    /**
     * Obtener listado de comentarios de un cliente sobre una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getCommentclient($fields, $code) {
        $query = "SELECT
                  c.*,
                  u.*
                  FROM comment c
                  INNER JOIN ownership own ON own.own_id = c.com_ownership
                  INNER JOIN user u ON u.user_id = c.com_user
                  WHERE own.own_mcp_code=:own_id;";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_id', $code);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $this->view->create($po, 200);
    }

    /**
     * Con este servicio se pueden adicionar los datos de un nuevo usuario al CBS.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function postRegisteruser($request) {
        $array_temp = array();
        if($request->request->get('name') != "") {
            $array_temp['user_name'] = $request->request->get('name');
        }
        else  throw new InvalidFormException();
        if($request->request->get('last_name') != "") {
            $array_temp['user_last_name'] = $request->request->get('last_name');
        }
        else  throw new InvalidFormException();
        if($request->request->get('email') != "") {
            $array_temp['user_email'] = $request->request->get('email');
        }
        else  throw new InvalidFormException();
        if($request->request->get('password') != "") {
            $array_temp['user_password'] = self::encryptPassword($request->request->get('password'));
        }
        else  throw new InvalidFormException();
        if($request->request->get('country') != "") {
            $array_temp['user_country'] = $request->request->get('country');
        }
        else  throw new InvalidFormException();
        if($request->request->get('enabled') != "") {
            $array_temp['user_enabled'] = $request->request->get('enabled');
        }
        else  throw new InvalidFormException();
        if(count($array_temp)) {
            //Chequeo que no exista un usuario con ese nombre
            $query = "SELECT * FROM user WHERE user_email=:email; ";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('email', $request->request->get('email'));
            $stmt->execute();
            $po = $stmt->fetchAll();
            if(count($po))
                throw new InvalidFormException('The email is used for other user');
            else
                $this->conn->insert('user', $array_temp);
        }

        return array('success' => true);
    }

    /**
     * Con este servicio se puede asignar un rol a un usuario determinado.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function postAddroluser($request) {
        $array_temp = array();
        if($request->request->get('user_id') == "")
            throw new InvalidFormException();
        if($request->request->get('role') != "") {
            $array_temp['user_role'] = $request->request->get('role');
        }
        else  throw new InvalidFormException();

        $temp = self::getTableById('user', 'user_id', $request->request->get('user_id'), 'fetchAll');
        if(count($temp)) {
            $this->conn->update('user', $array_temp, array('user_id' => $request->request->get('user_id')));
            return array('success' => true);
        }
        else
            throw new InvalidFormException('The user no exist.');
    }

    /**
     * Con este servicio se puede actualizar el estado de un usuario (Activado/Desactivado). Estado(100%).
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function putEnableduser($request) {
        $array_temp = array();
        if($request->request->get('user_id') == "")
            throw new InvalidFormException();
        if($request->request->get('enabled') != "") {
            $array_temp['user_enabled'] = $request->request->get('enabled');
        }
        else  throw new InvalidFormException();

        $temp = self::getTableById('user', 'user_id', $request->request->get('user_id'), 'fetchAll');
        if(count($temp)) {
            $this->conn->update('user', $array_temp, array('user_id' => $request->request->get('user_id')));
            return array('success' => true);
        }
        else
            throw new InvalidFormException('The user no exist.');
    }

    /**
     * Con este servicio se pueden obtener los datos registrados de un usuario.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function getUser($request) {
        if($request->query->get('user_id') != '') {
            $select = "u.user_id,";
            ($request->query->get('name') == 'true') ? $select .= 'u.user_name,' : $select;
            ($request->query->get('last_name') == 'true') ? $select .= 'u.user_last_name,' : $select;
            ($request->query->get('email') == 'true') ? $select .= 'u.user_email,' : $select;
            ($request->query->get('password') == 'true') ? $select .= 'u.user_password,' : $select;
            ($request->query->get('enabled') == 'true') ? $select .= 'u.user_enabled,' : $select;
            ($request->query->get('role') == 'true') ? $select .= 'u.user_role,' : $select;
            if($request->query->get('country') == 'true') {
                $select .= "c.co_name,";
                $inner = "INNER JOIN country c ON u.user_country=c.co_id";
            }
            $query = "SELECT " . substr($select, 0, -1) . " FROM user u " . $inner . " WHERE u.user_id=:user_id; ";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('user_id', $request->query->get('user_id'));
            $stmt->execute();
            $user = $stmt->fetchAll();
            return $user;
        }
        else {
            throw new InvalidFormException();
        }
    }

    /**
     * Adicionar la No Disponibilidad de una habitación por rango
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function postAddavailableroombyrange($request, $code) {
        /*
         $date=date_create("2013-03-15");
         echo date_format($date,"Y/m/d H:i:s");
         */
        $date = new \DateTime($request->request->get('start'));
        $date->modify('-1 day');
        $ud_to_date = $date->format('Y-m-d');
        $start = $request->request->get('start');
        $query = "UPDATE unavailabilitydetails SET  ud_to_date = :ud_to_date WHERE room_id = :room_id AND ud_from_date < :start AND ud_to_date >= :start ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('ud_to_date', $ud_to_date);
        $stmt->bindValue('room_id', $code);
        $stmt->bindValue('start', $start);
        $stmt->execute();

        $unavailability = json_decode($request->request->get('date_range'));

        $start = Date::createFromString($request->request->get('start'));
        $start = date_format($start, 'Y-m-d');
        $end = Date::createFromString($request->request->get('end'));
        $end = date_format($end, 'Y-m-d');

        //Find avaible room by code
        $temp = self::getUnavailability($request->request->get('start'), $request->request->get('end'), $code);
        $total = sizeof($unavailability);
        if(count($temp)) {
            //Si tiene reservaciones las elimino
            for ($i = 0; $i < count($temp); $i++) {
                $this->conn->delete('unavailabilitydetails', array('ud_id' => $temp[$i]['ud_id']));
            }
        }

        //sino tiene reservaciones las creo
        $reason = $request->request->get('reason');
        $reason = isset($reason) ? $reason : '';
        for ($i = 0; $i < $total; $i++) {
            $this->conn->insert('unavailabilitydetails', array('room_id' => $code, 'ud_sync_st' => 0, 'ud_from_date' => $unavailability[$i]->start, 'ud_to_date' => $unavailability[$i]->end, 'ud_reason' => $reason));
        }

        /**/
        $query = "SELECT room.room_ownership FROM ownership INNER JOIN room ON (ownership.own_id = room.room_ownership) WHERE room.room_id = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('code', $code);
        $stmt->execute();
        $r = $stmt->fetchAll();
        if(count($r) <= 0) {
            return $this->view->create(array('success' => false, 'code' => Response::HTTP_UNAUTHORIZED, 'message' => 'No tiene permisos para modificar la habitación'), Response::HTTP_UNAUTHORIZED);
        }

        $ownership = $r[0]['room_ownership'];
        $query = "UPDATE ownership SET own_availability_update = :own_availability_update WHERE own_id = :own_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_availability_update', date_format(new \DateTime(), 'Y-m-d'));
        $stmt->bindValue('own_id', $ownership);
        $stmt->execute();
        /**/

        return $this->view->create(array('success' => true), 200);
    }

    /**
     * Buscar las disponibilidad dada un rango de fechas
     * @param $date_from
     * @param $date_to
     * @param $code
     * @return mixed
     */
    public function getUnavailability($date_from, $date_to, $code) {
        $query = "SELECT * FROM unavailabilitydetails WHERE  (ud_from_date>=:from_date AND ud_from_date<=:to_date OR ud_to_date>=:from_date AND ud_to_date<=:to_date) AND room_id=:room_id; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('from_date', $date_from);
        $stmt->bindValue('to_date', $date_to);
        $stmt->bindValue('room_id', $code);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function login($request) {
        $user = $request->request->get('user');
        $encrypt_password = self::encryptPassword($request->request->get('password'));
        $query = "SELECT user_id,user_name,user_user_name,user_last_name,user_email,user_phone,user_enabled FROM user WHERE  (user_name=:user_name OR user_email=:user_name) AND user_password=:user_password; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('user_name', $user);
        $stmt->bindValue('user_password', $encrypt_password);
        $stmt->execute();
        $po = $stmt->fetch();

        /*$pathToCont = "xxxxxxx.txt";
        $file = fopen($pathToCont, "a");
        fwrite($file, '----------------' . PHP_EOL);
        fwrite($file, ' -->  user_name: ' . $user . ' --> user_password: ' . $encrypt_password . PHP_EOL);
        fwrite($file, '--------------' . PHP_EOL);
        fclose($file);*/

        if(isset($po['user_id'])) {
            //Servicio para MiCasa Renta
            if($request->request->get('start') != '' && $request->request->get('end') != '') {
                $own = self::getOwnByUser($po['user_id'], $request);
                $po["mobil_response"] = "5355599750";
                $po['property'] = $own;
            }
            //Servicio para MiCasa Ligth
            if($request->request->get('start') != '' && $request->request->get('end') == '') {
                $own = self::getReservationabydate($request->request->get('start'), $po['user_id']);
                $po['reservations'] = $own;
            }
            return $po;
        }
        else {
            return $this->view->create(array('success' => false, 'code' => Response::HTTP_UNAUTHORIZED, 'message' => 'Bad credentials'), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Buscar las propiedades dado un id de usuario
     * @param $iduser
     * @return array
     */
    public function getOwnByUser($iduser, $request) {
        $query = "SELECT * FROM usercasa WHERE  user_casa_user=:user_casa_user; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('user_casa_user', $iduser);
        $stmt->execute();
        $po = $stmt->fetchAll();
        $property = array();
        foreach ($po as $item)
            $property[] = self::getAcomodationsByCode($item['user_casa_ownership']);
        //Me muevo por las casas
        for ($j = 0; $j < count($property); $j++) {
            for ($i = 0; $i < count($property[$j]['room']); $i++) {
                $start = Date::createFromString($request->request->get('start'));
                $start = date_format($start, 'Y-m-d');
                $end = Date::createFromString($request->request->get('end'));
                $end = date_format($end, 'Y-m-d');
                $unavailability = self::getUnavailability($start, $end, $property[$j]['room'][$i]['room_id']);
                $reservations = self::getOwnerShipReservation($property[$j]['room'][$i]['room_id'], $start, $end);
                $property[$j]['room'][$i]['unavailability'] = $unavailability;
                $property[$j]['room'][$i]['reservations'] = $reservations;
            }
        }

        return $property;
    }

    /**
     * @param $room_id
     * @param null $start
     * @param null $end
     * @param int $status_cancel
     * @param int $status_reserv
     * @return mixed
     */
    public function getOwnerShipReservation($room_id, $start = null, $end = null, $status_cancel = 4, $status_reserv = 5) {
        $query = "SELECT ownership.own_commission_percent FROM room INNER JOIN ownership ON (room.room_ownership = ownership.own_id) WHERE room.room_id = :room_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('room_id', $room_id);
        $stmt->execute();
        $commissionPercent = $stmt->fetch()['own_commission_percent'] * 1;

        if($start != null && $end != null) {
            $query = "SELECT ownershipreservation.own_res_id, ownershipreservation.own_res_selected_room_id, ownershipreservation.own_res_status,
ownershipreservation.own_res_reservation_from_date, ownershipreservation.own_res_reservation_to_date,
CONCAT(user.user_user_name, ' ',user.user_last_name) AS user_full_name, user.user_email, country.co_name AS country,
ownershipreservation.own_res_night_price AS night_price, ownershipreservation.own_res_total_in_site AS total_in_site,
ownershipreservation.own_res_count_adults, ownershipreservation.own_res_count_childrens, generalreservation.gen_res_id AS cas
FROM ownershipreservation
INNER JOIN generalreservation ON (ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id)
INNER JOIN user ON (generalreservation.gen_res_user_id = user.user_id)
INNER JOIN country ON (user.user_country = country.co_id)
WHERE  (own_res_reservation_from_date>=:from_date AND own_res_reservation_from_date<=:to_date OR own_res_reservation_to_date>=:from_date AND own_res_reservation_to_date<=:to_date) AND own_res_selected_room_id=:own_res_selected_room_id AND own_res_status=:own_res_status_reserva";
        }
        else {
            $query = "SELECT ownershipreservation.own_res_id, ownershipreservation.own_res_selected_room_id, ownershipreservation.own_res_status,
ownershipreservation.own_res_reservation_from_date, ownershipreservation.own_res_reservation_to_date,
CONCAT(user.user_user_name, ' ',user.user_last_name) AS user_full_name, user.user_email, country.co_name as country,
ownershipreservation.own_res_night_price AS night_price, ownershipreservation.own_res_total_in_site AS total_in_site,
ownershipreservation.own_res_count_adults, ownershipreservation.own_res_count_childrens, generalreservation.gen_res_id AS cas
FROM ownershipreservation
INNER JOIN generalreservation ON (ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id)
INNER JOIN user ON (generalreservation.gen_res_user_id = user.user_id)
INNER JOIN country ON (user.user_country = country.co_id)
WHERE  own_res_selected_room_id=:own_res_selected_room_id AND own_res_status=:own_res_status_reserva";
        }

        $stmt = $this->conn->prepare($query);
        if($start != null && $end != null) {
            $stmt->bindValue('from_date', $start);
            $stmt->bindValue('to_date', $end);
        }
        //$stmt->bindValue('own_res_status_cancel', $status_cancel);
        $stmt->bindValue('own_res_status_reserva', $status_reserv);
        $stmt->bindValue('own_res_selected_room_id', $room_id);
        $stmt->execute();
        $po = $stmt->fetchAll();
        $reservations = array();
        foreach ($po as $item) {
            $aux['own_res_id'] = $item['own_res_id'];
            $aux['own_res_selected_room_id'] = $item['own_res_selected_room_id'];
            $aux['own_res_status'] = ($item['own_res_status'] == 5) ? 'reservada' : 'cancelada';

            $aux['adults'] = $item['own_res_count_adults'];
            $aux['childrens'] = $item['own_res_count_childrens'];

            $fromDate = new \DateTime($item['own_res_reservation_from_date']);
            $toDate = new \DateTime($item['own_res_reservation_to_date']);

            $pricePerInHomeAndNights = $this->getPricePerInHome($fromDate->getTimestamp(), $toDate->getTimestamp(), $item['night_price'], $item['total_in_site'], $commissionPercent);
            $aux['price_in_home'] = $pricePerInHomeAndNights['price_in_home'];
            $aux['nights'] = $pricePerInHomeAndNights['nights'];

            $toDate->modify('-1 day');
            $toDate = $toDate->format('Y-m-d');

            $aux['own_res_reservation_from_date'] = $item['own_res_reservation_from_date'];
            $aux['own_res_reservation_to_date'] = $toDate;

            $aux['user_full_name'] = $item['user_full_name'];
            $aux['user_email'] = $item['user_email'];
            $aux['user_country'] = $item['country'];
            $aux['cas'] = $item['cas'];
            $reservations[] = $aux;
        }
        return $reservations;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getAcomodationsByCode($code) {
        $select = "o.own_id,";
        $select_room = "";
        $innerJoin = "";
        $rooms = array();
        $select .= 'o.own_mcp_code,';
        $select .= 'o.own_name,';
        $select .= 'o.own_address_street,';
        $select .= 'o.own_address_number,';
        $select .= 'o.own_address_between_street_1,';
        $select .= 'o.own_address_between_street_2,';
        $select .= 'p.prov_name,';
        $innerJoin .= "INNER JOIN province p ON o.own_address_province = p.prov_id ";
        $select .= 'm.mun_name,';
        $innerJoin .= "INNER JOIN municipality m ON o.own_address_municipality = m.mun_id ";

        $select .= 'o.own_mobile_number,';
        $select .= 'o.own_phone_number,';
        $select .= 'o.own_email_1,';
        $select .= 'o.own_email_2,';
        $select_room .= 'r.room_id,';
        $select_room .= 'r.room_type,';
        $select_room .= 'r.room_num,';
        $select_room .= 'r.room_price_up_to,';
        $select_room .= 'r.room_price_down_to,';
        $select_room .= 'r.room_climate,';
        $select_room .= 'r.room_audiovisual,';
        $select_room .= 'r.room_smoker,';
        $select_room .= 'r.room_safe,';
        $select_room .= 'r.room_baby,';
        $select_room .= 'r.room_bathroom,';
        $select_room .= 'r.room_stereo,';
        $select_room .= 'r.room_windows,';
        $select_room .= 'r.room_balcony,';
        $select_room .= 'r.room_terrace,';
        $select_room .= 'r.room_yard,';

        $query = "SELECT " . substr($select, 0, -1) . " FROM ownership o " . $innerJoin . " WHERE o.own_id=:code; ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('code', $code);
        $stmt->execute();
        $ship = $stmt->fetchAll();
        if(count($ship)) {
            if($select_room != '') {
                $query = "SELECT " . substr($select_room, 0, -1) . " FROM room r WHERE r.room_ownership=:code; ";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue('code', $ship[0]['own_id']);
                $stmt->execute();
                $rooms = $stmt->fetchAll();
                $ship[0]['room'] = $rooms;
            }
            return $ship[0];
        }
    }

    /**
     * Obtener listado de reservas dada una fecha de inicio.
     * @param date $date Fecha de inicio
     * @return array
     */
    public function getReservationabydate($date, $iduser = null) {
        $date = Date::createFromString($date);
        $select = "DISTINCT gr.gen_res_id,gr.gen_res_from_date,gr.gen_res_to_date,gr.gen_res_own_id,owr.*,ow.*,p.prov_phone_code,p.prov_id,m.mun_id ";
        //InnerJoin
        $inner = "";
        $inner .= " INNER JOIN ownershipreservation owr ON gr.gen_res_id = owr.own_res_gen_res_id ";
        $inner .= " INNER JOIN ownership ow ON ow.own_id = gr.gen_res_own_id ";
        $inner .= "INNER JOIN province p ON ow.own_address_province = p.prov_id ";
        $inner .= "INNER JOIN municipality m ON ow.own_address_municipality = m.mun_id";
        $where = "gr.gen_res_to_date>=:date_from AND gr.gen_res_status=:gen_res_status";
        if($iduser != null) {
            $inner .= " INNER JOIN user u ON u.user_id = gr.gen_res_user_id ";
            $select .= ",u.*";
            $where .= " AND u.user_id =:client_id";
        }
        $query = "SELECT " . $select . " FROM  generalreservation gr " . $inner . " WHERE " . $where . " ; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('date_from', date_format($date, 'Y-m-d'));
        $stmt->bindValue('gen_res_status', 2);
        if($iduser != null)
            $stmt->bindValue('client_id', (int)$iduser);
        $stmt->execute();
        $po = $stmt->fetchAll();
        //return $po;
        $reservations = array();
        $array_aux = array();
        foreach ($po as $item) {
            if(!in_array($item['gen_res_id'], $array_aux)) {
                $array_aux[] = $item['gen_res_id'];
                $aux['gen_res_id'] = $item['gen_res_id'];
                $aux['gen_res_from_date'] = $item['gen_res_from_date'];
                $aux['gen_res_to_date'] = $item['gen_res_to_date'];
                $aux['accommodation'] = array('own_id' => $item['own_id'], 'own_mcp_code' => $item['own_mcp_code'], 'own_name' => $item['own_name'], 'address' => array('own_destination' => $item['own_destination'], 'prov_id' => $item['prov_id'], 'mun_id' => $item['mun_id'], 'own_address_street' => $item['own_address_street'], 'own_address_number' => $item['own_address_number'], 'own_address_between_street_1' => $item['own_address_between_street_1'], 'own_address_between_street_2' => $item['own_address_between_street_2'], 'own_address_province' => $item['own_address_province'], 'own_address_municipality' => $item['own_address_province']), 'own_mobile_number' => $item['own_mobile_number'], 'own_phone_number' => $item['prov_phone_code'] . ' ' . $item['own_phone_number'], 'own_email_1' => $item['own_email_1'], 'own_email_2' => $item['own_email_2'], 'own_geolocate_y' => $item['own_geolocate_y'], 'own_geolocate_x' => $item['own_geolocate_x']);
                $aux['details'] = self::getDetailsreserv($item['gen_res_id']);

                $reservations[] = $aux;
            }

        }
        return $reservations;
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
                $aux['own_res_id'] = $item['own_res_id'];
                $aux['own_res_count_childrens'] = $item['own_res_count_childrens'];
                $aux['own_res_count_adults'] = $item['own_res_count_adults'];
                $aux['own_res_reservation_from_date'] = $item['own_res_reservation_from_date'];
                $aux['own_res_reservation_to_date'] = $item['own_res_reservation_to_date'];
                $aux['room'] = array('room_type' => $item['own_res_room_type'], 'room_id' => $item['room_id'], 'room_num' => $item['room_num']);
                $res[] = $aux;
            }
        }
        return $res;

    }

    /**
     * Para obtener los datos de la geolocalizacion de una casa
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getLocationbycode($request, $code) {
        $query = "SELECT
                  own.own_geolocate_y,
                  own.own_geolocate_x
                  FROM ownership own
                  WHERE own.own_mcp_code=:code;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('code', $code);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $this->view->create($po, 200);
    }

    /**
     * Obtener listado de reservas de un cliente.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $userid identificador del usuario
     * @return array
     */
    public function getReservationclient($request, $userid) {
        $date = Date::createFromString($request->query->get('start'));
        $select = "u.user_id,u.user_name,u.user_user_name,u.user_last_name,u.user_email,u.user_enabled";
        $where = "u.user_id =:client_id";

        $query = "SELECT " . $select . " FROM  user u " . " WHERE " . $where . " ; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('client_id', (int)$userid);
        $stmt->execute();
        $po = $stmt->fetchAll();
        $i = 0;
        $res['user_id'] = $po[$i]['user_id'];
        $res['user_name'] = $po[$i]['user_name'];
        $res['user_user_name'] = $po[$i]['user_user_name'];
        $res['user_last_name'] = $po[$i]['user_last_name'];
        $res['user_email'] = $po[$i]['user_email'];
        $res['user_enabled'] = $po[$i]['user_enabled'];
        $res['reservations'] = self::getReservationabydate($request->query->get('start'), $po[$i]['user_id']);
        return $this->view->create($res, 200);
    }

    public function listProvinces() {
        try {
            $query = "SELECT province.prov_id,province.prov_name,province.prov_phone_code,province.prov_code,province.prov_own_code FROM province";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        }
        catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listMunicipality() {
        try {
            $query = "SELECT mun.mun_id,
                  mun.mun_name, prov.prov_id, prov.prov_name,
                  (select count(o.own_id) from ownership o where o.own_address_municipality = mun.mun_id) as accommodations
                  FROM municipality mun
                  JOIN province prov on prov.prov_id = mun.mun_prov_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        }
        catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listDestinations() {
        try {
            $query = "SELECT  destination.des_id,destination.des_name,municipality.mun_name, province.prov_name, destination.des_poblation,
            (SELECT count(ownership.own_id) FROM ownership WHERE ownership.own_destination=destination.des_id) as casas,
            destination.des_active
            FROM destination INNER JOIN destinationlocation ON destination.des_id = destinationlocation.des_loc_des_id
            INNER JOIN municipality ON destinationlocation.des_loc_mun_id = municipality.mun_id
            INNER JOIN  province ON destinationlocation.des_loc_prov_id = province.prov_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        }
        catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listOwnsCategories() {
        $cat_name = 'cat_name';
        $owns_categories = array();
        $owns_categories[] = array($cat_name => 'Económica');
        $owns_categories[] = array($cat_name => 'Rango medio');
        $owns_categories[] = array($cat_name => 'Premium');

        return $owns_categories;
    }

    function listOwnsTypes() {
        $type_name = 'ownt_name';
        $owns_types = array();
        $owns_types[] = array($type_name => 'Penthouse');
        $owns_types[] = array($type_name => 'Villa con piscina');
        $owns_types[] = array($type_name => 'Apartamento');
        $owns_types[] = array($type_name => 'Propiedad completa');
        $owns_types[] = array($type_name => 'Casa particular');

        return $owns_types;
    }

    public function listRoomTypes() {
        $type_name = 'roomt_name';
        $room_types = array();
        $room_types[] = array($type_name => 'Habitación Triple');
        $room_types[] = array($type_name => 'Habitación doble (Dos camas)');
        $room_types[] = array($type_name => 'Habitación doble');
        $room_types[] = array($type_name => 'Habitación individual');

        return $room_types;
    }

    public function listReservationStatus() {
        $status_name = 'roomt_name';
        $reservation_status = array();
        $reservation_status[] = array($status_name => 'cancelada');
        $reservation_status[] = array($status_name => 'reservada');

        return $reservation_status;
    }

    public function listClimate() {
        $climate_name = 'cl_name';
        $climates = array();
        $climates[] = array($climate_name => 'Aire acondicionado / Ventilador');
        $climates[] = array($climate_name => 'Aire acondicionado');
        $climates[] = array($climate_name => 'Ventilador');
        $climates[] = array($climate_name => 'Natural');

        return $climates;
    }

    public function listAudiovisual() {
        $audiovisual_name = 'av_name';
        $audiovisuals = array();
        $audiovisuals[] = array($audiovisual_name => 'TV');
        $audiovisuals[] = array($audiovisual_name => 'TV+DVD / Video');
        $audiovisuals[] = array($audiovisual_name => 'TV cable');
        $audiovisuals[] = array($audiovisual_name => 'No');

        return $audiovisuals;
    }

    public function listBathroomTypes() {
        $bathroom_type_name = 'bt_name';
        $bathroomTypes = array();
        $bathroomTypes[] = array($bathroom_type_name => 'Interior privado');
        $bathroomTypes[] = array($bathroom_type_name => 'Exterior privado');
        $bathroomTypes[] = array($bathroom_type_name => 'Compartido');

        return $bathroomTypes;
    }

    public function listLanguages() {
        $language_name = 'lan_name';
        $language_code = 'code';
        $languages = array();
        $languages[] = array($language_name => 'Inglés', $language_code => 'EN');
        $languages[] = array($language_name => 'Francés', $language_code => 'FR');
        $languages[] = array($language_name => 'Alemán', $language_code => 'DE');
        $languages[] = array($language_name => 'Italiano', $language_code => 'IT');

        return $languages;
    }

    public function listOwnsStatus() {
        try {
            $query = "SELECT ownershipstatus.status_id, ownershipstatus.status_name FROM ownershipstatus";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        }
        catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    /**
     * @param $bookingId
     * @return mixed
     * @internal param $request
     */
    public function checkBookingId($bookingId) {
        if(!isset($bookingId) || $bookingId == "") {
            return false;
        }

        $query = "SELECT booking.booking_id, payment.created FROM payment INNER JOIN booking ON (payment.booking_id = booking.booking_id) WHERE booking.booking_id = " . $bookingId;
        $stmt = $this->conn->prepare($query);
        //$stmt->bindValue('booking_id', $bookingId);
        $stmt->execute();
        $booking = $stmt->fetch();

        if($booking && isset($booking['booking_id']) && isset($booking['created'])) {
            return $booking['created'];
        }
        else {
            return false;
        }
    }

    public function getEmailsOfOwners($municipality) {
        $status = 1;

        try {
            $query = "SELECT ownership.own_homeowner_1, ownership.own_homeowner_2, ownership.own_email_1, ownership.own_email_2
FROM ownership
WHERE (ownership.own_email_1 <> '' OR  ownership.own_email_2 <> '') AND ownership.own_status = :status ";

            if(isset($municipality)) {
                $query .= "AND ownership.own_address_municipality = :id_municipality";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('status', $status);
            if(isset($municipality)) {
                $stmt->bindValue('id_municipality', $municipality);
            }

            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        }
        catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getCubaCouponOwnerships() {
        $status = 1;
        try {
            $query = "SELECT
  CONCAT('own', '_', ownership.own_id) AS id,
  ownership.own_name AS title,
  ownership.own_type AS type,
  ownership.own_geolocate_y AS longitude,
  ownership.own_geolocate_x AS latitude,
  CONCAT('https://mycasaparticular.com/uploads/ownershipImages', '/', photo.pho_name) AS image,
  CONCAT( ownership.own_address_street,' #',ownership.own_address_number,' ',ownership.own_address_between_street_1,' & ',ownership.own_address_between_street_2,' ',municipality.mun_name) AS address,
  destination.des_name,
  province.prov_name
FROM
  ownershipphoto
  RIGHT OUTER JOIN ownership ON (ownershipphoto.own_pho_own_id = ownership.own_id)
  INNER JOIN photo ON (ownershipphoto.own_pho_pho_id = photo.pho_id)
  INNER JOIN destination ON (ownership.own_destination = destination.des_id)
  INNER JOIN province ON (ownership.own_address_province = province.prov_id)
  INNER JOIN municipality ON (ownership.own_address_municipality = municipality.mun_id)
WHERE
  ownership.own_cubacoupon = :own_cubacoupon
GROUP BY
  ownership.own_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('own_cubacoupon', $status);

            $stmt->execute();
            $po = $stmt->fetchAll();

            return $po;
        }
        catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function insertLog($id_module, $operation, $dt_table = "", $description, $id_user=null) {
        $queryLog = "INSERT INTO log (log_user,log_module,log_description,log_date,log_time,operation,db_table)
VALUE (:log_user,:log_module,:log_description,:log_date,:log_time,:operation,:db_table)";

        $log_date = (new \DateTime(date('Y-m-d')))->format('Y-m-d');
        $log_time = strftime("%I:%M %p");

        $stmtLog = $this->conn->prepare($queryLog);
        $stmtLog->bindValue('log_user', $id_user);
        $stmtLog->bindValue('log_module', $id_module);
        $stmtLog->bindValue('log_description', $description);
        $stmtLog->bindValue('log_date', $log_date);
        $stmtLog->bindValue('log_time', $log_time);
        $stmtLog->bindValue('operation', $operation);
        $stmtLog->bindValue('db_table', $dt_table);
        $stmtLog->execute();
    }

    /*********************** **************************************/
    /******* Develop to MyCasa renta new **************************/
    /*********************** **************************************/

    /**
     * @param $request
     * @return mixed
     */
    public function loginMycasarenta($request) {
        $user = $request->request->get('user');
        $encrypt_password = self::encryptPassword($request->request->get('password'));
        $query = "SELECT user_id,user_name,user_user_name,user_last_name FROM user WHERE  (user_name=:user_name OR user_email=:user_name) AND user_password=:user_password; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('user_name', $user);
        $stmt->bindValue('user_password', $encrypt_password);
        $stmt->execute();
        $po = $stmt->fetch();

        if(isset($po['user_id']) && $request->request->get('start') != '' && $request->request->get('end') != '') {
            $own = self::getPropertiesByUser($po['user_id'], $request);
            $po["mobil_response"] = "5355599750";
            $po['property'] = $own;

            return $po;
        }
        else {
            return $this->view->create(array('success' => false, 'code' => Response::HTTP_UNAUTHORIZED, 'message' => 'Bad credentials'), Response::HTTP_UNAUTHORIZED);
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

        $queryUser = "SELECT user.user_id, room.room_ownership FROM user
INNER JOIN usercasa ON (user.user_id = usercasa.user_casa_user)
INNER JOIN ownership ON (usercasa.user_casa_ownership = ownership.own_id)
INNER JOIN room ON (ownership.own_id = room.room_ownership)
WHERE user.user_name = :user_name AND user.user_password = :user_password AND room.room_id = :room_id";

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
                $this->addavailableroombyrange($roomId, $start, $end, $availabilities, "Por propietario via wifi desde MyCasa Renta", $resUser['user_id']);

                $room = array();
                $start = new \DateTime();
                $end = (new \DateTime())->modify('1 years');
                $unavailability = self::getUnavailability($start->format('Y-m-d'), $end->format('Y-m-d'), $roomId);
                $reservations = self::getOwnerShipReservation($roomId, $start->format('Y-m-d'), $end->format('Y-m-d'));
                $room['room_id'] = $roomId;
                $room['unavailability'] = $unavailability;
                $room['reservations'] = $reservations;
                $ownership['room'][] = $room;
            }
        }

        return $this->view->create($ownership, Response::HTTP_OK);
    }

    /**
     * @param todos $request
     * @return array
     * @internal param código $code
     */
    public function updateCalendarRoomSms($request) {
        $mobile = str_replace('+53', '', $request->request->get('mobile'));
        $queryOws = "SELECT COUNT(ownership.own_id) AS l FROM ownership INNER JOIN room ON (ownership.own_id = room.room_ownership)
WHERE room.room_id = :room_id AND ownership.own_mobile_number = :own_mobile_number";

        $availability = $request->request->get('availability');
        $availabilityRooms = $this->parseAvailability($availability);
        foreach ($availabilityRooms as $availabilityRoom) {
            $room = $availabilityRoom['room'];
            $availabilities = $availabilityRoom['availabilities'];
            $start = $availabilityRoom['start'];
            $end = $availabilityRoom['end'];

            $stmtOws = $this->conn->prepare($queryOws);
            $stmtOws->bindValue('room_id', $room);
            $stmtOws->bindValue('own_mobile_number', $mobile);
            $stmtOws->execute();
            $r = $stmtOws->fetch();

            if(($r['l'] * 1) > 0) {
                $this->addavailableroombyrange($room, $start, $end, $availabilities, "Por propietario via sms desde MyCasa Renta. Numero telefonico:".$mobile);
            }
        }

        return $this->view->create(array("success" => true), Response::HTTP_OK);
    }

    /**
     * @param todos $request
     * @return array
     * @internal param código $code
     */
    public function addResponseQuickBooking($request) {
        $user = $request->request->get('user');
        $encrypt_password = self::encryptPassword($request->request->get('password'));
        $cas = $request->request->get('cas');
        $cas = str_replace("CAS", "", $cas);
        $cas *= 1;
        $availability = $request->request->get('availability') * 1;

        /**/
        $query = "SELECT COUNT(generalreservation.gen_res_id) AS l FROM generalreservation
INNER JOIN ownership ON (generalreservation.gen_res_own_id = ownership.own_id)
INNER JOIN usercasa ON (ownership.own_id = usercasa.user_casa_ownership)
INNER JOIN user ON (usercasa.user_casa_user = user.user_id)
WHERE generalreservation.gen_res_id = :gen_res_id AND user.user_name = :user_name AND user.user_password = :user_password";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('gen_res_id', $cas);
        $stmt->bindValue('user_name', $user);
        $stmt->bindValue('user_password', $encrypt_password);
        $stmt->execute();
        $r = $stmt->fetch();
        if(($r['l'] * 1) > 0) {
            $r = $this->executeAddResponseQuickBooking($cas, $availability);
            if($r) {
                return $this->view->create(array("success" => true), Response::HTTP_OK);
            }
        }

        return $this->view->create(array("success" => false), Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param todos $request
     * @return array
     * @internal param código $code
     */
    public function addResponseQuickBookingSms($request) {
        $mobile = str_replace('+53', '', $request->request->get('mobile'));
        $cas = $request->request->get('cas');
        $cas = str_replace("CAS", "", $cas);
        $cas *= 1;
        $availability = $request->request->get('availability') * 1;

        $query = "SELECT COUNT(generalreservation.gen_res_id) AS l FROM generalreservation INNER JOIN ownership ON (generalreservation.gen_res_own_id = ownership.own_id)
WHERE generalreservation.gen_res_id = :gen_res_id AND ownership.own_mobile_number = :own_mobile_number";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('gen_res_id', $cas);
        $stmt->bindValue('own_mobile_number', $mobile);
        $stmt->execute();
        $r = $stmt->fetch();
        if(($r['l'] * 1) > 0) {
            $r = $this->executeAddResponseQuickBooking($cas, $availability);
            if($r) {
                return $this->view->create(array("success" => true), Response::HTTP_OK);
            }
        }

        return $this->view->create(array("success" => false), Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param todos $request
     * @param código $code
     * @return array
     */
    public function updatePriceRoom($request) {
        $user = $request->request->get('user');
        $encrypt_password = self::encryptPassword($request->request->get('password'));

        $queryUser = "SELECT COUNT(user.user_id) AS l FROM user
INNER JOIN usercasa ON (user.user_id = usercasa.user_casa_user)
INNER JOIN ownership ON (usercasa.user_casa_ownership = ownership.own_id)
INNER JOIN room ON (ownership.own_id = room.room_ownership)
WHERE user.user_name = :user_name AND user.user_password = :user_password AND room.room_id = :room_id";

        $stmtUser = $this->conn->prepare($queryUser);
        $stmtUser->bindValue('user_name', $user);
        $stmtUser->bindValue('user_password', $encrypt_password);

        $prices = $request->request->get('prices');
        $pricesRooms = $this->parsePrices($prices);
        foreach ($pricesRooms as $priceRoom){
            $room = $priceRoom['room'];
            $stmtUser->bindValue('room_id', $room);
            $stmtUser->execute();
            $resUser = $stmtUser->fetch();
            if(($resUser['l'] * 1) > 0) {
                $priceup = $priceRoom['priceup'];
                $pricedon = $priceRoom['pricedon'];
                $this->executeUpdatePriceRoom($room, $priceup, $pricedon);
            }
        }

        return $this->view->create(array("success" => true), Response::HTTP_OK);
    }

    /**
     * @param todos $request
     * @param código $code
     * @return array
     */
    public function updatePriceRoomSms($request) {
        $mobile = str_replace('+53', '', $request->request->get('mobile'));

        $queryOws = "SELECT COUNT(ownership.own_id) AS l FROM ownership INNER JOIN room ON (ownership.own_id = room.room_ownership)
WHERE room.room_id = :room_id AND ownership.own_mobile_number = :own_mobile_number";
        $stmtOws = $this->conn->prepare($queryOws);
        $stmtOws->bindValue('own_mobile_number', $mobile);

        $prices = $request->request->get('prices');
        $pricesRooms = $this->parsePrices($prices);
        foreach ($pricesRooms as $priceRoom){
            $room = $priceRoom['room'];
            $stmtOws->bindValue('room_id', $room);
            $stmtOws->execute();
            $r = $stmtOws->fetch();

            if(($r['l'] * 1) > 0) {
                $priceup = $priceRoom['priceup'];
                $pricedon = $priceRoom['pricedon'];
                $this->executeUpdatePriceRoom($room, $priceup, $pricedon, 'sms');
            }
        }

        return $this->view->create(array("success" => true), Response::HTTP_OK);
    }

    public function getStatistics($request) {
        $user = $request->query->get('user');
        $encrypt_password = self::encryptPassword($request->query->get('password'));

        $queryUser = "SELECT ownership.own_id FROM user
INNER JOIN usercasa ON (user.user_id = usercasa.user_casa_user)
INNER JOIN ownership ON (usercasa.user_casa_ownership = ownership.own_id)
WHERE user.user_name = :user_name AND user.user_password = :user_password";

        $stmtUser = $this->conn->prepare($queryUser);
        $stmtUser->bindValue('user_name', $user);
        $stmtUser->bindValue('user_password', $encrypt_password);
        $stmtUser->execute();
        $resUser = $stmtUser->fetch();
        if($resUser != null && $resUser != false && isset($resUser)) {
            $own_id = $resUser['own_id'];
            return $this->view->create(array('own_id'=>$own_id, 'statistics'=>$this->getStatisticsByOwnership($own_id)), Response::HTTP_OK);
        }


        return $this->view->create(array('success' => false), Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param todos $request
     * @return array
     * @internal param código $code
     */
    public function addCancelBooking($request) {
        $user = $request->request->get('user');
        $encrypt_password = self::encryptPassword($request->request->get('password'));

        $cancelationData = $request->request->get('reservation');
        $cancelationDataExplode = explode('-', $cancelationData);
        $reservation = null;
        $reason = "";
        if(count($cancelationDataExplode) > 1){
            $reservation = $cancelationDataExplode[0] * 1;
        }

        /**/
        $query = "SELECT COUNT(generalreservation.gen_res_id) AS l
FROM
  generalreservation
  INNER JOIN ownership ON (generalreservation.gen_res_own_id = ownership.own_id)
  INNER JOIN usercasa ON (ownership.own_id = usercasa.user_casa_ownership)
  INNER JOIN user ON (usercasa.user_casa_user = user.user_id)
  INNER JOIN ownershipreservation ON (generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id)
WHERE
  ownershipreservation.own_res_id = :own_res_id AND user.user_name = :user_name AND user.user_password = :user_password";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_res_id', $reservation);
        $stmt->bindValue('user_name', $user);
        $stmt->bindValue('user_password', $encrypt_password);
        $stmt->execute();
        $r = $stmt->fetch();
        if(($r['l'] * 1) > 0) {
            $r = $this->executeAddCancelBooking($cancelationData);
            if($r) {
                return $this->view->create(array("success" => true), Response::HTTP_OK);
            }
        }

        return $this->view->create(array("success" => false), Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param todos $request
     * @return array
     * @internal param código $code
     */
    public function addCancelBookingSms($request) {
        $mobile = str_replace('+53', '', $request->request->get('mobile'));

        $cancelationData = $request->request->get('reservation');
        $cancelationDataExplode = explode('-', $cancelationData);
        $reservation = null;
        $reason = "";
        if(count($cancelationDataExplode) > 1){
            $reservation = $cancelationDataExplode[0] * 1;
        }

        $query = "SELECT COUNT(generalreservation.gen_res_id) AS l
FROM
  generalreservation
  INNER JOIN ownership ON (generalreservation.gen_res_own_id = ownership.own_id)
  INNER JOIN ownershipreservation ON (generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id)
WHERE
  ownershipreservation.own_res_id = :own_res_id AND
  ownership.own_mobile_number = :own_mobile_number";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_res_id', $reservation);
        $stmt->bindValue('own_mobile_number', $mobile);
        $stmt->execute();
        $r = $stmt->fetch();
        if(($r['l'] * 1) > 0) {
            $r = $this->executeAddCancelBooking($cancelationData);
            if($r) {
                return $this->view->create(array("success" => true), Response::HTTP_OK);
            }
        }

        return $this->view->create(array("success" => false), Response::HTTP_UNAUTHORIZED);
    }

    public function getNotifications($request) {
        $user = $request->query->get('user');
        $encrypt_password = self::encryptPassword($request->query->get('password'));

        $queryUser = "SELECT ownership.own_id FROM user
INNER JOIN usercasa ON (user.user_id = usercasa.user_casa_user)
INNER JOIN ownership ON (usercasa.user_casa_ownership = ownership.own_id)
WHERE user.user_name = :user_name AND user.user_password = :user_password";

        $stmtUser = $this->conn->prepare($queryUser);
        $stmtUser->bindValue('user_name', $user);
        $stmtUser->bindValue('user_password', $encrypt_password);
        $stmtUser->execute();
        $resUser = $stmtUser->fetch();
        if($resUser != null && $resUser != false && isset($resUser)) {
            $own_id = $resUser['own_id'];

            $newV = $user = $request->query->get('new_version');
            $n = $this->getNotificationsOwnership($own_id, $newV);
            return $this->view->create($n, Response::HTTP_OK);
        }


        return $this->view->create(array('success' => false), Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param $request
     * @param McprestController $controller
     * @return mixed
     */
    public function requestChangePass($request, McprestController $controller) {
        $user = $request->request->get('user');

        $query = "SELECT user.user_id, user.user_email, ownership.own_mobile_number AS mobile_number
FROM usercasa INNER JOIN user ON (usercasa.user_casa_user = user.user_id) INNER JOIN ownership ON (usercasa.user_casa_ownership = ownership.own_id)
WHERE user_name = :user_name OR user_email = :user_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('user_name', $user);
        $stmt->execute();
        $po = $stmt->fetch();

        if(isset($po['user_id']) ) {
            $response = $this->executeRequestChangePass($po, $controller);
            if($response != null && isset($response["code"]) && $response["code"] == Response::HTTP_CREATED){
                return $this->view->create(array('success' => true, 'message' => 'Mensaje a '.$user.' se enviado satisfactoriamente.'), Response::HTTP_OK);
            }
            else if($response == null){
                return $this->view->create(array('success' => false, 'message' => 'El usuario '.$user.' ya ha requerido cambio de contraseña.'), Response::HTTP_CONFLICT);
            }
            else{
                return $this->view->create(array('success' => false, 'message' => 'Mensaje a '.$user.' no ha podido enviarse.'), Response::HTTP_BAD_REQUEST);
            }

            /****/
            /*$encode_string = 'kjhop';
            $service_security = $controller->get('secure');
            $decode_string = $service_security->decodeString($encode_string);
            $user_atrib = explode('///', $decode_string);
            $userId = $user_atrib[1];
            $userEmail = $user_atrib[0];
            //$factory = $container->get('security.encoder_factory');
            //$user2 = new User();
            //$encoder = $factory->getEncoder($user2);
            //$encoder->encodePassword($pass, '222');
            $pass = 'baguvix.es';
            $password = self::encryptPassword($pass);*/
        }
        else {
            return $this->view->create(array('success' => false, 'message' => 'El usuario '.$user.' no existe.'), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @param $request
     * @param McprestController $controller
     * @return mixed
     */
    public function changePass($request) {
        $code = $request->request->get('code');
        $pass = $request->request->get('password');

        $query = "SELECT user_request_pass.id, user_request_pass.user FROM user_request_pass WHERE user_request_pass.code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('code', $code);
        $stmt->execute();
        $po = $stmt->fetch();

        if(isset($po['id'])) {
            $userId = $po['user'];
            $password = self::encryptPassword($pass);

            $this->conn->update('user', array('user_password' => $password), array('user_id'=>$userId));
            $this->conn->delete('user_request_pass', array('id' => $po['id']));

            $query = "SELECT user_id,user_name FROM user WHERE user_id=:user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('user_id', $userId);
            $stmt->execute();
            $po = $stmt->fetch();

            return $po;
        }
        else{
            return $this->view->create(array('success' => false, 'message' => 'Codigo de seguridad no registrado'), Response::HTTP_UNAUTHORIZED);
        }
    }

    public function getMessages($request) {
        $user = $request->query->get('user');
        $encrypt_password = self::encryptPassword($request->query->get('password'));

        $queryUser = "SELECT ownership.own_id FROM user
INNER JOIN usercasa ON (user.user_id = usercasa.user_casa_user)
INNER JOIN ownership ON (usercasa.user_casa_ownership = ownership.own_id)
WHERE user.user_name = :user_name AND user.user_password = :user_password";

        $stmtUser = $this->conn->prepare($queryUser);
        $stmtUser->bindValue('user_name', $user);
        $stmtUser->bindValue('user_password', $encrypt_password);
        $stmtUser->execute();
        $resUser = $stmtUser->fetch();
        if($resUser != null && $resUser != false && isset($resUser)) {
            $own_id = $resUser['own_id'];

            $start = Date::createFromString($request->query->get('start'));
            $start = date_format($start, 'Y-m-d');
            $end = Date::createFromString($request->query->get('end'));
            $end = date_format($end, 'Y-m-d');

            $findAll = $request->query->get('find_all') * 1;

            $r = $this->getMessagesOwnership($own_id, $start, $end, $findAll);
            return $this->view->create($r, Response::HTTP_OK);
        }


        return $this->view->create(array('success' => false), Response::HTTP_UNAUTHORIZED);
    }

    public function addMessage($request) {
        $user = $request->request->get('user');
        $encrypt_password = self::encryptPassword($request->request->get('password'));

        $queryUser = "SELECT user.user_id FROM user
INNER JOIN usercasa ON (user.user_id = usercasa.user_casa_user)
INNER JOIN ownership ON (usercasa.user_casa_ownership = ownership.own_id)
WHERE user.user_name = :user_name AND user.user_password = :user_password";

        $stmtUser = $this->conn->prepare($queryUser);
        $stmtUser->bindValue('user_name', $user);
        $stmtUser->bindValue('user_password', $encrypt_password);
        $stmtUser->execute();
        $resUser = $stmtUser->fetch();
        if($resUser != null && $resUser != false && isset($resUser)) {
            $userId = $resUser['user_id'];
            $idOwnres = $request->request->get('id_ownres');
            $message = $request->request->get('message');

            $r = $this->executeAddMessage($userId, $idOwnres, $message);
            return $this->view->create($r, Response::HTTP_OK);
        }

        return $this->view->create(array('success' => false), Response::HTTP_UNAUTHORIZED);
    }

    /********* Auxiliar functions **************/

    /**
     * Buscar las propiedades dado un id de usuario
     * @param $iduser
     * @param $request
     * @return array
     */
    public function getPropertiesByUser($iduser, $request) {
        $query = "SELECT * FROM usercasa WHERE  user_casa_user=:user_casa_user; ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('user_casa_user', $iduser);
        $stmt->execute();
        $po = $stmt->fetchAll();
        $property = array();
        foreach ($po as $item) {
            $property[] = self::getPropertiesByCode($item['user_casa_ownership']);
        }

        //Me muevo por las casas
        for ($j = 0; $j < count($property); $j++) {
            for ($i = 0; $i < count($property[$j]['room']); $i++) {
                $start = Date::createFromString($request->request->get('start'));
                $start = date_format($start, 'Y-m-d');
                $end = Date::createFromString($request->request->get('end'));
                $end = date_format($end, 'Y-m-d');
                $unavailability = self::getUnavailability($start, $end, $property[$j]['room'][$i]['room_id']);
                $reservations = self::getOwnerShipReservation($property[$j]['room'][$i]['room_id'], $start, $end);
                $property[$j]['room'][$i]['unavailability'] = $unavailability;
                $property[$j]['room'][$i]['reservations'] = $reservations;
            }

            $property[$j]['statistics'] = $this->getStatisticsByOwnership($property[$j]['own_id']);
        }

        return $property;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getPropertiesByCode($code) {
        $select = "o.own_id,";
        $select_room = "";
        $innerJoin = " INNER JOIN destination d ON (o.own_destination = d.des_id) ";
        $rooms = array();
        $select .= 'o.own_mcp_code,';
        $select .= 'o.own_name,';
        $select .= 'd.des_name,';
        $select .= 'o.own_commission_percent,';
        /*$select .= 'o.own_address_street,';
        $select .= 'o.own_address_number,';
        $select .= 'o.own_address_between_street_1,';
        $select .= 'o.own_address_between_street_2,';
        $select .= 'p.prov_name,';
        $innerJoin .= "INNER JOIN province p ON o.own_address_province = p.prov_id ";
        $select .= 'm.mun_name,';
        $innerJoin .= "INNER JOIN municipality m ON o.own_address_municipality = m.mun_id ";*/

        /*$select .= 'o.own_mobile_number,';
        $select .= 'o.own_phone_number,';
        $select .= 'o.own_email_1,';
        $select .= 'o.own_email_2,';*/
        $select_room .= 'r.room_id,';
        $select_room .= 'r.room_type,';
        $select_room .= 'r.room_num,';
        $select_room .= 'r.room_price_up_to,';
        $select_room .= 'r.room_price_down_to,';
        /*$select_room .= 'r.room_price_special,';*/
        /*$select_room .= 'r.room_climate,';
        $select_room .= 'r.room_audiovisual,';
        $select_room .= 'r.room_smoker,';
        $select_room .= 'r.room_safe,';
        $select_room .= 'r.room_baby,';
        $select_room .= 'r.room_bathroom,';
        $select_room .= 'r.room_stereo,';
        $select_room .= 'r.room_windows,';
        $select_room .= 'r.room_balcony,';
        $select_room .= 'r.room_terrace,';
        $select_room .= 'r.room_yard,';*/

        $query = "SELECT " . substr($select, 0, -1) . " FROM ownership o " . $innerJoin . " WHERE o.own_id=:code; ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('code', $code);
        $stmt->execute();
        $ship = $stmt->fetchAll();
        if(count($ship)) {
            if($select_room != '') {
                $query = "SELECT " . substr($select_room, 0, -1) . " FROM room r WHERE r.room_ownership=:code; ";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue('code', $ship[0]['own_id']);
                $stmt->execute();
                $rooms = $stmt->fetchAll();
                $ship[0]['room'] = $rooms;
            }
            return $ship[0];
        }
    }

    /**
     * @param $cas
     * @param $availability
     * @return bool
     */
    public function executeAddResponseQuickBooking($cas, $availability) {
        $query = "SELECT COUNT(generalreservation.gen_res_id) AS l FROM generalreservation WHERE generalreservation.gen_res_id = :gen_res_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('gen_res_id', $cas);

        $stmt->execute();
        $r = $stmt->fetch();

        if($r['l'] * 1 > 0) {
            $this->conn->insert('availability_owner', array('reservation' => $cas, 'res_status' => $availability, 'active' => 1));
        }

        return true;
    }

    public function executeUpdatePriceRoom($room, $priceup, $pricedon, $place = 'wifi') {
        $query = "update room set room.room_price_up_to =:priceup , room.room_price_down_to = :pricedown where room_id= :room";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('priceup', $priceup);
        $stmt->bindValue('pricedown', $pricedon);
        $stmt->bindValue('room', $room);

        $stmt->execute();

        $reason = 'Precios actualizados hab. ' . $room . ' Temporada alta:' . $priceup . ', Temporada baja:' . $pricedon . ' Via:' . $place;
        $this->insertLog(BackendModuleName::MODULE_OWNERSHIP, Operations::UPDATE_PRICES, 'room', $reason);

        return $this->view->create(array("success" => true), Response::HTTP_OK);
    }

    public function parseAvailability($availability) {
        $result = [];
        $availabilityByRooms = explode(',', $availability);
        foreach ($availabilityByRooms as $availabilityRoom) {
            $availabilityRoom = explode(':', $availabilityRoom);
            $room = $availabilityRoom[0];
            $availability = $availabilityRoom[1];
            $availabilities = [];

            $start = \DateTime::createFromFormat('Ymd', substr($availability, 0, 8));
            $r = (strlen($availability) - 8) * -1;
            $availability = ($r != 0) ? (substr($availability, $r)) : ("");

            $end = \DateTime::createFromFormat('Ymd', substr($availability, 0, 8));
            $r = (strlen($availability) - 8) * -1;
            $availability = ($r != 0) ? (substr($availability, $r)) : ("");

            while (strlen($availability) >= 8) {
                $from = \DateTime::createFromFormat('Ymd', $start->format("Y") . substr($availability, 0, 4));
                $to = \DateTime::createFromFormat('Ymd', $start->format("Y") . substr($availability, 4, 4));

                if($from < $start) {
                    $from->setDate($end->format("Y"), $from->format("m"), $from->format("d"));
                    $to->setDate($end->format("Y"), $to->format("m"), $to->format("d"));
                }

                if($to < $start) {
                    $to->setDate($end->format("Y"), $to->format("m"), $to->format("d"));
                }

                $o = new \stdClass();
                $o->start = $from->format('Y-m-d');
                $o->end = $to->format('Y-m-d');
                $availabilities[] = $o;

                $availability = (strlen($availability) > 8) ? (substr($availability, (strlen($availability) - 8) * -1)) : ('');
            }

            $result[] = array('room' => $room, 'start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d'), 'availabilities' => $availabilities);
        }

        return $result;
    }

    public function parsePrices($prices) {
        $result = [];
        $pricesByRooms = explode(',', $prices);
        foreach ($pricesByRooms as $priceRoom) {
            $priceRoom = explode(':', $priceRoom);
            $room = $priceRoom[0];
            $prices = explode("-", $priceRoom[1]);
            $priceup = $prices[0];
            $pricedon = $prices[1];

            $result[] = array('room' => $room, 'priceup' => $priceup, 'pricedon' => $pricedon);
        }

        return $result;
    }

    /**
     * Adicionar la No Disponibilidad de una habitación por rango
     * @param código $roomId código de la propiedad
     * @param $start
     * @param $end
     * @param $unavailabilities
     * @param $reason
     * @return array
     */
    public function addavailableroombyrange($roomId, $start, $end, $unavailabilities, $reason, $userId = null) {
        /*Obtener la no disponibilidad que el start cae dentro de la no disponibilidad*/
        $query = "SELECT unavailabilitydetails.ud_id, unavailabilitydetails.ud_to_date, unavailabilitydetails.ud_sync_st,  unavailabilitydetails.ud_reason FROM unavailabilitydetails";
        $whereAndLimit = " WHERE room_id = :room_id AND ud_from_date < :start AND ud_to_date >= :start LIMIT 1";
        $stmt = $this->conn->prepare($query . $whereAndLimit);
        $stmt->bindValue('room_id', $roomId);
        $stmt->bindValue('start', $start);
        $stmt->execute();
        $unavailability = $stmt->fetch();

        if($unavailability != false) {
            $ud_to_date_new = (new \DateTime($start))->modify('-1 day')->format('Y-m-d');
            $query = "UPDATE unavailabilitydetails SET  ud_to_date = :ud_to_date";
            $stmt = $this->conn->prepare($query . $whereAndLimit);
            $stmt->bindValue('ud_to_date', $ud_to_date_new);
            $stmt->bindValue('room_id', $roomId);
            $stmt->bindValue('start', $start);
            $stmt->execute();

            if((new \DateTime($unavailability['ud_to_date'])) > (new \DateTime($end))) {
                $ud_from_date = (new \DateTime($end))->modify('+1 day')->format('Y-m-d');

                $query = "INSERT INTO unavailabilitydetails (room_id, ud_sync_st, ud_from_date, ud_to_date, ud_reason) VALUE (:room_id, :ud_sync_st, :ud_from_date, :ud_to_date, :ud_reason)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue('room_id', $roomId);
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
        $stmt->bindValue('room_id', $roomId);
        $stmt->bindValue('end', $end);
        $stmt->execute();

        /*elimino las que estan en el rango*/
        $query = "DELETE FROM unavailabilitydetails WHERE room_id = :room_id AND ud_from_date >= :start AND ud_from_date <= :end AND ud_to_date >= :start AND ud_to_date <= :end ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('room_id', $roomId);
        $stmt->bindValue('start', $start);
        $stmt->bindValue('end', $end);
        $stmt->execute();

        /*incerto las no disponibilidades new*/
        $reason = isset($reason) ? $reason : '';
        foreach ($unavailabilities as $unavailability) {
            $this->conn->insert('unavailabilitydetails', array('room_id' => $roomId, 'ud_sync_st' => 0, 'ud_from_date' => $unavailability->start, 'ud_to_date' => $unavailability->end, 'ud_reason' => $reason));

            $reason .= ' Fechas('.$unavailability->start.' a '.$unavailability->end.')';
            $this->insertLog(BackendModuleName::MODULE_UNAVAILABILITY_DETAILS, Operations::SAVE_AND_NEW, 'unavailabilitydetails', $reason, $userId);
        }

        $query = "SELECT ownership.own_id FROM room INNER JOIN ownership ON (room.room_ownership = ownership.own_id) WHERE room.room_id = :room_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('room_id', $roomId);
        $stmt->execute();
        $ownership = $stmt->fetch();
        $ownership = $ownership['own_id'];
        $query = "UPDATE ownership SET own_availability_update = :own_availability_update WHERE own_id = :own_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_availability_update', date_format(new \DateTime(), 'Y-m-d'));
        $stmt->bindValue('own_id', $ownership);
        $stmt->execute();

        $this->addTaskFromMyCasaRenta(1, $roomId);

        $this->insertAccommodationCalendarFrequency($roomId, $reason);

        return $this->view->create(array('success' => true), 200);
    }

    public function getStatisticsByOwnership($own_id){
        $query = "SELECT
  ownership_ranking_extra.id,
  ownership_ranking_extra.startDate AS start_date,
  ownership_ranking_extra.endDate AS end_date,
  ownership_ranking_extra.ranking AS ranking_points,
  nomenclator.nom_name AS category,
  ownership_ranking_extra.place AS place,
  ownership_ranking_extra.destinationPlace AS destination_place,
  ownership_ranking_extra.visits AS visits,
  ownership_ranking_extra.totalAvailableRooms AS available,
  ownership_ranking_extra.totalNonAvailableRooms AS unavailable,
  ownership_ranking_extra.totalAvailableFacturation AS available_facturation,
  ownership_ranking_extra.totalNonAvailableFacturation AS unavailable_facturation,
  ownership_ranking_extra.totalReservedRooms AS total_reserved,
  ownership_ranking_extra.totalFacturation AS total_facturation,
  ownership_ranking_extra.currentMonthFacturation AS current_month_facturation
FROM ownership_ranking_extra
LEFT JOIN nomenclator ON (ownership_ranking_extra.category = nomenclator.nom_id)
WHERE ownership_ranking_extra.accommodation = :own_id
ORDER BY ownership_ranking_extra.startDate DESC LIMIT 2";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_id', $own_id);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    public function getNotificationsOwnership($own_id, $newVersion){
        $querySelect = "SELECT notification.id, notification.message, notification.subtype, notification.created AS string_created, notification.description AS reason
FROM notification WHERE notification.status = 0 AND notification.id_ownership = :own_id AND (notification.sync IS NULL OR notification.sync = 0)";
        $stmtSelect = $this->conn->prepare($querySelect);
        $stmtSelect->bindValue('own_id', $own_id);
        $stmtSelect->execute();
        $po = $stmtSelect->fetchAll();

        $query = "UPDATE notification SET sync = 1
WHERE notification.status = 0 AND notification.id_ownership = :own_id AND (notification.sync IS NULL OR notification.sync = 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_id', $own_id);
        $stmt->execute();

        if(isset($newVersion)){
            $newVersion = "2.2.2";
            $date = new \DateTime();
            $po[] = array("message"=>"La aplicación MyCasa Renta dispone de una nueva versión", "subtype"=>"NEW_VERSION","string_created"=>$date->format('Y-m-d H:i:s'),"reason"=>$newVersion);
        }

        return $po;
    }

    public function executeAddCancelBooking($reservation) {
        $reservation .= '';
        return $this->addTaskFromMyCasaRenta(0, $reservation);
    }

    public function executeRequestChangePass($userData, McprestController $controller){
        $userId = $userData['user_id'];

        $query = "SELECT user_request_pass.id, user_request_pass.user FROM user_request_pass WHERE user_request_pass.user = :user";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('user', $userId);
        $stmt->execute();
        $po = $stmt->fetch();
        if(isset($po['user']) ) {
            return null;
        }

        $encode_string = Utils::generateCode();
        $ok = false;

        while(!$ok){
            $query = "SELECT user_request_pass.id FROM user_request_pass WHERE user_request_pass.code = :code";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('code', $encode_string);
            $stmt->execute();
            $po = $stmt->fetch();

            if(isset($po['id'])) {
                $encode_string = Utils::generateCode();
            }
            else{
                $ok = true;
            }
        }

        $mobileNumber = $userData['mobile_number'];
        $message = 'MyCasaParticular codigo de confirmacion:'.$encode_string;

        $notificationServiceApiKey = $controller->getContainer()->getParameter('notification_service_api_key');
        $serviceNotificationUrl = $controller->getContainer()->getParameter('notification_service_url');

        $data['sms'] = array(
            'project' => $notificationServiceApiKey,//Obligatorio
            'to' => "53" . $mobileNumber,//8 digitos, comenzando con 5
            'msg' => $message,//No obligatorio
            'sms_type' => "CONFIRMATION_CODE",//Obligatorio
        );

        $url = $serviceNotificationUrl . '/api/sms/add';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        $code = $info['http_code'];
        $resp = array(
            "code" => $code,
            "response" => $response,
            "message" => $message,
            "mobile" => $mobileNumber
        );

        if($code == Response::HTTP_CREATED){
            $this->conn->insert('user_request_pass', array('user' => $userId, 'code' => $encode_string));
        }

        return $resp;
    }

    public function getMessagesOwnership($own_id, $start, $end, $findAll){
        $where = "WHERE (own_res_reservation_from_date >= :from_date AND own_res_reservation_from_date <= :to_date OR own_res_reservation_to_date >= :from_date AND
  own_res_reservation_to_date <= :to_date) AND own_res_status = 5 AND ownership.own_id = :own_id";

        if(!$findAll){
            $where = $where . " AND message.sync = 0";
        }

        $querySelect = "SELECT message.own_res_id,message.message_id,message.message_send_to,message.message_sender,message.mesage_body,message.message_date,message.reading, message.sync
FROM ownership
  INNER JOIN room ON (ownership.own_id = room.room_ownership)
  INNER JOIN ownershipreservation ON (room.room_id = ownershipreservation.own_res_selected_room_id)
  INNER JOIN message ON (ownershipreservation.own_res_id = message.own_res_id) " . $where;
        $stmtSelect = $this->conn->prepare($querySelect);
        $stmtSelect->bindValue('own_id', $own_id);
        $stmtSelect->bindValue('from_date', $start);
        $stmtSelect->bindValue('to_date', $end);
        $stmtSelect->execute();
        $po = $stmtSelect->fetchAll();

        $query = "UPDATE message
  INNER JOIN ownershipreservation ON (message.own_res_id = ownershipreservation.own_res_id)
  INNER JOIN room ON (ownershipreservation.own_res_selected_room_id = room.room_id)
  INNER JOIN ownership ON (room.room_ownership = ownership.own_id)
  SET message.sync = 1, message.reading = 1 " . $where;
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('own_id', $own_id);
        $stmt->bindValue('from_date', $start);
        $stmt->bindValue('to_date', $end);
        $stmt->execute();

        return $po;
    }

    public function executeAddMessage($userId, $idOwnres, $message){
        $queryUser = "SELECT user.user_id
FROM ownershipreservation
  INNER JOIN generalreservation ON (ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id)
  INNER JOIN user ON (generalreservation.gen_res_user_id = user.user_id)
WHERE ownershipreservation.own_res_id = :own_res_id";

        $stmtUser = $this->conn->prepare($queryUser);
        $stmtUser->bindValue('own_res_id', $idOwnres);
        $stmtUser->execute();
        $resUser = $stmtUser->fetch();
        if($resUser != null && $resUser != false && isset($resUser)) {
            $xuserId = $resUser['user_id'];
            $date = new \DateTime();

            $this->conn->insert('message', array('message_send_to' => $xuserId, 'message_sender' => $userId, 'mesage_body' => $message,
                'message_date' => $date->format('Y-m-d H:i:s'), 'own_res_id' => $idOwnres, 'reading' => true, 'sync' => true));
            $id = $this->conn->lastInsertId();
            return array('message_id'=>$id, 'message_send_to'=>$xuserId, 'message_date'=>$date->format('Y-m-d H:i:s'));
        }

        return array();
    }

    public function addTaskFromMyCasaRenta($type, $data){
        $creation_date = new \DateTime();
        $creation_date = $creation_date->format('Y-m-d');

        $this->conn->insert('task_renta', array('type' => $type, 'status' => 0, 'data' => $data, 'creation_date' => $creation_date));
        return true;
    }

    public function insertAccommodationCalendarFrequency($roomId, $source) {
        $queryOwnership = "SELECT room_ownership FROM room WHERE room.room_id = :room_id";
        $stmtOwnership = $this->conn->prepare($queryOwnership);
        $stmtOwnership->bindValue('room_id', $roomId);
        $stmtOwnership->execute();
        $resOwnership = $stmtOwnership->fetch();

        if(isset($resOwnership['room_ownership'])){
            $accommodation = $resOwnership['room_ownership'];
            $queryFrequency = "SELECT id FROM accommodation_calendar_frequency WHERE accommodation_calendar_frequency.accommodation = :accommodation AND accommodation_calendar_frequency.updatedDate = :updatedDate";
            $stmtFrequency = $this->conn->prepare($queryFrequency);
            $stmtFrequency->bindValue('accommodation', $accommodation);
            $stmtFrequency->bindValue('updatedDate', (new \DateTime(date('Y-m-d')))->format('Y-m-d'));
            $stmtFrequency->execute();
            $resFrequency = $stmtFrequency->fetch();
            if(!isset($resFrequency['id'])){
                $queryFrequency = "INSERT INTO accommodation_calendar_frequency (accommodation,updatedDate,source) VALUE(:accommodation,:updatedDate,:source)";
                $stmtFrequency = $this->conn->prepare($queryFrequency);
                $stmtFrequency->bindValue('accommodation', $accommodation);
                $stmtFrequency->bindValue('updatedDate', (new \DateTime(date('Y-m-d')))->format('Y-m-d'));
                $stmtFrequency->bindValue('source', $source);
                $stmtFrequency->execute();
            }
        }
    }

    /******************** metodos auxiliares para el calculo de precios ***************************/

    public function datesBetween($startdate, $enddate, $format = null) {

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
        $dates = $this->datesBetween($startdate, $enddate, $format);
        return count($dates) - 1;
    }

    public function getPriceTotalAndNights($startdate, $enddate, $ownResNightPrice, $ownResTotalInSite)
    {
        $nights = $this->nights($startdate, $enddate);
        $totalPrice = 0;
        if($ownResNightPrice > 0){
            $totalPrice += $ownResNightPrice * $nights;
        }
        else{
            $totalPrice += $ownResTotalInSite;
        }

        return array('nights'=>$nights, 'total_price'=>$totalPrice);
    }

    public function getPricePerInHome($startdate, $enddate, $ownResNightPrice, $ownResTotalInSite, $ownCommissionPercent)
    {
        $c = $ownCommissionPercent/100;
        $priceTotalAndNights = $this->getPriceTotalAndNights($startdate, $enddate, $ownResNightPrice, $ownResTotalInSite);
        $p = $priceTotalAndNights['total_price'];
        return  array('nights'=>$priceTotalAndNights['nights'], 'price_in_home'=>$p * (1 - $c));
    }
}