<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SkeletonController extends Controller
{
    public function indexAction()
    {
        return $this->render('BackendBundle:Skeleton:index.html.twig');
    }
}