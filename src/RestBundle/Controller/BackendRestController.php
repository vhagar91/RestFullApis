<?php


namespace RestBundle\Controller;


use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RestBundle\Exception\InvalidFormException;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotNull;

class BackendRestController extends RestController
{
    /**
     * Obtener listado de reservaciones de MyCasa. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener listado de reservaciones de MyCasa. Estado(100%).",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="start",default="0",nullable=true, description="Inicio.")
     * @Annotations\QueryParam(name="limit",default="20", nullable=true, description="Elementos por página.")
     * @Annotations\QueryParam(name="filter_date_reserve", nullable=true, description="Fecha reserva")
     * @Annotations\QueryParam(name="filter_offer_number", nullable=true, description="Número de oferta")
     * @Annotations\QueryParam(name="filter_reference", nullable=true, description="Referencia")
     * @Annotations\QueryParam(name="filter_date_from", nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to", nullable=true, description="Fecha hasta")
     * @Annotations\QueryParam(name="filter_booking_number", nullable=true, description="Número de booking")
     * @Annotations\QueryParam(name="filter_status", nullable=true, description="Estado")
     * @Annotations\QueryParam(name="filter_destination", nullable=true, description="Destino")
     * @Annotations\QueryParam(name="filter_user_name", nullable=true, description="Nombre turista")
     * @Annotations\QueryParam(name="filter_user_lastname", nullable=true, description="Apellido turista")
     * @Annotations\QueryParam(name="filter_id_reservation", nullable=true, description="Id reserva")
    * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @param string $code Código de la propiedad
     *
     * @return array
     */
    public function getReservationsListAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->listReservations($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de clientes con reservaciones de MyCasa. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener listado de clientes con reservaciones de MyCasa. Estado(100%).",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="start",default="0",nullable=true, description="Inicio.")
     * @Annotations\QueryParam(name="limit",default="20", nullable=true, description="Elementos por página.")
     * @Annotations\QueryParam(name="filter_user_name", nullable=true, description="Nombre del turista")
     * @Annotations\QueryParam(name="filter_user_email", nullable=true, description="Email del turista")
     * @Annotations\QueryParam(name="filter_user_city", nullable=true, description="Ciudad del turista")
     * @Annotations\QueryParam(name="filter_user_country", nullable=true, description="País del turista")
     * @Annotations\QueryParam(name="sort_by", nullable=true, description="Ordenar por")
     * @Annotations\QueryParam(name="own_id", nullable=true, description="Id casa")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @param string $code Código de la propiedad
     *
     * @return array
     */
    public function getReservationsClientsListAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->listClients($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }
    /**
     * Obtener listado de bookings de MyCasa. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener listado de bookings de MyCasa. Estado(100%).",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="start",default="0",nullable=true, description="Inicio.")
     * @Annotations\QueryParam(name="limit",default="20", nullable=true, description="Elementos por página.")
     * @Annotations\QueryParam(name="filter_date_booking", nullable=true, description="Fecha del booking")
     * @Annotations\QueryParam(name="filter_arrive_date_booking", nullable=true, description="Fecha de llegada")
     * @Annotations\QueryParam(name="filter_reservation", nullable=true, description="Cod reserva")
     * @Annotations\QueryParam(name="filter_ownership", nullable=true, description="Cod casa")
     * @Annotations\QueryParam(name="filter_booking_number", nullable=true, description="Numero booking")
     * @Annotations\QueryParam(name="filter_user_booking", nullable=true, description="Cliente turista")
     * @Annotations\QueryParam(name="filter_currency", nullable=true, description="Moneda")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @param string $code Código de la propiedad
     *
     * @return array
     */
    public function getBookingsListAction(Request $request)  {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->listBookings($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

       /**
        * Obtener listado de check-ins de MyCasa. Estado(100%).
        *
        * @ApiDoc(
                *  resource=true,
        *  description="Obtener listado de check-ins de MyCasa. Estado(100%).",
        *  statusCodes = {
        *     200 = "Returned when successful"
        *   },
        *  views = { "MyCpBackend" }
        * )
        * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
        * @Annotations\QueryParam(name="start",default="0",nullable=true, description="Inicio.")
        * @Annotations\QueryParam(name="limit",default="20", nullable=true, description="Elementos por página.")
        * @Annotations\QueryParam(name="filter_checkin_date", nullable=true, description="Fecha del check-in")
        * @Annotations\QueryParam(name="order_by", nullable=true, description="Order by")
        * @Annotations\View(
        *  templateVar="pages"
        * )
        *
        * @param Request $request the request object
        * @param string $code Código de la propiedad
        *
        * @return array
        */
    public function getCkeckinsListAction(Request $request)
     {
    $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
    if ($checkSecurityApi['success']) {
        try {
            $instance = $this->loadClass($checkSecurityApi['class']);
            return $this->view($instance->listCheckins($request), 200);
        } catch (InvalidFormException $exception) {
            return $this->view($exception->getMessage(), 400);
        }
    } else
        return $this->view($checkSecurityApi['msg'], 400);
}

    /**
     * Obtener listado de destinos. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of another API method",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getDestinationsListAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->listDestinations($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de municipios. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of another API method",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     * @internal param string $code Código de la propiedad
     */
    public function getMunicipalitiesListAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);

                return $this->view($instance->listMunicipality($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de países. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of another API method",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     * @internal param string $code Código de la propiedad
     */
    public function getCountriesListAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);

                return $this->view($instance->listCountries($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**

     * Obtener listado de provincias. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of another API method",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getProvincesListAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);

                return $this->view($instance->listProvinces($request), 200);

            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de alojamientos por estados. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener listado de alojamientos por estado",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="status", requirements="integer",nullable=true, description="Identificador del estado.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getAccommodationByStatusAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->listAccommodationByStatus($request->query->get('status')), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener reviews promedio de una propiedad. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener reviews promedio de una propiedad",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="accommodation_code", requirements="\d+",nullable=false, description="Codigo del alojamiento.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getAccommodationReviewAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getAverageReviewByAccommodationCode($request->query->get('accommodation_code')), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Buscar una propiedad. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Buscar una propiedad",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="id_destination", requirements="integer",nullable=false, description="Id del destino.")
     * @Annotations\QueryParam(name="arrival_date",nullable=false, description="Fecha de llegada (Y-m-d).")
     * @Annotations\QueryParam(name="leaving_date",nullable=false, description="Fecha de salida (Y-m-d).")
     * @Annotations\QueryParam(name="rooms_total", requirements="integer",nullable=false, description="Total de habitaciones.")
     * @Annotations\QueryParam(name="guest_total", requirements="integer",nullable=false, description="Total de huespedes.")
     * @Annotations\QueryParam(name="is_selection",requirements="boolean",default="0", nullable=true, description="Es casa seleccion.")
     * @Annotations\QueryParam(name="accommodation_type", requirements="\d+",nullable=false, description="Tipo de alojamiento.")
     * @Annotations\QueryParam(name="accommodation_category", requirements="\d+",nullable=false, description="Categoria del alojamiento.")
     * @Annotations\QueryParam(name="price_from", requirements="\d+",nullable=false, description="Rango de precio (Desde).")
     * @Annotations\QueryParam(name="price_to", requirements="\d+",nullable=false, description="Rango de precio (Hasta).")
     * @Annotations\QueryParam(name="has_climatization",default="0", nullable=true, description="Tiene climatizacion?")
     * @Annotations\QueryParam(name="has_pets",default="0", nullable=true, description="Mascotas?")
     * @Annotations\QueryParam(name="has_audiovisuals",default="0", nullable=true, description="Tiene audiovisuales?")
     * @Annotations\QueryParam(name="has_baby_facilities",default="0", nullable=true, description="Tiene cuna?")
     * @Annotations\QueryParam(name="allow_smoker",default="0", nullable=true, description="Permite fumadores?")
     * @Annotations\QueryParam(name="has_safe",default="0", nullable=true, description="Tiene caja fuerte?")
     * @Annotations\QueryParam(name="has_balcony",default="0", nullable=true, description="Tiene balcon?")
     * @Annotations\QueryParam(name="has_terrace",default="0", nullable=true, description="Tiene terraza?")
     * @Annotations\QueryParam(name="has_yard",default="0", nullable=true, description="Tiene patio?")
     * @Annotations\QueryParam(name="has_internet",default="0", nullable=true, description="Tiene internet o correo?")
     * @Annotations\QueryParam(name="has_jacuzzy",default="0", nullable=true, description="Tiene jacuzzy?")
     * @Annotations\QueryParam(name="has_pool",default="0", nullable=true, description="Tiene piscina?")
     * @Annotations\QueryParam(name="has_breakfast",default="0", nullable=true, description="Ofrece desayuno?")
     * @Annotations\QueryParam(name="has_dinner",default="0", nullable=true, description="Ofrece cena?")
     * @Annotations\QueryParam(name="has_laundry",default="0", nullable=true, description="Lavanderia?")
     * @Annotations\QueryParam(name="has_parking",default="0", nullable=true, description="Tiene parqueo?")
     * @Annotations\QueryParam(name="bathroom_type", requirements="\d+",nullable=false, description="Tipo de banno.")
     * @Annotations\QueryParam(name="spoken_languages_english", default="0", nullable=true, description="Idiomas que hablan en la casa (Ingles).")
     * @Annotations\QueryParam(name="spoken_languages_german", default="0", nullable=true, description="Idiomas que hablan en la casa (Aleman).")
     * @Annotations\QueryParam(name="spoken_languages_french", default="0", nullable=true, description="Idiomas que hablan en la casa (Frances).")
     * @Annotations\QueryParam(name="spoken_languages_italian", default="0", nullable=true, description="Idiomas que hablan en la casa (Italiano).")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getSearchAccommodationsAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $filters = array(
                    "idDestination" => $request->query->get('id_destination'),
                    "arrivalDate" => $request->query->get('arrival_date'),
                    "leavingDate" => $request->query->get('leaving_date'),
                    "roomsTotal" => $request->query->get('rooms_total'),
                    "guestTotal" => $request->query->get('guest_total'),
                    "isSelection" => $request->query->get('is_selection'),
                    "type" => $request->query->get('accommodation_type'),
                    "category" => $request->query->get('accommodation_category'),
                    "priceFrom" => $request->query->get('price_from'),
                    "priceTo" => $request->query->get('price_to'),
                    "hasClimatization" => $request->query->get('has_climatization'),
                    "hasPets" => $request->query->get('has_pets'),
                    "hasAudiovisuals" => $request->query->get('has_audiovisuals'),
                    "hasBabyFacilities" => $request->query->get('has_baby_facilities'),
                    "allowSmoker" => $request->query->get('allow_smoker'),
                    "hasSafe" => $request->query->get('has_safe'),
                    "hasBalcony" => $request->query->get('has_balcony'),
                    "hasTerrace" => $request->query->get('has_terrace'),
                    "hasYard" => $request->query->get('has_yard'),
                    "hasInternet" => $request->query->get('has_internet'),
                    "hasJacuzzy" => $request->query->get('has_jacuzzy'),
                    "hasPool" => $request->query->get('has_pool'),
                    "hasBreakfast" => $request->query->get('has_breakfast'),
                    "hasDinner" => $request->query->get('has_dinner'),
                    "hasLaundry" => $request->query->get('has_laundry'),
                    "hasParking" => $request->query->get('has_parking'),
                    "bathroomType" => $request->query->get('bathroom_type'),
                    "spokenLangEnglish" => $request->query->get('spoken_languages_english'),
                    "spokenLangGerman" => $request->query->get('spoken_languages_german'),
                    "spokenLangFrench" => $request->query->get('spoken_languages_french'),
                    "spokenLangItalian" => $request->query->get('spoken_languages_italian')
                );

                return $this->view($instance->getSearchAccommodations($filters), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Ordenar listado de propiedades según ranking de MyCP. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Ordenar listado de propiedades según ranking de MyCP",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getAccommodationOrdererByRankingAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getAccommodationOrdererByRanking($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener resumen de reservaciones de un cliente. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener resumen de reservaciones de un cliente.",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user_id", requirements="\d+",nullable=false, description="Identificador del cliente.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getClientReservationsStatsAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getClientResume($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener reservaciones de un cliente. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener reservaciones de un cliente.",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user_id", requirements="\d+",nullable=false, description="Identificador del cliente.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getClientReservationsListAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getClientReservations($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener detalles de una propiedad. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener detalles de una propiedad",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="accommodation_code", requirements="\d+",nullable=false, description="Codigo del alojamiento.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getAccommodationDetailsAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getAccommodationDetails($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener los comentarios publicados de una propiedad. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener los comentarios publicados de una propiedad",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="accommodation_code", requirements="\d+",nullable=false, description="Codigo del alojamiento.")
     * @Annotations\QueryParam(name="is_published", requirements="boolean",nullable=true, description="Determina si se desea obtener todos los comentarios, solo los publicados o solo los no publicados.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getAccommodationCommentsAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getAccommodationComments($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener las imágenes de una propiedad. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener las imágenes de una propiedad",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="accommodation_code", requirements="\d+",nullable=false, description="Codigo del alojamiento.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getAccommodationPhotosAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getAccommodationPhotos($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener las reservas de la cesta de reservaciones de un usuario. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener las reservas de la cesta de reservaciones de un usuario",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user_id", requirements="\d+",nullable=false, description="Id del usuario.")
     * @Annotations\QueryParam(name="session_id", requirements="\d+",nullable=false, description="Id de la session.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getUserCartAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getUserCart($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener las consultas de disponibilidad de un usuario. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener las consultas de disponibilidad de un usuario",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user_id", requirements="\d+",nullable=false, description="Id del usuario.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getUserConsultsAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getUserConsults($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener las reservas de un usuario. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener las reservas de un usuario",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user_id", requirements="\d+",nullable=false, description="Id del usuario.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getUserReservesAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getUserReserves($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener las reservas históricas de un usuario. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener las reservas históricas de un usuario",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user_id", requirements="\d+",nullable=false, description="Id del usuario.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getUserHistoryAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getUserHistory($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener las propiedades favoritas de un usuario. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener las propiedades favoritas de un usuario",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user_id", requirements="\d+",nullable=false, description="Id del usuario.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getUserFavoritiesAccommodationsAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getUserFavoritiesAccommodations($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener los comentarios enviados por un usuario. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener los comentarios enviados por un usuario",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user_id", requirements="\d+",nullable=false, description="Id del usuario.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getUserCommentsAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getUserComments($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener los comentarios enviados por un usuario. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener los comentarios enviados por un usuario",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="destination_name", requirements="\d+",nullable=true, description="Nombre del destino.")
     * @Annotations\QueryParam(name="prov_id", requirements="integer",nullable=true, description="Id de la provincia.")
     * @Annotations\QueryParam(name="mun_id", requirements="integer",nullable=true, description="Id del municipio.")
     * @Annotations\QueryParam(name="status", requirements="boolean",nullable=true, description="Estado.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getDestinationSearchAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getDestinationSearch($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Obtener listado de propiedades registradas. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Obtener listado de propiedades registradas",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="start",default="0",nullable=true, description="Inicio.")
     * @Annotations\QueryParam(name="limit",default="20", nullable=true, description="Elementos por página.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getAccommodationsAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getAccommodations($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }

    /**
     * Adicionar comentario de una propiedad. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user",nullable=false, description="id usuario.")
     * @Annotations\QueryParam(name="ownership",nullable=false, description="id de la casa.")
     * @Annotations\QueryParam(name="date",nullable=false, description="Fecha formato (Y-m-d).")
     * @Annotations\QueryParam(name="rate",nullable=false, description="Rate (1 al 5)")
     * @Annotations\QueryParam(name="public",nullable=false, description="Publicado o no (0 ó 1)")
     * @Annotations\QueryParam(name="comment", nullable=false, description="Comentario.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postAddCommentOwnershipAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $user = $request->request->get('user');
                $ownership = $request->request->get('ownership');
                $date = $request->request->get('date');
                $rate = $request->request->get('rate');
                $public = $request->request->get('public');
                $comments = $request->request->get('comment');
                return $this->view($instance->addCommentOwnership($user, $ownership, $date, $rate, $public, $comments), Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Obtener los datos de una propiedad . Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="code",nullable=false, description="codigo de la casa.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getOwnershipDataAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $code = $request->query->get('code');
                return $this->view($instance->getOwnershipData($code), Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Obtener las habitaciones de una propiedad. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="code",nullable=false, description="codigo de la casa.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getRoomsOfOwnershipsByCodeAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $code = $request->query->get('code');
                return $this->view($instance->getRoomsOfOwnershipsByCode($code), Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Cambiar estado de una reserva. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   views = {"MyCpBackend"}
     * )
     *
     * @Annotations\QueryParam(name="key", requirements="\d+", description="Llave del api.")
     * @Annotations\RequestParam(name="res_id", nullable=false, strict=true, description="Identificado de la reservation")
     * @Annotations\RequestParam(name="status", nullable=false, strict=true, description="Estado nuevo.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     */
    public function putChangeStatusReservationAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $res_id = $request->request->get('res_id');
                $status = $request->request->get('status');
                return $this->view($instance->changeStatusReservation($res_id, $status), Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Cancelar una reserva. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   views = {"MyCpBackend"}
     * )
     *
     * @Annotations\QueryParam(name="key", requirements="\d+", description="Llave del api.")
     * @Annotations\RequestParam(name="res_id", nullable=false, strict=true, description="Identificado de la reservation")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     */
    public function putCancelReservationAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $res_id = $request->request->get('res_id');
                $status = 6;
                return $this->view($instance->changeStatusReservation($res_id, $status), Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Cambiar estado de una casa. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   views = {"MyCpBackend"}
     * )
     *
     * @Annotations\QueryParam(name="key", requirements="\d+", description="Llave del api.")
     * @Annotations\RequestParam(name="own_id", nullable=false, strict=true, description="Identificador de la casa")
     * @Annotations\RequestParam(name="status", nullable=false, strict=true, description="Estado nuevo.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     */
    public function putChangeStatusOwnershipAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $own_id = $request->request->get('own_id');
                $status = $request->request->get('status');
                return $this->view($instance->changeStatusOwnership($own_id, $status), Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Cambiar estado de una habitación. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   views = {"MyCpBackend"}
     * )
     *
     * @Annotations\QueryParam(name="key", requirements="\d+", description="Llave del api.")
     * @Annotations\RequestParam(name="room_id", nullable=false, strict=true, description="Identificador de la habitacion (0 ó 1)")
     * @Annotations\RequestParam(name="status", nullable=false, strict=true, description="Estado nuevo.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     */
    public function putChangeStatusRoomAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $own_id = $request->request->get('room_id');
                $status = $request->request->get('status');
                return $this->view($instance->changeStatusRoom($own_id, $status), Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Insertar logs. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Insertar logs",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="user_id",requirements="integer",nullable=false, description="Id usuario.")
     * @Annotations\QueryParam(name="module_id",requirements="{1 - MODULE_DESTINATION| 2 - MODULE_FAQS| 3 - MODULE_ALBUM| 4 - MODULE_OWNERSHIP| 5 - MODULE_CURRENCY| 6 - MODULE_LANGUAGE| 7 - MODULE_RESERVATION| 8 - MODULE_USER| 9 - MODULE_GENERAL_INFORMATION| 10 - MODULE_COMMENT| 11 - MODULE_UNAVAILABILITY_DETAILS| 12 - MODULE_METATAGS| 13 - MODULE_MUNICIPALITY| 14 - MODULE_SEASON| 15 - MODULE_LODGING_RESERVATION| 16 - MODULE_LODGING_COMMENT| 17 - MODULE_LODGING_OWNERSHIP| 18 - MODULE_LODGING_USER| 19 - MODULE_MAIL_LIST| 20 - MODULE_BATCH_PROCESS| 21 - MODULE_CLIENT_MESSAGES| 22 - MODULE_CLIENT_COMMENTS| 23 - MODULE_AWARD| 24 - MODULE_RBAC}", nullable=false, description="Id del modulo.")
     * @Annotations\QueryParam(name="description", requirements="\d+",nullable=false, description="Descripcion del log.")
     * @Annotations\QueryParam(name="operation_id", requirements="{1 - OPERATION_INSERT| 2 - OPERATION_UPDATE| 3 - OPERATION_DELETE| 4 - OPERATION_VISIT| 5 - OPERATION_LOGIN| 6 - OPERATION_LOGOUT| 7 - OPERATION_NONE| 8 - OPERATION_REMOVE}",nullable=false, description="Id de la operacion.")
     * @Annotations\QueryParam(name="data_table_name", requirements="\d+",nullable=false, description="Nombre de la tabla en la base de datos")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    /*public function postLogAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));

        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->insertLog($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }*/


    /**
 * Reporte de clientes diario por países. Estado(100%).
 *
 * @ApiDoc(
 *   resource = true,
 *   statusCodes = {
 *     200 = "Returned when successful",
 *     400 = "Returned when the form has errors"
 *   },
 * views = {"MyCpBackend"}
 * )
 * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
 * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
 * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
 * @Annotations\QueryParam(name="country",nullable=false, description="País")
 * @Annotations\View(
 *  templateVar="pages"
 * )
 * @param Request $request the request object
 * @return array
 */
    public function getClientsXCountryDailyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['clients']=$instance->getClientsXCountryDailySummary($request);
                $result['available']=$instance->getClientsXCountryDailySummaryAvailable($request);
                $result['payments']=$instance->getClientsXCountryDailySummaryPayments($request);
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Reporte de clientes diario por destinos. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\QueryParam(name="destination",nullable=false, description="Destino")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getClientsXDestinationDailyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['clients']=$instance->getClientsXDestinationDailySummary($request);
                $result['available']=$instance->getClientsXDestinationDailySummaryAvailable($request);
                $result['payments']=$instance->getClientsXDestinationDailySummaryPayments($request);
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
 * Reporte de clientes mensual por destinos. Estado(100%).
 *
 * @ApiDoc(
 *   resource = true,
 *   statusCodes = {
 *     200 = "Returned when successful",
 *     400 = "Returned when the form has errors"
 *   },
 * views = {"MyCpBackend"}
 * )
 * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
 * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
 * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
 * @Annotations\QueryParam(name="destination",nullable=false, description="Destino")
 * @Annotations\View(
 *  templateVar="pages"
 * )
 * @param Request $request the request object
 * @return array
 */
    public function getClientsXDestinationMonthlyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $result=array();
                $result['clients']=$instance->getClientsXDestinationMonthlySummary($request);
                $result['available']=$instance->getClientsXDestinationMonthlySummaryAvailable($request);
                $result['payments']=$instance->getClientsXDestinationMonthlySummaryPayments($request);
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Reporte de clientes anual por destinos. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\QueryParam(name="destination",nullable=false, description="Destino")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getClientsXDestinationYearlyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $result=array();
                $result['clients']=$instance->getClientsXDestinationYearlySummary($request);
                $result['available']=$instance->getClientsXDestinationYearlySummaryAvailable($request);
                $result['payments']=$instance->getClientsXDestinationYearlySummaryPayments($request);
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Reporte de clientes mensual por países. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\QueryParam(name="country",nullable=false, description="País")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
       public function getClientsXCountryMonthlyAction(Request $request)
                {
                    $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
                    if ($checkSecurityApi['success']) {
                        try {
                            $instance = $this->loadClass($checkSecurityApi['class']);
                            $result=array();
                            $result['clients']=$instance->getClientsXCountryMonthlySummary($request);
                            $result['available']=$instance->getClientsXCountryMonthlySummaryAvailable($request);
                            $result['payments']=$instance->getClientsXCountryMonthlySummaryPayments($request);
                            return $this->view($result, Response::HTTP_OK);
                        } catch (InvalidFormException $exception) {
                            return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    } else{
                        return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
                    }
                }

    /**
     * Obtener listado corto de destinos. Estado(100%).
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of another API method",
     *  statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *  views = { "MyCpBackend" }
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     * @return array
     * @throws Exception
     */
    public function getDestinationsShortListAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->shortDestinationsList($request), 200);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), 400);
            }
        } else
            return $this->view($checkSecurityApi['msg'], 400);
    }
    /**
     * Reporte de clientes anual por países. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\QueryParam(name="country",nullable=false, description="País")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getClientsXCountryYearlyAction(Request $request)
                {
                    $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
                    if ($checkSecurityApi['success']) {
                        try {
                            $instance = $this->loadClass($checkSecurityApi['class']);
                            $result=array();
                            $result['clients']=$instance->getClientsXCountryYearlySummary($request);
                            $result['available']=$instance->getClientsXCountryYearlySummaryAvailable($request);
                            $result['payments']=$instance->getClientsXCountryYearlySummaryPayments($request);
                            return $this->view($result, Response::HTTP_OK);
                        } catch (InvalidFormException $exception) {
                            return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    } else{
                        return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
                    }
                }
    /**
     * Reporte de clientes diario vs solicitudes. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getClientsXRequestsDailyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['clients']=$instance->getClientsXRequestsDailySummary($request);
                $result['available']=$instance->getClientsXRequestsDailySummaryAvailable($request);
                $result['payments']=$instance->getClientsXRequestsDailySummaryPayments($request);
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }
    /**
     * Reporte de clientes mensual vs solicitudes. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getClientsXRequestsMonthlyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['clients']=$instance->getClientsXRequestsMonthlySummary($request);
                $result['available']=$instance->getClientsXRequestsMonthlySummaryAvailable($request);
                $result['payments']=$instance->getClientsXRequestsMonthlySummaryPayments($request);
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Reporte de clientes anual vs solicitudes. Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getClientsXRequestsYearlyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['clients']=$instance->getClientsXRequestsYearlySummary($request);
                $result['available']=$instance->getClientsXRequestsYearlySummaryAvailable($request);
                $result['payments']=$instance->getClientsXRequestsYearlySummaryPayments($request);
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Reporte de facturación diario . Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getClientsXFacturationDailyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['clients']=$instance->getClientsXRequestsDailySummary($request);
                $result['available']=$instance->getClientsXRequestsDailySummaryAvailable($request);
                $result['payments']=$instance->getClientsXFacturationDailySummary($request);
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }
    /**
     * Reporte de facturación mensual . Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getClientsXFacturationMonthlyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['clients']=$instance->getClientsXRequestsMonthlySummary($request);
                $result['available']=$instance->getClientsXRequestsMonthlySummaryAvailable($request);
                $result['payments']=$instance->getClientsXFacturationMonthlySummary($request);
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }
    /**
     * Reporte de facturación anual . Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful response",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getClientsXFacturationYearlyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['clients']=$instance->getClientsXRequestsYearlySummary($request);
                $result['available']=$instance->getClientsXRequestsYearlySummaryAvailable($request);
                $result['payments']=$instance->getClientsXFacturationYearlySummary($request);
                $clientsSummaryFacturation=$instance->getClientsXOnlyFacturationYearlySummary($request);
                foreach($clientsSummaryFacturation as  $i=>$y){
                    $result['payments'][$i]['facturacion']=$y['facturacion'];
                }
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
 * Reporte de solicitudes diaria . Estado(100%).
 *
 * @ApiDoc(
 *   resource = true,
 *   statusCodes = {
 *     200 = "Returned when successful",
 *     400 = "Returned when the form has errors"
 *   },
 * views = {"MyCpBackend"}
 * )
 * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
 * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
 * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
 * @Annotations\View(
 *  templateVar="pages"
 * )
 * @param Request $request the request object
 * @return array
 */
    public function getReservationsDailyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['reservations']=$instance->getReservationsDailySummary($request);
                $result['available']=$instance->getReservationsAvailableDailySummary($request);
                $result['payments']=$instance->getReservationsPaymentsDailySummary($request);
                /*$clientsSummaryFacturation=$instance->getClientsXOnlyFacturationYearlySummary($request);
                foreach($clientsSummaryFacturation as  $i=>$y){
                    $result['payments'][$i]['facturacion']=$y['facturacion'];
                }*/
                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Reporte de solicitudes mensuales . Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getReservationsMonthlyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['reservations']=$instance->getReservationsMonthlySummary($request);
                $result['available']=$instance->getReservationsAvailableMonthlySummary($request);
                $result['payments']=$instance->getReservationsPaymentsMonthlySummary($request);

                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Reporte de solicitudes anuales . Estado(100%).
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     * views = {"MyCpBackend"}
     * )
     * @Annotations\QueryParam(name="key", requirements="\d+",nullable=false, description="Llave del api.")
     * @Annotations\QueryParam(name="filter_date_from",nullable=true, description="Fecha desde")
     * @Annotations\QueryParam(name="filter_date_to",nullable=false, description="Fecha Hasta")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function getReservationsYearlyAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
//                return $this->view($instance->getClientsXCountryDailySummary($request), Response::HTTP_OK);
//                return $this->view($instance->getClientsXCountryDailySummaryAvailable($request), Response::HTTP_OK);
                $result=array();
                $result['reservations']=$instance->getReservationsYearlySummary($request);
                $result['available']=$instance->getReservationsAvailableYearlySummary($request);
                $result['payments']=$instance->getReservationsPaymentsYearlySummary($request);

                return $this->view($result, Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else{
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
        }
    }
}