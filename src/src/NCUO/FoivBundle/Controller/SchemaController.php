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
 * Schema controller.
  */
class SchemaController extends Controller
{
     /**
     * Lists all Foiv entities.
     *
     * @Route("/{id}/schema", name="schema")
     * @Method("GET")
     * @Template("ncuofoiv/Schema/index.html.twig")
     */
    public function indexAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($id);

        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        
        $schemaImagePath = $this->container->getParameter('ncuofoiv.path_img_foiv_schema')."/".$foiv->getId().'.png';
        if( is_file( $_SERVER["DOCUMENT_ROOT"] . $schemaImagePath ) ){
            $imageExists = true;
        }else{
            $imageExists = false;
        }
        
        return array(
            'foiv'      => $foiv,
            'imageExists' => $imageExists,
            'schemaImagePath' => $schemaImagePath,
            'urlFoivEdit' => $this->generateUrl( 'foiv_edit', array('id' => $foiv->getId() ) ),
        );
      
    }
}
?>