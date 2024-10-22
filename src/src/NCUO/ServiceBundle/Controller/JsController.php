<?php

namespace App\NCUO\ServiceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;

/**
 * Класс контроллера генерации файлов JS
 * #####################################
 * 
 */

class JsController extends BaseController {
     
    /**
     * @Route("/js/{file}", name = "service_js")
     * 
     * Возврат страницы JS
     */
    
    public function js($file) {
        try {
            return $this->render("ncuoservice/${file}.js.twig", []);
        } catch(\Exception $ex) {
            return $this->createResponse($resp = []);
        }
    }    
}