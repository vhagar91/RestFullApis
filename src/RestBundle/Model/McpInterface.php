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

Interface McpInterface
{
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
    public function getTableById($table, $field, $value_field, $operation);

    /**
     * Para obtener los datos de una casa
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getAccommodation($request, $code);

    /**
     * Editar datos de contacto de una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function putContactaccommodation($request, $code);

    /**
     * Enviar solicitud de cambio para una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return void
     */
    public function postSolicitude($request, $code);

    /**
     * Adicionar la No Disponibilidad de una habitación
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function postAddavailableroom($request, $code);

    /**
     * Eliminar, la No Disponibilidad de una habitación.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return void
     */
    public function deleteDelavailableroom($request, $code);

    /**
     * Consultar, la No Disponibilidad de una habitación.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return void
     */
    public function getAvailable($request, $code);

    /**
     * Obtener listado de reservas de una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getReservationaccommodation($request, $code);

    public function getReservation($request, $code);

    /**
     * Obtener detalles de una reserva.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code identificador de la reserva
     * @return array
     */
    public function getDetailsreservation($request, $code);

    /**
     * Obtener listado de clientes de una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getListclientaccommodation($request, $code);

    /**
     * Buscar clientes de una propiedad
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getClientaccommodation($request, $code);

    /**
     * Obtener listado de reservas de un cliente en una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getReservationclient($request, $code);

    /**
     * Obtener listado de comentarios de un cliente sobre una propiedad.
     * @param $request todos los filtros que se pasan como párametros
     * @param código $code código de la propiedad
     * @return array
     */
    public function getCommentclient($request, $code);

    /**
     * Con este servicio se pueden adicionar los datos de un nuevo usuario al CBS.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function postRegisteruser($request);

    /**
     * Con este servicio se puede asignar un rol a un usuario determinado.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function postAddroluser($request);

    /**
     * Con este servicio se puede asignar un rol a un usuario determinado.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function putEnableduser($request);

    /**
     * Con este servicio se pueden obtener los datos registrados de un usuario.
     * @param $request todos los filtros que se pasan como párametros
     * @return array
     */
    public function getUser($request);
}