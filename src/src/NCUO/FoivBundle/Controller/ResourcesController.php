<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\FoivBundle\Form\FoivType;
use App\NCUO\EifBundle\Entity\Protocol;

/**
 * Resources controller.
  */
class ResourcesController extends Controller
{
     /**
     * Lists all Foiv entities.
     *
     * @Route("/{id}/resources", name="resources")
     * @Method("GET")
     * @Template("ncuofoiv/Resources/index.html.twig")
     */
    public function indexAction($id)
    {
        $id = $this->checkUserFoivId($id);
        $em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($id);
        // get all foiv sources however it's only one
        $protocols = null;
        if( $sources = $em->getRepository('NCUOEifBundle:Source')->findBy( array("foiv" => $id) , array()) ){
            $protocols = $em->getRepository('NCUOEifBundle:Protocol')->findBy( array("source" => $sources[0]->getIdSource()) , array("protocol_name" => "asc"));
        }

        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }

        return array(
            'foiv'      => $foiv,
            'entities' => $protocols,
            'urlProtocol' => '/eif/protocols',
        );
      
    }

    public function checkUserFoivId($id){
        if ($this->isGranted('ROLE_FOIV')){
            $userId = $this->getUser()->getId();
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('NCUOPortalBundle:User')->find($userId);
            $user_foiv = $user->getFoiv();
            $user_foiv_id = $user_foiv->getId();
        }else{
            $user_foiv_id = $id;
        }
        return $user_foiv_id;
    }

}
?>