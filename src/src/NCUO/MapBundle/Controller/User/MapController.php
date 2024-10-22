<?php

namespace App\NCUO\MapBundle\Controller\User;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\MapBundle\Controller\BaseController;

/**
 * Класс контроллера списка сервисов
 * #################################
 * 
 */

class MapController extends BaseController {
 
    /**
     * @Route("/user/services", name = "map_base_user")
     * @Template("ncuoservice/user/map.html.twig")
     * 
     * Страница списка сервисов
     */
    
    public function services(Request $request) {
        // Формируем контент страницы
        $context = array();
        $context['msg_service_error'] = $this->container->getParameter('ncuoservice.msg.service_error');                    
        
        // Результирующий массив
        return $context;
    }    
}