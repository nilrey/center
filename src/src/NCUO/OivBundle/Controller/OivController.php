<?php namespace App\NCUO\OivBundle\Controller;


use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Oiv controller.
 */
class OivController extends Controller
{
    const SEC_ID = "short_info";
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


    public function __construct(Security $security, SessionInterface $session, ContainerInterface $container, RequestStack $requestStack)
    {
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

            // var_dump($this->rolePerms); die();

        endif;
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

    /** 
     * Lists all Oiv entities.
     *
     * @Route("/", name="oiv")
     * @Method("GET")
     * @Template("ncuooiv/oiv/index.html.twig")
     */
    public function indexAction(Security $security)
    {
        $loggedInUser = $this->security->getToken()->getUser();
        if($loggedInUser === 'anon.') return $this->redirect($this->generateUrl('login', array() ) );
        
        if ( $this->checkPageAccess() === false ) return $this->redirect($this->generateUrl('url_error', array() ) );

        if( $this->aldAuthUserCheck() === false ){
            return $this->redirect($this->generateUrl('ald_user_error', array() ) );
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if( $loggedInUser === 'anon.') return $this->redirect($this->generateUrl('login', array() ) );
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $stmt = $conn->prepare("SELECT * FROM cms.users WHERE username='".$loggedInUser->getUsername()."' LIMIT 1");
        $stmt->execute();
        $dbUserData = $stmt->fetchAll();

        /*
            if( in_array('ROLE_FOIV', $loggedInUser->getRoles()) ){
                if(!empty( $dbUserData[0]["oiv_id"]) )
                {
                    // check if Oiv exists

                    $em = $this->getDoctrine()->getManager();
                    $oiv = $em->getRepository('NCUOOivBundle:Oiv')->find( $dbUserData[0]["oiv_id"] );
                    if( intval($oiv->getIdOivType()) == 1)
                    {
                        return $this->redirect( $this->generateUrl('oiv_show', array('id_oiv'=>$oiv->getId() ) ) );
                    }else if( intval($oiv->getIdOivType()) == 2)
                    {
                        return $this->redirect( $this->generateUrl('region_show', array('id_reg'=>$oiv->getId() ) ) );
                    }else
                    {
                        return $this->redirect($this->generateUrl('ald_user_error', array() ) );
                    }
                    // 
                }
            }

        */
        /*
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findBy(array('username' => substr($_SERVER['REMOTE_USER'], 0, strpos( $_SERVER['REMOTE_USER'], '@') )  ), array('username' => 'ASC'));
        */
        /*      
                $user = $security->getUser();
                $user->setFirstname("test");
                var_dump($user->getFirstname());
        */
        /*
            $em = $this->getDoctrine()->getManager();
            $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
            $loggedInUser->setRoles('ROLE_NCUO');
            $loggedInUser->setFirstname('TEST');

            $em->persist($loggedInUser);
            // $em->flush();

            $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
              $loggedInUser,
              null,
              'main',
              $loggedInUser->getRoles()
            );

            $this->container->get('security.token_storage')->setToken($token);

            $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
            var_dump($loggedInUser);
        */
        //ALD_AUTH_USER
        /*
                $ALD_AUTH_ON = 'off';
                try{
                    $ALD_AUTH_ON = strtolower($this->getParameter('ALD_AUTH_ON') );
                }
                catch(\Exception $e){
                    $ALD_AUTH_ON = 'off';
                }
                if( $ALD_AUTH_ON === "on" ):
                    echo "Yes!";
                else:
                    echo "No";
                endif;
                $filename = 'C:\OpenServerRTI\OpenServer\domains\symfony41\test.txt';
                $somecontent = "Добавить это к файлу\n";
                
                if (is_writable($filename)) {

                    // В нашем примере мы открываем $filename в режиме "записи в конец".
                    // Таким образом, смещение установлено в конец файла и
                    // наш $somecontent допишется в конец при использовании fwrite().
                    if (!$handle = fopen($filename, 'a')) {
                         echo "Не могу открыть файл ($filename)";
                         exit;
                    }
                
                    // Записываем $somecontent в наш открытый файл.
                    if (fwrite($handle, $somecontent) === FALSE) {
                        echo "Не могу произвести запись в файл ($filename)";
                        exit;
                    }
                
                    echo "Ура! Записали ($somecontent) в файл ($filename)";
                
                    fclose($handle);
                
                } else {
                    echo "Файл $filename недоступен для записи";
                }
        */


        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv_type' => 1), array('name' => 'ASC'));


        $conn = $this->getDoctrine()->getManager()->getConnection();
        $stmt = $conn->prepare("SELECT * FROM oivs_passports.oivs_pass_seсs_fields WHERE id_fld LIKE '%__short_info__heraldic_img'");
        $stmt->execute();        
        $listFlags = $stmt->fetchAll();
        
        if( count($listFlags) > 0 && count($entities) > 0 )
        {
            foreach( $entities as &$entity )
            {
                foreach( $listFlags as $flag )
                {
                    if( strpos( $flag['id_sec'], $entity->getId() ) !== false )
                    {
                        if( !empty($flag['data']) )
                        {
                            $entity->setHeraldicImg($flag['data']);
                        }
                        else
                        {
                            $entity->setNoHeraldicImg();
                        }
                    }
                }
            }
        }

        $conn = $this->getDoctrine()->getManager()->getConnection();
        $stmt = $conn->prepare("SELECT * FROM oivs_passports.oivs_pass_seсs_fields WHERE id_fld LIKE '%__short_info__name_short'");
        $stmt->execute();        
        $listNameShort = $stmt->fetchAll();
        
        if( count($listNameShort) > 0 && count($entities) > 0 )
        {
            foreach( $entities as &$entity )
            {
                foreach( $listNameShort as $ns )
                {
                    if( strpos( $ns['id_sec'], $entity->getId() ) !== false )
                    {
                        $entity->setNameShort($ns['data']);
                    }
                }
            }
        }
        
        return array(
            'entities' => $entities,
            'rolePerms' => $this->rolePerms,
        );

    }

            
            /**
             * Update data 
             * @Route("/ald_user_error", name="ald_user_error")
             * @Method("GET")
             * @Template("ncuoportal/ald_user_error.html.twig")
             */
            
            public function svipUserError(){
                
                return array();
                
            }
            
            /**
             * Update data 
             * @Route("/svip_user_error", name="svip_user_error")
             * @Method("GET")
             * @Template("ncuoportal/svip_user_error.html.twig")
             */
            
            public function aldUserError(){
                
                return array();
                
            }

            /**
             * Finds and displays a Oiv entity.
             *
             * @Route("/new", name="oiv_new")
             * @Method("GET")
             * @Template("ncuooiv/oiv/new.html.twig")
             */
            public function actionNew()
            {
                if ( $this->checkPageAccess() === false || $this->rolePerms['create'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );

                $arResult = array();
                $arResult['urlAction'] = $this->generateUrl('oiv_create', array() );
                $arResult['rolePerms'] = $this->rolePerms;
                $arResult['FIELDS'] = array(
                                           "id_oiv" => "",
                                           "name" => "",
                                           "name_short" => "",
                                           //"descr" => "",
                                           "heraldic_img" => "",
                                           "flag_img" => "",
                                            );
                //$arResult = $this->prepareEditFormData(0, true);
                
                return $arResult;
            
            }
            

            /**
             * Finds and displays a Oiv entity.
             *
             * @Route("/new_create", name="oiv_create")
             * @Method("POST")
             * @Template("ncuooiv/oiv/new.html.twig")
             */
            public function actionCreateNew(Request $request)
            {
                if ( $this->checkPageAccess() === false || $this->rolePerms['create'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );

                        // $this->isEditGranted = true;

                $arResult = array();
                $message = array();
                if ( is_array($request->request->get("FIELDS") ) )
                {
                    foreach ($request->request->get("FIELDS") as $key => $value) {
                        $arData[$key] = trim($value);
                    }
                    $em = $this->getDoctrine()->getManager();
                    $oiv = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv' => $arData["id_oiv"]), array('name' => 'ASC'), null , null );

                    if ($oiv)
                    {
                        $message = array(
                            'message_error' => true,
                            'message_text'  => "ФОИВ с таким идентификатором уже существует",
                        );
                    }
                    elseif( empty($arData['id_oiv']) )
                    {
                        $message = array(
                            'message_error' => true,
                            'message_text'  => "Поле `Идентификатор ОИВ` должно быть заполнено.",
                            'error_explain' => ''
                        ); 
                    }
                    elseif( preg_match("/[^a-z]/", $arData['id_oiv']) )
                    // elseif( preg_match("/[^a-zA-Z_]/", $arData['id_oiv']) )
                    {
                        $message = array(
                            'message_error' => true,
                            'message_text'  => "В поле `Идентификатор ОИВ` должны использоваться только латинские буквы в нижнем регистре.",
                            'error_explain' => ''
                        ); 
                    }
                    elseif( empty($arData['name']) )
                    {
                        $message = array(
                            'message_error' => true,
                            'message_text'  => "Поле `Полное наименование ОИВ` должно быть заполнено.",
                            'error_explain' => ''
                        ); 
                    }
                    else
                    {
                        $sql_tmpl = 'INSERT INTO oivs_passports.oivs ( id_oiv, name, id_oiv_type, enabled)  VALUES (:id_oiv, :name, :id_oiv_type, :enabled)';
                        
                        $params['id_oiv'] = $arData["id_oiv"];
                        $params['name'] = $arData["name"];
                        //$params['descr'] = $arData["descr"];
                        $params['id_oiv_type'] = 1;
                        $params['enabled'] = true;
                        $stmt = $this->prepareCreateOiv($sql_tmpl, $params);
                        $stmt->execute();
                        
                        $oiv = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv' => $arData["id_oiv"]), array('name' => 'ASC'), null , null );
                        if ($oiv)
                        {
                            //------------------------------------------------- Sections ----------------------------------------------     
                      
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'common_info',
                                    "id_parent_sec"  => null,
                                    "sec_name"   => 'Общая информация',
                                    "view_order" => 100,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------          
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'oiv_info',
                                    "id_parent_sec"  => null,
                                    "sec_name"   => 'Структура ФОИВ',
                                    "view_order" => 200,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------          
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'resources_info',
                                    "id_parent_sec"  => null,
                                    "sec_name"   => 'Информационные ресурсы',
                                    "view_order" => 300,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            //------------------------------------------------------------------------------------------------------------------------------
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'short_info',
                                    "id_parent_sec"  => $arData["id_oiv"].'__'.'common_info',
                                    "sec_name"   => 'Краткая карточка ФОИВ',
                                    "view_order" => 110,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'foundamental_docs',
                                    "id_parent_sec"  => $arData["id_oiv"].'__'.'common_info',
                                    "sec_name"   => 'Основополагающие документы',
                                    "view_order" => 120,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            //------------------------------------------------------------------------------------------------------------------------------
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'competencies_info',
                                    "id_parent_sec"  => $arData["id_oiv"].'__'.'common_info',
                                    "sec_name"   => 'Область компетенции',
                                    "view_order" => 130,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            //------------------------------------------------------------------------------------------------------------------------------
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'contact_info',
                                    "id_parent_sec"  => $arData["id_oiv"].'__'.'common_info',
                                    "sec_name"   => 'Контакты',
                                    "view_order" => 140,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            //------------------------------------------------------------------------------------------------------------------------------
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'symbolics_info',
                                    "id_parent_sec"  => $arData["id_oiv"].'__'.'short_info',
                                    "sec_name"   => 'Официальная символика',
                                    "view_order" => 115,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            //------------------------------------------------------------------------------------------------------------------------------
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'executives_info',
                                    "id_parent_sec"  => $arData["id_oiv"].'__'.'oiv_info',
                                    "sec_name"   => 'Руководство',
                                    "view_order" => 210,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            //------------------------------------------------------------------------------------------------------------------------------
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'structure_scheme',
                                    "id_parent_sec"  => $arData["id_oiv"].'__'.'oiv_info',
                                    "sec_name"   => 'Структура',
                                    "view_order" => 220,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            //------------------------------------------------------------------------------------------------------------------------------
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'disposable_funds_info',
                                    "id_parent_sec"  => $arData["id_oiv"].'__'.'oiv_info',
                                    "sec_name"   => 'Подведомственные учреждения, силы и средства',
                                    "view_order" => 230,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            //------------------------------------------------------------------------------------------------------------------------------
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'director',
                                    "id_parent_sec"  => $arData["id_oiv"].'__'.'executives_info',
                                    "sec_name"   => 'Руководитель',
                                    "view_order" => 212,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                            //------------------------------------------------------------------------------------------------------------------------------
                            $arParams = array(
                                    "oiv_id"=> $arData["id_oiv"],
                                    "sec_id"  => $arData["id_oiv"].'__'.'deputy_head',
                                    "id_parent_sec"  => $arData["id_oiv"].'__'.'executives_info',
                                    "sec_name"   => 'Первый заместитель руководителя (заместитель)',
                                    "view_order" => 214,
                            );
                            $res = $this->createSection($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
            
                            //------------------------------------------------------------------------------------------------------------------------------
                          
                            // $arParams = array(
                            //         "oiv_id"=> $arData["id_oiv"],
                            //         "sec_id"  => $arData["id_oiv"].'__'.'SEC_IDITY',
                            //         "id_parent_sec"  => $arData["id_oiv"].'__'.'SEC_PARENT',
                            //         "sec_name"   => 'SEC_TITLE',
                            //         "view_order" => 100,
                            // );
                            // $res = $this->createSection($arParams);
                            // if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
            
            
            
                            // //------------------------------------------------------------------------------------------------------------------------------
                            
                            // $arParams = array(
                            //     "sec_id" => $arData["id_oiv"].'__'.'SEC_ID',
                            //     "id_fld"      =>  $sec_id.'__'.'FLD_ID',
                            //     "fld_name"    => 'FLD_TITLE',
                            //     "view_order"  => 10,
                            //     "data_source" => '',
                            //     "data_val" => '',
                            //     "id_fld_content_type" => 4,
                            //     "data_upd" => date('Y-m-d H:i:s'),
                            // );
                            // $res = $this->createSection($arParams);
                            // if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            // //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Полное наименование',
                                "view_order"  => 10,
                                "data_source" => '',
                                "data_val" => $arData["name"],
                                "id_fld_content_type" => 5,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'short_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'name_full';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Сокращенное наименование',
                                "view_order"  => 20,
                                "data_source" => '',
                                "data_val" => $arData["name_short"],
                                "id_fld_content_type" => 5,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'short_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'name_short';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Вышестоящий орган (руководитель) государственной власти',
                                "view_order"  => 30,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 5,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'short_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'parent_oiv_desc';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Геральдический знак (эмблема)',
                                "view_order"  => 50,
                                "data_source" => '',
                                "data_val" => $arData["heraldic_img"],
                                "id_fld_content_type" => 1,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'short_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'heraldic_img';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Флаг',
                                "view_order"  => 60,
                                "data_source" => '',
                                "data_val" => $arData["flag_img"],
                                "id_fld_content_type" => 1,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'short_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'flag_img';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Определяющие полномочия',
                                "view_order"  => 10,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'foundamental_docs';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'authorities';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Определяющие взаимодействие ФОИВ с МО РФ',
                                "view_order"  => 20,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'foundamental_docs';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'mil_cooperation';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Предназначение',
                                "view_order"  => 10,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'competencies_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'mission';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Цели',
                                "view_order"  => 20,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'competencies_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'goals';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Задачи',
                                "view_order"  => 30,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'competencies_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'objectives';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Направления (виды) деятельности',
                                "view_order"  => 40,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'competencies_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'activities';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Обязанности',
                                "view_order"  => 50,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'competencies_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'duties';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Полномочия',
                                "view_order"  => 60,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'competencies_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'credentials';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Права',
                                "view_order"  => 70,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'competencies_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'rights';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Функции',
                                "view_order"  => 80,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'competencies_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'functionality';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Услуги',
                                "view_order"  => 90,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'competencies_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'services';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Почтовый адрес',
                                "view_order"  => 10,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'contact_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'post_addr';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Адрес официального сайта',
                                "view_order"  => 20,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'contact_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'site_url';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Справочные телефоны',
                                "view_order"  => 30,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'contact_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'phone';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Адрес электронной почты',
                                "view_order"  => 40,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'contact_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'email';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Фамилия',
                                "view_order"  => 10,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'director';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dir_surename';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Имя',
                                "view_order"  => 20,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'director';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dir_firstname';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Отчество',
                                "view_order"  => 30,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'director';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dir_patronymic';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Должность',
                                "view_order"  => 40,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'director';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dir_position';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Звание',
                                "view_order"  => 50,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'director';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dir_rang';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Фото',
                                "view_order"  => 1,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 1,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'director';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dir_photo';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Фамилия',
                                "view_order"  => 10,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'deputy_head';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dh_surename';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Имя',
                                "view_order"  => 20,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'deputy_head';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dh_firstname';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Отчество',
                                "view_order"  => 30,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'deputy_head';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dh_patronymic';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Должность',
                                "view_order"  => 40,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'deputy_head';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dh_position';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Звание',
                                "view_order"  => 50,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'deputy_head';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dh_rang';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Фото',
                                "view_order"  => 1,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 1,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'deputy_head';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'dh_photo';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Структура (состав)',
                                "view_order"  => 10,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 1,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'structure_scheme';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'structure_img';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Подведомственные учреждения',
                                "view_order"  => 10,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'disposable_funds_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'org_cooperation';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Силы',
                                "view_order"  => 20,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'disposable_funds_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'forces';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Средства',
                                "view_order"  => 30,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'disposable_funds_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'funds';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Информационные системы',
                                "view_order"  => 10,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'resources_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'info_systems';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Базы (банки) данных, реестры, регистры',
                                "view_order"  => 20,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'resources_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'info_db';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            $arParams = array(
                                "fld_name"    => 'Онлайн-сервисы',
                                "view_order"  => 30,
                                "data_source" => '',
                                "data_val" => '',
                                "id_fld_content_type" => 4,
                                "data_upd" => date('Y-m-d H:i:s'),
                            );
                            $arParams["sec_id"] = $arData["id_oiv"].'__'.'resources_info';
                            $arParams["id_fld"] = $arParams["sec_id"].'__'.'info_online';
                            
                            $res = $this->createField($arParams);
                            if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
            
                            //------------------------------------------------------------------------------------------------------------------------------
                            
                            // $arParams = array(
                            //     "fld_name"    => 'FLD_TITLE',
                            //     "view_order"  => 10,
                            //     "data_source" => '',
                            //     "data_val" => '',
                            //     "id_fld_content_type" => 4,
                            //     "data_upd" => date('Y-m-d H:i:s'),
                            // );
                            // $arParams["sec_id"] = $arData["id_oiv"].'__'.'SEC_ID';
                            // $arParams["id_fld"] = $arParams["sec_id"].'__'.'FLD_ID';
                            
                            // $res = $this->createField($arParams);
                            // if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
            
            
                            
                            if(!empty($error_message_text))
                            {
                                $message = array(
                                    'message_error' => true,
                                    'message_text'  => "Ошибка записи в базу данных. Раздел Section: <br>{$error_message_text}",
                                );
                            }
                            $message = array(
                                'message_error' => false,
                                'message_text'  => "ФОИВ успешно создан",
                            );
                        
                        }
                        else
                        {
                            $message = array(
                                'message_error' => true,
                                'message_text'  => "Ошибка записи в базу данных",
                            );
                            
                        }
                    }
                }
                else
                {
                    $message = array(
                        'message_error' => true,
                        'message_text'  => "Поле `Идентификатор ОИВ` и поле `Полное наименование ОИВ` не могут быть пустыми",
                    );
                    
                }
                $arResult['urlAction'] = $this->generateUrl('oiv_create', array() );
                $arResult['FIELDS'] = $arData;
                $arResult['message'] = $message;
                $arResult['rolePerms'] = $this->rolePerms;
                
                return $arResult;
            
            }
            

            /**
             * Finds and displays a Oiv entity.
             *
             * @Route("/{id_oiv}", name="oiv_show")
             * @Method("GET")
             * @Template("ncuooiv/item_show.html.twig")
             */
            public function showAction($id_oiv)
            {
                if ( $this->checkPageAccess() === false || $this->rolePerms['read'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
                
                $em = $this->getDoctrine()->getManager();

                //$oiv = $em->getRepository('NCUOOivBundle:Oiv')->find($id_oiv);
                $oiv = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv' => $id_oiv, 'id_oiv_type' => 1), array('name' => 'ASC'), null , null );
                //$sections = $em->getRepository('NCUOOivBundle:Section')->findBy(array('id_oiv' => $id_oiv, 'id_oiv_type' => 1), array('view_order' => 'ASC'), null , null ); // Получить все секции данного фоива
        //        $sections = $em->getRepository('NCUOOivBundle:Section')->findBy(array('id_sec' => $id_oiv.'__short_info'), array('view_order' => 'ASC'), null , null );
        $fields = $em->getRepository('NCUOOivBundle:Field')->findBy(array('id_sec' => $id_oiv.'__short_info'), array('view_order' => 'ASC'), null , null );
        foreach( $fields  as $key => $arValues) :
            if( $arValues->getIdFldContentType() == 1 )
            {
                $tmpData = $arValues->getData();
                $arValues->setData( htmlspecialchars_decode($tmpData ) );
                
            }
            else
            {
                $arValues->setData( nl2br( $arValues->getData() ) );
            }
            
            if( $arValues->getIdFldShort() == 'heraldic_img' )
            {
                $oiv[0]->setHeraldicImg($arValues->getData());
            }
            
        endforeach;
  //echo '<pre>',print_r($fields[0]),'</pre>';
        if (!$oiv) {
            throw $this->createNotFoundException('Unable to find Oiv entity.');
        }

        $tabMenu["parent_id"] = 'oiv_common'; //$this::SEC_ID;
        $tabMenu["current_id"] = 'oiv_show'; 
        
        return array(
            'oiv'      => $oiv[0],
            'fields'      => $fields,
            'rolePerms' => $this->rolePerms,
            'urlEdit' => $this->generateUrl('short_info_edit', array('id_oiv'=>$oiv[0]->getId() )),
            'tabmenu' => $tabMenu,
            'pageTitle' => 'Карточка ФОИВ'
        );
    
    }
    

    /**
     * Delete OIV
     *
     * @Route("/{id_oiv}/delete", name="oiv_delete")
     * @Method("GET")
     * @Template("ncuooiv/oiv/delete.html.twig")
     */
    public function deleteAction($id_oiv)
    {
        
        if ( $this->checkPageAccess() === false || $this->rolePerms['delete'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
        $message = array();
        $oiv_name = null;
        if ($this->rolePerms['delete'] === true)
        {
            $em = $this->getDoctrine()->getManager();
            $oiv = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv' => $id_oiv, 'id_oiv_type' => 1), array('name' => 'ASC'), null , null );
            if (count($oiv) > 0 )
            {
                $oiv_name = $oiv[0]->getName();
    
                $sql_tmpl = 'ALTER TABLE oivs_passports.oivs DISABLE TRIGGER ALL';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $res = $stmt->execute();
                $sql_tmpl = 'ALTER TABLE oivs_passports.oivs_pass_sections DISABLE TRIGGER ALL';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $res = $stmt->execute();
                $sql_tmpl = 'ALTER TABLE oivs_passports.oivs_pass_seсs_fields DISABLE TRIGGER ALL';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $res = $stmt->execute();

                $sql_tmpl = "DELETE FROM oivs_passports.\"oivs_pass_seсs_fields\" WHERE id_fld like '{$id_oiv}__%'";
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $stmt->execute();
                $sql_tmpl = "DELETE FROM oivs_passports.oivs_pass_sections WHERE id_sec like '{$id_oiv}__%'";
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $stmt->execute();
                $sql_tmpl = "DELETE FROM oivs_passports.oivs WHERE id_oiv='{$id_oiv}'";
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $stmt->execute();

                $sql_tmpl = 'ALTER TABLE oivs_passports.oivs ENABLE TRIGGER ALL';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $res = $stmt->execute();
                $sql_tmpl = 'ALTER TABLE oivs_passports.oivs_pass_sections ENABLE TRIGGER ALL';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $res = $stmt->execute();
                $sql_tmpl = 'ALTER TABLE oivs_passports.oivs_pass_seсs_fields ENABLE TRIGGER ALL';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $res = $stmt->execute();
            }
            else
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "ФОИВ с идентификатором \"{$id_oiv}\" не существует",
                );
            }
        }
        else
        {

            $message = array(
                'message_error' => true,
                'message_text'  => "Доступ к данному функционалу ограничен",
            );    
        }
        
        return array(
                'oiv_name' => $oiv_name,
                'message' => $message,
                'rolePerms' => $this->rolePerms,
                );
    
    }

    
    public function prepareCreateOiv($sql_tmpl, $params)
    {
            $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
            foreach($params as $key => $value)
            {
                $stmt->bindValue(':'.$key,  $value);
                
            }
        return $stmt;
    }
    
    public function createSection($params)
    {
            
            $sql_tmpl = 'INSERT INTO oivs_passports.oivs_pass_sections ( id_sec, name, view_order, editable, auto_updatable, show_as_menu_item, id_oiv, id_parent_sec, id_sec_view_type) VALUES (:sec_id, :sec_name, :view_order, TRUE, TRUE, FALSE, :oiv_id, :id_parent_sec, 1)';
            
            $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
            foreach($params as $key => $value)
            {
                $stmt->bindValue(':'.$key,  $value);
                
            }
            $res = $stmt->execute();
            
        return $res;
    }
    
    public function createField($params)
    {
            
            $sql_tmpl = 'INSERT INTO oivs_passports.oivs_pass_seсs_fields (id_fld, name, id_sec, view_order, data, data_updated, data_info_src, id_fld_content_type)
                VALUES (:id_fld, :fld_name, :sec_id, :view_order, :data_val, :data_upd, :data_source, :id_fld_content_type)';
            
            $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
            foreach($params as $key => $value)
            {
                $stmt->bindValue(':'.$key,  $value);
                
            }
            $res = $stmt->execute();
            
        return $res;
    }
    
    
    
    /**
     * Finds and displays a Oiv entity.
     *
     * @Route("/{id_oiv}/short_info", name="short_info")
     * @Method("GET")
     * @Template("ncuooiv/oiv/show.html.twig")
     */
    public function shortInfoAction($id_oiv)
    {

        if ( $this->checkPageAccess() === false || $this->rolePerms['read'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
        
        return $this->redirect($this->generateUrl('oiv_show', array('id_oiv'=> $id_oiv ) ) );
    }
    
    /**
     * Отображение формы для редактирования существующего контакта
     *
     * @Route("/{id_oiv}/short_info/edit", name="short_info_edit")
     * @Method("GET")
     * @Template("ncuooiv/oiv/edit.html.twig")
     */
    public function editAction($id_oiv)
    {
        if ( $this->checkPageAccess() === false || $this->rolePerms['update'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
        $arResult = $this->prepareEditFormData($id_oiv);
        
        return $arResult;
    
    }
    
    /**
     * Update data 
     * @Route("/{id_oiv}/short_info/update", name="short_info_update")
     * @Method("POST")
     * @Template("ncuooiv/oiv/edit.html.twig")
     */
    
    public function updateAction(Request $request, $id_oiv)
    {
        
        if ( $this->checkPageAccess() === false || $this->rolePerms['update'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
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

            if( !empty($arData[$id_oiv."__short_info__name_full"]) )
            {
                
                $sql_tmpl = 'UPDATE oivs_passports.oivs SET name=:name WHERE id_oiv=:id_oiv';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $stmt->bindValue(':id_oiv',  $id_oiv);
                $stmt->bindValue(':name',  $arData[$id_oiv."__short_info__name_full"]);
                $res = $stmt->execute();
            }

            if( $this->userRoleName == 'ROLE_ADMIN' && !empty($arData[$id_oiv."__common_info__oiv_id"]) )
            {
                if( preg_match("/[^a-zA-Z_]/", $arData[$id_oiv."__common_info__oiv_id"]) ){
                    $message = array(
                        'message_error' => true,
                        'message_text'  => "В поле `Идентификатор ОИВ` должны использоваться только латинские буквы в верхнем регистре и знак нижнего подчеркивания _.",
                        'error_explain' => ''
                    );

                }else{
                    
                    $sql_tmpl = 'ALTER TABLE oivs_passports.oivs DISABLE TRIGGER ALL';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $res = $stmt->execute();
                    $sql_tmpl = 'ALTER TABLE oivs_passports.oivs_pass_sections DISABLE TRIGGER ALL';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $res = $stmt->execute();
                    $sql_tmpl = 'ALTER TABLE oivs_passports.oivs_pass_seсs_fields DISABLE TRIGGER ALL';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $res = $stmt->execute();
                    
                    $sql_tmpl = 'UPDATE oivs_passports.oivs SET id_oiv=:new_id WHERE id_oiv=:id_oiv';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $stmt->bindValue(':id_oiv',  $id_oiv);
                    $stmt->bindValue(':new_id',  $arData[$id_oiv."__common_info__oiv_id"]);
                    $res = $stmt->execute();
                    
                    $sql_tmpl = 'UPDATE oivs_passports.oivs_pass_sections SET id_oiv=:new_id ,
                        id_sec = replace(id_sec, \':id_oiv\', \':new_id\'),
                        id_parent_sec = replace(id_parent_sec, \':id_oiv\', \':new_id\') 
                        WHERE id_oiv=:id_oiv';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $stmt->bindValue(':id_oiv',  $id_oiv);
                    $stmt->bindValue(':new_id',  $arData[$id_oiv."__common_info__oiv_id"]);
                    $res = $stmt->execute();


                    $sql_tmpl = 'UPDATE oivs_passports.oivs_pass_seсs_fields SET 
                        id_fld = replace(id_fld, :id_oiv, :new_id),
                        id_sec = replace(id_sec, :id_oiv, :new_id) 
                        WHERE id_fld LIKE :like_oiv_id';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $stmt->bindValue(':id_oiv',  $id_oiv);
                    $stmt->bindValue(':new_id',  $arData[$id_oiv."__common_info__oiv_id"]);
                    $stmt->bindValue(':like_oiv_id',  $id_oiv.'__%');
                    $res = $stmt->execute();

                    $sql_tmpl = 'ALTER TABLE oivs_passports.oivs ENABLE TRIGGER ALL';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $res = $stmt->execute();
                    $sql_tmpl = 'ALTER TABLE oivs_passports.oivs_pass_sections ENABLE TRIGGER ALL';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $res = $stmt->execute();
                    $sql_tmpl = 'ALTER TABLE oivs_passports.oivs_pass_seсs_fields ENABLE TRIGGER ALL';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $res = $stmt->execute();

                    $id_oiv = $arData[$id_oiv."__common_info__oiv_id"];
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
            $fields = NCUO\OivBundle\Entity\Field;
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
        $urlAction = $this->generateUrl('short_info_update', array('id_oiv'=>$oiv[0]->getId() ));
        $urlView = $this->generateUrl('short_info_edit', array('id_oiv'=>$oiv[0]->getId() ));
        $urlBack = $this->generateUrl('oiv_show', array('id_oiv'=>$oiv[0]->getId() ));

        $tabMenu["parent_id"] = 'oiv_common'; 
        $tabMenu["current_id"] = 'oiv_show'; 

        
        return array(
            'oiv'      => $oiv[0],
            'fields'   => $fields,
            "urlAction"=> $urlAction,
            "urlView"  => $urlView,
            "urlBack"  => $urlBack,
            'rolePerms' => $this->rolePerms,
            'userRoleName' => $this->userRoleName,
            'tabmenu' => $tabMenu,
            'pageTitle' => 'Карточка ФОИВ'
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
    

}
