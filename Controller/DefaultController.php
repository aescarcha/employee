<?php

namespace Aescarcha\EmployeeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('AescarchaEmployeeBundle:Default:index.html.twig');
    }
}
