<?php
/*
 * This file is part of the API-REST CBS v1.0.0.0 alfa.
 *
 * (c) Development team HDS <correo>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace RestBundle\Service;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RestService extends Controller
{

    public function __construct(){
    }

    /**
     * @return string
     */
    public function createRestToken($id){
        return  'api-'.hash('md5', time() . rand(0, PHP_INT_MAX) . time()).$id;
    }
}
