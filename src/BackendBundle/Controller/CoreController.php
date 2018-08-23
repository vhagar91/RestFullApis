<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class CoreController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BackendBundle:Default:index.html.twig', array('name' => ''));
    }
}
