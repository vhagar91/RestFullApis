<?php


namespace RestBundle\Model;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;

class Base
{
    protected $conn;                  //variable que contiene la conexión
    protected $view;                  //variable que contiene la vista

    public function __construct($conn, $view)
    {
        $this->conn = $conn;
        $this->view = $view;
    }

    /**
     * @param $plainpassword
     * @param string $salt
     * @param string $algoritmhash
     * @return string
     */
    public function encryptPassword($plainpassword,$salt='222',$algoritmhash='sha512'){
        $salted= $plainpassword.'{'.$salt.'}';
        $digest = hash($algoritmhash, $salted, true);
        // "stretch" hash
        for ($i = 1; $i < 5000; $i++) {
            $digest = hash($algoritmhash, $digest.$salted, true);
        }
        return base64_encode($digest);
    }

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
    public function getTableById($table, $field, $value_field, $operation)
    {
        $query = "SELECT * FROM " . $table . " WHERE " . $field . "=:code; ";
        $stmt = $this->conn->prepare($query);
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

    public function resize($file_full_path, $new_height) {
        $imagine = new Imagine();
        $image = $imagine->open($file_full_path);
        $size = $image->getSize();
        $new_width = ($size->getWidth() * $new_height) / $size->getHeight();

        $image->resize(new Box($new_width, $new_height))
            ->save($file_full_path, array('format' => 'jpeg','quality' => 100));

        return $new_width;
    }

    public function createDirectoryIfNotExist($dirName)
    {
        if (!is_dir($dirName)) {
            mkdir($dirName, 0755, true);
        }
    }
}