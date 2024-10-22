<?php

namespace App\NCUO\PortalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
   private $menuItems;
    
    /**
     * @Route("/", name="homepage")
     * 
     * Функция генерации страницы функциональной системы
     */
    
   public function pageAction(Request $request)
   {
       return new RedirectResponse($this->generateUrl('oiv'));
        
      // return $this->render('ncuoportal/index.html.twig');
   }
    
    
   /**
    * @Route("/undercon", name="undercon")
    * @Template("ncuoportal/undercon.html.twig");
    *
    * Заглушка "в разработке"
    */
   
   public function undercon() {
      return [];
   }
    
}
