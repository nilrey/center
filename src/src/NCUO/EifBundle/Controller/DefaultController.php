<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Класс контроллера по-умолчанию
 * ##############################
 */

class DefaultController extends Controller {
    
    /**
     * @Route("/", name = "eif")
     * 
     * Корневая страница
     */

    public function indexAction(Request $request) {
        return new RedirectResponse($this->generateUrl('eif_sources'));
    } 
}