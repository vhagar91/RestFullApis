<?php

namespace NomenclatorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
class BaseController extends Controller
{
    /**
     *
     */
    public function getConnection(){
        $connectionFactory = $this->container->get('doctrine.dbal.connection_factory');
        $connection = $connectionFactory->createConnection(array(
            'driver' => 'pdo_mysql',
            'user' => 'root',
            'password' => 'root',
            'host' => '127.0.0.1',
            'dbname' => 'sabrus_dev',
            'charset' => 'utf8',
            'driverOptions' => array(
                1002 => 'SET NAMES utf8'
            )
        ));
        return $connection;
    }
    /**
     * Función que devuelve los datos de una tabla filtrando por id
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
    public function getTableById($table, $field, $value_field, $operation,$conn)
    {
        $query = "SELECT * FROM " . $table . " WHERE " . $field . "=:code; ";
        $stmt = $conn->prepare($query);
        $stmt->bindValue('code', $value_field);
        $stmt->execute();
        if ($operation == 'fetchAll')
            $po = $stmt->fetchAll();
        if ($operation == 'fetch')
            $po = $stmt->fetch();
        if ($operation == 'rowCount')
            $po = $stmt->rowCount();
        return $po;
    }
    /**
     * Función que devuelve los datos de una tabla
     * @param $table        Tabla que se quiere consultar
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
    public function getTable($table, $operation,$conn)
    {
        $query = "SELECT * FROM " . $table;
        $stmt = $conn->prepare($query);
        $stmt->execute();
        if ($operation == 'fetchAll')
            $po = $stmt->fetchAll();
        if ($operation == 'fetch')
            $po = $stmt->fetch();
        if ($operation == 'rowCount')
            $po = $stmt->rowCount();
        return $po;
    }
}
