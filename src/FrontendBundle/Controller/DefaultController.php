<?php

namespace FrontendBundle\Controller;

use FrontendBundle\Entity\DatProject;
use FrontendBundle\Form\DatApiType;
use FrontendBundle\Form\DatProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $user = $this->validUser();
        if (!$user) {
            $response = new RedirectResponse($this->container->get('router')->generate('user_login'));
            return $response;
        }

        $em = $this->get('doctrine')->getManager();
        $datProjectRepository = $em->getRepository('FrontendBundle:DatProject');

        $datApiRepository = $em->getRepository('FrontendBundle:DatApi');
        $apis = $datApiRepository->findAll();

        $projects = $datProjectRepository->findByUser($user);

        $form = $this->get('form.factory')->create(new DatProjectType()/*, $projects[0]*/);

        return $this->render('FrontendBundle:default:index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'projects' => $projects,
            'apis'=>$apis,
            'form'=>$form->createView()
        ));
    }

    /**
     * @Route("/frontend/save_project", name="save_project")
     * @param Request $request
     * @return JsonResponse
     */
    public function saveProjectAction(Request $request)
    {
        $user = $this->validUser();
        if (!$user) {
            $response = new RedirectResponse($this->container->get('router')->generate('user_login'));
            return $response;
        }

        if($request->getMethod() == 'POST'){
            $em = $this->get('doctrine')->getEntityManager();
            $datProjectRepository = $em->getRepository('FrontendBundle:DatProject');

            $datProject = new DatProject();
            $datProject->setUser($user);

            $form = $this->get('form.factory')->create(new DatProjectType(), $datProject);
            $form->handleRequest($request);

            if($form->isValid()){
                if(!$datProject->getApisprojects()->isEmpty()){
                    $apiproject = $datProject->getApisprojects()->first();
                    do {
                        $apiproject->setProject($datProject);
                        $apiproject = $datProject->getApisprojects()->next();
                    }
                    while ($apiproject);
                }

                if(!$datProject->getUrls()->isEmpty()){
                    $url = $datProject->getUrls()->first();
                    do {
                        $url->setProject($datProject);
                        $url = $datProject->getUrls()->next();
                    }
                    while ($url);
                }

                $datProjectRepository->save($datProject);
                return new JsonResponse(['success' => true, 'msg' => 'Adicionado correctamente']);
            }

            return new JsonResponse(['success' => false, 'msg' => 'Campos incorrectos u obligatorios']);
        }
    }

    /**
     * @Route("/frontend/edit_project", name = "edit_project")
     * @param Request $request
     * @return JsonResponse
     */
    public function editProjectAction(Request $request){
        $user = $this->validUser();
        if (!$user) {
            $response = new RedirectResponse($this->container->get('router')->generate('user_login'));
            return $response;
        }

        if($request->getMethod() == 'POST'){
            $em = $this->get('doctrine')->getEntityManager();
            $datProjectRepository = $em->getRepository('FrontendBundle:DatProject');

            $id = $request->request->get('id');
            $oldProject = $datProjectRepository->findById($id)[0];

            $form = $this->get('form.factory')->create(new DatProjectType(), $oldProject);
            $form->handleRequest($request);

            if($form->isValid()){
                if(!$oldProject->getApisprojects()->isEmpty()){
                    $apiproject = $oldProject->getApisprojects()->first();
                    do {
                        if($apiproject->getProject() == null){
                            $apiproject->setProject($oldProject);
                        }
                        $apiproject = $oldProject->getApisprojects()->next();
                    }
                    while ($apiproject);
                }

                if(!$oldProject->getUrls()->isEmpty()){
                    $url = $oldProject->getUrls()->first();
                    do {
                        if($url->getProject() == null){
                            $url->setProject($oldProject);
                        }
                        $url = $oldProject->getUrls()->next();
                    }
                    while ($url);
                }

                $datProjectRepository->save($oldProject);
                return new JsonResponse(['success' => true, 'msg' => 'Editado correctamente']);
            }

            return new JsonResponse(['success' => false, 'msg' => 'Campos incorrectos u obligatorios']);
        }
    }

    /**
     * @Route("/frontend/{id}/delete_project", name = "delete_project")
     * @ParamConverter("project", class="FrontendBundle\Entity\DatProject")
     * @param DatProject $project
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteProjectAction(DatProject $project){

        $user = $this->validUser();
        if (!$user) {
            $response = new RedirectResponse($this->container->get('router')->generate('user_login'));
            return $response;
        }

        $em = $this->get('doctrine')->getEntityManager();
        $datProjectRepository = $em->getRepository('FrontendBundle:DatProject');

        $datProjectRepository->deleteProject($project);

        $response = new RedirectResponse($this->container->get('router')->generate('homepage'));
        return $response;
    }

    /**
     * @Route("/frontend/generate_apikey", name="generate_apikey")
     * @param Request $request
     * @return JsonResponse
     */
    public function generateApiKeyAction(Request $request)
    {
        $user = $this->validUser();
        if (!$user) {
            $response = new RedirectResponse($this->container->get('router')->generate('user_login'));
            return $response;
        }

        $idApi = $request->request->get('idApi');
        $key = $this->generarCodigo($idApi);

        return new JsonResponse(['success' => true, 'key' => $key, 'msg' => 'Generada correctamente ']);
    }

    /**
     * @return bool|mixed
     */
    public function validUser(){
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            return false;
        }
        else{
            return $user;
        }
    }

    /**
     * @param $idApi
     * @return string
     */
    public function generarCodigo($idApi) {
        $service=$this->get('rest.tocken');
        $key = $service->createRestToken($idApi);
        return $key;
    }
}
