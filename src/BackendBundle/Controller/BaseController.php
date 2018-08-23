<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

interface BaseController
{
    public function mainAction(Request $request);
}
