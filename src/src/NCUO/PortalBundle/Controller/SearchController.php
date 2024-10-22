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

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Search controller.
 */
class SearchController extends Controller
{
    private $logger;
    private $userRoleId = null;
    private $userRoleName = null;
    private $idOiv = null;
    private $parentUrl = 'oiv';
    private $security = null;
    private $session = null;
    private $request = null;
    private $isAccessGranted = null;
    private $isEditGranted = false;
    private $rolePerms = array();
    private $extraPerms = array();
    private $arPriorityRoles = array('ROLE_ROIV');
    
    public function __construct(Security $security, SessionInterface $session, ContainerInterface $container, RequestStack $requestStack, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->security = $security;
        $this->session = $session;
        $this->request = $requestStack->getMasterRequest();

        $loggedInUser = $security->getToken()->getUser();

        if(!empty($session->get("role_id"))) {
            $loggedInUser->setRoles($session->get("role"));
            $loggedInUser->setRole(intval($session->get("role_id")));
        }
        if($loggedInUser !== 'anon.'):
            $this->userRoleName = $loggedInUser->getRoles()[0];
            $this->userRoleId = $loggedInUser->getRole()[0];

            if( !empty($this->userRoleName) && in_array($this->userRoleName, $this->arPriorityRoles) ){
                $conn = $container->get('doctrine')->getManager()->getConnection();
                $stmt = $conn->prepare("SELECT * FROM cms.users as u where u.username =:username  LIMIT 1");
                $stmt->bindValue(':username',  $loggedInUser->getUsername() );
                $stmt->execute();        
                $arUser = $stmt->fetchAll()[0];
                if (isset($arUser['oiv_id']) ) $this->idOiv = $arUser['oiv_id'];

                if( !empty($this->request->attributes->get('id_reg') ) ){
                    if( $this->request->attributes->get('id_reg') === $this->idOiv ){
                        $this->extraPerms[$this->userRoleName][] = 'U';
                    }
                }
            }

            $this->getRolePerms();

        endif;
    }

    /** 
     * Lists all Users entities.
     *
     * @Route("/search", name="search")
     * @Method("GET")
     * @Template("ncuoportal/search.html.twig")
     */
    public function indexAction()
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->checkPageAccess() === false || $this->rolePerms['update'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $em = $this->getDoctrine()->getManager();
        $oivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array(), array( 'id_oiv_type' => 'ASC', 'name' => 'ASC'), null , null );


        return array(
            'urlAction' => $this->generateUrl('search_update', array() ),
            'FIELDS' => array(
                'search' => ''
            ),
            'oivs' => $oivs,
        );
    }

    /** 
     * Lists all Users entities.
     *
     * @Route("/search_update", name="search_update")
     * @Method("POST")
     * @Template("ncuoportal/search.html.twig")
     */
    public function updateAction(Request $request)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->checkPageAccess() === false || $this->rolePerms['update'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $em = $this->getDoctrine()->getManager();
        $oivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array(), array( 'id_oiv_type' => 'ASC', 'name' => 'ASC'), null , null );
        $arData = array('search'=>'');
        $arSearchResults = array();
        $total = null;
        $message = array(
            'message_text'  => '',
            'message_error' => false,
            'error_explain' => ''
        );
        $ifOivId = '';
        $ifOivName = '';

        if ( is_array($request->request->get("FIELDS") ) )
        {
            $arData = $request->request->get("FIELDS");
            $arData['search'] = trim($arData['search']);
            if( !empty( $arData['oiv_id']) ){
                $ifOivId =  trim($arData['oiv_id']) ;
                foreach ($oivs as $key => $obOiv) {
                    if( $obOiv->getId() == $ifOivId ){
                        $ifOivName = $obOiv->getName();
                    }
                }
                // $ifOivName =  trim($arData['oiv_name']) ;

            }
            if( !empty($arData['search']) ){

                $conn = $this->getDoctrine()->getManager()->getConnection();
                if(!empty($ifOivId)){
                    $stmt = $conn->prepare("
                        SELECT  oiv.id_oiv_type, oiv.name as oiv_name, sec.id_oiv, sec.name as sec_name, sec.id_sec, fld.*
                        FROM oivs_passports.\"oivs_pass_seсs_fields\" fld
                        LEFT JOIN  oivs_passports.oivs_pass_sections sec
                        ON fld.id_sec = sec.id_sec 
                        LEFT JOIN  oivs_passports.oivs oiv
                        ON oiv.id_oiv = sec.id_oiv
                        WHERE 
                        fld.id_fld NOT LIKE '%_img'
                        AND oiv.id_oiv = :ifOivId
                        AND fld.data LIKE :searchWord
                        ---LIMIT 10 OFFSET 0
                    ");
                    $stmt->bindValue('ifOivId', $ifOivId );
                }else{
                    $stmt = $conn->prepare("
                        SELECT  oiv.id_oiv_type, oiv.name as oiv_name, sec.id_oiv, sec.name as sec_name, sec.id_sec, fld.*
                        FROM oivs_passports.\"oivs_pass_seсs_fields\" fld
                        LEFT JOIN  oivs_passports.oivs_pass_sections sec
                        ON fld.id_sec = sec.id_sec 
                        LEFT JOIN  oivs_passports.oivs oiv
                        ON oiv.id_oiv = sec.id_oiv
                        WHERE 
                        fld.id_fld NOT LIKE '%_img'
                        AND fld.data LIKE :searchWord
                        ---LIMIT 10 OFFSET 0
                    ");
                }
                $stmt->bindValue('searchWord',  '%'.($arData['search']).'%' );
                $stmt->execute();        
                $arSearchResults = $stmt->fetchAll();
                
                // var_dump($arSearchResults);
                $detailTextRange = 200;
                if (count($arSearchResults) > 0 ){
                    foreach ($arSearchResults as $key => &$arSearchResult) {
                        $fullText = strip_tags($arSearchResult['data']);
                        $fullText = str_replace(array('&nbsp;', '>', '<', chr(34), chr(44), chr(46), chr(13), chr(10) ) , chr(32), $fullText);

                        $start = strpos($fullText, $arData['search'] );
                        $start < 0 ? $start = 0 : null;
                        $length =  strlen( $arData['search']) + $detailTextRange;
                        $fragment = substr($fullText, $start, $length);
                        $arFragments = explode(' ', $fragment);
                        $fragment = trim($arFragments[0]);
                        $arSearchResult['data_res'] = str_replace($arData['search'] , "<b>{$arData['search']}</b>", $fragment);
                        
                        $start = strpos($fullText, $arData['search']) - $detailTextRange;
                        $start < 0 ? $start = 0 : null;
                        $length =  strlen( $arData['search']) + $detailTextRange*2;
                        $fragment = substr($fullText, $start, $length);
                        $start = strpos($fragment, ' ' );
                        $end = strrpos($fragment, ' ' );
                        $fragment = substr($fragment, $start, $end-$start);
                        $arSearchResult['data_res_txt'] = str_replace($arData['search'] , "<b>{$arData['search']}</b>", $fragment);
                        if(empty($arSearchResult['data_res_txt'])) $arSearchResult['data_res_txt'] = $arSearchResult['data_res'];
                        $arSearchResult['data_res_txt'] = "...{$arSearchResult['data_res_txt']}...";
                        // if($key == 0){
                        //     for ($i=0; $i<10; $i++) {
                        //         echo ord($arSearchResult['data_res_txt'][$i])."<br>";
                        //     }
                        // }

                        $oiv = $arSearchResult['id_oiv_type'] == 2 ? 'region' : 'oiv' ;
                        // $arSearchResult['oiv_name'] = $arSearchResult['id_oiv_type'] == 2 ? 'region' : 'oiv' ;
                        $arSearchResult['data_res_link'] = "/public/index.php/{$oiv}/".str_replace('__', '/', $arSearchResult['id_sec']);
                    }
                }

                $stmt = $conn->prepare("SELECT count(*) as cnt FROM oivs_passports.\"oivs_pass_seсs_fields\" fld WHERE fld.id_fld NOT LIKE '%_img' AND fld.data LIKE :searchWord ");
                $stmt->bindValue('searchWord',  '%'.$arData['search'].'%' );
                $stmt->execute();        
                $arStatResults = $stmt->fetchAll();
                // var_dump($arStatResults[0]['cnt']);
                $pageLength = 10;
                $total = $arStatResults[0]['cnt'];
/*                $cntPages = ceil( $total / $pageLength );
                var_dump($cntPages);
                $arPages = array(
                    'curPage' => '1',
                    'nexPage' => '2',
                    'prevPage' => '1',
                );*/

            }
        }
        // var_dump($ifOivId);

        return array(
            'urlAction' => $this->generateUrl('search_update', array() ),
            'FIELDS' => array(
                'search' => $arData['search']
            ),
            'entities' => $arSearchResults,
            'oivs' => $oivs,
            'total' => $total,
            'ifOivId' => $ifOivId,
            'ifOivName' => $ifOivName,
            // 'cntPages' => $cntPages,
            // 'pages' => $arPages
        );
    }

    public function checkPageAccess(){
        if( $this->isAccessGranted === null ){
            $symfRoute = $this->request->getPathInfo();
            $symfRoute = str_replace('/', '', $symfRoute);

            $conn = $this->getDoctrine()->getManager()->getConnection();
            $sql = '
                SELECT mi.*, mip.parent_id as isParent
                FROM cms.menu_items as mi
                LEFT JOIN cms.menu_roles as mr ON mi.id=mr.menu_id
                LEFT JOIN cms.roles as r ON r.id=mr.role_id
                LEFT JOIN ( SELECT parent_id FROM cms.menu_items WHERE parent_id IS NOT NULL GROUP BY parent_id ) AS mip ON mip.parent_id=mi.id
                WHERE 
                r.id = :id_role
                ORDER BY mi.item_position ASC
            ';
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id_role'=>$this->userRoleId]);
            $listPages = $stmt->fetchAll();

            $arMMenu = array();
            $arSubMenu = array();
            $isAccessGranted = false;
            foreach ($listPages as $arPage) {
                $arPage['url_alias'] = str_replace(array('/public/index.php', '/public', '/'), '', $arPage['url']);
                if ( !empty($arPage['url_alias'])) {
                    if( !$isAccessGranted && strpos( $symfRoute, $arPage['url_alias']) !== false) {
                        $isAccessGranted = true;
                        break;
                    }
                }
            }
            $this->isAccessGranted = $isAccessGranted;
        }
        return $this->isAccessGranted;
    }

    public function getRolePerms(){
        $arPerms = array('R');
        if($this->userRoleName == 'ROLE_ADMIN'){
            $arPerms = array('C','R','U','D');
        }else{
            if(count($this->extraPerms ) > 0 ){
                foreach ($this->extraPerms as $role => $arRolePerms) {
                    if($this->userRoleName == $role ){
                        foreach ($arRolePerms as $value) {
                            $arPerms[] = $value;
                        }
                        array_unique($arPerms);
                        break;
                    }
                }
            }
        }
        $this->rolePerms = array(
            'create' => in_array('C', $arPerms) ? true : false,
            'read' =>   in_array('R', $arPerms) ? true : false,
            'update' => in_array('U', $arPerms) ? true : false,
            'delete' => in_array('D', $arPerms) ? true : false,
        );
    }
    
    public function isAuthActive()
    {
        $loggedInUser = $this->security->getToken()->getUser();
        if($loggedInUser !== 'anon.'):
            return true;
        endif;

        return false;
    }

}
