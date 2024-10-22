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
 * AdminMenu controller.
 */
class AdminMenuController extends Controller
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
     * @Route("/admin/menu", name="oiv_admin_menu")
     * @Method("GET")
     * @Template("ncuoportal/admin/menu_list.html.twig")
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
        $stmt = $conn->prepare('
            SELECT mi.*, t1.roles FROM 
            (
                SELECT mr.menu_id, string_agg( r.description, \', \' ) AS roles
                FROM ( SELECT * FROM cms.menu_roles order by menu_id, role_id ) AS mr
                LEFT JOIN cms.roles AS r ON r.id=mr.role_id
                GROUP BY mr.menu_id 
                ORDER BY mr.menu_id 
            ) AS t1
            RIGHT JOIN cms.menu_items AS mi ON mi.id=t1.menu_id
            ORDER BY mi.item_name ASC
            ');       
        $stmt->execute();        
        $listMenu = $stmt->fetchAll();
       
        return array(
            'listMenu' => $listMenu,
        );
    }
        
    /**
     * Load New Menu Item form.
     *
     * @Route("/admin/menu/new", name="oiv_admin_menu_new")
     * 
     * @Template("ncuoportal/admin/menu_new.html.twig")
     */
    public function actionNew()
    {
        
        $arResult = array();
        $arResult['urlAction'] = $this->generateUrl('oiv_admin_menu_create');
        $arResult['urlBack'] = $this->generateUrl('oiv_admin_menu');

        $conn = $this->getDoctrine()->getManager()->getConnection();

        $stmt = $conn->prepare('
            SELECT mi.*, t1.roles FROM 
            (
                SELECT mr.menu_id, string_agg( r.description, \', \' ) AS roles
                FROM ( SELECT * FROM cms.menu_roles order by menu_id, role_id ) AS mr
                LEFT JOIN cms.roles AS r ON r.id=mr.role_id
                GROUP BY mr.menu_id 
                ORDER BY mr.menu_id 
            ) AS t1
            RIGHT JOIN cms.menu_items AS mi ON mi.id=t1.menu_id
            ORDER BY mi.item_name ASC
            ');       
        $stmt->execute();        
        $listMenu = $stmt->fetchAll();

        $stmt = $conn->prepare('SELECT *, 0 as selected FROM cms.roles ORDER BY description ASC');    
        $stmt->execute();
        $arGrantedUsers = $stmt->fetchAll();

        $arResult['menuItem'] = array(
           "item_name" => "",
           "parent_id" => "",
           "item_position" => "",
           "url" => "",
            );
        $arResult['arGrantedUsers'] = $arGrantedUsers;
        $arResult['listMenu'] = $listMenu;
        //$arResult = $this->prepareEditFormData(0, true);
        
        return $arResult;
    
    }
        
    /**
     * Delete Menu Item
     *
     * @Route("/admin/menu/{id_menu}/delete", name="oiv_admin_menu_delete")
     * 
     * @Template("ncuoportal/admin/menu_list.html.twig")
     */
    public function actionDelete($id_menu)
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

        if ($this->isGranted('ROLE_ADMIN')){

            $sql_tmpl = 'DELETE FROM cms.menu_roles WHERE menu_id=:id_menu';
            $stmt = $conn->prepare($sql_tmpl);
            $stmt->bindValue(':id_menu', intval($id_menu) );
            $res = $stmt->execute();

            $sql_tmpl = 'DELETE FROM cms.menu_items WHERE id=:id_menu';
            $stmt = $conn->prepare($sql_tmpl);
            $stmt->bindValue(':id_menu', intval($id_menu) );
            $res = $stmt->execute();
            $message['message_text'] = "Запись успешно удалена";
                
        }else{
            $message = array(
                'message_text'  => 'У вас нет доступа к данному функционалу.',
                'message_error' => true,
                'error_explain' => ''
            );

        }

        $stmt = $conn->prepare('
            SELECT mi.*, t1.roles FROM 
            (
                SELECT mr.menu_id, string_agg( r.description, \', \' ) AS roles
                FROM ( SELECT * FROM cms.menu_roles order by menu_id, role_id ) AS mr
                LEFT JOIN cms.roles AS r ON r.id=mr.role_id
                GROUP BY mr.menu_id 
                ORDER BY mr.menu_id 
            ) AS t1
            RIGHT JOIN cms.menu_items AS mi ON mi.id=t1.menu_id
            ORDER BY mi.item_name ASC
            ');       
        $stmt->execute();        
        $listMenu = $stmt->fetchAll();
       
        return array(
            'listMenu' => $listMenu,
            'message' => $message,
        );

    }
    
    
    /**
     * Save new item menu
     *
     * @Route("/admin/menu/create", name="oiv_admin_menu_create")
     * @Method("POST")
     * @Template("ncuoportal/admin/menu_new.html.twig")
     */
    public function actionCreateNew(Request $request)
    {
        $arResult = array();
        $message = array(
            'message_text'  => '',
            'message_error' => false,
            'error_explain' => ''
        );
        $conn = $this->getDoctrine()->getManager()->getConnection();
        if ( is_array($request->request->get("FIELDS") ) )
        {
            $arData = $request->request->get("FIELDS");
        }else{
            $arData = array(
                   "item_name" => "",
                   "parent_id" => "",
                   "item_position" => "",
                   "url" => ""
                );
        }

        if( empty($arData['item_name']) )
        {
            $message = array(
                'message_error' => true,
                'message_text'  => "Поле `Наименование` должно быть заполнено.",
                'error_explain' => ''
            ); 
        }
        else
        {
            intval($arData['parent_id']) < 1 ? $arData['parent_id'] = null : $arData['parent_id'] = intval($arData['parent_id']) ;
            intval($arData['item_position']) < 1 ? $arData['item_position'] = null : $arData['item_position'] = intval($arData['item_position']) ;
            $stmt = $conn->prepare('SELECT max(id) FROM cms.menu_items');
            $stmt->execute();        
            $id_max = $stmt->fetchAll();
            $id_insert = $id_max[0]['max']+1;
            $sql_tmpl = 'INSERT INTO cms.menu_items (id, item_name, parent_id, item_position, url  ) VALUES (:id , :item_name, :parent_id, :item_position, :url )';
            $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
            $stmt->bindValue(':id', $id_insert );
            $stmt->bindValue(':item_name', trim($arData['item_name']));
            $stmt->bindValue(':parent_id', intval($arData['parent_id']) );
            $stmt->bindValue(':item_position', intval($arData['item_position']));
            $stmt->bindValue(':url', $arData['url']);
            $res = $stmt->execute();

            if( isset($arData['roles']) && count($arData['roles']) > 0 && intval($id_insert) > 0 ){
                foreach ($arData['roles'] as $value) {
                    $sql_tmpl = 'INSERT INTO cms.menu_roles (menu_id, role_id) VALUES (:id_menu , :id_role)';
                    $stmt = $conn->prepare($sql_tmpl);
                    $stmt->bindValue(':id_menu', intval($id_insert) );
                    $stmt->bindValue(':id_role', intval($value) );
                    $res = $stmt->execute();
                }
            }
            $message['message_text'] = "Новая запись успешно создана";
            
            $arData = array(
                   "item_name" => "",
                   "parent_id" => "",
                   "item_position" => "",
                   "url" => ""
                );
        }

        $stmt = $conn->prepare('SELECT *, 0 as selected FROM cms.roles ORDER BY description ASC');    
        $stmt->execute();   
        $arGrantedUsers = $stmt->fetchAll();

        $stmt = $conn->prepare('
            SELECT mi.*, t1.roles FROM 
            (
                SELECT mr.menu_id, string_agg( r.description, \', \' ) AS roles
                FROM ( SELECT * FROM cms.menu_roles order by menu_id, role_id ) AS mr
                LEFT JOIN cms.roles AS r ON r.id=mr.role_id
                GROUP BY mr.menu_id 
                ORDER BY mr.menu_id 
            ) AS t1
            RIGHT JOIN cms.menu_items AS mi ON mi.id=t1.menu_id
            ORDER BY mi.item_name ASC
            ');       
        $stmt->execute();        
        $listMenu = $stmt->fetchAll();

        $arResult['urlAction'] = $this->generateUrl('oiv_admin_menu_create', array() );
        $arResult['menuItem'] = $arData;
        $arResult['message'] = $message;
        $arResult['urlBack'] = $this->generateUrl('oiv_admin_menu');
        $arResult['arGrantedUsers'] = $arGrantedUsers;
        $arResult["listMenu"] = $listMenu;

        return $arResult;
    }
    
    
    /**
     * Отображение формы для редактирования существующей записи
     *
     * @Route("/admin/menu/{id_menu}/edit", name="oiv_admin_menu_edit")
     * @Method("GET")
     * @Template("ncuoportal/admin/menu_edit.html.twig")
     */
    public function editAction($id_menu)
    {

        $arResult = $this->prepareEditFormData($id_menu);
        
        return $arResult;
    
    }
    
    /**
     * Update item menu data
     *  
     * @Route("/admin/menu/{id_menu}/update", name="oiv_admin_menu_update")
     * @Method("POST")
     * @Template("ncuoportal/admin/menu_edit.html.twig")
     */
    
    public function updateAction(Request $request, $id_menu){
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
            try{
                intval($arData['parent_id']) < 1 ? $arData['parent_id'] = null : $arData['parent_id'] = intval($arData['parent_id']) ;
                intval($arData['item_position']) < 1 ? $arData['item_position'] = null : $arData['item_position'] = intval($arData['item_position']) ;
                $sql_tmpl = 'UPDATE cms.menu_items SET';
                if (!empty($arData['item_name'])) $sql_tmpl .= ' item_name=:item_name, ';
                $sql_tmpl .= ' parent_id=:parent_id, item_position=:item_position, url=:url WHERE id=:id_menu';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                if (!empty($arData['item_name'])) $stmt->bindValue(':item_name', trim($arData['item_name']));
                $stmt->bindValue(':parent_id', $arData['parent_id']);
                $stmt->bindValue(':item_position', $arData['item_position']);
                $stmt->bindValue(':url', $arData['url']);
                $stmt->bindValue(':id_menu', intval($id_menu) );
                $res = $stmt->execute();

                // $sql_tmpl = 'UPDATE cms.menu_roles SET';
                $sql_tmpl = 'DELETE FROM cms.menu_roles WHERE menu_id=:id_menu';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $stmt->bindValue(':id_menu', intval($id_menu) );
                $res = $stmt->execute();

                if( is_array($arData['roles']) &&  count($arData['roles']) > 0 ){
                    foreach ($arData['roles'] as $value) {
                        $sql_tmpl = 'INSERT INTO cms.menu_roles (menu_id, role_id) VALUES (:id_menu , :id_role)';
                        $conn = $this->container->get('doctrine.orm.entity_manager')->getConnection();
                        $stmt = $conn->prepare($sql_tmpl);
                        $stmt->bindValue(':id_menu', intval($id_menu) );
                        $stmt->bindValue(':id_role', intval($value) );
                        $res = $stmt->execute();                
                    }
                }
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

        $arResult = $this->prepareEditFormData($id_menu);
        $arResult['message'] = $message;
        
        return $arResult;
        
    }
    
    public function prepareEditFormData($id_menu, $data_to_html = false)
    {
        $conn = $this->getDoctrine()->getManager()->getConnection();

        $stmt = $conn->prepare('
            SELECT mi.*, t1.roles FROM 
            (
                SELECT mr.menu_id, string_agg( r.description, \', \' ) AS roles
                FROM ( SELECT * FROM cms.menu_roles order by menu_id, role_id ) AS mr
                LEFT JOIN cms.roles AS r ON r.id=mr.role_id
                GROUP BY mr.menu_id 
                ORDER BY mr.menu_id 
            ) AS t1
            RIGHT JOIN cms.menu_items AS mi ON mi.id=t1.menu_id
            WHERE mi.parent_id IS NULL
            ORDER BY mi.item_name ASC
            ');       
        $stmt->execute();        
        $listMenu = $stmt->fetchAll();

        $stmt = $conn->prepare('
            SELECT * FROM cms.menu_items WHERE id=:id_menu
            ');
        $stmt->execute(['id_menu'=>intval($id_menu)]);        
        $arItem = $stmt->fetchAll();   

        $stmt = $conn->prepare('
                SELECT r.*, mr.menu_id as selected FROM cms.roles as r
                LEFT JOIN (
                SELECT * FROM
                cms.menu_roles 
                WHERE menu_id=:id_menu
                )
                AS mr
                ON r.id=mr.role_id
                ORDER BY r.description ASC
            ');    
        $stmt->execute(['id_menu'=>intval($id_menu)]);   
        $arGrantedUsers = $stmt->fetchAll();

        // динамически формируем URL для  выполнения операции обновления пользователя в БД
        $urlAction = $this->generateUrl('oiv_admin_menu_update', array('id_menu'=>$arItem[0]["id"] ));
        $urlView = $this->generateUrl('oiv_admin_menu');
        $urlBack = $this->generateUrl('oiv_admin_menu');
        
        return array(
            "urlAction"=> $urlAction,
            "urlView"  => $urlView,
            "urlBack"  => $urlBack,
            // 'users' => $users,
            // "roles" => $roles,
            // "oivs" => $oivs,
            "listMenu" => $listMenu,
            "menuItem" => $arItem[0],
            "arGrantedUsers" => $arGrantedUsers
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
