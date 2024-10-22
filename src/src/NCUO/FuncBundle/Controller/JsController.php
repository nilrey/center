<?php

namespace App\NCUO\FuncBundle\Controller;

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
     * @Route("/js/{file}", name = "func_js")
     * 
     * Возврат страницы JS
     */
    
    public function js($file) {
        try {
            return $this->render("ncuofunc/${file}.js.twig", []);
        } catch(\Exception $ex) {
            return $this->createResponse($resp = []);
        }
    }    
}