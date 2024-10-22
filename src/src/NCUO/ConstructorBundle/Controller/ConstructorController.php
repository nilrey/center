<?php
namespace App\NCUO\ConstructorBundle\Controller;


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


/**
 * Constructor controller.
 */
class ConstructorController extends Controller
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
     * Lists all Region entities.
     *
     * @Route("/", name="constructor")
     * @Method("GET")
     * @Template("constructor/pages/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        // if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        // if ( $this->checkPageAccess() === false || in_array('R', $this->rolePerms) === false ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $arFilter = array('id_oiv_type' => 2);

        // $em = $this->getDoctrine()->getManager();
        // $entities = $em->getRepository('NCUOOivBundle:Oiv')->findBy($arFilter, array('name' => 'ASC')); 
        
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $sql = "SELECT * FROM constructor.tables";
        $stmt = $conn->prepare($sql);
        $stmt->execute();        
        $tables = $stmt->fetchAll(); 

        
        //var_dump($entities);
       
        return array(
            'entities' => $tables,
            'rolePerms' => $this->rolePerms,
            'urlAction' => $this->generateUrl('table_new_step_1', array() ),
            'deleteUrl' => $this->generateUrl('constructor', array() )
        );
    }
    
    

    /**
     * Создание новой таблицы - начальные параметры
     *
     * @Route("/table_new_step_1", name="table_new_step_1")
     * @Method("POST")
     * @Template("constructor/pages/new_step_1.html.twig")
     */
    public function actionNewStep1(Request $request)
    {
        // if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        // if ( $this->checkPageAccess() === false || in_array('C', $this->rolePerms) === false ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
        $arData = array();
        $arResult = array();
        $message = array();
        $date_upd = date('Y-m-d H:i:s');

        if ( is_array($request->request->get("FIELDS") ) )
        {
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = trim($value);
            }
        }

        $arResult['urlAction'] = $this->generateUrl('table_new_step_2', array() );
        $arResult['FIELDS'] = array();

        return $arResult;
    }


    /**
     * Задать названия колонок
     *
     * @Route("/table_new_step_2", name="table_new_step_2")
     * @Method("POST")
     * @Template("constructor/pages/new_step_2.html.twig")
     */
    public function actionNewStep2(Request $request)
    {
        // if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        // if ( $this->checkPageAccess() === false || in_array('C', $this->rolePerms) === false ) return $this->redirect($this->generateUrl('url_error', array() ) );
        $_SESSION["ConstructorTable"] = array("New" => array() );
        $arData = array();
        $arResult = array();
        $message = array();
        $date_upd = date('Y-m-d H:i:s');

        if ( is_array($request->request->get("FIELDS") ) )
        {
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = trim($value);
            }
        }


        $_SESSION["ConstructorTable"]["New"] = $arData;

        $arResult['urlAction'] = $this->generateUrl('table_new_step_3', array() );
        $arResult['FIELDS'] = array();
        $arResult['PARAMS'] = $arData;

        return $arResult;
    }

    /**
     * Задать названия колонок 2 уровня если нужно
     *
     * @Route("/table_new_step_3", name="table_new_step_3")
     * @Method("POST")
     * @Template("constructor/pages/new_step_3.html.twig")
     */
    public function actionNewStep3(Request $request)
    {
        // if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        // if ( $this->checkPageAccess() === false || in_array('C', $this->rolePerms) === false ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $arData = $_SESSION["ConstructorTable"]["New"];
        $arResult = array();
        $message = array();
        $date_upd = date('Y-m-d H:i:s');

        if ( is_array($request->request->get("FIELDS") ) )
        {
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = $value;
            }
        }

        $_SESSION["ConstructorTable"]["New"] = $arData;
        // var_dump($_SESSION["ConstructorTable"]["New"] );

        $arResult['urlAction'] = $this->generateUrl('table_new_step_4', array() );
        $arResult['FIELDS'] = array();
        $arResult['PARAMS'] = $arData;

        return $arResult;
    }

    /**
     * Просмотр шаблона, добавить поля
     *
     * @Route("/table_new_step_4", name="table_new_step_4")
     * @Method("POST")
     * @Template("constructor/pages/new_step_4.html.twig")
     */
    public function actionNewStep4(Request $request)
    {
        // if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        // if ( $this->checkPageAccess() === false || in_array('C', $this->rolePerms) === false ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $arData = array();
        $arResult = array();
        $message = array();
        $date_upd = date('Y-m-d H:i:s');

        if ( is_array($request->request->get("FIELDS") ) )
        {
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = trim($value);
            }
        }
        
        $arResult['urlAction'] = $this->generateUrl('table_new_step_5', array() );
        $arResult['FIELDS'] = array();

        return $arResult;
    }

    /**
     * Сохранить шаблон
     *
     * @Route("/table_new_step_5", name="table_new_step_5")
     * @Method("POST")
     * @Template("constructor/pages/new_step_5.html.twig")
     */
    public function actionNewStep5(Request $request)
    {
        // if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        // if ( $this->checkPageAccess() === false || in_array('C', $this->rolePerms) === false ) return $this->redirect($this->generateUrl('url_error', array() ) );

        $arData = array();
        $arResult = array();
        $message = array();
        $date_upd = date('Y-m-d H:i:s');

        if ( is_array($request->request->get("FIELDS") ) )
        {
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = trim($value);
            }
        }

        $arResult['urlAction'] = $this->generateUrl('table_new_step_5', array() );
        $arResult['FIELDS'] = array();
        if ( !empty($request->request->get("save_form") ) ){
            $arResult['message']['message_text'] = "Шаблон таблицы 'Макроэкономические показатели региона' успешно сохранен";
        }

        return $arResult;
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