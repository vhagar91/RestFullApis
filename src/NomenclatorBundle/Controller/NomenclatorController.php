<?php

namespace NomenclatorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use NomenclatorBundle\Controller\BaseController;
class NomenclatorController extends BaseController
{
    /**
     * @Route("/backend/nomenclator", name="backend_main_nomenclator")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request){
        $connection = $this->getConnection();
        $classNom=$request->request->get('class');
        $noms = $this->getTable($classNom, 'fetchAll',$connection);
        return new JsonResponse([
            'html' => $this->renderView('NomenclatorBundle:Nomenclator:index.html.twig',array('noms' => $noms, 'id'=>$classNom,'idTab'=>$request->request->get('class'))),
        ]);
    }
}
