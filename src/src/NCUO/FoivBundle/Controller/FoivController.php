<?php

namespace App\NCUO\FoivBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\FoivBundle\Controller\ErrorResponseGenerator;
use App\NCUO\FoivBundle\Entity\ORMObjectMetadata;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\FoivBundle\Form\FoivType;
use App\NCUO\PortalBundle\Entity\User;

/**
 * Foiv controller.
 */
class FoivController extends Controller
{

    /** 
     * Lists all Foiv entities.
     *
     * @Route("/", name="foiv")
     * @Method("GET")
     * @Template("ncuofoiv/foiv/index.html.twig")
     */
    public function indexAction()
    {
        
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('NCUOFoivBundle:Foiv')->findBy(array(), array('name' => 'ASC'));
        
        if ($this->isGranted('ROLE_FOIV')){
            $user_foiv_id = $this->checkUserFoivId(0);
            if($user_foiv_id > 0 ){
                //return $this->redirect($this->generateUrl('foiv_show', array('id'=> $user_foiv_id ) ) );

                return $this->generateUrl('foiv');
            }
        }
     
       //запоминаем в сессии обратный URL-адрес.
       //Это понадобится для формы редактирования существующих ФОИВ
       $_SESSION['BACK_URL'] =  $this->generateUrl('foiv');
       
        return array(
            'entities' => $entities,
        );
    }    
}
