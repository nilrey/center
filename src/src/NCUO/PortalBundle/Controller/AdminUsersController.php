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
 * AdminUsers controller.
 */
class AdminUsersController extends Controller
{
    
    private $passwordEncoder;
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
     * @Route("/admin/users", name="oiv_admin_users")
     * @Method("GET")
     * @Template("ncuoportal/admin/index.html.twig")
     */
    public function indexAction()
    {

        // if( ! $this->isUserGranted() ){
        //     return $this->render('ncuoportal/admin/access_deny.html.twig');
        // }

        $loggedInUser = $this->security->getToken()->getUser();
        if($loggedInUser === 'anon.') return $this->redirect($this->generateUrl('login', array() ) );
        
        if ( $this->checkPageAccess() === false ) return $this->redirect($this->generateUrl('url_error', array() ) );

        if( $this->aldAuthUserCheck() === false ){
            return $this->redirect($this->generateUrl('ald_user_error', array() ) );
        }

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('App:User')->findAll();
        
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('App:Role')->findAll();

        foreach ($users as $user) {
            foreach ($roles as $role) {
                if($role->getId() == $user->getRole() )
                {
                    $user->setRoleDescription( $role->getDescription() );
                }
            }
        }
       
        return array(
            'users' => $users,
        );
    }
        

    /**
     * Delete User
     *
     * @Route("/admin/user/{id_user}/delete", name="oiv_admin_user_delete")
     * @Method("GET")
     * @Template("ncuoportal/admin/delete.html.twig")
     */
    public function deleteAction($id_user)
    {
        $isAdmin = false;
        $message = array();
        $user_name = null;
        if ($this->isGranted('ROLE_ADMIN'))
        {
            $isAdmin = true;
            //$arResult = $this->prepareEditFormData($id_oiv);
            
            $em = $this->getDoctrine()->getManager();
            $users = $em->getRepository('App:User')->findBy(array('id' => $id_user) );
            $user = $users[0];
            $user_name = $user->getUsername().' ('.$user->getLastname().' '.$user->getFirstname().' '.$user->getMiddlename().'), (ID='.$user->getId().')';
            $em->remove($user);
            $em->flush(); 
        }
        else
        {

            $message = array(
                'message_error' => true,
                'message_text'  => "Доступ к данному функционалу ограничен",
            );    
        }
        
        return array(
                'user_name' => $user_name,
                'message' => $message,
                );
    
    }


    /**
     * Load Create User form.
     *
     * @Route("/admin/users/new", name="oiv_admin_user_new")
     * 
     * @Template("ncuoportal/admin/new.html.twig")
     */
    public function actionNew()
    {
        
        $arResult = array();
        $arResult['urlAction'] = $this->generateUrl('oiv_admin_user_create');
        $arResult['urlBack'] = $this->generateUrl('oiv_admin_users');
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('App:Role')->findAll();
        $arResult['roles'] = $roles;
        // $oivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array(), array('id_oiv_type'=>'ASC', 'name' => 'ASC'));
        // $arResult['oivs'] = $oivs;

        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('App:Role')->findAll();

        $oivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv_type'=>1), array('id_oiv_type'=>'ASC', 'name' => 'ASC'));
        $roivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv_type'=>2), array('id_oiv_type'=>'ASC', 'name' => 'ASC'));
        
        $arResult['roles'] = $roles;
        $arResult['oivs'] = $oivs;
        $arResult['roivs'] = $roivs;
        

        $arResult['FIELDS'] = array(
            "username" => "",
            "password" => "",
            "role" => "",
            "oiv_id" => "",
            "lastname" => "",
            "firstname" => "",
            "middlename" => "",
            "email" => "",
            "user_email_autologin" => "",
            "user_ftp_autologin" => "",
            "user_email_login" => "",
            "user_email_pass" => "",
            "user_ftp_login" => "",
            "user_ftp_pass" => "",

                );
        //$arResult = $this->prepareEditFormData(0, true);
        
        return $arResult;
    
    }
    
    
    /**
     * Save Created User form.
     *
     * @Route("/admin/users/create", name="oiv_admin_user_create")
     * 
     * @Template("ncuoportal/admin/new.html.twig")
     */
    public function actionCreateNew(Request $request)
    {
        $arResult = array();
        $message = array();
        if ( is_array($request->request->get("FIELDS") ) )
        {
            $arData = $request->request->get("FIELDS");
        }

        $em = $this->getDoctrine()->getManager();
        $userExist = $em->getRepository('App:User')->findOneBy(array('username' => trim($arData['username'])) );
        
        $roles = $em->getRepository('App:Role')->findAll();

        $oivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv_type'=>1), array('id_oiv_type'=>'ASC', 'name' => 'ASC'));
        $roivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv_type'=>2), array('id_oiv_type'=>'ASC', 'name' => 'ASC'));

        if( empty($arData['username']) )
        {
            $message = array(
                'message_error' => true,
                'message_text'  => "Поле `Логин` должно быть заполнено",
            ); 
        }
        elseif( empty($arData['password']) )
        {
            $message = array(
                'message_error' => true,
                'message_text'  => "Поле `Пароль` должно быть заполнено",
            ); 
        }
        elseif( intval($arData['role']) < 1 )
        {
            $message = array(
                'message_error' => true,
                'message_text'  => "Поле `Роль` должно быть заполнено",
            ); 
        }
        elseif( !empty($userExist) && $userExist->getId() > 0)
        {
            $message = array(
                'message_error' => true,
                'message_text'  => "Пользователь с таким логином уже существует",
            ); 
        }
        else
        {

            $manager = $this->getDoctrine()->getManager();

            $user = new User();
            $user->setUsername($arData['username']);
            $user->setFirstname($arData['firstname']);
            $user->setLastname($arData['lastname']);
            $user->setMiddlename($arData['middlename']);
            // $user->setEmail($arData['email']);
            $user->setUserEmailLogin($arData['user_email_login']);
            $user->setUserEmailPass($arData['user_email_pass']);
            $user->setUserFtpLogin($arData['user_ftp_login']);
            $user->setUserFtpPass($arData['user_ftp_pass']);
            $user->setUserEmailAutologin(!empty($arData['user_email_autologin']));
            $user->setUserFtpAutologin(!empty($arData['user_ftp_autologin']));
            if( !empty($arData['password']) )
            {
                $user->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    $arData['password']
                ));
            }
            
            $user->setRole( $arData['role'] );
            // $user->setFoiv( $arData['foiv'] );
            if( $arData['role'] == 3 )
            {
                $arData['oiv_id'] = $arData['oiv_id'];
            }elseif($arData['role'] == 6 ){
                $arData['oiv_id'] = $arData['roiv_id'];
            }else{
                $arData['oiv_id'] = '';
            }
            $user->setOivId($arData['oiv_id']);
            $manager->persist($user);
            $manager->flush($user);
            $id_user = $user->getId();

            // ------------------------------ SET USER FOIV \ OIV ID'S --------------------------------------------------
            if( !empty( $arData['oiv_id']) )
            {
                $conn = $this->getDoctrine()->getManager()->getConnection();
                $stmt = $conn->prepare("SELECT * FROM oivs_passports.oiv_id_mapping WHERE id_chr = :oiv_id ");
                $stmt->bindValue(':oiv_id',  $arData['oiv_id']);
                $stmt->execute();        
                $listFoivId = $stmt->fetchAll();
            }else{
                $listFoivId = array();
            }

            if(count($listFoivId) > 0 && intval($listFoivId[0]['id_int']) > 0 )
            {
                $foiv_id = $listFoivId[0]['id_int'];
            }else
            {
                $foiv_id = 0;
            }

            $sql_tmpl = 'UPDATE cms.users SET foiv=:foiv_id WHERE id=:id_user';
            $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
            $stmt->bindValue(':id_user',  $id_user);
            $stmt->bindValue(':foiv_id',  $foiv_id);
            $res = $stmt->execute();
            // -----------------------------------------------------------------------------------------------------------          

            $message = array(
                'message_error' => false,
                'message_text'  => "Новый пользователь успешно создан",
            ); 
            
            $arData = array(
                "username" => "",
                "password" => "",
                "role" => "",
                "oiv_id" => "",
                "lastname" => "",
                "firstname" => "",
                "middlename" => "",
                "email" => "",
                "user_email_autologin" => "",
                "user_ftp_autologin" => "",
                "user_email_login" => "",
                "user_email_pass" => "",
                "user_ftp_login" => "",
                "user_ftp_pass" => "",
                );
        }

        $arResult['urlAction'] = $this->generateUrl('oiv_admin_user_create', array() );
        $arResult['urlBack'] = $this->generateUrl('oiv_admin_users');
        $arResult['FIELDS'] = $arData;
        $arResult['message'] = $message;
        $arResult['roles'] = $roles;
        $arResult['oivs'] = $oivs;
        $arResult['roivs'] = $roivs;
        
        return $arResult;
    
    }
    
    
    /**
     * Отображение формы для редактирования существующего пользователя
     *
     * @Route("/admin/users/{id_user}/edit", name="oiv_admin_users_edit")
     * @Method("GET")
     * @Template("ncuoportal/admin/edit.html.twig")
     */
    public function editAction($id_user)
    {
        $arResult = $this->prepareEditFormData($id_user);
        
        return $arResult;
    
    }
    
    /**
     * Update user data
     *  
     * @Route("/admin/users/{id_user}/update", name="oiv_admin_users_update")
     * @Method("POST")
     * @Template("ncuoportal/admin/edit.html.twig")
     */
    
    public function updateAction(Request $request, $id_user){
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
                $user = $em->getRepository('App:User')->find( $id_user );
                $user->setUsername($arData['username']);
                $user->setFirstname($arData['firstname']);
                $user->setLastname($arData['lastname']);
                $user->setMiddlename($arData['middlename']);
                // $user->setEmail($arData['email']);
                $user->setUserEmailLogin($arData['user_email_login']);
                $user->setUserEmailPass($arData['user_email_pass']);
                $user->setUserFtpLogin($arData['user_ftp_login']);
                $user->setUserFtpPass($arData['user_ftp_pass']);
                $user->setUserEmailAutologin(intval($arData['user_email_autologin']));
                $user->setUserFtpAutologin(intval($arData['user_ftp_autologin']));
                if( !empty($arData['password']) )
                {
                    $user->setPassword($this->passwordEncoder->encodePassword(
                        $user,
                        $arData['password']
                    ));
                }
                $user->setRole( $arData['role'] );
                if( intval($arData['role']) == 3 ){
                    $oiv_id = $arData['oiv_id'];
                }else if( intval($arData['role']) == 6 ){
                    $oiv_id = $arData['roiv_id'];
                }else{
                    $arData['oiv_id'] = '';
                    $arData['roiv_id'] = '';
                    $oiv_id = '';
                }
                // $user->setFoiv( $arData['foiv'] );
                $user->setOivId($oiv_id);

                if( !empty($user) )
                {
                    $em->flush();

                    // ------------------------------ SET USER FOIV \ OIV ID'S --------------------------------------------------
                    if( !empty( $arData['oiv_id']) )
                    {
                        $conn = $this->getDoctrine()->getManager()->getConnection();
                        $stmt = $conn->prepare("SELECT * FROM oivs_passports.oiv_id_mapping WHERE id_chr = :oiv_id ");
                        $stmt->bindValue(':oiv_id',  $arData['oiv_id']);
                        $stmt->execute();        
                        $listFoivId = $stmt->fetchAll();
                    }else{
                        $listFoivId = array();
                    }

                    if(count($listFoivId) > 0 && intval($listFoivId[0]['id_int']) > 0 )
                    {
                        $foiv_id = $listFoivId[0]['id_int'];
                    }else
                    {
                        $foiv_id = 0;
                    }

                    $sql_tmpl = 'UPDATE cms.users SET foiv=:foiv_id WHERE id=:id_user';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $stmt->bindValue(':id_user',  $id_user);
                    $stmt->bindValue(':foiv_id',  $foiv_id);
                    $res = $stmt->execute();
                    // -----------------------------------------------------------------------------------------------------------    

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

        $arResult = $this->prepareEditFormData($id_user);
        $arResult['message'] = $message;
        
        return $arResult;
        
    }
    
    public function prepareEditFormData($id_user, $data_to_html = false)
    {
        //получим информацию о выбранном пользователе        

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('App:User')->findBy(array('id' => $id_user ) );
        
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('App:Role')->findAll();

        $em = $this->getDoctrine()->getManager();
        $oivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv_type'=>1), array('id_oiv_type'=>'ASC', 'name' => 'ASC'));
        $roivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv_type'=>2), array('id_oiv_type'=>'ASC', 'name' => 'ASC'));

        foreach ($users as $user) {
            foreach ($roles as $role) {
                if($role->getId() == $user->getRole() )
                {
                    $user->setRoleDescription( $role->getDescription() );
                }
            }
        }

        
        // //динамически формируем URL для  выполнения операции обновления пользователя в БД
        $urlAction = $this->generateUrl('oiv_admin_users_update', array('id_user'=>$users[0]->getId() ));
        $urlView = $this->generateUrl('oiv_admin_users');
        $urlBack = $this->generateUrl('oiv_admin_users');
        
        return array(
            "urlAction"=> $urlAction,
            "urlView"  => $urlView,
            "urlBack"  => $urlBack,
            'users' => $users,
            "roles" => $roles,
            "oivs" => $oivs,
            "roivs" => $roivs,
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
