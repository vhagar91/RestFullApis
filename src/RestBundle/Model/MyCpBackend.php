<?php


namespace RestBundle\Model;


class MyCpBackend extends Base
{

    /**
     * Devuelve el listado de reservaciones en MyCasaParticular
     * @param $request
     * @return mixed
     */
    public function listReservations($request)
    {
        $array_temp = array();
        $page = 0;
        $limit = 10;
        ($request->query->get('start') != '') ? $page = $request->query->get('start') : $page;
        ($request->query->get('limit') != '') ? $limit = $request->query->get('limit') : $limit;
        ($request->query->get('filter_date_reserve') != '') ? $array_temp['filter_date_reserve'] = $request->query->get('filter_date_reserve') : $array_temp;
        ($request->query->get('filter_offer_number') != '') ? $array_temp['filter_offer_number'] = $request->query->get('filter_offer_number') : $array_temp;
        ($request->query->get('filter_reference') != '') ? $array_temp['filter_reference'] = $request->query->get('filter_reference') : $array_temp;
        ($request->query->get('filter_date_from') != '') ? $array_temp['filter_date_from'] = $request->query->get('filter_date_from') : $array_temp;
        ($request->query->get('filter_date_to') != '') ? $array_temp['filter_date_to'] = $request->query->get('filter_date_to') : $array_temp;
        ($request->query->get('filter_booking_number') != '') ? $array_temp['filter_booking_number'] = $request->query->get('filter_booking_number') : $array_temp;
        ($request->query->get('filter_status') != '') ? $array_temp['filter_status'] = $request->query->get('filter_status') : $array_temp;
        ($request->query->get('filter_destination') != '') ? $array_temp['filter_destination'] = $request->query->get('filter_destination') : $array_temp;
        ($request->query->get('filter_user_name') != '') ? $array_temp['filter_user_name'] = $request->query->get('filter_user_name') : $array_temp;
        ($request->query->get('filter_user_lastname') != '') ? $array_temp['filter_user_lastname'] = $request->query->get('filter_user_lastname') : $array_temp;
        ($request->query->get('filter_id_reservation') != '') ? $array_temp['filter_id_reservation'] = $request->query->get('filter_id_reservation') : $array_temp;
        $where = "";
        if (array_key_exists('filter_date_reserve', $array_temp)) {
            $resDate = $array_temp['filter_date_reserve'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " generalreservation.gen_res_date >= '$resDate'";
        }
        if (array_key_exists('filter_date_from', $array_temp)) {
            $filter_date_from = $array_temp['filter_date_from'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " generalreservation.gen_res_from_date >= '$filter_date_from'";
        }
        if (array_key_exists('filter_date_to', $array_temp)) {
            $filter_date_to = $array_temp['filter_date_to'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " generalreservation.gen_res_to_date <= '$filter_date_to'";
        }
        if (array_key_exists('filter_status', $array_temp)) {
            $filter_status = $array_temp['filter_status'];
            $where .= " AND generalreservation.gen_res_status = $filter_status ";
        }
        if (array_key_exists('filter_reference', $array_temp)) {
            $filter_reference = $array_temp['filter_reference'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " ownership.own_mcp_code LIKE '%$filter_reference%'";
        }
        if (array_key_exists('filter_destination', $array_temp)) {
            $filter_destination = $array_temp['filter_destination'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " destination.des_name LIKE '%$filter_destination%'";
        }
        if (array_key_exists('filter_user_name', $array_temp)) {
            $filter_user_name = $array_temp['filter_user_name'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " user.user_user_name LIKE '%$filter_user_name%'";
        }
        if (array_key_exists('filter_user_lastname', $array_temp)) {
            $filter_user_lastname = $array_temp['filter_user_lastname'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " user.user_last_name LIKE '%$filter_user_lastname%'";
        }
        if (array_key_exists('filter_id_reservation', $array_temp)) {
            $filter_id_reservation = $array_temp['filter_id_reservation'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " generalreservation.gen_res_id LIKE '%$filter_id_reservation%'";
        }
        if (array_key_exists('filter_booking_number', $array_temp)) {
            $filter_booking_number = $array_temp['filter_booking_number'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " (SELECT COUNT(ownershipreservation.own_res_id)  FROM ownershipreservation  WHERE generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id AND ownershipreservation.own_res_reservation_booking = $filter_booking_number) > 0 ";
        }

        $ls = " LIMIT $limit OFFSET $page";
        $query = "SELECT generalreservation.gen_res_date, generalreservation.gen_res_id, ownership.own_mcp_code, ownership.own_id, generalreservation.gen_res_total_in_site,generalreservation.gen_res_status,generalreservation.gen_res_from_date,
        (SELECT count(ownershipreservation.own_res_id) FROM ownershipreservation  WHERE own_res_gen_res_id = generalreservation.gen_res_id) AS rooms,
        (SELECT SUM(ownershipreservation.own_res_count_adults) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as adults,
        (SELECT SUM(ownershipreservation.own_res_count_childrens) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as childrens,
        (SELECT MIN(ownershipreservation.own_res_reservation_from_date) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as dateFrom,
        (SELECT MIN(ownershipreservation.own_res_reservation_to_date) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as dateTo,
        (SELECT SUM(DATEDIFF(ownershipreservation.own_res_reservation_to_date, ownershipreservation.own_res_reservation_from_date)) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) AS totalNights,
        user.user_user_name, user.user_last_name, user.user_email, destination.des_name
        FROM generalreservation INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id INNER JOIN destination ON ownership.own_destination = destination.des_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
        $where $ls";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        $query = "SELECT COUNT(generalreservation.gen_res_id) as total FROM generalreservation INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id INNER JOIN destination ON ownership.own_destination = destination.des_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
        $where";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $poTotal = $stmt->fetch();
        return $this->view->create(array('total' => $poTotal['total'], 'data' => $po), 200);

    }

    public function listClients($request)
    {
        $array_temp = array();
        $page = 0;
        $limit = 10;
        ($request->query->get('start') != '') ? $page = $request->query->get('start') : $page;
        ($request->query->get('limit') != '') ? $limit = $request->query->get('limit') : $limit;
        $ls = " LIMIT $limit OFFSET $page";
        $filter_user_name = strtolower($request->query->get('filter_user_name'));
        $filter_user_email = strtolower($request->query->get('filter_user_email'));
        $filter_user_city = strtolower($request->query->get('filter_user_city'));
        $filter_user_country = strtolower($request->query->get('filter_user_country'));
        $ownId = strtolower($request->query->get('own_id'));
        $sort_by = strtolower($request->query->get('sort_by'));

        $string_order = '';
        switch ($sort_by) {
            case 'reservations':
                $string_order = "ORDER BY total_reserves DESC";
                break;
            case 'user_name':
                $string_order = "ORDER BY user.user_user_name ASC";
                break;
            case 'user_city':
                $string_order = "ORDER BY user.user_city ASC, total_reserves DESC";
                break;
            case 'user_email':
                $string_order = "ORDER BY user.user_email ASC";
                break;
            case 'user_country':
                $string_order = "ORDER BY country.co_name ASC, total_reserves DESC";
                break;
            default:
                $string_order = "ORDER BY last_reservation DESC";
                break;
        }

        $whereOwn = "";
        $countReservations = "";

        if ($ownId != null) {
            $whereOwn = " AND ownership.own_id = $ownId AND (generalreservation.gen_res_status =2 OR generalreservation.gen_res_status = 5)";
            $countReservations = " AND generalreservation.gen_res_own_id = $ownId AND (generalreservation.gen_res_status =2 OR generalreservation.gen_res_status = 5)";
        }


        $queryString = "SELECT DISTINCT
            user.user_id,
            user.user_user_name,
            user.user_last_name,
            user.user_city,
            user.user_email,
            country.co_name,
            (SELECT count(generalreservation.gen_res_id) FROM generalreservation  WHERE generalreservation.gen_res_user_id = user.user_id $countReservations) as total_reserves,
            (SELECT MAX(generalreservation.gen_res_date) FROM generalreservation  WHERE generalreservation.gen_res_user_id = user.user_id $countReservations) as last_reservation,
            (SELECT min(lang.lang_name) FROM usertourist INNER JOIN lang ON user_tourist_language=lang.lang_id WHERE usertourist.user_tourist_user = user.user_id) as langName,
            (SELECT min(currency.curr_code) FROM usertourist INNER JOIN currency ON usertourist.user_tourist_currency = currency.curr_id WHERE usertourist.user_tourist_user = user.user_id) as currName
            FROM generalreservation
            INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id
            INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
            INNER JOIN country ON user.user_country = country.co_id
            WHERE (user.user_user_name LIKE :filter_user_name
            OR user.user_last_name LIKE :filter_user_name)
            AND user.user_email LIKE :filter_user_email
            AND user.user_city LIKE :filter_user_city
            AND country.co_name LIKE :filter_user_country $whereOwn $string_order $ls";

        $stmt = $this->conn->prepare($queryString);
        $stmt->bindValue('filter_user_name', "%" . $filter_user_name . "%");
        $stmt->bindValue('filter_user_email', "%" . $filter_user_email . "%");
        $stmt->bindValue('filter_user_city', "%" . $filter_user_city . "%");
        $stmt->bindValue('filter_user_country', "%" . $filter_user_country . "%");
        $stmt->execute();
        $po = $stmt->fetchAll();
        $queryString = "SELECT COUNT(DISTINCT(user.user_id))as total FROM generalreservation
            INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id
            INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
            INNER JOIN country ON user.user_country = country.co_id
            WHERE (user.user_user_name LIKE :filter_user_name
            OR user.user_last_name LIKE :filter_user_name)
            AND user.user_email LIKE :filter_user_email
            AND user.user_city LIKE :filter_user_city
            AND country.co_name LIKE :filter_user_country $whereOwn";
        $stmt = $this->conn->prepare($queryString);
        $stmt->bindValue('filter_user_name', "%" . $filter_user_name . "%");
        $stmt->bindValue('filter_user_email', "%" . $filter_user_email . "%");
        $stmt->bindValue('filter_user_city', "%" . $filter_user_city . "%");
        $stmt->bindValue('filter_user_country', "%" . $filter_user_country . "%");
        $stmt->execute();
        $poTotal = $stmt->fetch();
        return $this->view->create(array('total' => $poTotal['total'], 'data' => $po), 200);
    }

    public function listBookings($request)
    {
        $array_temp = array();
        $page = 0;
        $limit = 10;
        ($request->query->get('start') != '') ? $page = $request->query->get('start') : $page;
        ($request->query->get('limit') != '') ? $limit = $request->query->get('limit') : $limit;
        ($request->query->get('filter_date_reserve') != '') ? $array_temp['filter_date_reserve'] = $request->query->get('filter_date_reserve') : $array_temp;
        ($request->query->get('filter_offer_number') != '') ? $array_temp['filter_offer_number'] = $request->query->get('filter_offer_number') : $array_temp;
        ($request->query->get('filter_reference') != '') ? $array_temp['filter_reference'] = $request->query->get('filter_reference') : $array_temp;
        ($request->query->get('filter_date_from') != '') ? $array_temp['filter_date_from'] = $request->query->get('filter_date_from') : $array_temp;
        ($request->query->get('filter_date_to') != '') ? $array_temp['filter_date_to'] = $request->query->get('filter_date_to') : $array_temp;
        ($request->query->get('filter_booking_number') != '') ? $array_temp['filter_booking_number'] = $request->query->get('filter_booking_number') : $array_temp;
        ($request->query->get('filter_status') != '') ? $array_temp['filter_status'] = $request->query->get('filter_status') : $array_temp;
        $where = "";
        if (array_key_exists('filter_date_reserve', $array_temp)) {
            $resDate = $array_temp['filter_date_reserve'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " generalreservation.gen_res_date >= '$resDate'";
        }
        if (array_key_exists('filter_date_from', $array_temp)) {
            $filter_date_from = $array_temp['filter_date_from'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " generalreservation.gen_res_from_date >= '$filter_date_from'";
        }
        if (array_key_exists('filter_date_to', $array_temp)) {
            $filter_date_to = $array_temp['filter_date_to'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " generalreservation.gen_res_to_date <= '$filter_date_to'";
        }
        if (array_key_exists('filter_status', $array_temp)) {
            $filter_status = $array_temp['filter_status'];
            $where .= " AND generalreservation.gen_res_status = $filter_status ";
        }
        if (array_key_exists('filter_status', $array_temp)) {
            $filter_reference = $array_temp['filter_reference'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " ownership.own_mcp_code LIKE '%$filter_reference%'";
        }
        if (array_key_exists('filter_booking_number', $array_temp)) {
            $filter_booking_number = $array_temp['filter_booking_number'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " (SELECT COUNT(ownershipreservation.own_res_id)  FROM ownershipreservation  WHERE generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id AND ownershipreservation.own_res_reservation_booking = $filter_booking_number) > 0 ";
        }
        $ls = " LIMIT $limit OFFSET $page";
        $query = "SELECT generalreservation.gen_res_date, generalreservation.gen_res_id, ownership.own_mcp_code, ownership.own_id, generalreservation.gen_res_total_in_site,generalreservation.gen_res_status,generalreservation.gen_res_from_date,
        (SELECT count(ownershipreservation.own_res_id) FROM ownershipreservation  WHERE own_res_gen_res_id = generalreservation.gen_res_id) AS rooms,
        (SELECT SUM(ownershipreservation.own_res_count_adults) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as adults,
        (SELECT SUM(ownershipreservation.own_res_count_childrens) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as childrens,
        (SELECT MIN(ownershipreservation.own_res_reservation_from_date) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as dateFrom,
        (SELECT MIN(ownershipreservation.own_res_reservation_to_date) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as dateTo,
        (SELECT SUM(DATEDIFF(ownershipreservation.own_res_reservation_to_date, ownershipreservation.own_res_reservation_from_date)) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) AS totalNights,
        user.user_user_name, user.user_last_name, user.user_email, destination.des_name
        FROM generalreservation INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id INNER JOIN destination ON ownership.own_destination = destination.des_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
        $where $ls";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        $query = "SELECT COUNT(generalreservation.gen_res_id) as total
        FROM generalreservation INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id INNER JOIN destination ON ownership.own_destination = destination.des_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
        $where";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $poTotal = $stmt->fetch();
        return $this->view->create(array('total' => $poTotal['total'], 'data' => $po), 200);
    }

    public function listCheckins($request)
    {
        $array_temp = array();
        $page = 0;
        $limit = 10;
        ($request->query->get('start') != '') ? $page = $request->query->get('start') : $page;
        ($request->query->get('limit') != '') ? $limit = $request->query->get('limit') : $limit;
        ($request->query->get('filter_date_reserve') != '') ? $array_temp['filter_date_reserve'] = $request->query->get('filter_date_reserve') : $array_temp;
        ($request->query->get('filter_offer_number') != '') ? $array_temp['filter_offer_number'] = $request->query->get('filter_offer_number') : $array_temp;
        ($request->query->get('filter_reference') != '') ? $array_temp['filter_reference'] = $request->query->get('filter_reference') : $array_temp;
        ($request->query->get('filter_date_from') != '') ? $array_temp['filter_date_from'] = $request->query->get('filter_date_from') : $array_temp;
        ($request->query->get('filter_date_to') != '') ? $array_temp['filter_date_to'] = $request->query->get('filter_date_to') : $array_temp;
        ($request->query->get('filter_booking_number') != '') ? $array_temp['filter_booking_number'] = $request->query->get('filter_booking_number') : $array_temp;
        ($request->query->get('filter_status') != '') ? $array_temp['filter_status'] = $request->query->get('filter_status') : $array_temp;
        $where = "";
        if (array_key_exists('filter_date_reserve', $array_temp)) {
            $resDate = $array_temp['filter_date_reserve'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " generalreservation.gen_res_date >= '$resDate'";
        }
        if (array_key_exists('filter_date_from', $array_temp)) {
            $filter_date_from = $array_temp['filter_date_from'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " generalreservation.gen_res_from_date >= '$filter_date_from'";
        }
        if (array_key_exists('filter_date_to', $array_temp)) {
            $filter_date_to = $array_temp['filter_date_to'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " generalreservation.gen_res_to_date <= '$filter_date_to'";
        }
        if (array_key_exists('filter_status', $array_temp)) {
            $filter_status = $array_temp['filter_status'];
            $where .= " AND generalreservation.gen_res_status = $filter_status ";
        }
        if (array_key_exists('filter_status', $array_temp)) {
            $filter_reference = $array_temp['filter_reference'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " ownership.own_mcp_code LIKE '%$filter_reference%'";
        }
        if (array_key_exists('filter_booking_number', $array_temp)) {
            $filter_booking_number = $array_temp['filter_booking_number'];
            $where .= (($where != "") ? " AND " : " WHERE ") . " (SELECT COUNT(ownershipreservation.own_res_id)  FROM ownershipreservation  WHERE generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id AND ownershipreservation.own_res_reservation_booking = $filter_booking_number) > 0 ";
        }
        $ls = " LIMIT $limit OFFSET $page";
        $query = "SELECT generalreservation.gen_res_date, generalreservation.gen_res_id, ownership.own_mcp_code, ownership.own_id, generalreservation.gen_res_total_in_site,generalreservation.gen_res_status,generalreservation.gen_res_from_date,
        (SELECT count(ownershipreservation.own_res_id) FROM ownershipreservation  WHERE own_res_gen_res_id = generalreservation.gen_res_id) AS rooms,
        (SELECT SUM(ownershipreservation.own_res_count_adults) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as adults,
        (SELECT SUM(ownershipreservation.own_res_count_childrens) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as childrens,
        (SELECT MIN(ownershipreservation.own_res_reservation_from_date) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as dateFrom,
        (SELECT MIN(ownershipreservation.own_res_reservation_to_date) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) as dateTo,
        (SELECT SUM(DATEDIFF(ownershipreservation.own_res_reservation_to_date, ownershipreservation.own_res_reservation_from_date)) FROM ownershipreservation WHERE ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id) AS totalNights,
        user.user_user_name, user.user_last_name, user.user_email, destination.des_name
        FROM generalreservation INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id INNER JOIN destination ON ownership.own_destination = destination.des_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
        $where $ls";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        $query = "SELECT COUNT(generalreservation.gen_res_id) as total
        FROM generalreservation INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id INNER JOIN destination ON ownership.own_destination = destination.des_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
        $where";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $poTotal = $stmt->fetch();
        return $this->view->create(array('total' => $poTotal['total'], 'data' => $po), 200);
    }

    public function listDestinations($request)
    {
        try {
            $query = "SELECT  destination.des_name,municipality.mun_name, province.prov_name, destination.des_poblation,
            (SELECT count(ownership.own_id) FROM ownership WHERE ownership.own_destination=destination.des_id) as casas,
            destination.des_active
            FROM destination INNER JOIN destinationlocation ON destination.des_id = destinationlocation.des_loc_des_id
            INNER JOIN municipality ON destinationlocation.des_loc_mun_id = municipality.mun_id
            INNER JOIN  province ON destinationlocation.des_loc_prov_id = province.prov_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function shortDestinationsList($request)
    {
        try {
            $query = "SELECT  destination.des_name, destination.des_id
            FROM destination ORDER BY destination.des_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listMunicipality($request)
    {
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
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listProvinces($request)
    {
        try {
            $query = "SELECT province.prov_id,province.prov_name,province.prov_phone_code,province.prov_code,province.prov_own_code FROM province";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listCountries($request)
    {
        try {
            $query = "SELECT co_id, co_name FROM country";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listAccommodationByStatus($status)
    {
        try {
            $where = ($status != null && $status != "") ? "WHERE acc.own_status = :status" : "";

            $query = "SELECT acc.own_id,
              acc.own_mcp_code,
              acc.own_name,
              acc.own_type,
              acc.own_selection,
              m.mun_id as municipality_id,
              m.mun_name as municipality,
              p.prov_id as province_id,
              p.prov_name as province_name,
              d.des_id as destination_id,
              d.des_name as destination_name,
              acc.own_rooms_total,
              acc.own_rating,
              (SELECT min(p.pho_name) FROM ownershipphoto op JOIN photo p on p.pho_id = op.own_pho_pho_id WHERE op.own_pho_own_id=acc.own_id
                AND (p.pho_order = (select min(p1.pho_order) from  ownershipphoto op1 JOIN photo p1 on op1.own_pho_pho_id = p1.pho_id
                where op1.own_pho_own_id = acc.own_id) or p.pho_order is null)) as photo
              FROM ownership acc
              INNER JOIN municipality m on m.mun_id = acc.own_address_municipality
              INNER JOIN province p on p.prov_id = acc.own_address_province
              LEFT JOIN destination d on d.des_id = acc.own_destination
              $where
              ORDER BY LENGTH(acc.own_mcp_code), acc.own_mcp_code ASC";

            $stmt = $this->conn->prepare($query);

            if ($status != null && $status != "")
                $stmt->bindValue('status', $status);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getAverageReviewByAccommodationCode($accommodationCode)
    {
        try {
            $query = "SELECT acc.own_id,
              acc.own_mcp_code,
              acc.own_name,
              acc.own_rating
              FROM ownership acc
              WHERE acc.own_mcp_code LIKE :accommodationCode
              LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('accommodationCode', "%" . $accommodationCode . "%");
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getSearchAccommodations($filters)
    {
        try {
            $andWhere = "";

            if (isset($filters["idDestination"]) && $filters["idDestination"] != null && $filters["idDestination"] > 0)
                $andWhere .= " AND acc.own_destination = :idDestination";
            if (isset($filters["guestTotal"]) && $filters["guestTotal"] != null && $filters["guestTotal"] > 0)
                $andWhere .= " AND acc.own_maximun_number_guests = :guestTotal";
            if (isset($filters["roomsTotal"]) && $filters["roomsTotal"] != null && $filters["roomsTotal"] > 0)
                $andWhere .= " AND acc.own_rooms_total = :roomsTotal";
            if (isset($filters["isSelection"]) && $filters["isSelection"] != null)
                $andWhere .= " AND acc.own_selection = :isSelection";
            if (isset($filters["type"]) && $filters["type"] != null)
                $andWhere .= " AND acc.own_type = ':type'";
            if (isset($filters["category"]) && $filters["category"] != null)
                $andWhere .= " AND acc.own_category = ':category'";
            if (isset($filters["priceFrom"]) && $filters["priceFrom"] != null && $filters["priceFrom"] > 0)
                $andWhere .= " AND r.room_price_down_to <= ':priceFrom'";
            if (isset($filters["priceTo"]) && $filters["priceTo"] != null && $filters["priceTo"] > 0)
                $andWhere .= " AND r.room_price_down_to >= ':priceTo'";
            if (isset($filters["hasClimatization"]) && $filters["hasClimatization"] != null && $filters["hasClimatization"] == 1)
                $andWhere .= " AND r.room_climate LIKE '%Aire acondicionado%'";
            if (isset($filters["hasPets"]) && $filters["hasPets"] != null)
                $andWhere .= " AND acc.own_description_pets = :hasPets";
            if (isset($filters["hasAudiovisuals"]) && $filters["hasAudiovisuals"] != null && $filters["hasAudiovisuals"] == 1)
                $andWhere .= " AND r.room_audiovisual != 'No'";
            if (isset($filters["hasBabyFacilities"]) && $filters["hasBabyFacilities"] != null)
                $andWhere .= " AND r.room_baby = :hasBabyFacilities";
            if (isset($filters["allowSmoker"]) && $filters["allowSmoker"] != null)
                $andWhere .= " AND r.room_smoker = :allowSmoker";
            if (isset($filters["hasSafe"]) && $filters["hasSafe"] != null)
                $andWhere .= " AND r.room_safe = :hasSafe";
            if (isset($filters["hasBalcony"]) && $filters["hasBalcony"] != null)
                $andWhere .= " AND r.room_balcony = :hasBalcony";
            if (isset($filters["hasTerrace"]) && $filters["hasTerrace"] != null)
                $andWhere .= " AND r.room_terrace = :hasTerrace";
            if (isset($filters["hasYard"]) && $filters["hasYard"] != null)
                $andWhere .= " AND r.room_yard = :hasYard";
            if (isset($filters["hasInternet"]) && $filters["hasInternet"] != null)
                $andWhere .= " AND acc.own_description_internet = :hasInternet";
            if (isset($filters["hasJacuzzy"]) && $filters["hasJacuzzy"] != null)
                $andWhere .= " AND acc.own_water_jacuzee = :hasJacuzzy";
            if (isset($filters["hasPool"]) && $filters["hasPool"] != null)
                $andWhere .= " AND acc.own_water_piscina = :hasPool";
            if (isset($filters["hasBreakfast"]) && $filters["hasBreakfast"] != null)
                $andWhere .= " AND acc.own_facilities_breakfast = :hasBreakfast";
            if (isset($filters["hasDinner"]) && $filters["hasDinner"] != null)
                $andWhere .= " AND acc.own_facilities_dinner = :hasDinner";
            if (isset($filters["hasLaundry"]) && $filters["hasLaundry"] != null)
                $andWhere .= " AND acc.own_description_laundry = :hasLaundry";
            if (isset($filters["hasParking"]) && $filters["hasParking"] != null)
                $andWhere .= " AND acc.own_facilities_parking = :hasParking";
            if (isset($filters["bathroomType"]) && $filters["bathroomType"] != null)
                $andWhere .= " AND r.room_bathroom = ':bathroomType'";
            if (isset($filters["spokenLangEnglish"]) && $filters["spokenLangEnglish"] != null && $filters["spokenLangEnglish"] == 1)
                $andWhere .= " AND acc.own_langs LIKE '1___'";
            if (isset($filters["spokenLangFrench"]) && $filters["spokenLangFrench"] != null && $filters["spokenLangFrench"] == 1)
                $andWhere .= " AND acc.own_langs LIKE '_1__'";
            if (isset($filters["spokenLangGerman"]) && $filters["spokenLangGerman"] != null && $filters["spokenLangGerman"] == 1)
                $andWhere .= " AND acc.own_langs LIKE '__1_'";
            if (isset($filters["spokenLangItalian"]) && $filters["spokenLangItalian"] != null && $filters["spokenLangItalian"] == 1)
                $andWhere .= " AND acc.own_langs LIKE '___1'";

            $query = "SELECT DISTINCT acc.own_id,
              acc.own_mcp_code,
              acc.own_name,
              acc.own_type,
              acc.own_selection,
              m.mun_id as municipality_id,
              m.mun_name as municipality,
              p.prov_id as province_id,
              p.prov_name as province_name,
              d.des_id as destination_id,
              d.des_name as destination_name,
              acc.own_rooms_total,
              acc.own_rating
              FROM ownership acc
              INNER JOIN municipality m on m.mun_id = acc.own_address_municipality
              INNER JOIN province p on p.prov_id = acc.own_address_province
              INNER JOIN room r on r.room_ownership = acc.own_id
              LEFT JOIN destination d on d.des_id = acc.own_destination
              WHERE acc.own_status = 1 AND NOT EXISTS (select ud.ud_id from unavailabilitydetails ud where ud.`room_id` = r.room_id
              AND ud.`ud_from_date` <= :leavingDate AND ud.`ud_to_date` >= :arrivalDate )
              AND NOT EXISTS (select owres.own_res_id from ownershipreservation owres
              where owres.`own_res_selected_room_id` = r.room_id
              and owres.`own_res_reservation_booking` is not null
              and owres.`own_res_status` = 5
              AND owres.`own_res_reservation_from_date` <= :leavingDate AND owres.`own_res_reservation_to_date` >= :arrivalDate) $andWhere";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('arrivalDate', "'" . $filters["arrivalDate"] . "'");
            $stmt->bindValue('leavingDate', "'" . $filters["leavingDate"] . "'");
            if (isset($filters["idDestination"]) && $filters["idDestination"] != null && $filters["idDestination"] > 0)
                $stmt->bindValue('idDestination', $filters["idDestination"]);
            if (isset($filters["guestTotal"]) && $filters["guestTotal"] != null && $filters["guestTotal"] > 0)
                $stmt->bindValue('guestTotal', $filters["guestTotal"]);
            if (isset($filters["roomsTotal"]) && $filters["roomsTotal"] != null && $filters["roomsTotal"] > 0)
                $stmt->bindValue('roomsTotal', $filters["roomsTotal"]);
            if (isset($filters["isSelection"]) && $filters["isSelection"] != null)
                $stmt->bindValue('isSelection', $filters["isSelection"]);
            if (isset($filters["type"]) && $filters["type"] != null)
                $stmt->bindValue('type', $filters["type"]);
            if (isset($filters["category"]) && $filters["category"] != null)
                $stmt->bindValue('category', $filters["category"]);
            if (isset($filters["priceFrom"]) && $filters["priceFrom"] != null && $filters["priceFrom"] > 0)
                $stmt->bindValue('priceFrom', $filters["priceFrom"]);
            if (isset($filters["priceTo"]) && $filters["priceTo"] != null && $filters["priceTo"] > 0)
                $stmt->bindValue('priceTo', $filters["priceTo"]);
            if (isset($filters["hasPets"]) && $filters["hasPets"] != null)
                $stmt->bindValue('hasPets', $filters["hasPets"]);
            if (isset($filters["hasBabyFacilities"]) && $filters["hasBabyFacilities"] != null)
                $stmt->bindValue('hasBabyFacilities', $filters["hasBabyFacilities"]);
            if (isset($filters["allowSmoker"]) && $filters["allowSmoker"] != null)
                $stmt->bindValue('allowSmoker', $filters["allowSmoker"]);
            if (isset($filters["hasSafe"]) && $filters["hasSafe"] != null)
                $stmt->bindValue('hasSafe', $filters["hasSafe"]);
            if (isset($filters["hasBalcony"]) && $filters["hasBalcony"] != null)
                $stmt->bindValue('hasBalcony', $filters["hasBalcony"]);
            if (isset($filters["hasTerrace"]) && $filters["hasTerrace"] != null)
                $stmt->bindValue('hasTerrace', $filters["hasTerrace"]);
            if (isset($filters["hasYard"]) && $filters["hasYard"] != null)
                $stmt->bindValue('hasYard', $filters["hasYard"]);
            if (isset($filters["hasInternet"]) && $filters["hasInternet"] != null)
                $stmt->bindValue('hasInternet', $filters["hasInternet"]);
            if (isset($filters["hasJacuzzy"]) && $filters["hasJacuzzy"] != null)
                $stmt->bindValue('hasJacuzzy', $filters["hasJacuzzy"]);
            if (isset($filters["hasPool"]) && $filters["hasPool"] != null)
                $stmt->bindValue('hasPool', $filters["hasPool"]);
            if (isset($filters["hasBreakfast"]) && $filters["hasBreakfast"] != null)
                $stmt->bindValue('hasBreakfast', $filters["hasBreakfast"]);
            if (isset($filters["hasDinner"]) && $filters["hasDinner"] != null)
                $stmt->bindValue('hasDinner', $filters["hasDinner"]);
            if (isset($filters["hasLaundry"]) && $filters["hasLaundry"] != null)
                $stmt->bindValue('hasLaundry', $filters["hasLaundry"]);
            if (isset($filters["hasParking"]) && $filters["hasParking"] != null)
                $stmt->bindValue('hasParking', $filters["hasParking"]);
            if (isset($filters["bathroomType"]) && $filters["bathroomType"] != null)
                $stmt->bindValue('bathroomType', $filters["bathroomType"]);
            //$stmt->bindValue('accommodationCode', "%".$accommodationCode."%");
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getAccommodationOrdererByRanking($request)
    {
        try {
            $query = "SELECT acc.own_id,
              acc.own_mcp_code,
              acc.own_name,
              acc.own_type,
              acc.own_selection,
              m.mun_id as municipality_id,
              m.mun_name as municipality,
              p.prov_id as province_id,
              p.prov_name as province_name,
              d.des_id as destination_id,
              d.des_name as destination_name,
              acc.own_rooms_total,
              acc.own_rating,
              acc.own_status,
              ows.status_name,
              (SELECT min(p.pho_name) FROM ownershipphoto op JOIN photo p on p.pho_id = op.own_pho_pho_id WHERE op.own_pho_own_id=acc.own_id
                AND (p.pho_order = (select min(p1.pho_order) from  ownershipphoto op1 JOIN photo p1 on op1.own_pho_pho_id = p1.pho_id
                where op1.own_pho_own_id = acc.own_id) or p.pho_order is null)) as photo,
                own_ranking
              FROM ownership acc
              INNER JOIN municipality m on m.mun_id = acc.own_address_municipality
              INNER JOIN province p on p.prov_id = acc.own_address_province
              LEFT JOIN destination d on d.des_id = acc.own_destination
              JOIN ownershipstatus ows on ows.status_id = acc.own_status
              ORDER BY acc.own_ranking DESC,
              LENGTH(acc.own_mcp_code), acc.own_mcp_code ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getClientResume($request)
    {
        $userid = $request->query->get('user_id');
        $query = "SELECT user_user_name, user_last_name, user_email, lang.lang_name, country.co_name,currency.curr_code,
  (SELECT count(generalreservation.gen_res_id) FROM generalreservation WHERE generalreservation.gen_res_user_id=user.user_id AND (generalreservation.gen_res_status=2 OR generalreservation.gen_res_status=5))as reservadas,
  (SELECT count(generalreservation.gen_res_id) FROM generalreservation WHERE generalreservation.gen_res_user_id=user.user_id AND generalreservation.gen_res_status=1)as disponibles,
  (SELECT count(generalreservation.gen_res_id) FROM generalreservation WHERE generalreservation.gen_res_user_id=user.user_id AND generalreservation.gen_res_status=0)as pendientes,
  (SELECT count(generalreservation.gen_res_id) FROM generalreservation WHERE generalreservation.gen_res_user_id=user.user_id AND generalreservation.gen_res_status=3)as no_disponibles,
  (SELECT count(generalreservation.gen_res_id) FROM generalreservation WHERE generalreservation.gen_res_user_id=user.user_id AND generalreservation.gen_res_status=6)as canceladas,
  (SELECT count(generalreservation.gen_res_id) FROM generalreservation WHERE generalreservation.gen_res_user_id=user.user_id AND generalreservation.gen_res_status=8)as vencidas
FROM user INNER JOIN country ON user.user_country = country.co_id INNER JOIN usertourist ON user.user_id = usertourist.user_tourist_user INNER JOIN lang ON usertourist.user_tourist_language = lang.lang_id INNER JOIN currency ON usertourist.user_tourist_currency = currency.curr_id
WHERE user.user_id= $userid";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $this->view->create($po, 200);
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getClientReservations($request)
    {
        $userid = $request->query->get('user_id');
        $query = "SELECT generalreservation.*, user.user_user_name, user_last_name, user.user_email,lang.lang_name, country.co_name, currency.curr_code FROM generalreservation
INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN usertourist ON user.user_id = usertourist.user_tourist_user INNER JOIN  lang ON usertourist.user_tourist_language = lang.lang_id INNER JOIN  currency ON usertourist.user_tourist_currency = currency.curr_id
INNER JOIN country ON user.user_country = country.co_id
WHERE user.user_id= $userid
ORDER BY generalreservation.gen_res_date DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $this->view->create($po, 200);
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getAccommodationDetails($request)
    {
        try {
            $accommodationCode = strtoupper($request->query->get('accommodation_code'));

            $query = "SELECT acc.own_id,
              acc.own_mcp_code,
              acc.own_name,
              acc.own_homeowner_1,
              acc.own_homeowner_2,
              acc.own_type,
              acc.own_minimum_price,
              acc.own_category,
              acc.own_geolocate_x,
              acc.own_geolocate_y,
              acc.own_rating
              FROM ownership acc
              WHERE acc.own_mcp_code = :code
              LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('code', $accommodationCode);
            $stmt->execute();
            $po = $stmt->fetchAll();
            if (count($po)) {
                $query = "SELECT r.room_type,
                          r.room_price_down_to as price,
                          r.room_beds,
                          r.room_climate,
                          r.room_audiovisual,
                          r.room_bathroom,
                          r.room_smoker,
                          r.room_safe,
                          r.room_windows,
                          r.room_balcony,
                          r.room_terrace,
                          r.room_yard,
                          r.room_stereo,
                          r.room_baby
                          FROM room r WHERE r.room_ownership=:idAccommodation; ";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue('idAccommodation', $po[0]['own_id']);
                $stmt->execute();
                $rooms = $stmt->fetchAll();
                $po[0]['rooms'] = $rooms;

                $query = "SELECT acc.own_facilities_breakfast, acc.own_facilities_breakfast_price,
                          acc.own_facilities_dinner, acc.own_facilities_dinner_price_from, acc.own_facilities_dinner_price_to,
                          acc.own_facilities_parking, acc.own_facilities_parking_price,
                          acc.own_water_jacuzee, acc.own_water_sauna, acc.own_water_piscina,
                          acc.own_description_bicycle_parking, acc.own_description_pets, acc.own_description_laundry, acc.own_description_internet
                          FROM ownership acc WHERE acc.own_id=:idAccommodation; ";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue('idAccommodation', $po[0]['own_id']);
                $stmt->execute();
                $servicesRaw = $stmt->fetchAll();
                $services = array();

                foreach ($servicesRaw as $item) {
                    if ($item["own_facilities_breakfast"] && $item["own_facilities_breakfast_price"] > 0)
                        $services[] = array("name" => "Desayuno", "price_from" => $item["own_facilities_breakfast_price"], "price_to" => "");

                    if ($item["own_facilities_dinner"] && $item["own_facilities_dinner_price_from"] > 0)
                        $services[] = array("name" => "Cena", "price_from" => $item["own_facilities_dinner_price_from"], "price_to" => $item["own_facilities_dinner_price_to"]);

                    if ($item["own_facilities_parking"] && $item["own_facilities_parking_price"] > 0)
                        $services[] = array("name" => "Parqueo", "price_from" => $item["own_facilities_parking_price"]);

                    if ($item["own_water_jacuzee"])
                        $services[] = array("name" => "Jacuzee", "price_from" => "", "price_to" => "");

                    if ($item["own_water_sauna"])
                        $services[] = array("name" => "Sauna", "price_from" => "", "price_to" => "");

                    if ($item["own_water_piscina"])
                        $services[] = array("name" => "Piscina", "price_from" => "", "price_to" => "");

                    if ($item["own_description_bicycle_parking"])
                        $services[] = array("name" => "Parqueo de bicicletas", "price_from" => "", "price_to" => "");

                    if ($item["own_description_pets"])
                        $services[] = array("name" => "Mascotas", "price_from" => "", "price_to" => "");

                    if ($item["own_description_laundry"])
                        $services[] = array("name" => "Lavanderia", "price_from" => "", "price_to" => "");

                    if ($item["own_description_internet"])
                        $services[] = array("name" => "Internet", "price_from" => "", "price_to" => "");
                }

                $po[0]['services'] = $services;
                return $po[0];
            } else
                throw new InvalidFormException('The ownership no exist.');
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getAccommodationComments($request)
    {
        try {
            $accommodationCode = strtoupper($request->query->get('accommodation_code'));
            $status = strtoupper($request->query->get('is_published'));

            $query = "SELECT TRIM(CONCAT(CONCAT(u.user_user_name,' '), u.user_last_name)) as client_name,
              c.com_date,
              c.com_rate,
              c.com_comments,
              c.com_public
              FROM comment c
              JOIN ownership acc on acc.own_id = c.com_ownership
              JOIN user u on u.user_id = c.com_user
              WHERE acc.own_mcp_code = :accommodationCode";

            if ($status != null)
                $query .= " AND c.com_public = :status";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('accommodationCode', $accommodationCode);
            if ($status != null)
                $stmt->bindValue('status', $status);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getAccommodationPhotos($request)
    {
        try {
            $accommodationCode = strtoupper($request->query->get('accommodation_code'));

            $query = "SELECT p.pho_id, p.pho_name, p.pho_order
              FROM ownershipphoto op
              JOIN ownership acc on acc.own_id = op.own_pho_own_id
              JOIN photo p on p.pho_id = op.own_pho_pho_id
              WHERE acc.own_mcp_code = :accommodationCode
              ORDER BY p.pho_order ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('accommodationCode', $accommodationCode);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getUserCart($request)
    {
        try {
            $userId = strtoupper($request->query->get('user_id'));
            $sessionId = strtoupper($request->query->get('session_id'));

            $query = "SELECT c.cart_date_from, c.cart_date_to, acc.own_mcp_code,
              acc.own_name, r.room_num, r.room_type, c.cart_count_adults, c.cart_count_children,
              r.room_price_up_to, r.room_price_down_to, r.room_price_special
              FROM cart c
              JOIN room r on r.room_id = c.cart_room
              join ownership acc on acc.own_id = r.room_ownership";

            if ($userId != null && $userId != "")
                $query .= " WHERE c.cart_user = :userId";
            else if ($sessionId != null && $sessionId != "")
                $query .= " WHERE c.cart_session_id = :sessionId";

            $stmt = $this->conn->prepare($query);
            if ($userId != null && $userId != "")
                $stmt->bindValue('userId', $userId);
            else if ($sessionId != null && $sessionId != "")
                $stmt->bindValue('sessionId', $sessionId);

            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getUserConsults($request)
    {
        try {
            $userId = strtoupper($request->query->get('user_id'));

            $date = \date('Y-m-j');
            $new_date = strtotime('-30 day', strtotime($date));
            $new_date = \date('Y-m-j', $new_date);

            $query = "SELECT gres.gen_res_id,
              CONCAT('CAS.',gres.gen_res_id) as reservation_code,
              gres.gen_res_status_date,
              if(gres.gen_res_status = 0, 'Pendiente', if(gres.gen_res_status = 1, 'Disponible', if(gres.gen_res_status = 3, 'No Disponible', if(gres.gen_res_status = 8, 'Vencida', 'Otro estado')))) as status,
              gres.gen_res_total_in_site, gres.gen_res_from_date, gres.gen_res_to_date,
              own.own_id,own.own_mcp_code,
              own.own_name, d.des_id, d.des_name,
              mun.mun_id, mun.mun_name
              FROM generalreservation gres
              JOIN ownership own on own.own_id = gres.gen_res_own_id
              JOIN destination d on d.des_id = own.own_destination
              JOIN municipality mun on mun.mun_id = own.own_address_municipality
              WHERE gres.gen_res_status != 2 AND gres.gen_res_status != 6 AND
              gen_res_user_id = :userId AND gres.gen_res_status_date > :newDate
              ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('userId', $userId);
            $stmt->bindValue('newDate', $new_date);
            $stmt->execute();
            $po = $stmt->fetchAll();

            if (count($po)) {
                for ($i = 0; $i < count($po); $i++) {
                    $query = "SELECT owres.own_res_id,
                          owres.own_res_selected_room_id,
                          owres.own_res_room_type,
                          owres.own_res_count_childrens,
                          owres.own_res_count_adults,
                          owres.own_res_reservation_from_date,
                          owres.own_res_reservation_to_date
                          FROM ownershipreservation owres WHERE owres.own_res_gen_res_id = :genResId; ";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindValue('genResId', $po[$i]['gen_res_id']);
                    $stmt->execute();
                    $rooms = $stmt->fetchAll();
                    $po[$i]['rooms'] = $rooms;
                }
            }
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getUserReserves($request)
    {
        try {
            $userId = strtoupper($request->query->get('user_id'));

            $date = \date('Y-m-j');
            $new_date = strtotime('-30 day', strtotime($date));
            $new_date = \date('Y-m-j', $new_date);

            $query = "SELECT gres.gen_res_id,
              CONCAT('CAS.',gres.gen_res_id) as reservation_code,
              gres.gen_res_status_date,
              if(gres.gen_res_status = 2, 'Reservada', 'Otro estado') as status,
              gres.gen_res_total_in_site, gres.gen_res_from_date, gres.gen_res_to_date,
              own.own_id,own.own_mcp_code,
              own.own_name, d.des_id, d.des_name,
              mun.mun_id, mun.mun_name
              FROM generalreservation gres
              JOIN ownership own on own.own_id = gres.gen_res_own_id
              JOIN destination d on d.des_id = own.own_destination
              JOIN municipality mun on mun.mun_id = own.own_address_municipality
              WHERE gres.gen_res_status = 2 AND
              EXISTS (SELECT owres.own_res_id from ownershipreservation owres WHERE owres.own_res_gen_res_id = gres.gen_res_id and owres.own_res_reservation_booking is not null and owres.own_res_status = 5)
              AND gen_res_user_id = :userId AND gres.gen_res_status_date > :newDate
              ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('userId', $userId);
            $stmt->bindValue('newDate', $new_date);
            $stmt->execute();
            $po = $stmt->fetchAll();

            if (count($po)) {
                for ($i = 0; $i < count($po); $i++) {
                    $query = "SELECT owres.own_res_id,
                          owres.own_res_selected_room_id,
                          owres.own_res_room_type,
                          owres.own_res_count_childrens,
                          owres.own_res_count_adults,
                          owres.own_res_reservation_from_date,
                          owres.own_res_reservation_to_date
                          FROM ownershipreservation owres WHERE owres.own_res_gen_res_id = :genResId; ";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindValue('genResId', $po[$i]['gen_res_id']);
                    $stmt->execute();
                    $rooms = $stmt->fetchAll();
                    $po[$i]['rooms'] = $rooms;
                }
            }
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getUserHistory($request)
    {
        try {
            $userId = strtoupper($request->query->get('user_id'));

            $date = \date('Y-m-j');
            $new_date = strtotime('-30 day', strtotime($date));
            $new_date = \date('Y-m-j', $new_date);

            $query = "SELECT gres.gen_res_id,
              CONCAT('CAS.',gres.gen_res_id) as reservation_code,
              gres.gen_res_status_date,
              if(gres.gen_res_status = 0, 'Pendiente', if(gres.gen_res_status = 1, 'Disponible', if(gres.gen_res_status = 3, 'No Disponible', if(gres.gen_res_status = 8, 'Vencida', if(gres.gen_res_status = 2, 'Reservada', 'Otro estado'))))) as status,
              gres.gen_res_total_in_site, gres.gen_res_from_date, gres.gen_res_to_date,
              own.own_id,own.own_mcp_code,
              own.own_name, d.des_id, d.des_name,
              mun.mun_id, mun.mun_name
              FROM generalreservation gres
              JOIN ownership own on own.own_id = gres.gen_res_own_id
              JOIN destination d on d.des_id = own.own_destination
              JOIN municipality mun on mun.mun_id = own.own_address_municipality
              WHERE gres.gen_res_status != 6 AND gres.gen_res_status != 4 AND gres.gen_res_status != 5 AND gres.gen_res_status != 7
              AND gen_res_user_id = :userId AND gres.gen_res_status_date <= :newDate
              ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('userId', $userId);
            $stmt->bindValue('newDate', $new_date);
            $stmt->execute();
            $po = $stmt->fetchAll();

            if (count($po)) {
                for ($i = 0; $i < count($po); $i++) {
                    $query = "SELECT owres.own_res_id,
                          owres.own_res_selected_room_id,
                          owres.own_res_room_type,
                          owres.own_res_count_childrens,
                          owres.own_res_count_adults,
                          owres.own_res_reservation_from_date,
                          owres.own_res_reservation_to_date
                          FROM ownershipreservation owres WHERE owres.own_res_gen_res_id = :genResId; ";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindValue('genResId', $po[$i]['gen_res_id']);
                    $stmt->execute();
                    $rooms = $stmt->fetchAll();
                    $po[$i]['rooms'] = $rooms;
                }
            }
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getUserFavoritiesAccommodations($request)
    {
        try {
            $userId = $request->query->get('user_id');

            $query = "SELECT acc.own_id, acc.own_mcp_code,
              acc.own_name, acc.own_type,d.des_id, d.des_name,
              mun.mun_id, mun.mun_name,
              acc.own_rooms_total,
              (select count(c.com_id) from comment c WHERE c.com_ownership = acc.own_id) as comments,
              (SELECT count(res.own_res_id) FROM ownershipreservation res JOIN generalreservation gen on res.own_res_gen_res_id = gen.gen_res_id WHERE gen.gen_res_own_id = acc.own_id AND res.own_res_status = 5) as reservations,
              acc.own_minimum_price
              FROM favorite fav
              JOIN ownership acc on acc.own_id = fav.favorite_ownership
              JOIN destination d on d.des_id = acc.own_destination
              JOIN municipality mun on mun.mun_id = acc.own_address_municipality
              WHERE fav.favorite_user = :userId
              ORDER BY fav.favorite_creation_date DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('userId', $userId);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getUserComments($request)
    {
        try {
            $userId = $request->query->get('user_id');

            $query = "SELECT c.com_id,
              acc.own_mcp_code,
              acc.own_name,
              c.com_date,
              c.com_rate,
              c.com_comments
              FROM comment c
              JOIN ownership acc on acc.own_id = c.com_ownership
              WHERE c.com_user = :userId
              ORDER BY c.com_date DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('userId', $userId);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getDestinationSearch($request)
    {
        try {
            $desName = trim($request->query->get('destination_name'));
            $provId = $request->query->get('prov_id');
            $munId = $request->query->get('mun_id');
            $status = $request->query->get('status');
            $where = "";

            if ($desName != null && $desName != "")
                $where .= (($where == "") ? " WHERE " : " AND ") . "d.des_name LIKE :desName";
            if ($provId != null && $provId != "")
                $where .= (($where == "") ? " WHERE " : " AND ") . "p.prov_id = :provId";
            if ($munId != null && $munId != "")
                $where .= (($where == "") ? " WHERE " : " AND ") . "mun.mun_id = :munId";
            if ($status != null && $status != "")
                $where .= (($where == "") ? " WHERE " : " AND ") . "d.des_active = :status";

            $query = "SELECT d.des_id, d.des_name,
              p.prov_id, p.prov_name, mun.mun_id, mun.mun_name, d.des_active,
              (select count(o.own_id) from ownership o where o.own_destination = d.des_id) as accommodations
              FROM destination d
              JOIN destinationlocation dl on d.des_id = dl.des_loc_des_id
              JOIN municipality mun on mun.mun_id = dl.des_loc_mun_id
              JOIN province p on p.prov_id = dl.des_loc_prov_id
              $where";

            $stmt = $this->conn->prepare($query);
            if ($desName != null && $desName != "")
                $stmt->bindValue('desName', "%" . $desName . "%");
            if ($provId != null && $provId != "")
                $stmt->bindValue('provId', $provId);
            if ($munId != null && $munId != "")
                $stmt->bindValue('munId', $munId);
            if ($status != null && $status != "")
                $stmt->bindValue('status', $status);

            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getAccommodations($request)
    {
        try {
            $page = 0;
            $limit = 10;
            $page = ($request->query->get('start') != '') ? $request->query->get('start') : $page;
            $limit = ($request->query->get('limit') != '') ? $request->query->get('limit') : $limit;

            $query = "SELECT o.own_id, o.own_mcp_code, o.own_name, m.mun_id, m.mun_name, p.prov_id, p.prov_name, d.des_id, d.des_name,
              st.status_id, st.status_name
              FROM ownership o
              JOIN province p on p.prov_id = o.own_address_province
              JOIN municipality m on m.mun_id = o.own_address_municipality
              JOIN destination d on d.des_id = o.own_destination
              JOIN ownershipstatus st on st.status_id = o.own_status
              ORDER BY LENGTH(o.own_mcp_code), o.own_mcp_code LIMIT $limit OFFSET $page
              ";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function addCommentOwnership($user, $ownership, $date, $rate, $public, $comments)
    {
        try {
            $queryComment = "INSERT INTO comment (com_user,com_ownership,com_date,com_rate,com_public,com_comments)
VALUE (:com_user,:com_ownership,:com_date,:com_rate,:com_public,:com_comments)";

            $stmtComment = $this->conn->prepare($queryComment);
            $stmtComment->bindValue('com_user', $user);
            $stmtComment->bindValue('com_ownership', $ownership);
            $stmtComment->bindValue('com_date', $date);
            $stmtComment->bindValue('com_rate', $rate);
            $stmtComment->bindValue('com_public', $public);
            $stmtComment->bindValue('com_comments', $comments);
            $stmtComment->execute();
            return array('success' => true);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getOwnershipData($code)
    {
        try {
            $queryOwnership = "SELECT
  ownership.own_id,
  ownership.own_name,
  ownership.own_address_province,
  ownership.own_address_municipality,
  ownership.own_licence_number,
  ownership.own_mcp_code,
  ownership.own_address_street,
  ownership.own_address_number,
  ownership.own_address_between_street_1,
  ownership.own_address_between_street_2,
  ownership.own_mobile_number,
  ownership.own_homeowner_1,
  ownership.own_homeowner_2,
  ownership.own_phone_number,
  ownership.own_email_1,
  ownership.own_email_2,
  ownership.own_category,
  ownership.own_type,
  ownership.own_facilities_breakfast,
  ownership.own_facilities_breakfast_price,
  ownership.own_facilities_dinner,
  ownership.own_facilities_dinner_price_from,
  ownership.own_facilities_dinner_price_to,
  ownership.own_facilities_parking,
  ownership.own_facilities_parking_price,
  ownership.own_water_jacuzee,
  ownership.own_water_sauna,
  ownership.own_water_piscina,
  ownership.own_description_bicycle_parking,
  ownership.own_description_pets,
  ownership.own_description_laundry,
  ownership.own_description_internet,
  ownership.own_top_20,
  ownership.own_geolocate_y,
  ownership.own_rating,
  ownership.own_maximun_number_guests,
  ownership.own_minimum_price,
  ownership.own_maximum_price,
  ownership.own_comments_total,
  ownership.own_facilities_notes,
  ownership.own_langs,
  ownership.own_geolocate_x,
  ownership.own_phone_code,
  ownership.own_sync_st,
  ownership.own_status,
  ownership.own_comment,
  ownership.own_commission_percent,
  ownership.own_rooms_total,
  ownership.own_not_recommendable,
  ownership.own_saler,
  ownership.own_visit_date,
  ownership.own_creation_date,
  ownership.own_last_update,
  ownership.own_destination,
  ownership.own_selection,
  ownership.own_owner_photo,
  ownership.own_ranking,
  ownership.own_sended_to_team,
  ownership.own_publish_date,
  ownership.own_inmediate_booking,
  ownership.own_automatic_mcp_code,
  ownership.own_mcp_code_generated,
  ownership.own_cubacoupon,
  ownership.own_sms_notifications
FROM
  ownership
WHERE
  ownership.own_mcp_code = :own_mcp_code";

            $stmtOwnership = $this->conn->prepare($queryOwnership);
            $stmtOwnership->bindValue('own_mcp_code', $code);
            $stmtOwnership->execute();
            $ownership = $stmtOwnership->fetch();
            return array('success' => true, 'ownership' => $ownership);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getRoomsOfOwnershipsByCode($code)
    {
        try {
            $queryOwnershipsRooms = "SELECT
  room.room_id,
  room.room_ownership,
  room.room_type,
  room.room_beds,
  room.room_price_up_from,
  room.room_price_up_to,
  room.room_price_down_from,
  room.room_price_down_to,
  room.room_climate,
  room.room_audiovisual,
  room.room_smoker,
  room.room_safe,
  room.room_baby,
  room.room_bathroom,
  room.room_stereo,
  room.room_windows,
  room.room_balcony,
  room.room_terrace,
  room.room_yard,
  room.room_sync_st,
  room.room_num,
  room.room_active,
  room.room_price_special
FROM
  ownership
  INNER JOIN room ON (ownership.own_id = room.room_ownership)
WHERE
  ownership.own_mcp_code = :own_mcp_code";

            $stmtOwnershipsRooms = $this->conn->prepare($queryOwnershipsRooms);
            $stmtOwnershipsRooms->bindValue('own_mcp_code', $code);
            $stmtOwnershipsRooms->execute();
            $resOwnershipsRooms = $stmtOwnershipsRooms->fetchAll();

            return array('success' => true, 'roms' => $resOwnershipsRooms);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function changeStatusReservation($res_id, $status)
    {
        try {
            $queryComment = "UPDATE generalreservation SET gen_res_status = :gen_res_status WHERE gen_res_id = :gen_res_id";

            $stmtComment = $this->conn->prepare($queryComment);
            $stmtComment->bindValue('gen_res_id', $res_id);
            $stmtComment->bindValue('gen_res_status', $status);
            $stmtComment->execute();
            return array('success' => true);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function changeStatusOwnership($own_id, $status)
    {
        try {
            $queryComment = "SELECT ownershipstatus.status_name FROM ownershipstatus WHERE ownershipstatus.status_id = :status_id";
            $stmtComment = $this->conn->prepare($queryComment);
            $stmtComment->bindValue('status_id', $status);
            $stmtComment->execute();
            $res = $stmtComment->fetch();
            if ($res === false) {
                return 'Estado no encontrado';
            }

            $queryComment = "UPDATE ownership SET own_status = :status_id
WHERE own_id = :own_id";

            $stmtComment = $this->conn->prepare($queryComment);
            $stmtComment->bindValue('status_id', $status);
            $stmtComment->bindValue('own_id', $own_id);
            $stmtComment->execute();
            return array('success' => true, 'data' => $res);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function changeStatusRoom($room_id, $status)
    {
        try {
            $queryComment = "UPDATE room SET room_active = :room_active WHERE room_id = :room_id";

            $stmtComment = $this->conn->prepare($queryComment);
            $stmtComment->bindValue('room_active', $status * 1);
            $stmtComment->bindValue('room_id', $room_id);
            $stmtComment->execute();
            return array('success' => true);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /*public function insertLog($request)
    {
        $fields = array();
        if ($request->request->get('user_id') != '') {
            $fields['user_id'] = $request->request->get('user_id');
        } else {
            throw new InvalidFormException();
        }
        if ($request->request->get('module_id') != '') {
            $fields['module_id'] = $request->request->get('module_id');
        } else {
            throw new InvalidFormException();
        }
        if ($request->request->get('description') != '') {
            $fields['description'] = $request->request->get('description');
        }
        else {
            throw new InvalidFormException();
        }
        if ($request->request->get('operation_id') != '') {
            $fields['operation_id'] = $request->request->get('operation_id');
        }
        else {
            throw new InvalidFormException();
        }
        if ($request->request->get('data_table_name') != '') {
            $fields['data_table_name'] = $request->request->get('data_table_name');
        }
        else {
            throw new InvalidFormException();
        }

        $date = new \DateTime(date('Y-m-d'));
        $time = strftime("%I:%M %p");

        $this->conn->insert('log', array(
            'log_user' => $fields['user_id'],
            'log_module' => $fields['module_id'],
            'log_description' =>$fields['description'],
            'log_date' => $date,
            'log_time' => $time,
            'operation' => $fields['operation_id'],
            'db_table' => $fields['data_table_name']
        ));

        return $this->view->create(array('success' => true), 200);

    }*/

    /***Aqui los resumenes por clientes**/
    function getClientsXCountryDailySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $country = $request->query->get('country');

        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($country != null && $country != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "co_id ='$country'";

        $query = "SELECT gen_res_date as fecha, country.co_name as pais, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN country ON user.user_country = country.co_id
                   $where GROUP BY gen_res_date, pais;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getClientsXCountryDailySummaryAvailable($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $country = $request->query->get('country');

        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($country != null && $country != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "co_id ='$country'";

        $query = "SELECT gen_res_date as fecha, country.co_name as pais, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN country ON user.user_country = country.co_id
                   $where GROUP BY gen_res_date, pais;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXCountryDailySummaryPayments($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $country = $request->query->get('country');

        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($country != null && $country != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "co_id ='$country'";

        $query = "SELECT gen_res_date as fecha, country.co_name as pais, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                  SUM( DISTINCT CASE WHEN current_cuc_change_rate IS NULL THEN payed_amount*curr_cuc_change ELSE payed_amount*current_cuc_change_rate END) as facturacion
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN country ON user.user_country = country.co_id
                  INNER JOIN booking ON ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id INNER JOIN currency ON booking.booking_currency = currency.curr_id
                  $where GROUP BY gen_res_date, pais;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }


    function getClientsXDestinationDailySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $destination = $request->query->get('destination');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($destination != null && $destination != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "des_id ='$destination'";

        $query = "SELECT gen_res_date as fecha, dest.des_name as destino,
                count(DISTINCT gen_res_user_id) as clientes,
                count(DISTINCT gen_res_id) as solicitudes,
                sum(owres.own_res_count_adults+owres.own_res_count_childrens) as personas_involucradas,
                count(own_res_id) as habitaciones,
                sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                FROM ownershipreservation owres
                INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                INNER JOIN ownership o ON o.own_id = gres.gen_res_own_id
                INNER JOIN destination dest ON dest.des_id = o.own_destination
                $where
                GROUP BY gen_res_date, destino
                ORDER BY gen_res_date, clientes DESC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXCountryMonthlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $country = $request->query->get('country');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($country != null && $country != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "co_id ='$country'";

        $query = "SELECT MONTHNAME(gen_res_date) as fecha, MONTH(gen_res_date) as month, YEAR(gen_res_date) as year, country.co_name as pais, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN country ON user.user_country = country.co_id
                   $where GROUP BY month, pais ORDER BY year ASC, month ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getClientsXDestinationDailySummaryAvailable($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $destination = $request->query->get('destination');

        $andWhere = "";
        if ($filter_date_from != null && $filter_date_from != "")
            $andWhere .= " AND gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $andWhere .= " AND gen_res_date <='$filter_date_to'";
        if ($destination != null && $destination != "")
            $andWhere .= " AND des_id ='$destination'";

        $query = "SELECT gen_res_date as fecha, dest.des_name as destino,
                count(DISTINCT gen_res_user_id) as clientes,
                count(DISTINCT gen_res_id) as solicitudes,
                sum(owres.own_res_count_adults+owres.own_res_count_childrens) as personas_involucradas,
                count(own_res_id) as habitaciones,
                sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                FROM ownershipreservation owres
                INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                INNER JOIN ownership o ON o.own_id = gres.gen_res_own_id
                INNER JOIN destination dest ON dest.des_id = o.own_destination
                WHERE (gres.gen_res_status = 1 or gres.gen_res_status = 2 or gres.gen_res_status = 8 or gres.gen_res_status = 6)
                $andWhere
                GROUP BY gen_res_date, destino ORDER BY gen_res_date, clientes DESC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXDestinationDailySummaryPayments($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $destination = $request->query->get('destination');

        $where = "";
        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . " gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . " gen_res_date <='$filter_date_to'";
        if ($destination != null && $destination != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . " des_id ='$destination'";

        $query = "SELECT gen_res_date as fecha, dest.des_name as destino,
                count(DISTINCT gen_res_user_id) as clientes,
                count(DISTINCT gen_res_id) as solicitudes,
                sum(owres.own_res_count_adults+owres.own_res_count_childrens) as personas_involucradas,
                count(own_res_id) as habitaciones,
                sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                SUM( DISTINCT CASE WHEN p.current_cuc_change_rate IS NULL THEN p.payed_amount*curr.curr_cuc_change ELSE p.payed_amount*p.current_cuc_change_rate END) as facturacion
                FROM ownershipreservation owres
                INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                INNER JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                INNER JOIN ownership o ON o.own_id = gres.gen_res_own_id
                INNER JOIN payment p on p.booking_id = b.booking_id
                INNER JOIN destination dest ON dest.des_id = o.own_destination
                INNER JOIN currency curr on p.currency_id = curr.curr_id
                $where
                GROUP BY gen_res_date, destino ORDER BY gen_res_date, clientes DESC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXDestinationMonthlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $destination = $request->query->get('destination');

        $where = "";
        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($destination != null && $destination != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "des_id ='$destination'";

        $query = "SELECT MONTHNAME(DATE(gres.gen_res_date)) as fecha, MONTH(gres.gen_res_date) as month, YEAR(gres.gen_res_date) as year, dest.des_name as destino,
                count(DISTINCT gen_res_user_id) as clientes,
                count(DISTINCT gen_res_id) as solicitudes,
                sum(owres.own_res_count_adults+owres.own_res_count_childrens) as personas_involucradas,
                count(own_res_id) as habitaciones,
                sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                FROM ownershipreservation owres
                INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                INNER JOIN ownership o ON o.own_id = gres.gen_res_own_id
                INNER JOIN destination dest ON dest.des_id = o.own_destination
                $where
                GROUP BY month, destino
                ORDER BY year, month, clientes DESC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getClientsXDestinationMonthlySummaryAvailable($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $destination = $request->query->get('destination');

        $andWhere = "";
        if ($filter_date_from != null && $filter_date_from != "")
            $andWhere .= " AND gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $andWhere .= " AND gen_res_date <='$filter_date_to'";
        if ($destination != null && $destination != "")
            $andWhere .= " AND des_id ='$destination'";

        $query = "SELECT MONTHNAME(DATE(gres.gen_res_date)) as fecha, MONTH(gres.gen_res_date) as month, YEAR(gres.gen_res_date) as year, dest.des_name as destino,
                count(DISTINCT gen_res_user_id) as clientes,
                count(DISTINCT gen_res_id) as solicitudes,
                sum(owres.own_res_count_adults+owres.own_res_count_childrens) as personas_involucradas,
                count(own_res_id) as habitaciones,
                sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                FROM ownershipreservation owres
                INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                INNER JOIN ownership o ON o.own_id = gres.gen_res_own_id
                INNER JOIN destination dest ON dest.des_id = o.own_destination
                WHERE (gres.gen_res_status = 1 or gres.gen_res_status = 2 or gres.gen_res_status = 8 or gres.gen_res_status = 6)
                $andWhere
                GROUP BY month, destino
                ORDER BY year, month, clientes DESC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXDestinationMonthlySummaryPayments($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $destination = $request->query->get('destination');

        $where = "";
        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . " gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . " gen_res_date <='$filter_date_to'";
        if ($destination != null && $destination != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . " des_id ='$destination'";

        $query = "SELECT MONTHNAME(DATE(gres.gen_res_date)) as fecha, MONTH(gres.gen_res_date) as month, YEAR(gres.gen_res_date) as year, dest.des_name as destino,
                count(DISTINCT gen_res_user_id) as clientes,
                count(DISTINCT gen_res_id) as solicitudes,
                sum(owres.own_res_count_adults+owres.own_res_count_childrens) as personas_involucradas,
                count(own_res_id) as habitaciones,
                sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                SUM( DISTINCT CASE WHEN p.current_cuc_change_rate IS NULL THEN p.payed_amount*curr.curr_cuc_change ELSE p.payed_amount*p.current_cuc_change_rate END) as facturacion
                FROM ownershipreservation owres
                INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                INNER JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                INNER JOIN ownership o ON o.own_id = gres.gen_res_own_id
                INNER JOIN payment p on p.booking_id = b.booking_id
                INNER JOIN destination dest ON dest.des_id = o.own_destination
                INNER JOIN currency curr on p.currency_id = curr.curr_id
                $where
                GROUP BY month, destino
                ORDER BY year, month, clientes DESC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }
    function getClientsXDestinationYearlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $destination = $request->query->get('destination');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($destination != null && $destination != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "des_id ='$destination'";

        $query = "SELECT YEAR(gres.gen_res_date) as fecha, dest.des_name as destino,
                count(DISTINCT gen_res_user_id) as clientes,
                count(DISTINCT gen_res_id) as solicitudes,
                sum(owres.own_res_count_adults+owres.own_res_count_childrens) as personas_involucradas,
                count(own_res_id) as habitaciones,
                sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                FROM ownershipreservation owres
                INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                INNER JOIN ownership o ON o.own_id = gres.gen_res_own_id
                INNER JOIN destination dest ON dest.des_id = o.own_destination
                $where
                GROUP BY fecha, destino
                ORDER BY fecha;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }
    function getClientsXDestinationYearlySummaryAvailable($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $destination = $request->query->get('destination');

        $andWhere = "";
        if ($filter_date_from != null && $filter_date_from != "")
            $andWhere .= " AND gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $andWhere .= " AND gen_res_date <='$filter_date_to'";
        if ($destination != null && $destination != "")
            $andWhere .= " AND des_id ='$destination'";

        $query = "SELECT YEAR(gres.gen_res_date) as fecha, dest.des_name as destino,
                count(DISTINCT gen_res_user_id) as clientes,
                count(DISTINCT gen_res_id) as solicitudes,
                sum(owres.own_res_count_adults+owres.own_res_count_childrens) as personas_involucradas,
                count(own_res_id) as habitaciones,
                sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                FROM ownershipreservation owres
                INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                INNER JOIN ownership o ON o.own_id = gres.gen_res_own_id
                INNER JOIN destination dest ON dest.des_id = o.own_destination
                WHERE (gres.gen_res_status = 1 or gres.gen_res_status = 2 or gres.gen_res_status = 8 or gres.gen_res_status = 6)
                $andWhere
                GROUP BY fecha, destino
                ORDER BY fecha;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }
    function getClientsXDestinationYearlySummaryPayments($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $destination = $request->query->get('destination');

        $where = "";
        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . " gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . " gen_res_date <='$filter_date_to'";
        if ($destination != null && $destination != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . " des_id ='$destination'";

        $query = "SELECT YEAR(gres.gen_res_date) as fecha, dest.des_name as destino,
                count(DISTINCT gen_res_user_id) as clientes,
                count(DISTINCT gen_res_id) as solicitudes,
                sum(owres.own_res_count_adults+owres.own_res_count_childrens) as personas_involucradas,
                count(own_res_id) as habitaciones,
                sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                SUM( DISTINCT CASE WHEN p.current_cuc_change_rate IS NULL THEN p.payed_amount*curr.curr_cuc_change ELSE p.payed_amount*p.current_cuc_change_rate END) as facturacion
                FROM ownershipreservation owres
                INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                INNER JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                INNER JOIN ownership o ON o.own_id = gres.gen_res_own_id
                INNER JOIN payment p on p.booking_id = b.booking_id
                INNER JOIN destination dest ON dest.des_id = o.own_destination
                INNER JOIN currency curr on p.currency_id = curr.curr_id
                $where
                GROUP BY fecha, destino
                ORDER BY fecha;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXCountryMonthlySummaryAvailable($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $country = $request->query->get('country');

        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";
        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($country != null && $country != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "co_id ='$country'";

        $query = "SELECT MONTHNAME(gen_res_date) as fecha, MONTH(gen_res_date) as month, YEAR(gen_res_date) as year, country.co_name as pais, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN country ON user.user_country = country.co_id
                   $where GROUP BY month, pais ORDER BY year ASC, month ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXCountryMonthlySummaryPayments($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $country = $request->query->get('country');

        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($country != null && $country != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "co_id ='$country'";

        $query = "SELECT MONTHNAME(gen_res_date) as fecha, MONTH(gen_res_date) as month, YEAR(gen_res_date) as year, country.co_name as pais, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                  SUM( DISTINCT CASE WHEN current_cuc_change_rate IS NULL THEN payed_amount*curr_cuc_change ELSE payed_amount*current_cuc_change_rate END) as facturacion
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN country ON user.user_country = country.co_id
                  INNER JOIN booking ON ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id INNER JOIN currency ON booking.booking_currency = currency.curr_id
                  $where GROUP BY month, pais ORDER BY year ASC, month ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }


    function getClientsXCountryYearlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $country = $request->query->get('country');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($country != null && $country != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "co_id ='$country'";

        $query = "SELECT YEAR(gen_res_date) as fecha, country.co_name as pais, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN country ON user.user_country = country.co_id
                   $where GROUP BY fecha, pais ORDER BY fecha ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getClientsXCountryYearlySummaryAvailable($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $country = $request->query->get('country');

        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($country != null && $country != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "co_id ='$country'";

        $query = "SELECT YEAR(gen_res_date) as fecha, country.co_name as pais, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN country ON user.user_country = country.co_id
                   $where GROUP BY fecha, pais ORDER BY fecha ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXCountryYearlySummaryPayments($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $country = $request->query->get('country');

        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";
        if ($country != null && $country != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "co_id ='$country'";

        $query = "SELECT YEAR(gen_res_date) as fecha, country.co_name as pais, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                  SUM( DISTINCT CASE WHEN current_cuc_change_rate IS NULL THEN payed_amount*curr_cuc_change ELSE payed_amount*current_cuc_change_rate END) as facturacion
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN country ON user.user_country = country.co_id
                  INNER JOIN booking ON ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id INNER JOIN currency ON booking.booking_currency = currency.curr_id
                  $where GROUP BY fecha, pais ORDER BY fecha ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXRequestsDailySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');


        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT gen_res_date as fecha, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
                   $where GROUP BY gen_res_date;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getClientsXRequestsDailySummaryAvailable($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');

        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT gen_res_date as fecha, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
                   $where GROUP BY gen_res_date;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXRequestsDailySummaryPayments($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');


        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT gen_res_date as fecha, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                  SUM( DISTINCT CASE WHEN current_cuc_change_rate IS NULL THEN payed_amount*curr_cuc_change ELSE payed_amount*current_cuc_change_rate END) as facturacion
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
                  INNER JOIN booking ON ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id INNER JOIN currency ON booking.booking_currency = currency.curr_id
                  $where GROUP BY gen_res_date;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXRequestsMonthlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT MONTHNAME(gen_res_date) as fecha, MONTH(gen_res_date) as month, YEAR(gen_res_date) as year, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
                   $where GROUP BY month ORDER BY year ASC, month ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getClientsXRequestsMonthlySummaryAvailable($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');

        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";
        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT MONTHNAME(gen_res_date) as fecha, MONTH(gen_res_date) as month, YEAR(gen_res_date) as year, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
                   $where GROUP BY month ORDER BY year ASC, month ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXRequestsMonthlySummaryPayments($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');


        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT MONTHNAME(gen_res_date) as fecha, MONTH(gen_res_date) as month, YEAR(gen_res_date) as year, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                  SUM( DISTINCT CASE WHEN current_cuc_change_rate IS NULL THEN payed_amount*curr_cuc_change ELSE payed_amount*current_cuc_change_rate END) as facturacion
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
                  INNER JOIN booking ON ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id INNER JOIN currency ON booking.booking_currency = currency.curr_id
                  $where GROUP BY month ORDER BY year ASC, month ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }


    function getClientsXRequestsYearlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT YEAR(gen_res_date) as fecha, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
                   $where GROUP BY fecha ORDER BY fecha ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getClientsXRequestsYearlySummaryAvailable($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');


        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT YEAR(gen_res_date) as fecha, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
                   $where GROUP BY fecha ORDER BY fecha ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    function getClientsXRequestsYearlySummaryPayments($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');

        $where = "WHERE (gen_res_status=1 OR gen_res_status=2 OR gen_res_status=8 OR gen_res_status=6)";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT YEAR(gen_res_date) as fecha, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                  SUM( DISTINCT CASE WHEN current_cuc_change_rate IS NULL THEN payed_amount*curr_cuc_change ELSE payed_amount*current_cuc_change_rate END) as facturacion
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
                  INNER JOIN booking ON ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id INNER JOIN currency ON booking.booking_currency = currency.curr_id
                  $where GROUP BY fecha ORDER BY fecha ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;
    }

    /**Facturación*/
    function getClientsXFacturationDailySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "created >='$filter_date_from 00:00:00'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to 23:59:59'";

        $query = "SELECT DATE(created) as fecha, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                  (SELECT SUM(CASE WHEN current_cuc_change_rate IS NOT NULL THEN payed_amount*current_cuc_change_rate ELSE payed_amount*curr_cuc_change END) from payment WHERE DATE(created)=fecha) as facturacion
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN booking ON  ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id INNER JOIN currency ON payment.currency_id = currency.curr_id
                   $where GROUP BY fecha;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getClientsXFacturationMonthlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "created >='$filter_date_from 00:00:00'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to 23:59:59'";

        $query = "SELECT MONTHNAME(DATE(created)) as fecha, MONTH(created) as month, YEAR(created) as year, , count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                  (SELECT SUM(CASE WHEN current_cuc_change_rate IS NOT NULL THEN payed_amount*current_cuc_change_rate ELSE payed_amount*curr_cuc_change END) from payment WHERE MONTH(created)=month AND YEAR(created)=year as facturacion
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN booking ON  ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id INNER JOIN currency ON payment.currency_id = currency.curr_id
                   $where GROUP BY month ORDER BY year ASC , month;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }
    function getClientsXFacturationYearlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "created >='$filter_date_from 00:00:00'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to 23:59:59'";

        $query = "SELECT YEAR(DATE(created)) as fecha, count(DISTINCT gen_res_user_id) as clientes, count(DISTINCT gen_res_id) as solicitudes, sum(ownershipreservation.own_res_count_adults+ownershipreservation.own_res_count_childrens) as personas_involucradas, count(own_res_id) as habitaciones, sum(DATEDIFF(own_res_reservation_to_date, own_res_reservation_from_date)) as noches,
                  0 as facturacion
                  FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id INNER JOIN booking ON  ownershipreservation.own_res_reservation_booking = booking.booking_id INNER JOIN payment ON booking.booking_id = payment.booking_id
                   $where GROUP BY fecha ORDER BY fecha;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }
    function getClientsXOnlyFacturationYearlySummary($request)
    {

        $query = "SELECT YEAR(DATE(created)) as fecha,
        SUM(CASE WHEN current_cuc_change_rate IS NULL THEN payed_amount*curr_cuc_change ELSE payed_amount*current_cuc_change_rate END) as facturacion
        FROM payment INNER JOIN currency ON payment.currency_id = currency.curr_id
        GROUP BY fecha HAVING fecha>2013 ORDER BY fecha;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getReservationsDailySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT gres.gen_res_date as fecha, count(distinct gres.gen_res_id) as cantidad, count(owres.own_res_id) as habitaciones,
                  sum(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date)) as noches
                  FROM ownershipreservation owres
                  INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                   $where GROUP BY fecha
                   ORDER BY fecha ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getReservationsAvailableDailySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = " WHERE (gres.gen_res_status = 1 or gres.gen_res_status = 2 or gres.gen_res_status = 8 or gres.gen_res_status = 6) ";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT gres.gen_res_date as fecha, count(distinct gres.gen_res_id) as cantidad, count(owres.own_res_id) as habitaciones,
                  sum(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date)) as noches
                  FROM ownershipreservation owres
                  INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                   $where GROUP BY fecha
                   ORDER BY fecha ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getReservationsPaymentsDailySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT gres.gen_res_date as fecha, count(distinct gres.gen_res_id) as cantidad, count(owres.own_res_id) as habitaciones,
                  sum(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date)) as noches,
                  SUM( DISTINCT CASE WHEN p.current_cuc_change_rate IS NULL THEN p.payed_amount*curr.curr_cuc_change ELSE p.payed_amount*p.current_cuc_change_rate END) as facturacion
                  FROM ownershipreservation owres
                  INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                  INNER JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                  INNER JOIN payment p on p.booking_id = b.booking_id
                  INNER JOIN currency curr on curr.curr_id = p.currency_id
                   $where GROUP BY fecha
                   ORDER BY fecha ASC;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getReservationsMonthlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT MONTHNAME(gres.gen_res_date) as fecha, MONTH(gres.gen_res_date) as month, YEAR(gres.gen_res_date) as year, count(distinct gres.gen_res_id) as cantidad, count(owres.own_res_id) as habitaciones,
                  sum(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date)) as noches
                  FROM ownershipreservation owres
                  INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                   $where GROUP BY month
                   ORDER BY year ASC, month;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getReservationsAvailableMonthlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = " WHERE (gres.gen_res_status = 1 or gres.gen_res_status = 2 or gres.gen_res_status = 8 or gres.gen_res_status = 6) ";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT MONTHNAME(gres.gen_res_date) as fecha, MONTH(gres.gen_res_date) as month, YEAR(gres.gen_res_date) as year, count(distinct gres.gen_res_id) as cantidad, count(owres.own_res_id) as habitaciones,
                  sum(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date)) as noches
                  FROM ownershipreservation owres
                  INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                   $where GROUP BY month
                   ORDER BY year ASC, month;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getReservationsPaymentsMonthlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT MONTHNAME(gres.gen_res_date) as fecha, MONTH(gres.gen_res_date) as month, YEAR(gres.gen_res_date) as year, count(distinct gres.gen_res_id) as cantidad, count(owres.own_res_id) as habitaciones,
                  sum(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date)) as noches,
                  SUM( DISTINCT CASE WHEN p.current_cuc_change_rate IS NULL THEN p.payed_amount*curr.curr_cuc_change ELSE p.payed_amount*p.current_cuc_change_rate END) as facturacion
                  FROM ownershipreservation owres
                  INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                  INNER JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                  INNER JOIN payment p on p.booking_id = b.booking_id
                  INNER JOIN currency curr on curr.curr_id = p.currency_id
                   $where GROUP BY month
                   ORDER BY year ASC, month;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getReservationsYearlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT YEAR(gres.gen_res_date) as fecha, count(distinct gres.gen_res_id) as cantidad, count(owres.own_res_id) as habitaciones,
                  sum(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date)) as noches
                  FROM ownershipreservation owres
                  INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                   $where GROUP BY fecha
                   ORDER BY fecha;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getReservationsAvailableYearlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = " WHERE (gres.gen_res_status = 1 or gres.gen_res_status = 2 or gres.gen_res_status = 8 or gres.gen_res_status = 6) ";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT YEAR(gres.gen_res_date) as fecha, count(distinct gres.gen_res_id) as cantidad, count(owres.own_res_id) as habitaciones,
                  sum(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date)) as noches
                  FROM ownershipreservation owres
                  INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                   $where GROUP BY fecha
                   ORDER BY fecha;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

    function getReservationsPaymentsYearlySummary($request)
    {
        $filter_date_from = $request->query->get('filter_date_from');
        $filter_date_to = $request->query->get('filter_date_to');
        $where = "";

        if ($filter_date_from != null && $filter_date_from != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date >='$filter_date_from'";
        if ($filter_date_to != null && $filter_date_to != "")
            $where .= (($where == "") ? " WHERE " : " AND ") . "gen_res_date <='$filter_date_to'";

        $query = "SELECT YEAR(gres.gen_res_date) as fecha, count(distinct gres.gen_res_id) as cantidad, count(owres.own_res_id) as habitaciones,
                  sum(DATEDIFF(owres.own_res_reservation_to_date, owres.own_res_reservation_from_date)) as noches,
                  SUM( DISTINCT CASE WHEN p.current_cuc_change_rate IS NULL THEN p.payed_amount*curr.curr_cuc_change ELSE p.payed_amount*p.current_cuc_change_rate END) as facturacion
                  FROM ownershipreservation owres
                  INNER JOIN generalreservation gres ON owres.own_res_gen_res_id = gres.gen_res_id
                  INNER JOIN booking b on b.booking_id = owres.own_res_reservation_booking
                  INNER JOIN payment p on p.booking_id = b.booking_id
                  INNER JOIN currency curr on curr.curr_id = p.currency_id
                   $where GROUP BY fecha
                   ORDER BY fecha;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $po = $stmt->fetchAll();
        return $po;

    }

}