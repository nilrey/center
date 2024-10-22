<?php

namespace App\NCUO\MapBundle\Controller;

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
     * @Route("/", name = "map")
     * 
     * Корневая страница
     */

    public function indexAction(Request $request) {
        if ($this->isGranted('ROLE_ADMIN'))
            return new RedirectResponse($this->generateUrl('map_base_adm'));
        else if ($this->isGranted('ROLE_FOIV') || $this->isGranted('ROLE_ROIV'))
            return new RedirectResponse($this->generateUrl('map_base_user'));
        else if ($this->isGranted('ROLE_NCUO') || $this->isGranted('ROLE_VDL'))
            return new RedirectResponse($this->generateUrl('map_base_ncuo'));
        else
            throw $this->createAccessDeniedException();
    } 
}