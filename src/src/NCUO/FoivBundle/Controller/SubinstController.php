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

/**
 * Subinst controller.
  */
class SubinstController extends Controller
{
     /**
     * Lists all Foiv entities.
     *
     * @Route("/{id}/subinsts", name="subinsts")
     * @Method("GET")
     * @Template("ncuofoiv/Subinsts/index.html.twig")
     */
    public function indexAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($id);
        
        //$repository = $this->getDoctrine()->getRepository('NCUOFoivBundle:Foiv');
        //$foiv_children = $repository->findBy(array('superfoiv' => $id) );

        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }

        return array(
            'foiv'      => $foiv,
            'entities'    => $foiv->getFoivchildren(),
            'createURL'   => "/foiv/new",
        );
      
    }
    
}
?>