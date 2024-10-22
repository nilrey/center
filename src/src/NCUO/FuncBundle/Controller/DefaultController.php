<?php

namespace App\NCUO\FuncBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Класс контроллера по-умолчанию
 * ##############################
 */

class DefaultController extends Controller {
    
    /**
     * @Route("/", name = "func")
     * @Template("ncuofunc/default.html.twig");
     * 
     * Корневая страница
     */

    public function indexAction(Request $request) {
		return [];
    } 
}
