<?php
/*
 * This file is part of the API-REST CBS v1.0.0.0 alfa.
 *
 * (c) Development team HDS <correo>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace RestBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;

use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RestBundle\Exception\InvalidFormException;
use Symfony\Component\HttpFoundation\Response;

class McpAppController extends RestController
{
    /*Test apiKey: api-0eb3a9ae99d076d7c26a2f3bf71fcf858 */

    /**
     * Con este servicio se puede autenticar a los usuarios de MyCasaParticular. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user", "dataType"="string", "required"=true, "description"="User/Código de la casa"},
     *      {"name"="password", "dataType"="string", "required"=true, "description"="Password"},
     *      {"name"="start", "dataType"="string", "required"=false, "description"="Fecha de inicio"}
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postLoginMcpAppAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->login($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"McpApp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     *
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getDestinationsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getDestinations();
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"McpApp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="language",nullable=true, description="Lenguage ejemp: ES")
     * @Annotations\QueryParam(name="start",nullable=true, description="Inicio del paginado")
     * @Annotations\QueryParam(name="user_id",nullable=true, description="id del usuario")
     * @Annotations\QueryParam(name="currency",nullable=true, description="currency del usuario")
     * @Annotations\QueryParam(name="top",nullable=true, description="1 si se quiere obtener solo las del top")
     * @Annotations\QueryParam(name="destination_id",nullable=true, description="destino como parte del filtro")
     * @Annotations\QueryParam(name="guests",nullable=true, description="cantidad de personas")
     * @Annotations\QueryParam(name="rooms",nullable=true, description="cantidad de habitaciones")
     * @Annotations\QueryParam(name="from",nullable=false, description="Fecha de inicio (Y-m-d).")
     * @Annotations\QueryParam(name="to",nullable=false, description="Fecha de fin (Y-m-d).")
     * @Annotations\QueryParam(name="favorite",nullable=true, description="1 si se quiere obtener solo las favoritas")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getAccomodationsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);

                $top     = ( $request->query->get( 'top' ) != '' && $request->query->get( 'top' ) == '1') ? true : false;
                if($top){
                    return $instance->getAccomodationsTop($request);
                }
                else{
                    return $instance->getAccomodations($request);
                }
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"McpApp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="language",nullable=true, description="Lenguage ejemp: ES")
     * @Annotations\QueryParam(name="user_id",nullable=true, description="id del usuario")
     * @Annotations\QueryParam(name="currency",nullable=true, description="currency del usuario")
     * @Annotations\QueryParam(name="own_id",nullable=true, description="id la casa")
     *
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getAccomodationAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getAccomodation($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Con este servicio se puede modificar el favorito Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="string", "required"=true, "description"="User/Código de la casa"},
     *      {"name"="token", "dataType"="string", "required"=true, "description"="Token de seguridad"},
     *      {"name"="ownership_id", "dataType"="string", "required"=false, "description"="Identificador de la casa"},
     *      {"name"="action", "dataType"="string", "required"=false, "description"="Accion adicionar a favorito = 1, eliminar = 0"}
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postFavoriteAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->favorite($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"McpApp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="currency",nullable=true, description="currency del usuario")
     * @Annotations\QueryParam(name="own_id",nullable=true, description="id la casa")
     * @Annotations\QueryParam(name="from",nullable=false, description="Fecha de inicio (Y-m-d).")
     * @Annotations\QueryParam(name="to",nullable=false, description="Fecha de fin (Y-m-d).")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getAvailableRoomsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $instance->setContainer($this->getContainer());
                return $instance->getAvailableRooms($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Con este servicio se puede autenticar a los usuarios de MyCasaParticular. Estado(100%).
     *
     * //TO YANETMORALESR: Aqui se declara el servicio, adiciona todos los parametros q tu quieras y en la descripcion me explicas, recuerda que el ultimo parametro no lleba coma al final despues del cierre de llabe.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="string", "required"=false, "description"="Codigo del usuario si este esta autenticado"},
     *      {"name"="session_id", "dataType"="string", "required"=false, "description"="Codigo de la sesion si el usuario no esta autenticado"},
     *      {"name"="token", "dataType"="string", "required"=true, "description"="Token de seguridad"},
     *      {"name"="check_dispo", "dataType"="string", "required"=true, "description"="Esto indica si la casa es de reserva inmediata o no. Si es de reserva inmediata se reserva directamente"},
     *      {"name"="from_date", "dataType"="string", "required"=false, "description"="Fecha de llegada"},
     *      {"name"="to_date", "dataType"="string", "required"=false, "description"="Fecha de salida"},
     *      {"name"="ids_rooms", "dataType"="string", "required"=false, "description"="Aqui van los ids de las habitaciones seleccionadas, separados por &"},
     *      {"name"="adults", "dataType"="string", "required"=false, "description"="Cantidad de adultos"},
     *      {"name"="kids", "dataType"="string", "required"=false, "description"="Cantidad de niños"},
     *      {"name"="hasCompleteReservation", "dataType"="string", "required"=false, "description"="Esto indica si la casa tiene reserva completa o no"},
     *      {"name"="kidsAge_1", "dataType"="string", "required"=false, "description"="Edad del primer niño"},
     *      {"name"="kidsAge_2", "dataType"="string", "required"=false, "description"="Edad del segundo niño"},
     *      {"name"="kidsAge_3", "dataType"="string", "required"=false, "description"="Edad del tercer niño"}
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postAddToCartAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->addToCart($request);  //TO YANETMORALESR: este es el metodo q se llama en el modelo pasandole el $request completo.
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Servicio para obtener el listado de reservas Disponibles de un turista. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="string", "required"=false, "description"="Codigo del usuario si este esta autenticado"},
     *      {"name"="token", "dataType"="string", "required"=true, "description"="Token de seguridad"},
     *      {"name"="date", "dataType"="string", "required"=false, "description"="Fecha a partir de la cual se van a mostrar las reservas"},
     *      {"name"="currency", "dataType"="string", "required"=false, "description"="currency del usuario"},
     *      {"name"="count", "dataType"="string", "required"=false, "description"="si solo quiere obtener la cantidad"}
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function getTouristReservationListAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $instance->setContainer($this->getContainer());
                return $instance->touristReservationList($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }
    /**
     * Servicio para obtener el listado de reservas Pagadas de un turista. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="string", "required"=false, "description"="Codigo del usuario si este esta autenticado"},
     *      {"name"="status", "dataType"="string", "required"=true, "description"="Estado de las reservaciones"},
     *      {"name"="token", "dataType"="string", "required"=true, "description"="Token de seguridad"},
     *      {"name"="date", "dataType"="string", "required"=false, "description"="Fecha a partir de la cual se van a mostrar las reservas"},
     *      {"name"="currency", "dataType"="string", "required"=false, "description"="currency del usuario"},
     *      {"name"="count", "dataType"="string", "required"=false, "description"="si solo quiere obtener la cantidad"}
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function getTouristReservationListBookedAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $instance->setContainer($this->getContainer());
                return $instance->touristReservationListBooked($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Con este servicio se registraun nuevo usuario. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="name", "dataType"="string", "required"=false, "description"="Nombre del usuario"},
     *      {"name"="last_name", "dataType"="string", "required"=false, "description"="Apellidos del usuario"},
     *      {"name"="password", "dataType"="string", "required"=false, "description"="Contraseña"},
     *      {"name"="email", "dataType"="string", "required"=true, "description"="Correo del usuario"},
     *      {"name"="country_id", "dataType"="string", "required"=true, "description"="Id del país del usuario"},
     *      {"name"="currency_id", "dataType"="string", "required"=true, "description"="Id de la moneda seleccionada"},
     *      {"name"="language_id", "dataType"="string", "required"=true, "description"="Id del idioma seleccionado"}
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postRegisterUserAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->registerUser($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Con este servicio se registraun nuevo usuario. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="token", "dataType"="string", "required"=true, "description"="Token de seguridad"},
     *      {"name"="reservations_list", "dataType"="string", "required"=true, "description"="Lista de ids de los ownershipreservations separados por comas"},
     *      {"name"="user_id", "dataType"="string", "required"=true, "description"="Id del usuario"},
     *      {"name"="user_name", "dataType"="string", "required"=true, "description"="Nombre del usuario"},
     *      {"name"="user_last_name", "dataType"="string", "required"=true, "description"="Apellidos del usuario"},
     *      {"name"="user_email", "dataType"="string", "required"=true, "description"="Correo del usuario"},
     *      {"name"="currency_id", "dataType"="string", "required"=true, "description"="Moneda en que se realizará el pago"},
     *      {"name"="currency", "dataType"="string", "required"=false, "description"="currency del usuario"},
     *      {"name"="amount", "dataType"="string", "required"=false, "description"="amount del usuario"},
     *      {"name"="return_url", "dataType"="string", "required"=true, "description"="Url de retorno a MyCasa cuando el pago es exitoso. Hay que ver que se pone aqui para la app"},
     *      {"name"="return_url_text", "dataType"="string", "required"=true, "description"="Este texto lo usa Skrill."},
     *      {"name"="cancel_url", "dataType"="string", "required"=true, "description"="Url de retorno a MyCasa para cancelar el pago. Hay que ver que se pone aqui para la app"},
     *      {"name"="status_url", "dataType"="string", "required"=true, "description"="Url de MyCasa para ejecutar procesos despues del pago exitoso. Hay que ver que se pone aqui para la app"},
     *      {"name"="language_code", "dataType"="string", "required"=true, "description"="Codigo del lenguaje. Ejemplo para español es ES"},
     *      {"name"="confirmation_note", "dataType"="string", "required"=true, "description"="Mensaje que Skrill le muestra al usuario luego de haber realizado un pago exitoso"},
     *      {"name"="skrill_submit_button", "dataType"="string", "required"=true, "description"="Texto que Skrill pone en el botón de pago"},
     *      {"name"="user_address", "dataType"="string", "required"=true, "description"="Dirección postal del usuario"},
     *      {"name"="user_zip_code", "dataType"="string", "required"=false, "description"="Código postal del usuario"},
     *      {"name"="user_city", "dataType"="string", "required"=false, "description"="Ciudad del usuario"},
     *      {"name"="user_country_code", "dataType"="string", "required"=true, "description"="Código del país del usuario"}
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postPayReservationsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->payReservations($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"McpApp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     *
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getCountiesAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getCounties();
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"McpApp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     *
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getCurrenciesAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getCurrencies();
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Con este servicio se puede modificar el favorito Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="string", "required"=true, "description"="User/Código de la casa"},
     *      {"name"="token", "dataType"="string", "required"=true, "description"="Token de seguridad"},
     *      {"name"="currency_id", "dataType"="string", "required"=true, "description"="Moneda en que se realizará el pago"}
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postChangeCurrencyAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->changeCurrency($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Con este servicio se puede modificar el favorito Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="string", "required"=true, "description"="User/Código de la casa"},
     *      {"name"="token", "dataType"="string", "required"=true, "description"="Token de seguridad"},
     *      {"name"="password", "dataType"="string", "required"=false, "description"="Contraseña"}
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postChangePassAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->changePass($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"McpApp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     *
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getLanguagesAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getLanguages();
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Con este servicio se puede modificar el favorito Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="string", "required"=true, "description"="User/Código de la casa"},
     *      {"name"="token", "dataType"="string", "required"=true, "description"="Token de seguridad"},
     *      {"name"="language_id", "dataType"="string", "required"=true, "description"="Language"}
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postChangeLanguageAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->changeLanguage($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }
    /**
     * Con este servicio se puede modificar el perfil de usuario(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
    parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="string", "required"=true, "description"="User/Código de la casa"},
     *      {"name"="phone", "dataType"="string", "required"=false, "description"="Telefono del usuario"},
     *      {"name"="email", "dataType"="string", "required"=true, "description"="Correo del usuario"},
     *      {"name"="country_code", "dataType"="string", "required"=true, "description"="Id del país del usuario"},
     *     {"name"="city", "dataType"="string", "required"=false, "description"="Ciudad"}
     *
     *
     *  },
     *   views = {"McpApp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postProfileUserAction(Request $request) {

        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->SaveProfileUser($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }
}