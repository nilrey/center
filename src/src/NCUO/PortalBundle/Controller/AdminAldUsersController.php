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
 * AdminAldUsers controller.
 */
class AdminAldUsersController extends Controller
{
    
    private $passwordEncoder;

    public function __construct(Security $security, SessionInterface $session, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $loggedInUser = $security->getToken()->getUser();
        if(!empty($session->get("role_id"))) {
            $loggedInUser->setRoles($session->get("role"));
            $loggedInUser->setRole($session->get("role_id"));
            // $session->set("role_id", null);
        }
    }
    
    /** 
     * Lists all Users entities.
     *
     * @Route("/admin/users", name="oiv_admin_users")
     * @Method("GET")
     * @Template("ncuooiv/admin/ald_users.html.twig")
     */
    public function indexAction()
    {

        if( ! $this->isUserGranted() ){
            return $this->render('ncuooiv/admin/access_deny.html.twig');
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
     * Load Create User form.
     *
     * @Route("/admin/users/new", name="oiv_admin_user_new")
     * 
     * @Template("ncuooiv/admin/new.html.twig")
     */
    public function actionNew()
    {
        
        $arResult = array();
        $arResult['urlAction'] = $this->generateUrl('oiv_admin_user_create');
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('App:Role')->findAll();
        $arResult['roles'] = $roles;
        $oivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array(), array('id_oiv_type'=>'ASC', 'name' => 'ASC'));
        $arResult['oivs'] = $oivs;
        $arResult['FIELDS'] = array(
                                   "username" => "",
                                   "password" => "",
                                   "role" => "",
                                   "oiv_id" => "",
                                   "lastname" => "",
                                   "firstname" => "",
                                   "middlename" => "",
                                   "email" => "",
                                    );
        //$arResult = $this->prepareEditFormData(0, true);
        
        return $arResult;
    
    }
    
    
    /**
     * Save Created User form.
     *
     * @Route("/admin/users/create", name="oiv_admin_user_create")
     * 
     * @Template("ncuooiv/admin/new.html.twig")
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
        
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('App:Role')->findAll();

        $em = $this->getDoctrine()->getManager();
        $oivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array(), array('id_oiv_type'=>'ASC', 'name' => 'ASC'));

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
            $user->setEmail($arData['email']);
            if( !empty($arData['password']) )
            {
                $user->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    $arData['password']
                ));
            }
            $user->setRole( $arData['role'] );
            // $user->setFoiv( $arData['foiv'] );
            if($arData['role'] != 3)
            {
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
                );
        }

        $arResult['urlAction'] = $this->generateUrl('oiv_admin_user_create', array() );
        $arResult['FIELDS'] = $arData;
        $arResult['message'] = $message;
        $arResult['roles'] = $roles;
        $arResult['oivs'] = $oivs;
        
        return $arResult;
    
    }
    
    
    /**
     * Отображение формы для редактирования существующего пользователя
     *
     * @Route("/admin/users/{id_user}/edit", name="oiv_admin_users_edit")
     * @Method("GET")
     * @Template("ncuooiv/admin/edit.html.twig")
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
     * @Template("ncuooiv/admin/edit.html.twig")
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
                $user->setEmail($arData['email']);
                if( !empty($arData['password']) )
                {
                    $user->setPassword($this->passwordEncoder->encodePassword(
                        $user,
                        $arData['password']
                    ));
                }
                $user->setRole( $arData['role'] );
                if($arData['role'] != 3)
                {
                    $arData['oiv_id'] = '';
                }
                // $user->setFoiv( $arData['foiv'] );
                $user->setOivId($arData['oiv_id']);

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
        $oivs = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array(), array('id_oiv_type'=>'ASC', 'name' => 'ASC'));

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

}
