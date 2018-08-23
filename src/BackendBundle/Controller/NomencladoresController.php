<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FrontendBundle\Entity\NomApiType;
use FrontendBundle\Form\NomApiTypeType;
use FrontendBundle\Form\NomDriver;
use FrontendBundle\Entity\NomApplicationType;
use FrontendBundle\Form\NomApplicationTypeType;
use FrontendBundle\Entity\NomDriverType;
use Symfony\Component\HttpFoundation\Request;

class NomencladoresController extends Controller
{
    /**
     * @param $class
     * @return mixed
     */
    public function getClass($class){
        $em = $this->getDoctrine()->getManager();
        $metas = $em->getMetadataFactory()->getAllMetadata();
        $nomenclatorClass= 'FrontendBundle\Entity\Nomenclator\Nomenclator';
        $nomenclators= array();
        foreach($metas as $meta){
            $className= $meta->name;
            $rfcEntity     = new \ReflectionClass($className);
            if(!$rfcEntity->isSubclassOf($nomenclatorClass)){
                continue;
            }
            $nomenclators[$className]= (new \ReflectionClass(new $className))->getShortName();
        }
        return array_search($class,$nomenclators);
    }

    /**
     * @Route("/backend/nomencladores", name="backend_main_nomencladores")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $classNom=$request->request->get('class');
        $repository=self::getClass($classNom);
        $datApiRepository = $em->getRepository($repository);
        $noms = $datApiRepository->findAll();
        $name_form=$classNom.'Type';
        $class = "FrontendBundle\\Form\\$name_form";
        $form = $this->get('form.factory')->create(new $class());
        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('BackendBundle:Nomencladores:nomencladores.html.twig', array('noms' => $noms, 'form'=>$form->createView(), 'id'=>$classNom,'idTab'=>$request->request->get('class'))),
            'sms' => 'Vista del listado de nomencladores'
           ]);
    }

    /**
     * @Route("/backend/save_nom", name="backend_save_nom")
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request){
        if($request->getMethod() == 'POST'){
            $classNom=$request->request->get('class');
            $name_form=$classNom.'Type';
            $class = "FrontendBundle\\Form\\$name_form";
            $class_aux = "FrontendBundle\\Entity\\$classNom";
            $em = $this->getDoctrine()->getManager();
            $dat = new $class_aux();
            $form = $this->get('form.factory')->create(new $class(), $dat);
            $form->handleRequest($request);
            if($form->isValid()){
                $em->persist($dat);
                $em->flush();
                return new JsonResponse(['success' => true, 'sms' => 'El nomenclador se ha adicionado correctamente']);
            }
            return new JsonResponse(['success' => false, 'sms' => 'Adicionado incorrectamente']);
        }
    }

    /**
     * @Route("/backend/edit_nom", name = "backend_edit_nom")
     * @param Request $request
     * @return JsonResponse
     */
    public function editNomAction(Request $request){
        if($request->getMethod() == 'POST'){
            $classNom = $request->request->get('class');
            $nameForm = $classNom.'Type';
            $classNomForm = "FrontendBundle\\Form\\$nameForm";

            $em = $this->getDoctrine()->getManager();
            $nomRepository = $em->getRepository('FrontendBundle:'.$classNom);

            $id = $request->request->get('id');
            $oldNom = $nomRepository->findById($id)[0];

            $form = $this->get('form.factory')->create(new $classNomForm(), $oldNom);
            $form->handleRequest($request);

            if($form->isValid()){
                $nomRepository->save($oldNom);
                return new JsonResponse(['success' => true, 'sms' => 'Editado correctamente']);
            }

            return new JsonResponse(['success' => false, 'sms' => 'Campos incorrectos u obligatorios']);
        }
    }

    /**
     * @Route("/backend/{id}/delete_nom", name = "backend_delete_nom")
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteNomAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $classNom=$request->request->get('class');
        $nom = $em->getRepository('FrontendBundle:'.$classNom)->find($id);
        $em->remove($nom);
        $em->flush();
        return new JsonResponse(['success' => true, 'sms' => 'El nomenclador ha sido eliminado satisfactoriamente']);
    }
}