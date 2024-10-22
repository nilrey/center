<?php

namespace App\NCUO\OivBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\OivBundle\Controller\ErrorResponseGenerator;
use App\NCUO\OivBundle\Entity\ORMObjectMetadata;
use App\NCUO\OivBundle\Entity\Oiv;
use App\NCUO\OivBundle\Entity\Section;
use App\NCUO\OivBundle\Entity\Field;
use Doctrine\ORM\EntityRepository;
//use App\NCUO\OivBundle\Form\OivType;
//use App\NCUO\PortalBundle\Entity\User;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * StructureScheme controller.
 */
class StructureSchemeController extends Controller
{
    const SEC_ID = "structure_scheme";
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
    private $arPriorityRoles = array('ROLE_FOIV');
    private $pageTitle = 'Структура';


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

                if( !empty($this->request->attributes->get('id_oiv') ) ){
                    if( $this->request->attributes->get('id_oiv') === $this->idOiv ){
                        $this->extraPerms[$this->userRoleName][] = 'U';
                    }
                }
            }

            $this->getRolePerms();

        endif;
    }
        
    /**
     * Finds and displays a structure_scheme entity.
     *
     * @Route("/{id_oiv}/structure_scheme", name="structure_scheme_show")
     * @Method("GET")
     * @Template("ncuooiv/structure_scheme/show.html.twig")
     */
    public function showAction($id_oiv)
    {
        if ( $this->rolePerms['read'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $em = $this->getDoctrine()->getManager();

        $oiv = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv' => $id_oiv, 'id_oiv_type' => 1), array('name' => 'ASC'), null , null );
        if (!$oiv) {
            throw $this->createNotFoundException('Unable to find Oiv entity.');
            $fields = App\NCUO\OivBundle\Entity\Field;
        }
        else
        {
            $fields = $em->getRepository('NCUOOivBundle:Field')->findBy(array('id_sec' => $id_oiv.'__'.$this::SEC_ID), array('view_order' => 'ASC'), null , null );
            foreach( $fields  as $key => $arValues) :
                if( $arValues->getIdFldContentType() == 1 ) // if type = image, then convert `&lq;` into `<`
                {
                    $tmpData = $arValues->getData();
                    $arValues->setData( htmlspecialchars_decode($tmpData ) );
                }
                else
                {
                    $arValues->setData( nl2br( $arValues->getData() ) );
                }
            endforeach;

            $tmpRes = $em->getRepository('NCUOOivBundle:Field')->findBy(array('id_fld' => $id_oiv.'__short_info__heraldic_img'), array('view_order' => 'ASC'), null , null );
            foreach( $tmpRes  as $key => $arValues) :
                if( $arValues->getIdFldShort() == 'heraldic_img' )
                {
                    $oiv[0]->setHeraldicImg($arValues->getData());
                }
            endforeach;            
            
        }
        
        $tabMenu["parent_id"] = 'executives_info'; 
        $tabMenu["current_id"] = $this::SEC_ID;

        return array(
            'oiv'      => $oiv[0],
            'fields'      => $fields,
            'rolePerms' => $this->rolePerms,
            'urlEdit' => $this->generateUrl('structure_scheme_edit', array('id_oiv'=>$oiv[0]->getId() )),
            'tabmenu' => $tabMenu,
            'pageTitle' => $this->pageTitle,
        );
    
    }
    

    /**
     * Отображение формы для редактирования  
     *
     * @Route("/{id_oiv}/structure_scheme/edit", name="structure_scheme_edit")
     * @Method("GET")
     * @Template("ncuooiv/structure_scheme/edit.html.twig")
     */
    public function editAction($id_oiv)
    {
        if ( $this->rolePerms['update'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
        $arResult = $this->prepareEditFormData($id_oiv);
        
        return $arResult;
    
    }
    
    /**
     * Update data 
     * @Route("/{id_oiv}/structure_scheme/update", name="structure_scheme_update")
     * @Method("POST")
     * @Template("ncuooiv/structure_scheme/edit.html.twig")
     */
    
    public function updateAction(Request $request, $id_oiv)
    {
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
                //$id_fld = $id_oiv.'__'.$this::SEC_ID.'__'.$key;
                
                try{
                    $entity = $em->getRepository('NCUOOivBundle:Field')->find( $key );
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

        $arResult = $this->prepareEditFormData($id_oiv);
        $arResult['message'] = $message;
        
        return $arResult;
        
    }
    
    public function prepareEditFormData($id_oiv, $data_to_html = false)
    {
        //пытаемся получить информацию о выбранном контакте        
        $em = $this->getDoctrine()->getManager();
        $oiv = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv' => $id_oiv, 'id_oiv_type' => 1), array('name' => 'ASC'), null , null );
        if (!$oiv)
        {
            $fields = App\NCUO\OivBundle\Entity\Field;
            throw $this->createNotFoundException('Unable to find Oiv entity.');
            
        }
        else
        {
            $fields = $em->getRepository('NCUOOivBundle:Field')->findBy(array('id_sec' => $id_oiv.'__'.$this::SEC_ID), array('view_order' => 'ASC'), null , null );
            foreach( $fields  as $key => $arValues) :
                $tmpData = trim($arValues->getData());
                if( $arValues->getIdFldContentType() == 1 ) // if type = image, then convert `&lq;` into `<`
                {
                    $arValues->setData( htmlspecialchars_decode($tmpData ) );
                }
                else
                {
                    //$arValues->setData( nl2br( $arValues->getData() ) );
                    if( $data_to_html )
                        $arValues->setData( nl2br($tmpData) );
                    else
                        $arValues->setData( $tmpData );
                }
            endforeach;
        }

        $tmpRes = $em->getRepository('NCUOOivBundle:Field')->findBy(array('id_fld' => $id_oiv.'__short_info__heraldic_img'), array('view_order' => 'ASC'), null , null );
        foreach( $tmpRes  as $key => $arValues) :
            if( $arValues->getIdFldShort() == 'heraldic_img' )
            {
                $oiv[0]->setHeraldicImg($arValues->getData());
            }
        endforeach;
        
        //динамически формируем URL для  выполнения операции обновления контакта в БД
        $urlAction = $this->generateUrl('structure_scheme_update', array('id_oiv'=>$oiv[0]->getId() ));
        $urlView = $this->generateUrl('structure_scheme_edit', array('id_oiv'=>$oiv[0]->getId() ));
        $urlBack = $this->generateUrl('structure_scheme_show', array('id_oiv'=>$oiv[0]->getId() ));
        
        $tabMenu["parent_id"] = 'executives_info'; 
        $tabMenu["current_id"] = $this::SEC_ID;

        return array(
            'oiv'      => $oiv[0],
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
    
}
