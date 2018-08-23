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

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RestBundle\Exception\InvalidFormException;
use Symfony\Component\HttpFoundation\Response;

class McprestController extends RestController {
    /**
     * Obtener datos de una propiedad. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="code_own",default="true",nullable=true, description="Código de la casa.")
     * @Annotations\QueryParam(name="name",default="true",nullable=true, description="Nombre de la porpiedad.")
     * @Annotations\QueryParam(name="address_street", default="true",nullable=true, description="Dirección de la calle.")
     * @Annotations\QueryParam(name="address_number",  default="true",nullable=true, description="Número de la casa.")
     * @Annotations\QueryParam(name="address_between_street_1", default="true",nullable=true, description="Entre calle 1.")
     * @Annotations\QueryParam(name="address_between_street_2",default="true",nullable=true, description="Entre calle 2.")
     * @Annotations\QueryParam(name="address_province",default="true",nullable=true, description="Provincia.")
     * @Annotations\QueryParam(name="address_municipality",default="true",nullable=true, description="Municipio.")
     * @Annotations\QueryParam(name="mobile_number",default="true",nullable=true, description="Número de movil.")
     * @Annotations\QueryParam(name="phone_number",default="true",nullable=true, description="Número de teléfono.")
     * @Annotations\QueryParam(name="email_1",default="true",nullable=true, description="Email principal.")
     * @Annotations\QueryParam(name="email_2",default="true",nullable=true, description="Email secundario.")
     * @Annotations\QueryParam(name="list_room",default="true",nullable=true, description="Listado de Habitaciones.")
     * @Annotations\QueryParam(name="price_up_to",default="true",nullable=true, description="Precio (temporada alta).")
     * @Annotations\QueryParam(name="price_down_to",default="true",nullable=true, description="Precio (temporada baja).")
     * @Annotations\QueryParam(name="climate",default="true",nullable=true, description="Climatización.")
     * @Annotations\QueryParam(name="audiovisual",default="true",nullable=true, description="Audiovisuales.")
     * @Annotations\QueryParam(name="smoker",default="true",nullable=true, description="Fumador.")
     * @Annotations\QueryParam(name="safe",default="true",nullable=true, description="Caja fuerte.")
     * @Annotations\QueryParam(name="baby_facility",default="true",nullable=true, description="Facilidades para bebé.")
     * @Annotations\QueryParam(name="bathroom_type",default="true",nullable=true, description="Tipo de baño.")
     * @Annotations\QueryParam(name="stereo",default="true",nullable=true, description="Estéreo.")
     * @Annotations\QueryParam(name="windows",default="true",nullable=true, description="Ventana.")
     * @Annotations\QueryParam(name="balcony",default="true",nullable=true, description="Balcón.")
     * @Annotations\QueryParam(name="terrace",default="true",nullable=true, description="Terraza.")
     * @Annotations\QueryParam(name="yard",default="true",nullable=true, description="Patio.")
     * @Annotations\QueryParam(name="user_casa",default="null",nullable=true, description="Identificador del usuario.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @param string $code Código de la propiedad
     *
     * @return array
     */
    public function getAccommodationAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                if($code == '{code}' && $request->query->get('user_casa') == 'null')
                    throw new InvalidFormException('The accommodation code is required field');
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getAccommodation($request, $code), 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Registrar propiedad . Estado(90%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mcp", "McpAvailable"}
     * )
     * @Annotations\RequestParam(name="key",nullable=false, description="Llave del api.")
     * @Annotations\RequestParam(name="accommodation", nullable=false, description="Casa" )
     * @Annotations\RequestParam(name="rooms", nullable=true, description="Arreglo de habitaciones de la propiedad." )
     * @Annotations\RequestParam(name="user", nullable=true, description="Usuario." )
     * @Annotations\RequestParam(name="pass", nullable=true, description="Contraseña del usuario." )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postOwnershipCreateAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $accommodation = json_decode($request->request->get('accommodation'));
                $accommodation->rooms = json_decode($request->request->get('rooms'));
                $user = $request->request->get('user');
                $pass = $request->request->get('pass');

                /**/
                /*$pathToCont = "xxxxxxx.txt";
                $file = fopen($pathToCont, "a");
                fwrite($file, '----------------' . PHP_EOL);
                fwrite($file, 'api-key: ' . $request->request->get('key')   . PHP_EOL);
                fwrite($file, 'usuario: ' . $user  . ' pass:' . $pass . PHP_EOL);
                fwrite($file, 'accommodation: ' . $request->request->get('accommodation')  . PHP_EOL);
                fwrite($file, 'rooms: ' . $request->request->get('rooms')  . PHP_EOL);
                fwrite($file, '--------------' . PHP_EOL);
                fclose($file);*/
                /**/

                return $this->view($instance->createOwnership($accommodation, $user, $pass), Response::HTTP_OK);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Registrar propietario . Estado(20%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\RequestParam(name="key", requirements="integer",nullable=false, description="Llave del api.")
     * @Annotations\RequestParam(name="name",nullable=false, description="Nombre y Apellidos")
     * @Annotations\RequestParam(name="email", nullable=false, requirements="email", description="Email.")
     * @Annotations\RequestParam(name="phone",nullable=true, requirements="\d+", description="Teléfono")
     * @Annotations\RequestParam(name="own_name",nullable=false, description="Nombre de la casa.")
     * @Annotations\RequestParam(name="license",nullable=false, description="No. de la licencia.")
     * @Annotations\RequestParam(name="province",nullable=false, requirements="\d+", description="Provincia.")
     * @Annotations\RequestParam(name="municipality",nullable=false, requirements="\d+", description="Municipio.")
     * @Annotations\RequestParam(name="rooms", nullable=true, array=true, description="Arreglo de habitaciones de la propiedad." )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     *
     *
     * @return array
     */
    public function postOwnershipRegisterownerAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->registerOwner($request), 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Registrar habitacion en una propiedad . Estado(90%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\RequestParam(name="key", nullable=false, description="Llave del api.")
     * @Annotations\RequestParam(name="roomNumber",nullable=false, description="Número de la habitación")
     * @Annotations\RequestParam(name="roomType", nullable=false, requirements="{Habitación individual| Habitación Doble| Habitación Triple}", description="Tipo de habitación")
     * @Annotations\RequestParam(name="status",nullable=true, requirements="boolean", default="0", description="Activa?.")
     * @Annotations\RequestParam(name="beds",nullable=false, requirements="integer", description="Cantidad de camas.")
     * @Annotations\RequestParam(name="priceHighFrom",nullable=false, requirements="float", description="Precio Temp. Alta. Min.")
     * @Annotations\RequestParam(name="priceHighTo",nullable=false, requirements="float", description="Precio Temp. Alta. Max.")
     * @Annotations\RequestParam(name="priceLowFrom",nullable=false, requirements="float", description="Precio Temp. Baja Min.")
     * @Annotations\RequestParam(name="priceLowTo",nullable=false, requirements="float", description="Precio Temp. Baja Max.")
     * @Annotations\RequestParam(name="priceSpecial",nullable=true, requirements="float", description="Precio Temp. Especial.")
     * @Annotations\RequestParam(name="climate",nullable=false, description="Climatización de la habitación.")
     * @Annotations\RequestParam(name="audiovisual",nullable=false, description="Audiovisuales de la habitación.")
     * @Annotations\RequestParam(name="smokers",nullable=true, requirements="boolean", description="Permite fumadores.")
     * @Annotations\RequestParam(name="safeBox",nullable=true, requirements="boolean", description="Tiene caja fuerte.")
     * @Annotations\RequestParam(name="baby",nullable=true, requirements="boolean", description="Facilidades para bebés.")
     * @Annotations\RequestParam(name="bathroomType",nullable=false, description="Tipo de baño")
     * @Annotations\RequestParam(name="stereo",nullable=true, requirements="boolean", default="0", description="Tipo de baño")
     * @Annotations\RequestParam(name="windows",nullable=false, requirements="integer" ,description="No. de ventanas.")
     * @Annotations\RequestParam(name="balcony",nullable=false, requirements="integer", description="No. de balcones.")
     * @Annotations\RequestParam(name="terrace",nullable=true, requirements="boolean", default="0", description="Tiene terraza.")
     * @Annotations\RequestParam(name="yard",nullable=true, requirements="boolean", default="0" ,description="Tiene patio.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     *
     *
     * @return array
     */
    public function postOwnershipAddroomAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->addRoomToOwnership($request, $code), 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Editar datos de una propiedad. Estado(90%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\RequestParam(name="key", nullable=false, description="Llave del api.")
     * @Annotations\RequestParam(name="owner2",nullable=true, description="Propietario2")
     * @Annotations\RequestParam(name="email_1", nullable=true, requirements="email", description="Email principal.")
     * @Annotations\RequestParam(name="email_2",nullable=true, requirements="email", description="Email secundario.")
     * @Annotations\RequestParam(name="mobile",nullable=true, requirements="\d+", description="Celular.")
     * @Annotations\RequestParam(name="phone",nullable=true, requirements="\d+", description="Teléfono fijo.")
     * @Annotations\RequestParam(name="address",nullable=false, description="Dirección.")
     * @Annotations\RequestParam(name="photo",nullable=true, description="Foto.")
     * @Annotations\RequestParam(name="languages",nullable=true, description="Idiomas en la casa.")
     * @Annotations\RequestParam(name="category",nullable=false, requirements="{Económica|Rango Medio|Premium}" , description="Categoría.")
     * @Annotations\RequestParam(name="rent_type",nullable=false, requirements="{Casa completa| Habitaciones}" , description="Tipo de renta.")
     * @Annotations\RequestParam(name="own_type",nullable=false, requirements="{Casa Particular|Apartamento|Penthouse|Villa|Estudio|Chalet}" , description="Tipo de propiedad.")
     * @Annotations\RequestParam(name="breakfast",nullable=true, default="0", requirements="boolean", description="Desayuno incluido.")
     * @Annotations\RequestParam(name="breakfastPrice",nullable=true,  description="Precio Desayuno.")
     * @Annotations\RequestParam(name="dinner",nullable=true, default="0", requirements="boolean", description="Cena incluida.")
     * @Annotations\RequestParam(name="dinnerMinPrice",nullable=true, description="Cena precio mínimo.")
     * @Annotations\RequestParam(name="dinnerMaxPrice",nullable=true, description="Cena precio máximo.")
     * @Annotations\RequestParam(name="parking",nullable=true, default="0", requirements="boolean", description="Parqueo incluido.")
     * @Annotations\RequestParam(name="parkingPrice",nullable=true, description="Precio Parqueo.")
     * @Annotations\RequestParam(name="parkingCycle",nullable=true, default="0", requirements="boolean", description="Parqueo ciclos incluido.")
     * @Annotations\RequestParam(name="parkingCyclePrice",nullable=true, description="Precio Parqueo Ciclos.")
     * @Annotations\RequestParam(name="pets",nullable=true, default="0", requirements="boolean", description="Mascotas permitidas.")
     * @Annotations\RequestParam(name="laundry",nullable=true, default="0", requirements="boolean", description="Lavandería.")
     * @Annotations\RequestParam(name="emailService",nullable=true, default="0", requirements="boolean", description="Servicio de Email.")
     * @Annotations\RequestParam(name="internetService",nullable=true, description="Servicio de Internet.")
     * @Annotations\RequestParam(name="otherServices",nullable=true, default="0", requirements="string", description="Otras facilidades.")
     * @Annotations\RequestParam(name="jacuzzi",nullable=true, default="0", requirements="boolean", description="Jacuzzi.")
     * @Annotations\RequestParam(name="sauna",nullable=true,default="0", requirements="boolean", description="Sauna.")
     * @Annotations\RequestParam(name="pool",nullable=true, default="0", requirements="boolean", description="Piscina.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     *
     *
     * @return array
     */
    public function postOwnershipEditAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->editOwnership($request, $code), 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Eliminar una propiedad. Estado(90%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\RequestParam(name="key", nullable=false, description="Llave del api.")
     * @Annotations\RequestParam(name="password", nullable=false, description="Contraseña de la casa.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     *
     *
     * @return array
     */
    public function putOwnershipDeleteAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->deleteOwnership($request, $code), 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Cambiar estado de una propiedad. Estado(90%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\RequestParam(name="key", nullable=false, description="Llave del api.")
     * @Annotations\RequestParam(name="status", nullable=false, requirements="integer", description="Estado de la casa.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     *
     *
     * @return array
     */
    public function putOwnershipSetstatusAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->setOwnershipStatus($request, $code), 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Cambiar estado de una habitación de una propiedad. Estado(90%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\RequestParam(name="key", nullable=false, description="Llave del api.")
     * @Annotations\RequestParam(name="roomNumber", nullable=false, requirements="integer", description="Numero de la habitación.")
     * @Annotations\RequestParam(name="status", nullable=false, requirements="boolean", description="Estado de la casa.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     *
     *
     * @return array
     */
    public function putOwnershipRoomSetstatusAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->setOwnershipRoomStatus($request, $code), 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener habitaciones de una propiedad. Estado(90%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mcp"}
     * )
     *
     * @Annotations\RequestParam(name="key", requirements="string",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     *
     *
     * @return array
     */
    public function getOwnershipRoomsAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getOwnershipRooms($request, $code), 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Editar datos de contacto de una propiedad. Estado(100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="mobile",nullable=true, description="Teléfono móvil.")
     * @Annotations\QueryParam(name="phone",nullable=true, description="Teléfono fijo.")
     * @Annotations\QueryParam(name="email_1",nullable=true, description="Email principal.")
     * @Annotations\QueryParam(name="email_2",nullable=true, description="Email secundario.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @param string $code Código de la propiedad
     *
     * @return array
     */
    public function putContactaccommodationAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                if($code == '{code}')
                    throw new InvalidFormException('The accommodation code is required field');
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->putContactaccommodation($request, $code), 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Enviar solicitud de cambio para una propiedad.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   },
     * views = {"Mcp"}
     * )
     *
     * @return array
     */
    public function postSolicitudeAction(Request $request, $code) {

    }

    /**
     * Adicionar, la No Disponibilidad de una habitación por rangos de fechas. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="date_range",nullable=false, description="Rango de fechas formato (Y-m-d).")
     * @Annotations\QueryParam(name="start",nullable=false, description="Fecha de inicio (Y-m-d).")
     * @Annotations\QueryParam(name="end",nullable=false, description="Fecha fin (Y-m-d).")
     * @Annotations\QueryParam(name="reason", nullable=true, description="Motivo de la no disponibilidad.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @param string $code Código de la  habitación
     * @return array
     */
    public function postAddavailableroombyrangeAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                if($code == '{code}')
                    throw new InvalidFormException('The room code is required field');

                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->postAddavailableroombyrange($request, $code);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Adicionar, la No Disponibilidad de una habitación. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="from_date",nullable=false, description="Fecha de inicio.")
     * @Annotations\QueryParam(name="to_date",nullable=false, description="Fecha fin.")
     * @Annotations\QueryParam(name="reason", nullable=true, description="Motivo de la no disponibilidad.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @param string $code Código de la  habitación
     * @return array
     */
    public function postAddavailableroomAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                if($code == '{code}')
                    throw new InvalidFormException('The room code is required field');

                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->postAddavailableroom($request, $code);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Eliminar, la No Disponibilidad de una habitación. Estado (100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="from_date",nullable=false, description="Fecha de inicio.")
     * @Annotations\QueryParam(name="to_date",nullable=false, description="Fecha fin.")
     * @Annotations\QueryParam(name="ud_id", nullable=true, description="Código de la no diponibilidad.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @param string $code Código de la  habitación
     * @return array
     */
    public function deleteDelavailableroomAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                if($code == '{code}')
                    throw new InvalidFormException('The room code is required field');
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->deleteDelavailableroom($request, $code);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Consultar la disponibilidad de una habitación. Estado (100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="from_date",nullable=true, description="Fecha de inicio.")
     * @Annotations\QueryParam(name="to_date",nullable=true, description="Fecha fin.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @param string $code Código de la  habitación
     * @return array
     */
    public function getAvailableAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                if($code == '{code}')
                    throw new InvalidFormException('The room code is required field');
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getAvailable($request, $code);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de reservas de una propiedad. Estado(100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @param string $code Código de la  propiedad
     * @return array
     */
    public function getReservationaccommodationAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $field = array();
                if($code == '{code}')
                    throw new InvalidFormException('The ownership code is required field');
                $field = array();
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getReservationaccommodation($field, $code);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Buscar reservas de una propiedad.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   },
     * views = {"Mcp"}
     * )
     *
     * @return array
     */
    public function getReservationActionAction(Request $request, $code) {
    }

    /**
     * Obtener detalles de una reserva. Estado (100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @param string $code Identificador de la  reserva
     * @return array
     */
    public function getDetailsreservationAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $field = array();
                if($code == '{code}')
                    throw new InvalidFormException('The reservation code is required field');
                $field = array();
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getDetailsreservation($field, $code);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de clientes de una propiedad.Estado (se repite con otro revisar y unirlos)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *   views = {"Mcp"}
     * )
     *
     * @return array
     */
    public function getListclientaccommodationAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        try {
            $field = array();
            if($code == '{code}')
                throw new InvalidFormException('The ownership code is required field');
            $field = array();
            $instance = $this->loadClass($checkSecurityApi['class']);
            return $instance->getListclientaccommodation($field, $code);
        }
        catch (InvalidFormException $exception) {
            return $this->view($exception->getMessage(), 400);
        }
    }

    /**
     * Buscar clientes de una propiedad. Estado(100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="client_name",nullable=true, description="Nombre del cliente.")
     * @Annotations\QueryParam(name="client_email",nullable=true, description="Email del cliente.")
     * @Annotations\QueryParam(name="client_country",nullable=true, description="País.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @param string $code Código de la  propiedad
     * @return array
     */
    public function getClientaccommodationAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                if($code == '{code}')
                    throw new InvalidFormException('The accommodation code is required field');
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getClientaccommodation($request, $code);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de reservas de un cliente en una propiedad. Estado(100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *   views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="client_name",nullable=true, description="Nombre del cliente.")
     * @Annotations\QueryParam(name="client_email",nullable=true, description="Email del cliente.")
     * @Annotations\QueryParam(name="client_country",nullable=true, description="País.")
     * @Annotations\QueryParam(name="client_id",nullable=true, description="Id del cliente.")
     *
     * @return array
     */
    public function getReservationclientbypropertyAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $field = array();
                if($code == '{code}')
                    throw new InvalidFormException('The ownership code is required field');
                $field = array();
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getReservationclientbyproperty($request, $code);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de comentarios de un cliente sobre una propiedad. Estado(100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *   views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     *
     * @return array
     */
    public function getCommentclientAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $field = array();
                if($code == '{code}')
                    throw new InvalidFormException('The ownership code is required field');
                $field = array();
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getCommentclient($field, $code);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Con este servicio se pueden adicionar los datos de un nuevo usuario al CBS. Estado(80%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="name", "dataType"="string", "required"=true, "description"="Nombre de usuario"},
     *      {"name"="last_name", "dataType"="string", "required"=true, "description"="Apellidos"},
     *      {"name"="email", "dataType"="string", "required"=true, "description"="Correo"},
     *      {"name"="password", "dataType"="string", "required"=true, "description"="Contraseña"},
     *      {"name"="country", "dataType"="string", "required"=false, "description"="País"},
     *      {"name"="enabled", "dataType"="integer", "required"=true, "description"="Estado"}
     *  },
     *   views = {"Mcp"}
     * )
     * @return array
     */
    public function postRegisteruserAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->postRegisteruser($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Con este servicio se puede asignar un rol a un usuario determinado. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="integer", "required"=true, "description"="Código de usuario"},
     *      {"name"="role", "dataType"="string", "required"=true, "description"="Código del rol. Ej: ROLE_CLIENT_TOURIST,ROLE_CLIENT_CASA,ROLE_CLIENT_PARTNER,ROLE_CLIENT_STAFF"}
     *  },
     *   views = {"Mcp"}
     * )
     * @return array
     */
    public function postAddroluserAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->postAddroluser($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Con este servicio se puede actualizar el estado de un usuario (Activado/Desactivado). Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="integer", "required"=true, "description"="Código de usuario"},
     *      {"name"="enabled", "dataType"="integer", "required"=true, "description"="Estado activado/desactivado. Ej: 0-desactivado,1-activado"}
     *  },
     *   views = {"Mcp"}
     * )
     * @return array
     */
    public function putEnableduserAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->putEnableduser($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Con este servicio se pueden obtener los datos registrados de un usuario. Estado(0%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user_id", "dataType"="integer", "required"=true, "description"="Código de usuario"}
     *  },
     *   views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="name",default="true",nullable=true, description="Nombre de usuario.")
     * @Annotations\QueryParam(name="last_name",default="true",nullable=true, description="Apellido del usuario.")
     * @Annotations\QueryParam(name="email",default="true",nullable=true, description="Correo del usuario.")
     * @Annotations\QueryParam(name="password",default="true",nullable=true, description="Contraseña.")
     * @Annotations\QueryParam(name="country",default="true",nullable=true, description="País.")
     * @Annotations\QueryParam(name="enabled",default="true",nullable=true, description="Estado.")
     * @Annotations\QueryParam(name="role",default="true",nullable=true, description="Rol.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @return array
     */
    public function getUserAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getUser($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de reservas dada una fecha de inicio. Estado(100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   views = {"Mcp","McpApp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @param date $date Fecha de inicio
     * @return array
     */
    public function getReservationabydateAction(Request $request, $date) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $field = array();
                if($date == '{date}')
                    throw new InvalidFormException('The date code is required field');
                $instance = $this->loadClass($checkSecurityApi['class']);
                $po = $instance->getReservationabydate($date);
                return $this->view($po, 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener datos de la geolocaclizacion dado el codigo de la casa. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *   views = {"Mcp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @param string $code Código de la propiedad
     *
     * @return array
     */
    public function getLocationbycodeAction(Request $request, $code) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                if($code == '{code}')
                    throw new InvalidFormException('The accommodation code is required field');
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getLocationbycode($request, $code), 200);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de reservas de un cliente. Estado(100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *   views = {"Mcp","McpApp"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="start", requirements="\d+",nullable=false, description="Fecha de inicio.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @param string $userid Identificador del usuario
     * @return array
     */
    public function getReservationclientAction(Request $request, $userid) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $field = array();
                if($userid == '{userid}')
                    throw new InvalidFormException('The ownership code is required field');
                $field = array();
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getReservationclient($request, $userid);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Con este servicio se se obtienen todos los nomencladores. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"}
     *  },
     *   views = {"McpAvailable"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \Exception
     * @throws \RestBundle\Controller\Exception
     */
    public function getListNomencladoresAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getNomencladores();
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Registrar imagenes de una propiedad . Estado(90%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"xxx"}
     * )
     * @Annotations\RequestParam(name="key",nullable=false, description="Llave del api.")
     * @Annotations\RequestParam(name="user", nullable=true, description="Usuario." )
     * @Annotations\RequestParam(name="pass", nullable=true, description="Contraseña del usuario." )
     * @Annotations\RequestParam(name="code", nullable=true, description="Codigo de la casa." )
     * @Annotations\RequestParam(name="images", array=true, nullable=false, description="Foto" )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postImagesAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $code = $request->request->get('code');
                $user = $request->request->get('user');
                $pass = $request->request->get('pass');
                $images_base64 = $request->get('images', array());

                /**/
                /*$pathToCont = "xxxxxxx.txt";
                $file = fopen($pathToCont, "a");
                fwrite($file, '----------------' . PHP_EOL);
                fwrite($file, 'api-key: ' . $request->request->get('key')   . PHP_EOL);
                fwrite($file, 'usuario: ' . $user  . ' pass:' . $pass . PHP_EOL);
                fwrite($file, 'code: ' . $code  . PHP_EOL);
                foreach ($images_base64 as $key=>$image_base64) {
                    $base64     = $image_base64;
                    fwrite($file, 'base64: ' . $base64  . PHP_EOL);
                }
                fwrite($file, '--------------' . PHP_EOL);
                fclose($file);*/
                /**/

                $result = $instance->saveImages($user, $pass, $code, $images_base64, $this->container, $this->getRequest()->getSchemeAndHttpHost());

                return $this->view($result, Response::HTTP_OK);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Con este servicio se verificar un booking_id (para cc). Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="booking_id", "dataType"="string", "required"=true, "description"="BookingId"},
     *  },
     *   views = {"Mcp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function getCheckBookingIdAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);

                $booking_id = $request->query->get('booking_id');
                $r = $instance->checkBookingId($booking_id);

                return ($r != false) ? ($this->view(array("success" => true, "data" => array("create" => $r)), Response::HTTP_OK)) : ($this->view(array("success" => false), Response::HTTP_OK));
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Con este servicio se obtienen los correos de las casas dado un destino (para cc). Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *     {"name"="municipality", "dataType"="string", "required"=true, "description"="Identificador del municipio"}
     *  },
     *   views = {"Mcp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \Exception
     * @throws \RestBundle\Controller\Exception
     */
    public function getListEmailsOfOwnersAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getEmailsOfOwners($request->query->get('municipality'));
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Con este servicio se obtienen las casas en cuba coupon (para cc). Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"}
     *  },
     *   views = {"Mcp"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \Exception
     * @throws \RestBundle\Controller\Exception
     */
    public function getListCubacouponOwnershipsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getCubaCouponOwnerships();
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Con este servicio se puede autenticar a los usuarios. Estado(100%).
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
     *      {"name"="start", "dataType"="string", "required"=false, "description"="Fecha de inicio"},
     *      {"name"="end", "dataType"="string", "required"=false, "description"="Fecha fin"}
     *  },
     *   views = {"Mcp", "McpAvailable"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @return array
     */
    public function postLoginAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->login($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
            catch (Exception $e) {
                return $this->view($e->getMessage(), 400);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /*********************** **************************************/
    /******* Develop to Infopoint *********************************/
    /*********************** **************************************/

    /**
     * Con este servicio se puede autenticar a los usuarios. Estado(100%).
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
     *      {"name"="password", "dataType"="string", "required"=true, "description"="Password"}
     *  },
     *   views = {"Infopoint"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postLoginInfopointAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->loginInfopoint($request);
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
     * Con este servicio se se obtienen las reservas y la no disponibilidad de las casas de un destino. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="des_id", "dataType"="string", "required"=true, "description"="Identificador del destino"}
     *  },
     *   views = {"Infopoint"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \Exception
     * @throws \RestBundle\Controller\Exception
     */
    public function getOwnershipsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->ownerships($request);
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
     * Con este servicio se se obtienen las reservas y la no disponibilidad de las casas de un destino. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
    *       {"name"="user", "dataType"="string", "required"=true, "description"="User/Código de la casa"},
     *      {"name"="password", "dataType"="string", "required"=true, "description"="Password"},
     *      {"name"="own_id", "dataType"="string", "required"=true, "description"="Identificador del destino"},
     *      {"name"="start", "dataType"="string", "required"=false, "description"="Fecha de inicio"},
     *      {"name"="end", "dataType"="string", "required"=false, "description"="Fecha fin"}
     *  },
     *   views = {"Infopoint"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \Exception
     * @throws \RestBundle\Controller\Exception
     */
    public function getRoomsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->rooms($request);
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
     * Aactualizacion de calendario. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Infopoint"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user",nullable=true, description="Usuario staff manager.")
     * @Annotations\QueryParam(name="password",nullable=true, description="Password staff manager.")
     * @Annotations\QueryParam(name="availability",nullable=false, description="Disponibilidad (Disponible cadena contenedora de la disponibilidad)")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postInfopointUpdateCalendarRoomAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->updateCalendarRoom($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Con este servicio se se obtienen todos los datos asociados a un usuario infopoint. Estado(100%).
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
     *      {"name"="des_id", "dataType"="string", "required"=true, "description"="Identificador del destino"},
     *      {"name"="start", "dataType"="string", "required"=false, "description"="Fecha de inicio"},
     *      {"name"="end", "dataType"="string", "required"=false, "description"="Fecha fin"}
     *  },
     *   views = {"Infopoint"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \Exception
     * @throws \RestBundle\Controller\Exception
     */
    public function getInfopointAllAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getAll($request);
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

    /*********************** **************************************/
    /******* Develop to MyCasa renta new **************************/
    /*********************** **************************************/

    /**
     * Con este servicio se puede autenticar a los usuarios de MyCasaRenta.(MyCasa Renta New) Estado(100%).
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
     *      {"name"="start", "dataType"="string", "required"=false, "description"="Fecha de inicio"},
     *      {"name"="end", "dataType"="string", "required"=false, "description"="Fecha fin"}
     *  },
     *   views = {"McpRenta"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postLoginMycasarentaAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->loginMycasarenta($request);
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
     * Aactualizacion de calendario.(MyCasa Renta New) Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user",nullable=true, description="Usuario staff manager.")
     * @Annotations\QueryParam(name="password",nullable=true, description="Password staff manager.")
     * @Annotations\QueryParam(name="availability",nullable=false, description="Disponibilidad (Disponible cadena contenedora de la disponibilidad)")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postUpdateCalendarRoomAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->updateCalendarRoom($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Actualizacion de calendario mediante SMS.(MyCP SMS, MyCasa Renta New) Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="mobile",nullable=false, description="Mobile que envia disponibilidad (eje: +5355555555)")
     * @Annotations\QueryParam(name="availability",nullable=false, description="Disponibilidad (Disponible cadena contenedora de la disponibilidad)")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postUpdateCalendarRoomSmsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->updateCalendarRoomSms($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Adicionar la respuesta de disponibilidad de un propietario.(MyCP SMS, MyCasa Renta New) Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user",nullable=true, description="Usuario staff manager.")
     * @Annotations\QueryParam(name="password",nullable=true, description="Password staff manager.")
     * @Annotations\QueryParam(name="cas",nullable=false, description="Cas de la reserva")
     * @Annotations\QueryParam(name="availability",nullable=false, description="Disponibilidad (Disponible - 1 ó No Disponible - 0)")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postAddResponseQuickBookingAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->addResponseQuickBooking($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Adicionar la respuesta de disponibilidad de un propietario mediante un sms.(MyCP SMS, MyCasa Renta New) Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="mobile",nullable=false, description="Mobile que envia disponibilidad (eje: +5355555555)")
     * @Annotations\QueryParam(name="cas",nullable=false, description="Cas de la reserva")
     * @Annotations\QueryParam(name="availability",nullable=false, description="Disponibilidad (Disponible - 1 ó No Disponible - 0)")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postAddResponseQuickBookingSmsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->addResponseQuickBookingSms($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Actualizar el precio de una determinada habitación que no sea especial.(MyCP SMS, MyCasa Renta New) Estado(100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user",nullable=true, description="Usuario staff manager.")
     * @Annotations\QueryParam(name="password",nullable=true, description="Password staff manager.")
     * @Annotations\QueryParam(name="prices",nullable=true, description="String que contiene precios en el siguiente formato:(idhabi:precioalta-preciobaja)")
     *
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postUpdatePriceRoomAction(Request $request) {

        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->updatePriceRoom($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Actualizacion de los precios de una habitacion mediante SMS.(MyCP SMS, MyCasa Renta New) Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="mobile",nullable=false, description="Mobile que envia disponibilidad (eje: +5355555555)")
     * @Annotations\QueryParam(name="prices",nullable=false, description="Precios")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postUpdatePriceRoomSmsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->updatePriceRoomSms($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Con este servicio se las estadisticas de una propiedad dadu un usuario. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user",nullable=true, description="Usuario staff manager.")
     * @Annotations\QueryParam(name="password",nullable=true, description="Password staff manager.")
     *
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getStatisticsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getStatistics($request);
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
     * Adicionar cancelacion de reserva de un propietario.(MyCP SMS, MyCasa Renta New) Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user",nullable=true, description="Usuario staff manager.")
     * @Annotations\QueryParam(name="password",nullable=true, description="Password staff manager.")
     * @Annotations\QueryParam(name="reservation",nullable=false, description="Id de la reserva")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postAddCancelBookingAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->addCancelBooking($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Adicionar cancelacion de reserva de un propietario mediante un sms.(MyCP SMS, MyCasa Renta New) Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="mobile",nullable=false, description="Mobile que envia disponibilidad (eje: +5355555555)")
     * @Annotations\QueryParam(name="reservation",nullable=false, description="Id de la reserva")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postAddCancelBookingSmsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->addCancelBookingSms($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Con este servicio se obtienen las notificaciones de una propiedad dada. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user",nullable=true, description="Usuario staff manager.")
     * @Annotations\QueryParam(name="password",nullable=true, description="Password staff manager.")
     * @Annotations\QueryParam(name="new_version",nullable=true, description="Si quiere obtener confirmacion de nueva version")
     *
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getNotificationsAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getNotifications($request);
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
     * Con este servicio se requiere el cambio de pass.(MyCasa Renta New) Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="user", "dataType"="string", "required"=true, "description"="User/Código de la casa"}
     *  },
     *   views = {"McpRenta"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postRequestChangePassAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->requestChangePass($request, $this);
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
     * Con este servicio se cambia la contraseña.(MyCasa Renta New) Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *  parameters={
     *      {"name"="key", "dataType"="string", "required"=true, "description"="Llave del api"},
     *      {"name"="code", "dataType"="string", "required"=true, "description"="Código de seguridad"},
     *      {"name"="password", "dataType"="string", "required"=true, "description"="Password"}
     *  },
     *   views = {"McpRenta"}
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
     * Con este servicio se obtienen los mesages de una propiedad dada. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"Mcp", "McpRenta"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user",nullable=true, description="Usuario staff manager.")
     * @Annotations\QueryParam(name="password",nullable=true, description="Password staff manager.")
     * @Annotations\QueryParam(name="start",nullable=true, description="Fecha de inicio.")
     * @Annotations\QueryParam(name="end",nullable=true, description="Fecha fin.")
     * @Annotations\QueryParam(name="find_all",nullable=true, description="Todos o solo los no sinc.")
     *
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getMessagesAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->getMessages($request);
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
     * Con este servicio se adiciona un message.(MyCasa Renta New) Estado(100%).
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
     *      {"name"="id_ownres", "dataType"="string", "required"=true, "description"="Identificador de la reserva"},
     *      {"name"="message", "dataType"="string", "required"=true, "description"="Message"}
     *  },
     *   views = {"McpRenta"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postAddMessageAction(Request $request) {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->addMessage($request);
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