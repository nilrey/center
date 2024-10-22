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
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * AdminRoles controller.
 */
class SyncPspOivController extends Controller
{
    private $logger;
    private $userRoleId = null;
    private $userRoleName = null;
    private $idOiv = null;
    private $security = null;
    private $session = null;
    private $request = null;
    private $isAccessGranted = null;
    private $isEditGranted = false;
    private $rolePerms = array();
    private $extraPerms = array();
    private $arPriorityRoles = array('ROLE_ADMIN');
    
    public function __construct(Security $security, SessionInterface $session, UserPasswordEncoderInterface $passwordEncoder, ContainerInterface $container, RequestStack $requestStack, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->security = $security;
        $this->session = $session;
        $this->request = $requestStack->getMasterRequest();

        $this->passwordEncoder = $passwordEncoder;
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
     * Lists all Users entities.
     *
     * @Route("/admin/syncpspoiv", name="syncpspoiv_list")
     * @Method("GET")
     * @Template("ncuoportal/admin/syncpspoiv_list.html.twig")
     */
    public function indexAction()
    {

        $loggedInUser = $this->security->getToken()->getUser();
        if($loggedInUser === 'anon.') return $this->redirect($this->generateUrl('login', array() ) );
        
        if ( $this->checkPageAccess() === false ) return $this->redirect($this->generateUrl('url_error', array() ) );

        if( $this->aldAuthUserCheck() === false ){
            return $this->redirect($this->generateUrl('ald_user_error', array() ) );
        }

        $conn = $this->getDoctrine()->getManager()->getConnection();
        $stmt = $conn->prepare('SELECT * FROM oivs_passports.oivs_triggers_params ORDER BY id ASC');
        $stmt->execute();
        $listRoles = $stmt->fetchAll();
       
        return array(
            'listRoles' => $listRoles,
        );
    }
        
    /**
     * Load New Roles Item form.
     *
     * @Route("/admin/syncpspoiv/new", name="syncpspoiv_new")
     * 
     * @Template("ncuoportal/admin/syncpspoiv_new.html.twig")
     */
    public function actionNew()
    {
        
        $arResult = array();
        $arResult['urlAction'] = $this->generateUrl('syncpspoiv_create');
        $arResult['urlBack'] = $this->generateUrl('syncpspoiv_list');  

        $arResult['menuItem'] = array(
           "name" => "",
           "value" => "",
           "default_value" => "",
           "comment" => "",
            );
        
        return $arResult;
    
    }
        
    /**
     * Delete Roles Item
     *
     * @Route("/admin/syncpspoiv/{id_param}/delete", name="syncpspoiv_delete")
     * 
     * @Template("ncuoportal/admin/syncpspoiv_list.html.twig")
     */
    public function actionDelete($id_param)
    {
        $message = array(
            'message_text'  => '',
            'message_error' => false,
            'error_explain' => ''
        );
        if( ! $this->isUserGranted() ){
            return $this->render('ncuoportal/admin/access_deny.html.twig');
        }
        $conn = $this->getDoctrine()->getManager()->getConnection();

        if ( $this->isUserGranted() ){

            $sql_tmpl = 'DELETE FROM oivs_passports.oivs_triggers_params WHERE id=:id_param';
            $stmt = $conn->prepare($sql_tmpl);
            $stmt->bindValue(':id_param', intval($id_param) );
            $res = $stmt->execute();

            $message['message_text'] = "Запись успешно удалена";
                
        }else{
            $message = array(
                'message_text'  => 'У вас нет доступа к данному функционалу.',
                'message_error' => true,
                'error_explain' => ''
            );

        }

        $stmt = $conn->prepare('SELECT * FROM oivs_passports.oivs_triggers_params ORDER BY id ASC');        
        $stmt->execute();        
        $listRoles = $stmt->fetchAll();
       
        return array(
            'listRoles' => $listRoles,
            'message' => $message,
        );

    }
    
    
    /**
     * Save new item roles
     *
     * @Route("/admin/syncpspoiv/create", name="syncpspoiv_create")
     * @Method("POST")
     * @Template("ncuoportal/admin/syncpspoiv_new.html.twig")
     */
    public function actionCreateNew(Request $request)
    {
        $arResult = array();
        $arData = array();
        $message = array(
            'message_text'  => '',
            'message_error' => false,
            'error_explain' => ''
        );

        $conn = $this->getDoctrine()->getManager()->getConnection();
        if ( is_array($request->request->get("FIELDS") ) )
        {
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = trim($value);
            }

            if( empty($arData['name']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "Поле `Наименование` должно быть заполнено.",
                    'error_explain' => ''
                ); 
            }
            elseif( preg_match("/[^A-z_]/", $arData['name']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "В поле `Наименование` должны использоваться только латинские буквы и знак нижнего подчеркивания _.",
                    'error_explain' => ''
                ); 
            }
            elseif( empty($arData['value']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "Поле `Значение` должно быть заполнено.",
                    'error_explain' => ''
                ); 
            }
            elseif( empty($arData['comment']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "Поле `Комментарий к параметру` должно быть заполнено.",
                    'error_explain' => ''
                ); 
            }
            else
            {
                $stmt = $conn->prepare('SELECT max(id) FROM oivs_passports.oivs_triggers_params');
                $stmt->execute();        
                $id_max = $stmt->fetchAll();
                $id_insert = $id_max[0]['max']+1;
                $sql_tmpl = 'INSERT INTO oivs_passports.oivs_triggers_params (id, name, value, default_value, comment  ) VALUES ( :id_param, :name, :value, :default_value, :comment )';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $stmt->bindValue(':name', trim($arData['name']));
                $stmt->bindValue(':value', trim($arData['value']) );
                $stmt->bindValue(':default_value', trim($arData['default_value']) );
                $stmt->bindValue(':comment', trim($arData['comment']));
                $stmt->bindValue(':id_param', intval($id_insert) );
                $res = $stmt->execute();

                $message['message_text'] = "Новая запись успешно создана";
                
                $arData = array(
                       "name" => "",
                       "value" => "",
                       "default_value" => "",
                       "comment" => "",
                    );
            }
        }else{
            $arData = array(
                   "name" => "",
                   "value" => "",
                   "default_value" => "",
                   "comment" => "",
                );
        }

        $arResult['urlAction'] = $this->generateUrl('syncpspoiv_create', array() );
        $arResult['urlBack'] = $this->generateUrl('syncpspoiv_list');
        $arResult['menuItem'] = $arData;
        $arResult['message'] = $message;

        return $arResult;
    }
    
    
    /**
     * Отображение формы для редактирования существующей записи
     *
     * @Route("/admin/syncpspoiv/{id_param}/edit", name="syncpspoiv_edit")
     * @Method("GET")
     * @Template("ncuoportal/admin/syncpspoiv_edit.html.twig")
     */
    public function editAction($id_param)
    {

        $arResult = $this->prepareEditFormData($id_param);
        
        return $arResult;
    
    }
    
    /**
     * Update item roles data
     *  
     * @Route("/admin/syncpspoiv/{id_param}/update", name="syncpspoiv_update")
     * @Method("POST")
     * @Template("ncuoportal/admin/syncpspoiv_edit.html.twig")
     */
    
    public function updateAction(Request $request, $id_param){
        $arData = array();
        $message = array(
            'message_text'  => '',
            'message_error' => false,
            'error_explain' => ''
        );
        $em = $this->getDoctrine()->getManager();

        if ( is_array($request->request->get("FIELDS") ) )
        {
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = trim($value);
            }

            if( empty($arData['name']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "Поле `Наименование` должно быть заполнено.",
                    'error_explain' => ''
                ); 
            }
            elseif( preg_match("/[^A-z_]/", $arData['name']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "В поле `Наименование` должны использоваться только латинские буквы и знак нижнего подчеркивания _.",
                    'error_explain' => ''
                ); 
            }
            elseif( empty($arData['value']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "Поле `Значение` должно быть заполнено.",
                    'error_explain' => ''
                ); 
            }
            elseif( empty($arData['comment']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "Поле `Комментарий к параметру` должно быть заполнено.",
                    'error_explain' => ''
                ); 
            }
            else
            {
                try{
                    $sql_tmpl = 'UPDATE oivs_passports.oivs_triggers_params SET';
                    $sql_tmpl .= ' name=:name, value=:value, default_value=:default_value, comment=:comment WHERE id=:id_param';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $stmt->bindValue(':name', trim($arData['name']));
                    $stmt->bindValue(':value', trim($arData['value']) );
                    $stmt->bindValue(':default_value', trim($arData['default_value']) );
                    $stmt->bindValue(':comment', trim($arData['comment']));
                    $stmt->bindValue(':id_param', intval($id_param) );
                    $res = $stmt->execute();

                    $message['message_text'] = "Изменения успешно сохранены.";

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

        $arResult = $this->prepareEditFormData($id_param);
        if( count($arData) > 0 ){
            foreach ($arData as $key => $value) {
                $arResult['menuItem'][$key] = trim($value);
            }
        }
        $arResult['message'] = $message;
        
        return $arResult;
        
    }
    
    public function prepareEditFormData($id_param)
    {
        $conn = $this->getDoctrine()->getManager()->getConnection();

        $stmt = $conn->prepare('SELECT * FROM oivs_passports.oivs_triggers_params WHERE id=:id_param');

        $stmt->execute(["id_param"=>$id_param]);        
        $arItem = $stmt->fetchAll();

        // динамически формируем URL для  выполнения операции обновления пользователя в БД
        $urlAction = $this->generateUrl('syncpspoiv_update', array('id_param'=>$arItem[0]["id"] ));
        $urlView = $this->generateUrl('syncpspoiv_list');
        $urlBack = $this->generateUrl('syncpspoiv_list');
        
        return array(
            "urlAction"=> $urlAction,
            "urlView"  => $urlView,
            "urlBack"  => $urlBack,
            "menuItem" => $arItem[0],
            );
       
    }

    public function isUserGranted(){
        $user = $this->getUser();
        if( in_array( $user->getRole(), array(1))  )
        {
            return true;
        }
        return false;
    }

    public function checkUserOivId($id){
        if ($this->isGranted('ROLE_FOIV')){
            $userId = $this->getUser()->getId();
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('NCUOPortalBundle:User')->find($userId);
            $user_oiv = $user->getOiv();
            $user_oiv_id = $user_oiv->getId();
        }else{
            $user_oiv_id = $id;
        }
        return $user_oiv_id;
    }

    public function aldAuthUserCheck(){
        if ( 'on' === strtolower($_ENV['ALD_AUTH_ON'] ) ){
            $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
            if( $loggedInUser->getUsername() == 'ALD_AUTH_USER' ){
                return false;
            }
        }
        return true;
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
}
