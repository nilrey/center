<?php

namespace App\NCUO\PortalBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\PortalBundle\Controller\ErrorResponseGenerator;
use App\NCUO\PortalBundle\Entity\ORMObjectMetadata;
use App\NCUO\OivBundle\Entity\Oiv;
use App\NCUO\OivBundle\Entity\Section;
use App\NCUO\OivBundle\Entity\Field;
use Doctrine\ORM\EntityRepository;
//use App\NCUO\OivBundle\Form\OivType;
use App\Entity\User;
use App\Entity\Role;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * AdminMenu controller.
 */
class UrlManagerController extends Controller
{
    /** 
     * Lists all Users entities.
     *
     * @Route("/admin/url_manager", name="url_manager")
     * @Method("GET")
     */
    public function indexAction(Security $security, SessionInterface $session )
    {
        $redirectUrl = null;
        $loggedInUser = $security->getToken()->getUser();
        if(!empty($session->get("role_id"))) {
            $loggedInUser->setRoles($session->get("role"));
            $loggedInUser->setRole(intval($session->get("role_id")));
        }

        $conn = $this->getDoctrine()->getManager()->getConnection();
        
        $sql = "SELECT mi.url FROM cms.menu_items as mi, cms.menu_roles as mr WHERE mr.role_id=:id_role AND mr.menu_id=mi.id AND mi.url != '' order by item_position ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id_role',  $loggedInUser->getRole() );
        $stmt->execute();        
        $roleAllowedUrls = $stmt->fetchAll();
        if(count($roleAllowedUrls) > 0){
            foreach ($roleAllowedUrls as $allowedUrl) {
                $aliasUrl = str_replace(array('/public/index.php', '/public', '/'), '', $allowedUrl['url'] );
                if( empty($redirectUrl) && substr($aliasUrl, 0, 4) != 'http' ) $redirectUrl = $allowedUrl['url'];
            }
        }

        if( empty($redirectUrl) ) return  $this->redirect($this->generateUrl("user_empty_page"));

        return  $this->redirect($redirectUrl);
    }

    /**
     * Update data 
     * @Route("/oiv/user_empty_page", name="user_empty_page")
     * @Method("GET")
     * @Template("ncuoportal/user_empty_page.html.twig")
     */
    
    public function userEmptyPage(){
        
        return array();
    }
    
        
}
