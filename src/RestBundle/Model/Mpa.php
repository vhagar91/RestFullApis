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
use RestBundle\Helpers\Date;
use RestBundle\Helpers\Utils;
use RestBundle\Exception\InvalidFormException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;

class Mpa extends Base
{
    /**
     * @param $request
     * @return mixed
     */
    public function login($request)
    {
        $user = $this->checkUserInfopoint($request->request->get('user'), $request->request->get('password'));

        if (isset($user)) {
            $destinations = $this->getDestinationByUserInfoPoint($user['id_uip']);
            $user['destinations'] = $destinations;
            $user['nomencladores'] = $this->getNomencladores($user['id_uip']);

            unset($user['password']);
            unset($user['salt']);
            unset($user['id_uip']);

            return $user;
        }
        else {
            return $this->view->create(array('success' => false, 'code' => Response::HTTP_UNAUTHORIZED, 'message' => 'Bad credentials 1'), Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Crear negosio.
     * @param $bussiness
     * @param $user
     * @param $pass
     * @return array
     */
    public function createBussiness($bussiness, $user, $pass)
    {
        $desId = $bussiness->id_destination;
        $user = $this->checkUserInfopoint($user, $pass, $desId);
        if(!$user){
            return $this->view->create(array('success' => false, 'code' => Response::HTTP_UNAUTHORIZED, 'message' => 'No tiene permisos para adicionar propiedades en este destino'), Response::HTTP_UNAUTHORIZED);
        }
        else if(!property_exists($bussiness, 'id_municipality') || $bussiness->id_municipality == ""){
            return $this->view->create(array('success' => false, 'code' => Response::HTTP_BAD_REQUEST, 'message' => 'Indique el municipio'), Response::HTTP_BAD_REQUEST);
        }

        $keyValuesBussiness = array();
        (property_exists($bussiness, 'name') && $bussiness->name != "") ? $keyValuesBussiness['name'] = $bussiness->name : $keyValuesBussiness;
        (property_exists($bussiness, 'id_municipality') && $bussiness->id_municipality != "") ? $keyValuesBussiness['code'] = $this->getCode($bussiness->id_municipality) : $keyValuesBussiness;
        (property_exists($bussiness, 'licence') && $bussiness->licence != "") ? $keyValuesBussiness['licence'] = $bussiness->licence : $keyValuesBussiness;
        (property_exists($bussiness, 'owner_name') && $bussiness->owner_name != "") ? $keyValuesBussiness['owner_name'] = $bussiness->owner_name : $keyValuesBussiness;
        (property_exists($bussiness, 'contact_name') && $bussiness->contact_name != "") ? $keyValuesBussiness['contact_name'] = $bussiness->contact_name : $keyValuesBussiness;
        (property_exists($bussiness, 'email') && $bussiness->email != "") ? $keyValuesBussiness['email'] = $bussiness->email : $keyValuesBussiness;
        (property_exists($bussiness, 'phone') && $bussiness->phone != "") ? $keyValuesBussiness['phone'] = $bussiness->phone : $keyValuesBussiness;
        (property_exists($bussiness, 'movil') && $bussiness->movil != "") ? $keyValuesBussiness['movil'] = $bussiness->movil : $keyValuesBussiness;
        (property_exists($bussiness, 'id_bussiness_status') && $bussiness->id_bussiness_status != "") ? $keyValuesBussiness['id_bussiness_status'] = $bussiness->id_bussiness_status : $keyValuesBussiness;
        (property_exists($bussiness, 'cubacoupon') && $bussiness->cubacoupon != "") ? $keyValuesBussiness['cubacoupon'] = $bussiness->cubacoupon : $keyValuesBussiness;
        (property_exists($bussiness, 'service_cubacoupon') && $bussiness->service_cubacoupon != "") ? $keyValuesBussiness['service_cubacoupon'] = $bussiness->service_cubacoupon : $keyValuesBussiness;
        (property_exists($bussiness, 'top') && $bussiness->top != "") ? $keyValuesBussiness['top'] = $bussiness->top : $keyValuesBussiness;
        (property_exists($bussiness, 'id_municipality') && $bussiness->id_municipality != "") ? $keyValuesBussiness['id_municipality'] = $bussiness->id_municipality : $keyValuesBussiness;
        (property_exists($bussiness, 'street') && $bussiness->street != "") ? $keyValuesBussiness['street'] = $bussiness->street : $keyValuesBussiness;
        (property_exists($bussiness, 'address_number') && $bussiness->address_number != "") ? $keyValuesBussiness['address_number'] = $bussiness->address_number : $keyValuesBussiness;
        (property_exists($bussiness, 'between_street_one') && $bussiness->between_street_one != "") ? $keyValuesBussiness['between_street_one'] = $bussiness->between_street_one : $keyValuesBussiness;
        (property_exists($bussiness, 'between_street_two') && $bussiness->between_street_two != "") ? $keyValuesBussiness['between_street_two'] = $bussiness->between_street_two : $keyValuesBussiness;
        (property_exists($bussiness, 'id_destination') && $bussiness->id_destination != "") ? $keyValuesBussiness['id_destination'] = $bussiness->id_destination : $keyValuesBussiness;
        (property_exists($bussiness, 'longitude') && $bussiness->longitude != "") ? $keyValuesBussiness['longitude'] = $bussiness->longitude : $keyValuesBussiness;
        (property_exists($bussiness, 'latitude') && $bussiness->latitude != "") ? $keyValuesBussiness['latitude'] = $bussiness->latitude : $keyValuesBussiness;
        (property_exists($bussiness, 'schedules') && $bussiness->schedules != "") ? $keyValuesBussiness['schedules'] = $bussiness->schedules : $keyValuesBussiness;
        (property_exists($bussiness, 'price_average') && $bussiness->price_average != "") ? $keyValuesBussiness['price_average'] = $bussiness->price_average : $keyValuesBussiness;
        (property_exists($bussiness, 'id_bussiness_ranking') && $bussiness->id_bussiness_ranking != "") ? $keyValuesBussiness['id_bussiness_ranking'] = $bussiness->id_bussiness_ranking : $keyValuesBussiness;
        (property_exists($bussiness, 'web') && $bussiness->web != "") ? $keyValuesBussiness['web'] = $bussiness->web : $keyValuesBussiness;
        (property_exists($bussiness, 'facebook') && $bussiness->facebook != "") ? $keyValuesBussiness['facebook'] = $bussiness->facebook : $keyValuesBussiness;
        (property_exists($bussiness, 'twitter') && $bussiness->twitter != "") ? $keyValuesBussiness['twitter'] = $bussiness->twitter : $keyValuesBussiness;
        (property_exists($bussiness, 'google') && $bussiness->google != "") ? $keyValuesBussiness['google'] = $bussiness->google : $keyValuesBussiness;
        (property_exists($bussiness, 'instagram') && $bussiness->instagram != "") ? $keyValuesBussiness['instagram'] = $bussiness->instagram : $keyValuesBussiness;
        (property_exists($bussiness, 'description_original') && $bussiness->description_original != "") ? $keyValuesBussiness['description_original'] = $bussiness->description_original : $keyValuesBussiness;
        $this->conn->insert('bussiness', $keyValuesBussiness);
        $id_bussiness = $this->conn->lastInsertId();
        (property_exists($bussiness, 'id_municipality') && $bussiness->id_municipality != "") ? $this->updateAutoCode($bussiness->id_municipality) : null;

        if(property_exists($bussiness, 'bussiness_types')){
            foreach ($bussiness->bussiness_types as $bussinessType) {
                $keyValues = array();
                $keyValues['id_bussiness'] = $id_bussiness;
                $keyValues['id_bussiness_type'] = $bussinessType;
                $this->conn->insert('bussiness_bussiness_type', $keyValues);
            }
        }

        if(property_exists($bussiness, 'bussiness_details')){
            foreach ($bussiness->bussiness_details as $bussiness_detail) {
                $keyValues = array();
                $keyValues['id_bussiness'] = $id_bussiness;
                $keyValues['id_bussiness_detail'] = $bussiness_detail;
                $this->conn->insert('bussiness_bussiness_detail', $keyValues);
            }
        }

        if(property_exists($bussiness, 'coins')){
            foreach ($bussiness->coins as $coin) {
                $keyValues = array();
                $keyValues['id_bussiness'] = $id_bussiness;
                $keyValues['id_coin'] = $coin;
                $this->conn->insert('bussiness_coin', $keyValues);
            }
        }

        if(property_exists($bussiness, 'food_types')){
            foreach ($bussiness->food_types as $food_type) {
                $keyValues = array();
                $keyValues['id_bussiness'] = $id_bussiness;
                $keyValues['id_food_type'] = $food_type;
                $this->conn->insert('bussiness_food_type', $keyValues);
            }
        }

        if(property_exists($bussiness, 'kitchen_types')){
            foreach ($bussiness->kitchen_types as $kitchen_type) {
                $keyValues = array();
                $keyValues['id_bussiness'] = $id_bussiness;
                $keyValues['id_kitchen_type'] = $kitchen_type;
                $this->conn->insert('bussiness_kitchen_type', $keyValues);
            }
        }

        if(property_exists($bussiness, 'offers')){
            foreach ($bussiness->offers as $offer) {
                $keyValuesOffer = array();
                (property_exists($offer, 'name') && $offer->name != "") ? ($keyValuesOffer['name'] = $offer->name) : null;
                $keyValuesOffer['id_bussiness'] = $id_bussiness;

                $this->conn->insert('offer', $keyValuesOffer);
                $id_offer = $this->conn->lastInsertId();

                if(property_exists($offer, 'products')){
                    foreach ($offer->products as $product) {
                        $keyValuesProduct = array();
                        (property_exists($product, 'name') && $product->name != "") ? ($keyValuesProduct['name'] = $product->name) : null;
                        (property_exists($product, 'price') && $product->price != "") ? ($keyValuesProduct['price'] = $product->price) : null;
                        (property_exists($product, 'description') && $product->description != "") ? ($keyValuesProduct['description'] = $product->description) : null;
                        $keyValuesProduct['id_offer'] = $id_offer;

                        $this->conn->insert('product', $keyValuesProduct);
                    }
                }
            }
        }

        /*$date = new \DateTime();
        self::insertLog(4, 1, 'ownership', 'Casa con código '.$values['own_mcp_code'].' adicionada desde la app movil por usuario:'.$user['user_name'].' el dia:'.$date->format('Y-m-d H:i:s.u e'), $user['user_id']);*/

        return array('success' => true, 'code' => $keyValuesBussiness['code'], 'id' => $id_bussiness);
    }

    public function saveImages($user, $pass, $code, $images_base64, $container, $host){

        $desId = $this->getDestinationByCode($code);
        $user = $this->checkUserInfopoint($user, $pass, $desId);
        if(!$user){
            return $this->view->create(array('success' => false, 'code' => Response::HTTP_UNAUTHORIZED, 'message' => 'No tiene permisos para adicionar imagenes a esta propiedad'), Response::HTTP_UNAUTHORIZED);
        }

        $path = $container->getParameter('bussiness.dir.photos.originals');
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

    public function getBussiness(){
        try {
            $query = "SELECT
CONCAT('dinner', '_', bussiness.id) AS id,
bussiness.name AS title,
'Restaurante' AS type,
bussiness.longitude,
bussiness.latitude,
CONCAT('http://mypaladar.com/uploads/medias', '/', media.path) AS image,
  CONCAT( bussiness.street,' #',bussiness.address_number,' ',bussiness.between_street_one,' & ',bussiness.between_street_two,' ',municipality.name) AS address,
  province.name AS prov_name,
  destination.name AS des_name
FROM media RIGHT OUTER JOIN bussiness ON (media.id = bussiness.id_logo)
INNER JOIN municipality ON (bussiness.id_municipality = municipality.id)
INNER JOIN province ON (municipality.id_province = province.id)
 INNER JOIN destination ON (bussiness.id_destination = destination.id)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $bussinesses = $stmt->fetchAll();

            foreach ($bussinesses as $index => $bussiness) {
                if(!array_key_exists('image', $bussiness) || !isset($bussiness['path'])){
                    $bussinesses[$index]['image'] = 'http://mypaladar.com/uploads/medias/default.png';
                }
            }

            return array('success' => true, 'bussiness' => $bussinesses);
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    /* *********************************************************************** */

    public function checkUserInfopoint($user, $password, $idDestination = null){
        if(!isset($idDestination)){
            $query = "SELECT user.id, user.username, user.password, user.salt, user.name, user.lastname, user.email, user_info_point.id AS id_uip
FROM user INNER JOIN user_info_point ON (user.id = user_info_point.id_user)
WHERE  user.username=:username OR user.email=:username";
            $stmt = $this->conn->prepare($query);
        }
        else{
            $query = "SELECT user.id, user.username, user.password, user.salt, user.name, user.lastname, user.email, user_info_point.id AS id_uip
FROM user INNER JOIN user_info_point ON (user.id = user_info_point.id_user) INNER JOIN user_info_point_destination ON (user_info_point.id = user_info_point_destination.id_user_info_point)
WHERE (user.username=:username OR user.email=:username) AND  user_info_point_destination.id_destination = :id_destination";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('id_destination', $idDestination);
        }

        $stmt->bindValue('username', $user);
        $stmt->execute();
        $user = $stmt->fetch();

        $encrypt_password = self::encryptPassword($password, $user['salt']);
        if (isset($user['id_uip']) && $user['password'] == $encrypt_password){
            return $user;
        }
        else{
            return null;
        }
    }

    public function getDestinationByUserInfoPoint($idUserInfopoint){
        try {
            $queryDest = "SELECT destination.id, destination.name, destination.id_municipality
FROM user_info_point_destination INNER JOIN destination ON (user_info_point_destination.id_destination = destination.id)
WHERE user_info_point_destination.id_user_info_point = :id_user_info_point";

            $stmtDest = $this->conn->prepare($queryDest);
            $stmtDest->bindValue('id_user_info_point', $idUserInfopoint);
            $stmtDest->execute();
            $resDest = $stmtDest->fetchAll();

            return $resDest;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getDestinationByCode($code){
        try {
            $queryDest = "SELECT destination.id FROM destination INNER JOIN bussiness ON (destination.id = bussiness.id_destination) WHERE bussiness.code = :code";

            $stmtDest = $this->conn->prepare($queryDest);
            $stmtDest->bindValue('code', $code);
            $stmtDest->execute();
            $resDest = $stmtDest->fetch();

            return $resDest['id'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getNomencladores($id_uip){
        $nomencladores = [
            'provinces'=>$this->listProvinces($id_uip),
            'municipalities'=>$this->listMunicipality($id_uip),
            'bussiness_type'=>$this->listBussinessType(),
            'bussiness_status'=>$this->listBussinessStatus(),
            'bussiness_detail'=>$this->listBussinessDetail(),
            'coin'=>$this->listCoin(),
            'bussiness_ranking'=>$this->listBussinessRanking(),
            'food_type'=>$this->listFoodType(),
            'kitchen_type'=>$this->listKitchenType()
        ];

        return $nomencladores;
    }

    public function listProvinces($id_uip)
    {
        try {
            $query = "SELECT province.id, province.name
FROM destination INNER JOIN user_info_point_destination ON (destination.id = user_info_point_destination.id_destination)
  INNER JOIN municipality ON (destination.id_municipality = municipality.id) INNER JOIN province ON (municipality.id_province = province.id)
WHERE user_info_point_destination.id_user_info_point = :id_user_info_point GROUP BY province.id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('id_user_info_point', $id_uip);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listMunicipality($id_uip)
    {
        try {
            $query = "SELECT municipality.id, municipality.id_province, municipality.name
FROM destination INNER JOIN user_info_point_destination ON (destination.id = user_info_point_destination.id_destination)
  INNER JOIN municipality ON (destination.id_municipality = municipality.id)
WHERE user_info_point_destination.id_user_info_point = :id_user_info_point GROUP BY municipality.id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue('id_user_info_point', $id_uip);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listBussinessType()
    {
        try {
            $query = "SELECT bussiness_type.id, bussiness_type.name FROM bussiness_type";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listBussinessStatus()
    {
        try {
            $query = "SELECT bussiness_status.id, bussiness_status.name FROM bussiness_status";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listBussinessDetail()
    {
        try {
            $query = "SELECT bussiness_detail.id, bussiness_detail.name FROM bussiness_detail";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listCoin()
    {
        try {
            $query = "SELECT coin.id, coin.name, coin.code FROM coin";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listBussinessRanking()
    {
        try {
            $query = "SELECT bussiness_ranking.id, bussiness_ranking.name FROM bussiness_ranking";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listFoodType()
    {
        try {
            $query = "SELECT food_type.id, food_type.name FROM food_type";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function listKitchenType()
    {
        try {
            $query = "SELECT kitchen_type.id, kitchen_type.name FROM kitchen_type";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $po = $stmt->fetchAll();
            return $po;
        } catch (\Exception $exc) {
            return array('success' => false, 'message' => $exc->getMessage());
        }
    }

    public function getCode($idMunicipality)
    {
        $query = "SELECT province.id, province.code, province.auto_code FROM province INNER JOIN municipality ON (province.id = municipality.id_province) WHERE municipality.id = :id_municipality";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('id_municipality', $idMunicipality);
        $stmt->execute();
        $province = $stmt->fetch();

        $provCode = $province['code'];

        $number = $province['auto_code'] + 1;
        $str_number = $number.'';

        if($number < 100){
            $str_number = str_pad($str_number, 3, "0", STR_PAD_LEFT);
        }

        $code = 'P'.$provCode . $str_number;
        return $code;
    }

    public function updateAutoCode($idMunicipality)
    {
        $query = "SELECT province.id, province.code, province.auto_code FROM province INNER JOIN municipality ON (province.id = municipality.id_province) WHERE municipality.id = :id_municipality";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('id_municipality', $idMunicipality);
        $stmt->execute();
        $province = $stmt->fetch();

        $idProvince = $province['id'];
        $newAutoCode = $province['auto_code'] + 1;

        $values = array('auto_code'=>$newAutoCode);
        $id = array('id'=>$idProvince);

        $this->conn->update('province', $values, $id);
    }

    public function resizeAndWatermark($fileName, $container, $subPath = "") {
        $watermark_full_path = $container->getParameter('watermark.mypa.full.path');
        $dirPhotosOriginals = $container->getParameter('bussiness.dir.photos.originals');
        $dirPhotos = $container->getParameter('cbs.dir.web').$container->getParameter('bussiness.dir.photos');
        $new_height = $container->getParameter('bussiness.height.photos');

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

        return $container->getParameter('bussiness.dir.photos').$subPath."/".$fileName;
    }

    public function saveImagesInDb($code, $webDirPhotoFull, $order){
        $array_photo = array();
        $array_photo['name'] = "nombre";
        $array_photo['path'] = $webDirPhotoFull;
        $array_photo['created'] = (new \DateTime(date('Y-m-d')))->format('Y-m-d');
        $array_photo['description'] = "description";

        $this->conn->insert('media', $array_photo);
        $id = $this->conn->lastInsertId();

        $query= "SELECT bussiness.id FROM bussiness WHERE bussiness.code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('code', $code);
        $stmt->execute();
        $ownership = $stmt->fetch();

        $array_photo = array();
        $array_photo['id_media'] = $id;
        $array_photo['id_bussiness'] = $ownership['id'];
        $this->conn->insert('bussiness_media', $array_photo);
    }
}