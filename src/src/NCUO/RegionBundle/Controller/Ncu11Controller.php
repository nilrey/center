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
 * Ncu11  controller.
 */
class Ncu11Controller extends Controller
{
    
    const SEC_ID = "ncu_11";

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
    private $pageTitle = 'НЦУ/11. Характеристика основных водных артерий';

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
     * @Route("/{id_reg}/ncu11", name="ncu11")
     * @Method("GET")
     * @Template("ncuoregion/item_show.html.twig")
     */
    public function showAction($id_reg)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->rolePerms['read'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
        $arResult = $this->prepareEditFormData($id_reg);
        $arResult['urlEdit'] = $this->generateUrl('ncu11_edit', array('id_reg'=>$id_reg ));
        
        return $arResult;    
    
    }
    
    /**
     *
     * @Route("/{id_reg}/ncu11/edit", name="ncu11_edit")
     * @Method("GET")
     * @Template("ncuoregion/item_edit.html.twig")
     */
    public function editAction(Request $request, $id_reg)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        if ( $this->rolePerms['update'] !== true ) return $this->redirect($this->generateUrl('url_error', array() ) );
        
        $arResult = $this->prepareEditFormData($id_reg);
     
        $ncu_form_nmbr = 11;
        
        $templ = $request->get('templ');
        $fld = $request->get('fld');
        if ( $templ == 'default' )
        {
            foreach($arResult['fields'] as $objField )
            {
                if( $objField->getId() == $fld )
                {
                    $filename = $this->get('kernel')->getProjectDir()."/templates/ncu_table/ncu_table_{$ncu_form_nmbr}.php";
                    $handle = fopen($filename, "r");
                    if( $handle ) {
                        $content = fread($handle, filesize($filename));
                        
                        $objField->setData($content);
                        
                    }
                }
            }
        }
        
        return $arResult;
    
    }
    

    /**
     *
     * @Route("/{id_reg}/ncu11/export", name="ncu11_export")
     * @Method("GET")
     * @Template("ncuoregion/item_edit.html.twig")
     */
    public function exportAction(Request $request, $id_reg)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        $arResult = $this->prepareEditFormData($id_reg);
        $arResult['export'] = true;
        $outputData="";
        $headerRowsExсlude = 1;
        $message = array(
            'message_text'  => '',
            'message_error' => false,
            'error_explain' => ''
        );
     
        $content = $arResult['fields'][0]->getData();
        $content = mb_convert_encoding($content, 'CP1251', 'UTF-8');
        $tblStart = strpos($content, '<table class="form_ncu');
        $tblEnd = strpos($content, '</table>');
        $tblContent = substr($content, $tblStart, ($tblEnd - $tblStart) );

        $arTblContent = explode('<tr', $tblContent);
        array_shift($arTblContent);
        $arTrContent = array_slice($arTblContent, $headerRowsExсlude);
        foreach ($arTrContent as $trContent) {
            $arTmp = explode('<td', $trContent);
            array_shift($arTmp);
            foreach ($arTmp as $key => &$value) {
                $value = '<td'.$value;
                $value = trim(strip_tags($value));
                $value = str_replace(array('&nbsp;'), '', $value);
                $value = str_replace(';', '', $value);
                // echo "<br>{$value}";
            }
            $arTdContent[] = $arTmp;
        }
        $sep = ';'; // chr(9) // tab
        foreach ($arTdContent as $key => $arValues) {
            $outputData .= implode($sep, $arValues).PHP_EOL;
        }

        $filename = 'export_'.$this::SEC_ID.'.csv';
        $file_path = $this->get('kernel')->getProjectDir().'/public/export/'.$this::SEC_ID.'/'.$filename;
        if (is_writable($file_path)) {
            if (!$handle = fopen($file_path, 'w+')) {
                $message['message_error'] = true;
                $message['message_text'] = "Ошибка открытия файла ({$file_path})";
                exit;
            }
            if (fwrite($handle, $outputData) === FALSE) {
                $message['message_error'] = true;
                $message['message_text'] = "Ошибка записи в файл ({$file_path})";
                exit;
            }
            if( !$message['message_error'] ) $arResult['export'] = true;
            fclose($handle);
        } else {
                $message['message_error'] = true;
                $message['message_text'] = "Ошибка. Файл {$file_path} недоступен для записи";
        }
        $arResult['export_file_link'] = $this::SEC_ID.'/'.$filename;
        
        return $arResult;
    }
    
    /**
     *
     * @Route("/{id_reg}/ncu11/import", name="ncu11_import")
     * @Method("GET")
     * @Template("ncuoregion/item_edit.html.twig")
     */
    public function importAction(Request $request, $id_reg)
    {
        if ( !$this->isAuthActive() ) return $this->redirect($this->generateUrl('login', array() ) );
        $arResult = $this->prepareEditFormData($id_reg);
        $arResult['import'] = true;
        $strContent="";
        $strTemplate = "";
        $message = array(
            'message_text'  => '',
            'message_error' => false,
            'error_explain' => ''
        );
        $data = $request->request->all();
        $file = $request->files->get('import_file');
        if($file):
            $handle = @fopen($file->getPathname(), "r");

            $cnt = 0;
            $cntColumn = 0;
            while (($line = fgets($handle)) !== false ) {
                $strContentTr = mb_convert_encoding($line, 'UTF-8', 'CP1251');
                $arContentTr = explode(';', $strContentTr);
                if( !$cnt++): // check First line
                    $lastTd = 0;
                    // count columns 
                    while ( $lastTd == 0 && count($arContentTr) > 0 ){
                        $lastTd = intval(array_pop($arContentTr));
                        if( $lastTd > 0 ) $arContentTr[]=$lastTd;
                    }
                    $cntColumn = count($arContentTr);
                else:
                    $arContentTr = array_slice($arContentTr, 0, $cntColumn);
                    $strContent .= '<tr><td>'.implode('</td><td>', $arContentTr).'</td></tr>'.PHP_EOL;
                endif;
            }

            $fileTemplate = $this->get('kernel')->getProjectDir()."/templates/ncu_table/import_tpl_".$this::SEC_ID.".php";

            $handle = fopen($fileTemplate, "r");
            if( $handle ) {
                $strTemplate = fread($handle, filesize($fileTemplate));
            }

        endif;

        $strData = str_replace(array("##IMPORT_YEAR_NOW##", "##IMPORT_YEAR_PREV##", "##IMPORT_DATE_NOW##", "##IMPORT_DATA##"), array(date("Y") , date("Y")-1, date("d.m.y") , $strContent ), $strTemplate);

        $arResult['fields'][0]->setData($strData);


        if( !$message['message_error'] ) $message['message_text'] = 'Данные успешно обработаны. Для завершения загрузки сохраните данные.';
        
        $arResult['message'] = $message;
        
        return $arResult;
    
    }
    
    /**
     * Update data
     * @Route("/{id_reg}/ncu11/update", name="ncu11_update")
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
        $urlAction = $this->generateUrl('ncu11_update', array('id_reg'=>$region[0]->getId() ));
        $urlView = $this->generateUrl('ncu11_edit', array('id_reg'=>$region[0]->getId() ));
        $urlBack = $this->generateUrl('ncu11', array('id_reg'=>$region[0]->getId() ));

        $tabMenu["parent_id"] = 'ncu_forms'; 
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
