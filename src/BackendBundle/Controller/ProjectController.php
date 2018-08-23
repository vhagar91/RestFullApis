<?php

namespace BackendBundle\Controller;

use FrontendBundle\Entity\DatApi;
use FrontendBundle\Entity\DatProject;
use FrontendBundle\Form\DatApiType;
use FrontendBundle\Form\DatProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ProjectController extends CoreController implements BaseController
{

    /**
     * @Route("/backend/main_project", name="backend_main_project")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mainAction(Request $request)
    {
        $em = $this->get('doctrine')->getEntityManager();
        $datProjectRepository = $em->getRepository('FrontendBundle:DatProject');

        $projects = $datProjectRepository->findAll();

        $form = $this->get('form.factory')->create(new DatProjectType());

        //return $this->render('BackendBundle:Api:api.html.twig', array('apis' => $apis));
        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('BackendBundle:Project:project.html.twig', array(
                'projects' => $projects,
                'form'=>$form->createView())),
            'msg' => 'Vista del listado de apis']);
    }

    /**
     * @Route("/backend/edit_project", name = "backend_edit_project")
     * @param Request $request
     * @return JsonResponse
     */
    public function editProjectAction(Request $request){
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
     * @Route("/backend/{id}/delete_project", name = "backend_delete_project")
     * @ParamConverter("project", class="FrontendBundle\Entity\DatProject")
     * @param DatProject $project
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteProjectAction(DatProject $project){

        $em = $this->get('doctrine')->getEntityManager();
        $datProjectRepository = $em->getRepository('FrontendBundle:DatProject');

        $datProjectRepository->deleteProject($project);

        return new JsonResponse(['success' => true, 'msg' => 'Eliminado incorrectamente']);
    }
}
