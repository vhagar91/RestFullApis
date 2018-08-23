<?php

namespace RestBundle\Controller;

/*
 * This file is part of the API-REST CBS v1.0.0.0 alfa.
 *
 * (c) Development team HDS <correo>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
use FOS\RestBundle\Controller\FOSRestController;
use RestBundle\Model\Mcp;
use FOS\RestBundle\View\View;
use RestBundle\Exception\InvalidFormException;

class RestController extends FOSRestController
{
    private $key;

    /**
     * Función que valida si un api es valida
     * @param $key
     * @return bool
     */
    public function checkSecurityApi($key)
    {
        $this->key = $key;
        if ($key != '') {
            $em = $this->get('doctrine')->getEntityManager();
            $datApiProjectRepository = $em->getRepository('FrontendBundle:DatApiProject');
            $res = $datApiProjectRepository->findByApiKey(trim($key));
            if (count($res)) {
                //Validar para ver si el api esta activa
                $id = substr($key, -1);
                $em = $this->get('doctrine')->getEntityManager();
                $api = $em->getRepository('FrontendBundle:DatApi')->find($id);
                if ($api->getStatus() == 1)
                    return array('success' => true, 'msg' => 'The api is active', 'class' => $api->getClass());
                else
                    return array('success' => false, 'msg' => 'The api is inactive');
            } else
                return array('success' => false, 'msg' => 'The key is not exist');
        } else
            return array('success' => false, 'msg' => 'The key is required');
    }

    /**
     * Función para devolver una instancia de la clase
     * @return $connection
     */
    public function loadClass($class)
    {
        $instance = null;
        $view = new View();
        $class = "RestBundle\\Model\\$class";

        try {
            $instance = new $class($this->getConnection(), $view);
        }
        catch(Exception $e){
            throw $e;
        }
        return $instance;

    }

    /**
     * Función que devuelve la conexión
     * @return $connection
     */
    public function getConnection()
    {
        $id = substr($this->key, -1);
        $em = $this->get('doctrine')->getEntityManager();
        $api = $em->getRepository('FrontendBundle:DatApi')->find($id);
        $connectionFactory = $this->container->get('doctrine.dbal.connection_factory');
        $connection = $connectionFactory->createConnection(array(
            'driver' => $api->getDriver()->getName(),
            'user' => $api->getDbUser(),
            'password' => $api->getDbPassword(),
            'host' => $api->getDbHost(),
            'dbname' => $api->getDbName(),
            'charset' => 'utf8',
            'driverOptions' => array(
                1002 => 'SET NAMES utf8'
            )
        ));
        return $connection;
    }

    /**
     * Función que crea un token para el api
     * @return string
     */
    public function createRestToken()
    {
        $service = $this->get('rest.tocken');
        return $service->createRestToken();
    }

    public function getContainer(){
        return $this->container;
    }
}
