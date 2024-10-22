<?php

namespace App\NCUO\RegionBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\RegionBundle\Controller\ErrorResponseGenerator;
use App\NCUO\RegionBundle\Entity\ORMObjectMetadata;
use App\NCUO\RegionBundle\Entity\Oiv;
use App\NCUO\RegionBundle\Entity\Section;
use App\NCUO\RegionBundle\Entity\Field;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

//use App\NCUO\OivBundle\Form\OivType;
//use App\NCUO\PortalBundle\Entity\User;

/**
 * SmallBusiness  controller.
 */
class SmallBusinessController extends Controller
{
    
    const SEC_ID = "s_small_business";

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
    private $pageTitle = 'Малые предприятия';

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
     * Finds and displays a Region entity.
     *
     * @Route("/{id_reg}/s_small_business", name="s_small_business")
     * @Method("GET")
     * @Template("ncuoregion/item_show.html.twig")
     */
    public function showAction($id_reg)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->rolePerms['read'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $em = $this->getDoctrine()->getManager();

        //$region = $em->getRepository('NCUORegionBundle:Oiv')->find($id_reg);
        $region = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv' => $id_reg, 'id_oiv_type' => 2), array('name' => 'ASC'), null , null );
        //$sections = $em->getRepository('NCUOOivBundle:Section')->findBy(array('id_oiv' => $id_reg, 'id_oiv_type' => 2), array('view_order' => 'ASC'), null , null ); // Получить все секции данного фоива
//        $sections = $em->getRepository('NCUOOivBundle:Section')->findBy(array('id_sec' => $id_reg.'__short_info'), array('view_order' => 'ASC'), null , null );
        $fields = $em->getRepository('NCUOOivBundle:Field')->findBy(array('id_sec' => $id_reg.'__'.$this::SEC_ID), array('view_order' => 'ASC'), null , null );
        foreach( $fields  as $key => $arValues) :
            $tmpData = trim($arValues->getData());
            if( $arValues->getIdFldContentType() == 1 )
            {
                $arValues->setData( htmlspecialchars_decode($tmpData ) );
                
            }
            else
            {
                $arValues->setData( ( $tmpData ) );
            }
        endforeach;
  //echo '<pre>',print_r($fields[0]),'</pre>';
        if (!$region) {
            throw $this->createNotFoundException('Unable to find Region entity.');
        }
        

        $tmpRes = $em->getRepository('NCUOOivBundle:Field')->findBy(array('id_fld' => $id_reg.'__common_info__flag_img'), array('view_order' => 'ASC'), null , null );
        foreach( $tmpRes  as $key => $arValues) :
            if( $arValues->getIdFldShort() == 'flag_img' )
            {
                $region[0]->setHeraldicImg($arValues->getData());
            }
        endforeach;

        $tabMenu["parent_id"] = 'economic_info'; 
        $tabMenu["current_id"] = $this::SEC_ID;            

        return array(
            'oiv'      => $region[0],
            'fields'      => $fields,
            'rolePerms' => $this->rolePerms,
            'urlEdit' => $this->generateUrl('s_small_business_edit', array('id_reg'=>$region[0]->getId() )),
            'tabmenu' => $tabMenu,
            'pageTitle' => $this->pageTitle,
        );
    
    }
    
    /**
     *
     * @Route("/{id_reg}/s_small_business/edit", name="s_small_business_edit")
     * @Method("GET")
     * @Template("ncuoregion/item_edit.html.twig")
     */
    public function editAction($id_reg)
    {
        if ( $this->rolePerms['update'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
        $arResult = $this->prepareEditFormData($id_reg);
        
        return $arResult;
    
    }
    
    /**
     * Update data
     * @Route("/{id_reg}/s_small_business/update", name="s_small_business_update")
     * @Method("POST")
     * @Template("ncuoregion/item_edit.html.twig")
     */
    
    public function updateAction(Request $request, $id_reg)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->rolePerms['update'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
        $arData = array();
        $message = array(
            'message_text'  => '',
            'message_error' => false,
            'error_explain' => ''
        );
        $em = $this->getDoctrine()->getManager();
        if ( is_array($request->request->get("FIELDS") ) )
        {
            $arData = $request->request->get("FIELDS");
        }
        
        if(count($arData) > 0 )
        {
            foreach( $arData as $key => $value)
            {
                $id_fld = $id_reg.'__'.$this::SEC_ID.'__'.$key;
                
                try{
                    $entity = $em->getRepository('NCUOOivBundle:Field')->find( $id_fld );
                    if( !empty($entity) )
                    {
                        $entity->setData(trim($value));
                        $em->flush();
                        $message['message_text'] = 'Данные успешно сохранены.';
                    }
                    
                }
                catch(\Exception $e){
                    $message = array(
                        'message_error' => true,
                        'message_text'  => $e->getMessage(),
                        'error_explain'  => $e->getTraceAsString()
                    );
                }
            }
        }
        //echo '<pre>',print_r($arData),'</pre>';

        $arResult = $this->prepareEditFormData($id_reg);
        $arResult['message'] = $message;
        
        return $arResult;
        
    }
    
    public function prepareEditFormData($id_reg)
    {     
        $em = $this->getDoctrine()->getManager();
        $region = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv' => $id_reg, 'id_oiv_type' => 2), array('name' => 'ASC'), null , null );
        if (!$region)
        {
            throw $this->createNotFoundException('Unable to find FoivContacts entity.');
            
        }
        else
        {
            $fields = $em->getRepository('NCUOOivBundle:Field')->findBy(array('id_sec' => $id_reg.'__'.$this::SEC_ID), array('view_order' => 'ASC'), null , null );
            foreach( $fields  as $key => $arValues) :
                $tmpData = trim($arValues->getData());
                if( $arValues->getIdFldContentType() == 1 ) // if type = image, then convert `&lq;` into `<`
                {
                    $arValues->setData( htmlspecialchars_decode($tmpData ) );
                }
                else
                {
                    //$arValues->setData( nl2br( $arValues->getData() ) );
                    $arValues->setData( $tmpData );
                }
            endforeach;
        }
        
        $tmpRes = $em->getRepository('NCUOOivBundle:Field')->findBy(array('id_fld' => $id_reg.'__common_info__flag_img'), array('view_order' => 'ASC'), null , null );
        foreach( $tmpRes  as $key => $arValues) :
            if( $arValues->getIdFldShort() == 'flag_img' )
            {
                $region[0]->setHeraldicImg($arValues->getData());
            }
        endforeach;
        
        //динамически формируем URL для  выполнения операции обновления контакта в БД
        $urlAction = $this->generateUrl('s_small_business_update', array('id_reg'=>$region[0]->getId() ));
        $urlView = $this->generateUrl('s_small_business_edit', array('id_reg'=>$region[0]->getId() ));
        $urlBack = $this->generateUrl('s_small_business', array('id_reg'=>$region[0]->getId() ));
        
        $tabMenu["parent_id"] = 'economic_info'; 
        $tabMenu["current_id"] = $this::SEC_ID;
        
        return array(
            'oiv'      => $region[0],
            'fields'   => $fields,
            "urlAction"=> $urlAction,
            "urlView"  => $urlView,
            "urlBack"  => $urlBack,
            'rolePerms' => $this->rolePerms,
            'tabmenu' => $tabMenu,
            'pageTitle' => $this->pageTitle,
            );
        
       
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
