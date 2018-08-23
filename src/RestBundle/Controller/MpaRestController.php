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

class MpaRestController extends RestController{
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
     *   views = {"Mpa"}
     * )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request
     * @return array
     * @throws \RestBundle\Controller\Exception
     */
    public function postLoginMpainfoAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $instance->login($request);
            }
            catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            catch(Exception $e){
                return $this->view($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        } else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Registrar propiedad . Estado(100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mpa"}
     * )
     * @Annotations\RequestParam(name="key",nullable=false, description="Llave del api.")
     * @Annotations\RequestParam(name="bussiness", nullable=false, description="Propiedad" )
     * @Annotations\RequestParam(name="user", nullable=true, description="Usuario." )
     * @Annotations\RequestParam(name="pass", nullable=true, description="Contraseña del usuario." )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postBussinessCreateAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                $bussiness = json_decode($request->request->get('bussiness'));
                $user = $request->request->get('user');
                $pass = $request->request->get('pass');

                /**/
                /*$pathToCont = "xxxxxxx.txt";
                $file = fopen($pathToCont, "a");
                fwrite($file, '----------------' . PHP_EOL);
                fwrite($file, 'api-key: ' . $request->request->get('key')   . PHP_EOL);
                fwrite($file, 'usuario: ' . $user  . ' pass:' . $pass . PHP_EOL);
                fwrite($file, 'bussiness: ' . $request->request->get('bussiness')  . PHP_EOL);
                fwrite($file, 'rooms: ' . $request->request->get('rooms')  . PHP_EOL);
                fwrite($file, '--------------' . PHP_EOL);
                fclose($file);*/
                /**/

                return $this->view($instance->createBussiness($bussiness, $user, $pass), Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        } else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Registrar imagenes de una propiedad . Estado(100%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mpa"}
     * )
     * @Annotations\RequestParam(name="key",nullable=false, description="Llave del api.")
     * @Annotations\RequestParam(name="user", nullable=true, description="Usuario." )
     * @Annotations\RequestParam(name="pass", nullable=true, description="Contraseña del usuario." )
     * @Annotations\RequestParam(name="code", nullable=true, description="Codigo de la propiedad." )
     * @Annotations\RequestParam(name="images", array=true, nullable=false, description="Foto" )
     * @Annotations\View(
     *  templateVar="pages"
     * )
     * @param Request $request the request object
     * @return array
     */
    public function postImagesAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->request->get('key'));
        if ($checkSecurityApi['success']) {
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
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        } else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Obtener los negosios de Mypaladar. Estado(90%)
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error",
     *   },
     * views = {"Mpa"}
     * )
     *
     * @Annotations\RequestParam(name="key", requirements="string",nullable=true, description="Llave del apiiiiiii.")
     * @Annotations\View(
     *  templateVar="pages"
     * )
     *
     * @param Request $request the request object
     *
     *
     * @return array
     */
    public function getBussinesssAction(Request $request)
    {
        $checkSecurityApi = $this->checkSecurityApi($request->query->get('key'));
        if ($checkSecurityApi['success']) {
            try {
                $instance = $this->loadClass($checkSecurityApi['class']);
                return $this->view($instance->getBussiness(), Response::HTTP_OK);
            } catch (InvalidFormException $exception) {
                return $this->view($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        } else
            return $this->view($checkSecurityApi['msg'], Response::HTTP_UNAUTHORIZED);
    }
}