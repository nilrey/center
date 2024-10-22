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
use App\NCUO\PortalBundle\Entity\User;

use Doctrine\ORM\EntityManager;


/**
 * SearchFoivPassport controller.
 */
class SearchFoivPassport extends Controller
{
    
    /** 
     * Generate sql query to ( table, table_fields ) return array
     *
     */
    public function stFoiv( $foivId)
    {
        
		$em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery(
		'
		SELECT p FROM NCUOFoivBundle:Foiv p WHERE p.id=:foivId
		'
		)->setParameter('foivId', '48');
		
		$results = array(); // $query->getResult();

		return $results;
    }
    
}