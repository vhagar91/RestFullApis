<?php

namespace BackendBundle\Controller;

use FrontendBundle\Entity\DatApi;
use FrontendBundle\Form\DatApiType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ApiController extends CoreController implements BaseController
{

    /**
     * @Route("/backend/main_api", name="backend_main_api")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mainAction(Request $request)
    {
        $em = $this->get('doctrine')->getEntityManager();
        $datApiRepository = $em->getRepository('FrontendBundle:DatApi');

        $apis = $datApiRepository->findAll();
        $form = $this->get('form.factory')->create(new DatApiType());


        //return $this->render('BackendBundle:Api:api.html.twig', array('apis' => $apis));
        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('BackendBundle:Api:api.html.twig', array('apis' => $apis, 'form'=>$form->createView())),
            'msg' => 'Vista del listado de apis']);
    }

    /**
     * @Route("/backend/save_api", name="backend_save_api")
     * @param Request $request
     * @return JsonResponse
     */
    public function saveApiAction(Request $request)
    {
        if($request->getMethod() == 'POST'){
            $em = $this->get('doctrine')->getEntityManager();
            $datApiRepository = $em->getRepository('FrontendBundle:DatApi');

            $datApi = new DatApi();

            $parameters =  $request->request->get('frontendbundle_datapi');
            $parameters['code'] = $this->generateCode();
            $request->request->set('frontendbundle_datapi', $parameters);

            $form = $this->get('form.factory')->create(new DatApiType(), $datApi);
            $form->handleRequest($request);

            if($form->isValid()){
                $datApiRepository->save($datApi);
                return new JsonResponse(['success' => true, 'msg' => 'Adicionado correctamente']);
            }

            return new JsonResponse(['success' => false, 'msg' => 'Campos incorrectos u obligatorios']);
        }
    }

    /**
     * @Route("/backend/edit_api", name = "backend_edit_api")
     * @param Request $request
     * @return JsonResponse
     */
    public function editApiAction(Request $request){
        if($request->getMethod() == 'POST'){
            $em = $this->get('doctrine')->getEntityManager();
            $datApiRepository = $em->getRepository('FrontendBundle:DatApi');

            $id = $request->request->get('id');
            $oldApi = $datApiRepository->findById($id)[0];

            $oldApi->setStatus(false);
            $form = $this->get('form.factory')->create(new DatApiType(), $oldApi);
            $form->handleRequest($request);

            if($form->isValid()){
                $status = $request->get('frontendbundle_datapi')['status'];
                $oldApi->setStatus($status);
                $datApiRepository->save($oldApi);
                return new JsonResponse(['success' => true, 'msg' => 'Editado correctamente']);
            }

            return new JsonResponse(['success' => false, 'msg' => 'Campos incorrectos u obligatorios']);
        }
    }

    /**
     * @Route("/backend/{id}/delete_api", name = "backend_delete_api")
     * @ParamConverter("api", class="FrontendBundle\Entity\DatApi")
     * @param DatApi $api
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteApiAction(DatApi $api){

        $em = $this->get('doctrine')->getEntityManager();
        $datApiRepository = $em->getRepository('FrontendBundle:DatApi');

        $datApiProjectRepository = $em->getRepository('FrontendBundle:DatApiProject');
        $datApisProjects = $datApiProjectRepository->findByApi($api);

        if(count($datApisProjects) >= 1){
            return new JsonResponse(['success' => false, 'msg' => 'Esta api esta en uso']);
        }

        $datApiRepository->delete($api);

        return new JsonResponse(['success' => true, 'msg' => 'Eliminado incorrectamente']);
    }

    public function generateCode() {
        $code = '';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
        $max = strlen($pattern)-1;
        for($i=0;$i < 8;$i++) $code .= $pattern{mt_rand(0,$max)};
        return $code;
    }
}
