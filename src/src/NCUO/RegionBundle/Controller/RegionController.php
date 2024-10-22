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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Routing\Generator\UrlGenerator;

//use App\NCUO\OivBundle\Form\OivType;
//use App\NCUO\PortalBundle\Entity\User;
/**
 * Region controller.
 */
class RegionController extends Controller
{
    
    const SEC_ID = "s_geo_position";
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
    private $pageTitle = 'Административно-географическое положение';    


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
     * Update data 
     * @Route("/url_error", name="url_error")
     * @Method("GET")
     * @Template("ncuoportal/url_error.html.twig")
     */
    
    public function aldUserError(){
        
        return array();
        
    }

    /** 
     * Lists all Region entities.
     *
     * @Route("/", name="region")
     * @Method("GET")
     * @Template("ncuoregion/region/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->checkPageAccess() === false || in_array('R', $this->rolePerms) === false ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $arFilter = array('id_oiv_type' => 2);

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('NCUOOivBundle:Oiv')->findBy($arFilter, array('name' => 'ASC'));
        //if ($this->isGranted('ROLE_FOIV')){
        //    $user_region_id = $this->checkUserRegionId(0);
        //    if($user_region_id > 0 ){
        //        return $this->redirect($this->generateUrl('region_show', array('id'=> $user_region_id ) ) );
        //    }
        //}
        
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $sql = "SELECT * FROM oivs_passports.oivs_pass_seсs_fields WHERE id_fld LIKE '%__common_info__flag_img'";
        $stmt = $conn->prepare($sql);
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

        
        //var_dump($entities);
       
        return array(
            'entities' => $entities,
            'rolePerms' => $this->rolePerms,
        );
    }
    
    
    /**
     * Finds and displays a Oiv entity.
     *
     * @Route("/new", name="region_new")
     * @Method("GET")
     * @Template("ncuoregion/region/new.html.twig")
     */
    public function actionNew()
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->checkPageAccess() === false || in_array('C', $this->rolePerms) === false ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $arResult = array();
        $arResult['urlAction'] = $this->generateUrl('region_create', array() );
        $arResult['rolePerms'] = $this->rolePerms;
        $arResult['FIELDS'] = array(
                                   "id_oiv" => "",
                                   "name" => "",
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
     * @Route("/new_create", name="region_create")
     * @Method("POST")
     * @Template("ncuoregion/region/new.html.twig")
     */
    public function actionCreateNew(Request $request)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->checkPageAccess() === false || in_array('C', $this->rolePerms) === false ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $arData = array();
        $arResult = array();
        $message = array();
        $date_upd = date('Y-m-d H:i:s');

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
                    'message_text'  => "Субьект РФ с таким идентификатором уже существует",
                );
            }
            elseif( empty($arData['id_oiv']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "Поле `Идентификатор Субьекта РФ (РОИВ)` должно быть заполнено.",
                    'error_explain' => ''
                ); 
            }
            // elseif( preg_match("/[^a-zA-Z_]/", $arData['id_oiv']) )
            elseif( preg_match("/[^a-z]/", $arData['id_oiv']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "В поле `Идентификатор Субьекта РФ (РОИВ)` должны использоваться только латинские буквы в нижнем регистре.",
                    'error_explain' => ''
                ); 
            }
            elseif( empty($arData['name']) )
            {
                $message = array(
                    'message_error' => true,
                    'message_text'  => "Поле `Полное наименование Субьекта РФ (РОИВ)` должно быть заполнено.",
                    'error_explain' => ''
                ); 
            }
            else
            {
                $sql_tmpl = 'INSERT INTO oivs_passports.oivs ( id_oiv, name, id_oiv_type, enabled)  VALUES (:id_oiv, :name, :id_oiv_type, :enabled)';
                
                $params['id_oiv'] = $arData["id_oiv"];
                $params['name'] = $arData["name"];
                $params['id_oiv_type'] = 2;
                $params['enabled'] = true;
                $stmt = $this->prepareCreateOiv($sql_tmpl, $params);
                $stmt->execute();
                
                $oiv = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv' => $arData["id_oiv"]), array('name' => 'ASC'), null , null );
                if ($oiv)
                {
                    //------------------------------------- Sections -------------------------------------------
              
                    //-----------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'common_info',
                            "id_parent_sec"  => null,
                            "sec_name"   => 'Общие сведения',
                            "view_order" => 100,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'economic_info',
                            "id_parent_sec"  => null,
                            "sec_name"   => 'Экономическое развитие',
                            "view_order" => 200,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'social_info',
                            "id_parent_sec"  => null,
                            "sec_name"   => 'Социальная сфера',
                            "view_order" => 300,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'socio_politic_info',
                            "id_parent_sec"  => null,
                            "sec_name"   => 'Общественно-политическая ситуация',
                            "view_order" => 400,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_geo_position',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'common_info',
                            "sec_name"   => 'Административно-географическое положение',
                            "view_order" => 110,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_adm_structure',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'common_info',
                            "sec_name"   => 'Административно-территориальное устройство субъекта РФ',
                            "view_order" => 120,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_gov_structure',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'common_info',
                            "sec_name"   => 'Органы государственной власти и местного самоуправления',
                            "view_order" => 130,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_macroecon',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Макроэкономические показатели',
                            "view_order" => 210,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_industrial',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Промышленное производство',
                            "view_order" => 220,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_foreign_capital_org',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Организации с участием иностранного капитала',
                            "view_order" => 230,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_small_business',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Малые предприятия',
                            "view_order" => 240,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_agriculture',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Сельское хозяйство',
                            "view_order" => 245,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_communication',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Транспорт и связь',
                            "view_order" => 250,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }

                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_building',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Строительство',
                            "view_order" => 255,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_investments',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Инвестиции в основной капитал',
                            "view_order" => 260,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_consumers',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Потребительский рынок',
                            "view_order" => 265,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_prices',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Цены',
                            "view_order" => 270,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_export',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Внешнеэкономическая деятельность',
                            "view_order" => 275,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_finance',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Финансы',
                            "view_order" => 280,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_banking',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'economic_info',
                            "sec_name"   => 'Банковская система',
                            "view_order" => 285,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_demography',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'social_info',
                            "sec_name"   => 'Демография',
                            "view_order" => 310,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_healthcare',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'social_info',
                            "sec_name"   => 'Здравоохранение',
                            "view_order" => 320,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_education',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'social_info',
                            "sec_name"   => 'Образование',
                            "view_order" => 330,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_living_conditions',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'social_info',
                            "sec_name"   => 'Уровень жизни населения',
                            "view_order" => 340,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_housing_conditions',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'social_info',
                            "sec_name"   => 'Жилищные условия населения',
                            "view_order" => 350,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_utilities',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'social_info',
                            "sec_name"   => 'Коммунальное хозяйство',
                            "view_order" => 360,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_culture',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'social_info',
                            "sec_name"   => 'Культура',
                            "view_order" => 370,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_sport',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'social_info',
                            "sec_name"   => 'Физическая культура',
                            "view_order" => 380,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_labor',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'social_info',
                            "sec_name"   => 'Рынок труда',
                            "view_order" => 390,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_criminality',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'social_info',
                            "sec_name"   => 'Криминогенная обстановка',
                            "view_order" => 395,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_electorat',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'socio_politic_info',
                            "sec_name"   => 'Электоральная характеристика',
                            "view_order" => 410,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }

                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_federal_elections',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'socio_politic_info',
                            "sec_name"   => 'Результаты Федеральных выборов',
                            "view_order" => 420,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }

                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_local_elections',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'socio_politic_info',
                            "sec_name"   => 'Выборы органов власти субъекта',
                            "view_order" => 430,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }

                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_situation_rating',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'socio_politic_info',
                            "sec_name"   => 'Оценка населением ситуации в регионе',
                            "view_order" => 440,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }

                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_parties',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'socio_politic_info',
                            "sec_name"   => 'Общественно-политические партии и организации',
                            "view_order" => 450,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }

                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_religion',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'socio_politic_info',
                            "sec_name"   => 'Религия',
                            "view_order" => 460,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }

                    //-----------------------------------------------------------------------------------------------------------------------

                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'s_massmedia',
                            "id_parent_sec"  => $arData["id_oiv"].'__'.'socio_politic_info',
                            "sec_name"   => 'Средства массовой информации',
                            "view_order" => 470,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }

                    //-----------------------------------------------------------------------------------------------------------------------


                

                    // $arParams = array(
                    //         "oiv_id"=> $arData["id_oiv"],
                    //         "sec_id"  => $arData["id_oiv"].'__'.'SEC_IDITY',
                    //         "id_parent_sec"  => $arData["id_oiv"].'__'.'SEC_PARENT',
                    //         "sec_name"   => 'SEC_TITLE',
                    //         "view_order" => 100,
                    // );
                    // $res = $this->createSection($arParams);
                    // if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }


    
                        //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Флаг региона',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => $arData['flag_img'],
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'common_info';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'flag_img';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                        //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Административно-географическое положение',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '
                <table cellpadding="0" cellspacing="0" class="geo_position_1_1__common_1">
                    <tbody>
                        <tr>
                            <td colspan="3">
                                <p><strong>НАИМНОВАНИЕ СУБЪЕКТА РФ</strong></p>
                                <p>Административный центр - г.ГОРОД.</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <p>Площадь территории на&nbsp;1&nbsp;января 2017&nbsp;г. составляет 0000,00 тыс. кв. км</p>
                                <p>(00,00% от территории РФ).</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <p>Население области на&nbsp;1&nbsp;января 2018&nbsp;г. составляет 000,0 тыс. чел.</p>
                                <p>(0,00% от численности населения РФ).</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <p>Доля городского населения на&nbsp;1&nbsp;января 2018&nbsp;г.&nbsp;&ndash; 00,0%.</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <p>Плотность населения на&nbsp;1&nbsp;января 2018&nbsp;г.&nbsp;&ndash; 0,0 чел./кв. км.</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <p>Национальный состав населения</p>
                                <p>(по данным Всероссийской переписи населения 2010&nbsp;г.), %:</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>якуты</p>
                            </td>
                            <td>
                                <p>-</p>
                            </td>
                            <td>
                                <p>00,00</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                        ',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_geo_position';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'geo_position';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Административно-территориальное устройство субъекта РФ',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_adm_structure';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'adm_structure';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Органы государственной власти и местного самоуправления',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_gov_structure';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'gov_structure';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Макроэкономические показатели',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_macroecon';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'macroecon';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Промышленное производство',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_industrial';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'industrial';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Организации с участием иностранного капитала',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_foreign_capital_org';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'foreign_capital_org';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Малые предприятия',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_small_business';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'small_business';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Сельское хозяйство',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_agriculture';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'agriculture';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Транспорт и связь',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_communication';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'communication';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Строительство',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_building';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'building';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Инвестиции в основной капитал',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_investments';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'investments';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Потребительский рынок',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_consumers';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'consumers';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Цены',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_prices';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'prices';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Внешнеэкономическая деятельность',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_export';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'export';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Финансы',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_finance';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'finance';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Банковская система',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_banking';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'banking';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Демография',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_demography';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'demography';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Здравоохранение',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_healthcare';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'healthcare';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Образование',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_education';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'education';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Уровень жизни населения',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_living_conditions';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'living_conditions';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Жилищные условия населения',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_housing_conditions';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'housing_conditions';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Коммунальное хозяйство',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_utilities';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'utilities';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Культура',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_culture';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'culture';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Физическая культура',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_sport';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'sport';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Рынок труда',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_labor';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'labor';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Криминогенная обстановка',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_criminality';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'criminality';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Электоральная характеристика',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_electorat';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'electorat';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Результаты Федеральных выборов',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_federal_elections';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'federal_elections';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Выборы органов власти субъекта',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_local_elections';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'local_elections';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Оценка населением ситуации в регионе',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_situation_rating';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'situation_rating';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Общественно-политические партии и организации',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_parties';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'parties';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Религия',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_religion';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'religion';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //------------------------------------------------------------------------------------------------------------------
    
                    $arParams = array(
                        "fld_name"    => 'Средства массовой информации',
                        "view_order"  => 10,
                        "data_source" => '',
                        "data_val" => '',
                        "id_fld_content_type" => 4,
                        "data_upd" => $date_upd,
                    );
                    $arParams["sec_id"] = $arData["id_oiv"].'__'.'s_massmedia';
                    $arParams["id_fld"] = $arParams["sec_id"].'__'.'massmedia';
                    
                    $res = $this->createField($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
    
                 
                    //------------------------------------------------------------------------------------------------------------------   
                    // $arParams = array(
                    //     "fld_name"    => 'FLD_TITLE',
                    //     "view_order"  => 10,
                    //     "data_source" => '',
                    //     "data_val" => '',
                    //     "id_fld_content_type" => 4,
                    //     "data_upd" => $date_upd,
                    // );
                    // $arParams["sec_id"] = $arData["id_oiv"].'__'.'SEC_ID';
                    // $arParams["id_fld"] = $arParams["sec_id"].'__'.'FLD_ID';
                    
                    // $res = $this->createField($arParams);
                    // if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                  
                    // $arParams = array(
                    //     "fld_name"    => 'FLD_TITLE',
                    //     "view_order"  => 10,
                    //     "data_source" => '',
                    //     "data_val" => '',
                    //     "id_fld_content_type" => 4,
                    //     "data_upd" => $date_upd,
                    // );
                    // $arParams["sec_id"] = $arData["id_oiv"].'__'.'SEC_ID';
                    // $arParams["id_fld"] = $arParams["sec_id"].'__'.'FLD_ID';
                    
                    // $res = $this->createField($arParams);
                    // if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
    
                    //-----------------------------------------------------------------------------------------------------------------------          
                    
                    $arNames = array(
                    1 => "Сведения о количестве детей военнослужащих, состоящих в очереди на получение мест в дошкольных образовательных организациях",
                    2 => "Сведения о количестве членов семей военнослужащих, зарегистрированных в центрах занятости для поиска работы",
                    3 => "Сведения о численности участников регионального отделения Всероссийского детско-юношеского военно-патриотического общественного движения «ЮНАРМИЯ», количестве территориальных организаций и отрядов",
                    4 => "Сведения об основных мероприятиях военно-патриотической направленности, проведенных за прошедший и планируемых на предстоящий месяц",
                    5 => "Сведения о ходе создания парка «Патриот» на территории",
                    6 => "Сведения об оснащенности информационно-коммуникационными системами ситуационного центра",
                    7 => "Сведения по основным региональным автомобильным дорогам",
                    8 => "Перечень государственных строительных и дорожных организаций, расположенных на территории",
                    9 => "Данные по железнодорожной инфраструктуре",
                    10 => "Данные по аэродромной сети",
                    11 => "Характеристика основных водных артерий",
                    12 => "Сведения о составе органов исполнительной власти субъекта Российской Федерации и органов местного самоуправления",
                    13 => "Сведения о состоянии объектов, обеспечивающих жизнедеятельность населения, функционирование транспорта, коммуникаций и связи, объектов энергетики, а также объектов, представляющих повышенную опасность для жизни и здоровья людей и окружающей природной среды",
                    );
                    
                    $arParams = array(
                            "oiv_id"=> $arData["id_oiv"],
                            "sec_id"  => $arData["id_oiv"].'__'.'form_ncu',
                            "id_parent_sec"  => null,
                            "sec_name"   => 'Формы НЦУ',
                            "view_order" => 500,
                    );
                    $res = $this->createSection($arParams);
                    if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                    
                    
                    for($i=1; $i< 14; $i++ )
                    {
                        $sec_id = $arData["id_oiv"].'__'.'ncu_'.$i;

                        $arParams = array(
                                "oiv_id"=> $arData["id_oiv"],
                                "sec_id"  => $sec_id,
                                "id_parent_sec"  => $arData["id_oiv"].'__'.'form_ncu',
                                "sec_name"   => $arNames[$i],
                                "view_order" => 500+($i*5),
                        );
                        $res = $this->createSection($arParams);
                        if( !$res) { $error_message_text .=  "{$arParams['sec_id']}<br>"; }
                        
                                    //----------------------------------------------------------------------------
                        
                        
                        
                        $filename = $_SERVER['DOCUMENT_ROOT']."/templates/ncu_table/ncu_table_{$i}.php";
                        $handle = fopen($filename, "r");
                        $contents = fread($handle, filesize($filename));
                        fclose($handle);
                            
                        $arParams = array(
                            "fld_name"    => $arNames[$i],
                            "view_order"  => 500+($i*5),
                            "data_source" => '',
                            "data_val" => $contents,
                            "id_fld_content_type" => 4,
                            "data_upd" => $date_upd,
                        );
                        $arParams["sec_id"] = $sec_id;
                        $arParams["id_fld"] = $arParams["sec_id"].'__'.'ncu_'.$i;
                        
                        $res = $this->createField($arParams);
                        if( !$res) { $error_message_text .=  "{$arParams['id_fld']}<br>"; }
                        
                    }
                    
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
        $arResult['urlAction'] = $this->generateUrl('region_create', array() );
        $arResult['FIELDS'] = $arData;
        $arResult['rolePerms'] = $this->rolePerms;
        $arResult['message'] = $message;
        
        return $arResult;
    
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
     *
     * @Route("/{id_reg}/edit", name="region_show_edit")
     * @Method("GET")
     * @Template("ncuoregion/item_edit.html.twig")
     */
    public function editAction($id_reg)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->checkPageAccess() === false || $this->rolePerms['update'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
        $arResult = $this->prepareEditFormData($id_reg);
        
        return $arResult;
    
    }
    
    /**
     * Update data
     * @Route("/{id_reg}/update", name="region_show_update")
     * @Method("POST")
     * @Template("ncuoregion/item_edit.html.twig")
     */
    
    public function updateAction(Request $request, $id_reg)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
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
            

            if( isset($arData[$id_reg."__common_info__name_full"]) )
            {
                
                $sql_tmpl = 'UPDATE oivs_passports.oivs SET name=:name WHERE id_oiv=:id_oiv';
                $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                $stmt->bindValue(':id_oiv',  $id_reg);
                $stmt->bindValue(':name',  $arData[$id_reg."__common_info__name_full"]);
                $res = $stmt->execute();
            } 
            if( isset($arData[$id_reg."__common_info__flag_img"]) )
            {
                $id_fld = $id_reg."__common_info__flag_img";
                try{
                    $entity = $em->getRepository('NCUOOivBundle:Field')->find( $id_fld );
                    $entity->setData(trim($arData[$id_reg."__common_info__flag_img"]));
                    $em->flush();
                    $message['message_text'] = 'Данные успешно сохранены.';
                    
                }
                catch(\Exception $e){
                    $message = array(
                        'message_error' => true,
                        'message_text'  => $e->getMessage(),
                        'error_explain'  => $e->getTraceAsString()
                    );
                }
                
            }       

            if( $this->userRoleName == 'ROLE_ADMIN' && !empty($arData[$id_reg."__common_info__oiv_id"]) )
            {

                if( preg_match("/[^a-zA-Z_]/", $arData[$id_reg."__common_info__oiv_id"]) ){
                    $message = array(
                        'message_error' => true,
                        'message_text'  => "В поле `Наименование` должны использоваться только латинские буквы в верхнем регистре и знак нижнего подчеркивания _.",
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
                    $stmt->bindValue(':id_oiv',  $id_reg);
                    $stmt->bindValue(':new_id',  $arData[$id_reg."__common_info__oiv_id"]);
                    $res = $stmt->execute();
                    
                    $sql_tmpl = 'UPDATE oivs_passports.oivs_pass_sections SET id_oiv=:new_id ,
                        id_sec = replace(id_sec, \':id_oiv\', \':new_id\'),
                        id_parent_sec = replace(id_parent_sec, \':id_oiv\', \':new_id\') 
                        WHERE id_oiv=:id_oiv';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $stmt->bindValue(':id_oiv',  $id_reg);
                    $stmt->bindValue(':new_id',  $arData[$id_reg."__common_info__oiv_id"]);
                    $res = $stmt->execute();


                    $sql_tmpl = 'UPDATE oivs_passports.oivs_pass_seсs_fields SET 
                        id_fld = replace(id_fld, :id_oiv, :new_id),
                        id_sec = replace(id_sec, :id_oiv, :new_id) 
                        WHERE id_fld LIKE :like_oiv_id';
                    $stmt = $this->container->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql_tmpl);
                    $stmt->bindValue(':id_oiv',  $id_reg);
                    $stmt->bindValue(':new_id',  $arData[$id_reg."__common_info__oiv_id"]);
                    $stmt->bindValue(':like_oiv_id',  $id_reg.'__%');
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

                    $id_reg = $arData[$id_reg."__common_info__oiv_id"];

                }
            }       
        }
        //echo '<pre>',print_r($arData),'</pre>';

        $arResult = $this->prepareEditFormData($id_reg);
        $arResult['message'] = $message;
        
        return $arResult;
        
    }
    

    /**
     * Finds and displays a Region entity.
     *
     * @Route("/{id_reg}", name="region_show")
     * @Method("GET")
     * @Template("ncuoregion/item_show.html.twig")
     */
    public function showAction($id_reg)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->checkPageAccess() === false || $this->rolePerms['read'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
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

        $tabMenu["parent_id"] = 'common_info'; 
        $tabMenu["current_id"] = $this::SEC_ID;             

        //var_dump($region[0]);
        return array(
            'oiv'      => $region[0],
            'fields'      => $fields,
            'rolePerms' => $this->rolePerms,
            'urlEdit' => $this->generateUrl('region_show_edit', array('id_reg'=>$region[0]->getId() )),
            'tabmenu' => $tabMenu,
            'pageTitle' => $this->pageTitle,
        );
    
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
        $urlAction = $this->generateUrl('region_show_update', array('id_reg'=>$region[0]->getId() ));
        $urlView = $this->generateUrl('region_show_edit', array('id_reg'=>$region[0]->getId() ));
        $urlBack = $this->generateUrl('region_show', array('id_reg'=>$region[0]->getId() ));

        $tabMenu["parent_id"] = 'common_info'; 
        $tabMenu["current_id"] = $this::SEC_ID;  
        
        return array(
            'oiv'      => $region[0],
            'fields'   => $fields,
            "urlAction"=> $urlAction,
            "urlView"  => $urlView,
            "urlBack"  => $urlBack,
            'rolePerms' => $this->rolePerms,
            'userRoleName' => $this->userRoleName,
            'tabmenu' => $tabMenu,
            'pageTitle' => $this->pageTitle,
            );
        
       
    }

    
    /**
     * Delete OIV
     *
     * @Route("/{id_oiv}/delete", name="region_delete")
     * @Method("GET")
     * @Template("ncuoregion/region/delete.html.twig")
     */
    public function deleteAction($id_oiv)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->checkPageAccess() === false || $this->rolePerms['delete'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $message = array();
        $oiv_name = null;
        if ($this->rolePerms['delete'] === true)
        {
            $em = $this->getDoctrine()->getManager();
            $oiv = $em->getRepository('NCUOOivBundle:Oiv')->findBy(array('id_oiv' => $id_oiv, 'id_oiv_type' => 2), array('name' => 'ASC'), null , null );
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
                    'message_text'  => "Субьект РФ (РОИВ) с идентификатором \"{$id_oiv}\" не существует",
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
                'region_name' => $oiv_name,
                'message' => $message,
                'rolePerms' => $this->rolePerms,
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
