<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\FoivBundle\Controller\ErrorResponseGenerator;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\FoivBundle\Entity\FoivItsMonitoring;
use App\NCUO\PortalBundle\Entity\User;

use App\NCUO\EifBundle\Entity\Protocol;

/**
 * Foiv's ITS controller.
  */
class FoivItsMonitoringController extends Controller
{

    /**
     * Displays a list of all Foiv's ITS
     *
     * @Route("/0/foiv_its_monitoring", name="foiv_its_monitoring")
     * @Method("GET")
     * @Template("ncuofoiv/FoivItsMonitoring/index.html.twig")
     */
    public function indexAction()
    {
        /*
        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->getMonitoringFoivListByName();
        $entities = $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->findAll();


        $em = $this->getDoctrine()->getEntityManager();
        foreach( $entities as $entity ){
            if( intval(  $entity->getFoivId()->getId() ) > 0 ){
                $entity->setCabinetSum(  
                   $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countCabinets( $entity->getFoivId()->getId() )
                );
                $entity->setEmailSum(  
                   $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countEmails( $entity->getFoivId()->getId(), "@centr2014.local" )
                );
                
                $entity->setItsProtocolsByStatus(  
                   //$this->getDoctrine()->getRepository('NCUOEifBundle:FoivItsMonitoring')->countItsProtocol( $entity->getFoivId()->getId() )
                   $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countItsProtocol( $entity->getFoivId()->getId() )
                );
                
                $entity->setSystemSum(  
                   $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countSystems( $entity->getFoivId()->getId() )
                );
                
                //file_put_contents("/var/www/ncuo-cms/app/logs/1.log", print_r($entity->getItsProtocolsByStatus(), true ) );
            }
        }
        
        //$sources = $em->getRepository('NCUOFoivBundle:Systems')->findBy(array(), array('name' => 'ASC'));
        $protocols = $em->getRepository('NCUOEifBundle:Protocol')->findBy( array() , array("protocol_name" => "asc"));
        */

        $conn = $this->getDoctrine()->getManager()->getConnection();
        $stmt = $conn->prepare("SELECT * FROM eif_data.dict_foiv_its_monitoring");
        $stmt->execute();        
        $entities = $stmt->fetchAll();

        $stmt = $conn->prepare("SELECT * FROM eif.protocols ORDER BY protocol_name ASC");
        $stmt->execute();        
        $protocols = $stmt->fetchAll();

        if ($this->isGranted('ROLE_ADMIN')){
            $urlProtocol = '/eif/adm/forms';
        }else if ($this->isGranted('ROLE_FOIV')){
            $urlProtocol = '/eif/user/forms';
        }else if ($this->isGranted('ROLE_NCUO') || $this->isGranted('ROLE_VDL')){
            $urlProtocol = '/eif/ncuo/forms';
        }else{
            $urlProtocol = '';
        }
         
        return array(
          "entities"=>$entities,
          'urlCreate' => $this->generateUrl('foiv_its_monitoring_edit', array('id'=> 0 ) ),
          'protocols' => $protocols,
          'urlProtocol' => $urlProtocol,
        );
    }
    
    /**
     * Displays a form to create a new FoivItsMonitoring entity.
     *
     * @Route("/0/foiv_its_monitoring/{id}/view", name="foiv_its_monitoring_view")
     * @Method("GET")
     * @Template("ncuofoiv/FoivItsMonitoring/view.html.twig")
     */
    public function viewRecord($id)
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->find($id);
        $foivList = $em->getRepository('NCUOFoivBundle:Foiv')->findBy( array() , array("name" => "asc"));
        $protocols = $em->getRepository('NCUOEifBundle:Protocol')->findBy( array() , array("protocol_name" => "asc"));
        if ($this->isGranted('ROLE_ADMIN')){
            $urlProtocol = '/eif/adm/forms';
        }else if ($this->isGranted('ROLE_FOIV')){
            $urlProtocol = '/eif/user/forms';
        }else if ($this->isGranted('ROLE_NCUO') || $this->isGranted('ROLE_VDL')){
            $urlProtocol = '/eif/ncuo/forms';
        }else{
            $urlProtocol = '#';
        }
        

        if( intval(  $entity->getFoivId()->getId() ) > 0 ){
            $entity->setCabinetSum(  
               $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countCabinets( $entity->getFoivId()->getId() )
            );
            $entity->setItsProtocolsByStatus(  
               $this->getDoctrine()->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countItsProtocol( $entity->getFoivId()->getId() )
            );
            $entity->setEmailSum(  
               $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countEmails( $entity->getFoivId()->getId(), "@centr2014.local" )
            );
            $entity->setSystemSum(  
               $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countSystems( $entity->getFoivId()->getId() )
            );
        }        
        
        $statusYn = $em->getRepository('NCUOFoivBundle:StatusYn')->findAll();
        $status3Opt = $em->getRepository('NCUOFoivBundle:Status3Opt')->findAll();
        $conventionList = $em->getRepository('NCUOFoivBundle:FoivItsConvention')->findAll();
        
        return array(
            'entity' => $entity,
            'message' => $message,
            'foivList' => $foivList,
            'protocols' => $protocols,
            'urlProtocol' => $urlProtocol,
            'statusYn'  => $statusYn,
            'status3Opt'  => $status3Opt,
            'conventionList' => $conventionList,
            'urlCreate'   => $this->generateUrl('foiv_its_monitoring_edit', array('id'=> 0 ) ),
            'urlBack'   => $this->generateUrl('foiv_its_monitoring'),
            'urlEdit'   => $this->generateUrl('foiv_its_monitoring_edit', array('id'=> $entity->getId() ) ),
        );
    }

    /**
     * Displays a form to create a new FoivItsMonitoring entity.
     *
     * @Route("/0/foiv_its_monitoring/0/edit", name="foiv_its_monitoring_new")
     * @Method("GET")
     * @Template("ncuofoiv/FoivItsMonitoring/edit.html.twig")
     */
    public function newForm()
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
        $entity = new Systems();
        $foivList = $em->getRepository('NCUOFoivBundle:Foiv')->findBy( array() , array("name" => "asc"));
        $protocols = $em->getRepository('NCUOEifBundle:Protocol')->findBy( array() , array("protocol_name" => "asc"));
        if ($this->isGranted('ROLE_ADMIN')){
            $urlProtocol = '/eif/adm/forms';
        }else if ($this->isGranted('ROLE_FOIV')){
            $urlProtocol = '/eif/user/forms';
        }else if ($this->isGranted('ROLE_NCUO') || $this->isGranted('ROLE_VDL')){
            $urlProtocol = '/eif/ncuo/forms';
        }else{
            $urlProtocol = '#';
        }
        
        return array(
            'entity' => $entity,
            'message' => $message,
            'foivList' => $foivList,
            'protocols' => $protocols,
            'urlProtocol' => $urlProtocol,
            'createURL'   => $this->generateUrl('foiv_its_monitoring_edit', array('id'=> 0 ) ),
            'urlAction'   => $this->generateUrl('foiv_its_monitoring_edit', array('id'=> 0 ) ),
            'urlBack'   => $this->generateUrl('foiv_its_monitoring'),
        );
    }
    
    /**
     * Displays a form to create a new FoivItsMonitoring entity.
     *
     * @Route("/0/foiv_its_monitoring/0/edit", name="foiv_its_monitoring_create")
     * @Method("POST")
     * @Template("ncuofoiv/FoivItsMonitoring/edit.html.twig")
     */
    public function createNewRecord(Request $request)
    {
        try{
          $message = $this->initMessage();
          $em = $this->getDoctrine()->getManager();
          $entity = new Systems();
          $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find( $request->request->get("foiv") );
          if( strlen($request->request->get("protocol_id")) > 0 ){
            $protocol = $em->getRepository('NCUOEifBundle:Protocol')->find( $request->request->get("protocol_id") );
          }else{
            $protocol = null;
          }
          
          $entity->setName($request->request->get("name" ));
          $entity->setEngName($request->request->get("engName" ));
          $entity->setOwner($request->request->get("owner" ));
          $entity->setDeveloper($request->request->get("developer" ));
          $entity->setContactperson($request->request->get("contactperson" ));
          $entity->setContactphone($request->request->get("contactphone" ));
          $entity->setDescription($request->request->get("description" ));
          $entity->setType($request->request->get("type" ));
          $entity->setLogin($request->request->get("login" ));
          $entity->setPassword($request->request->get("password" ));
          $entity->setUrl($request->request->get("url" ));
          $entity->setVersion(intval($request->request->get("version" )));
          $entity->setAutologinForm($request->request->get("autologinForm" ));
          $entity->setRegistryLink($request->request->get("registryLink" ));
          $entity->setProtocolId( $protocol );
          $entity->setFoiv($foiv);
  
          $em->persist($entity);
          $em->flush();

          $this->addFlash(
              'notice',
              $this->container->getParameter('msg.updated')
          );
            
        }catch(Exception $e){
            $message = array(
                'message_error' => true,
                'message_text'  => $e->getMessage(),
                'error_explain'  => $e->getTraceAsString()
            );
        }
        
        return $this->redirect($this->generateUrl('foiv_its_monitoring_edit', array( 'id'=> $entity->getId() ) ) );
    }
    

    /**
     * Displays a form to create a new FoivItsMonitoring entity.
     *
     * @Route("/0/foiv_its_monitoring/{id}/edit", name="foiv_its_monitoring_edit")
     * @Method("GET")
     * @Template("ncuofoiv/FoivItsMonitoring/edit.html.twig")
     */
    public function editForm($id)
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->find($id);
        $foivList = $em->getRepository('NCUOFoivBundle:Foiv')->findBy( array() , array("name" => "asc"));
        $protocols = $em->getRepository('NCUOEifBundle:Protocol')->findBy( array() , array("protocol_name" => "asc"));
        if ($this->isGranted('ROLE_ADMIN')){
            $urlProtocol = '/eif/adm/forms';
        }else if ($this->isGranted('ROLE_FOIV')){
            $urlProtocol = '/eif/user/forms';
        }else if ($this->isGranted('ROLE_NCUO') || $this->isGranted('ROLE_VDL')){
            $urlProtocol = '/eif/ncuo/forms';
        }else{
            $urlProtocol = '#';
        }
        

        if( intval(  $entity->getFoivId()->getId() ) > 0 ){
            $entity->setCabinetSum(  
               $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countCabinets( $entity->getFoivId()->getId() )
            );
            $entity->setItsProtocolsByStatus(  
               $this->getDoctrine()->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countItsProtocol( $entity->getFoivId()->getId() )
            );
            $entity->setEmailSum(  
               $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countEmails( $entity->getFoivId()->getId(), "@centr2014.local" )
            );
            $entity->setSystemSum(  
               $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->countSystems( $entity->getFoivId()->getId() )
            );
        }        
        
        $statusYn = $em->getRepository('NCUOFoivBundle:StatusYn')->findAll();
        $status3Opt = $em->getRepository('NCUOFoivBundle:Status3Opt')->findAll();
        $conventionList = $em->getRepository('NCUOFoivBundle:FoivItsConvention')->findAll();
        
        return array(
            'entity' => $entity,
            'message' => $message,
            'foivList' => $foivList,
            'protocols' => $protocols,
            'urlProtocol' => $urlProtocol,
            'statusYn'  => $statusYn,
            'status3Opt'  => $status3Opt,
            'conventionList' => $conventionList,
            'createURL'   => $this->generateUrl('foiv_its_monitoring_edit', array('id'=> 0 ) ),
            'urlAction'   => $this->generateUrl('foiv_its_monitoring_edit', array('id'=> $entity->getId() ) ),
            'urlBack'   => $this->generateUrl('foiv_its_monitoring'),
            'urlView'   => $this->generateUrl('foiv_its_monitoring_view', array('id'=> $entity->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivItsMonitoring entity.
     *
     * @Route("/0/foiv_its_monitoring/{id}/edit", name="foiv_its_monitoring_update")
     * @Method("POST")
     * @Template("ncuofoiv/FoivItsMonitoring/edit.html.twig")
     */
    public function updateRecord(Request $request, $id)
    {
        try{
          $message = $this->initMessage();
          $em = $this->getDoctrine()->getManager();
          
          $entity = $em->getRepository('NCUOFoivBundle:FoivItsMonitoring')->find($id);
          $entity->setConvention($em->getRepository('NCUOFoivBundle:FoivItsConvention')->find( $request->request->get("convention" ) ));
          $entity->setVipnet($request->request->get("vipnet" ));
          $entity->setVideo($request->request->get("video" ));
          $entity->setArm($request->request->get("arm" ));
          
          //$foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find( $entity->getFoivId() );         
          //$entity->setFoivId($foiv);
  

          $em->flush();

          $this->addFlash(
              'notice',
              $this->container->getParameter('msg.updated')
          );                        
            
        }catch(Exception $e){
             $this->addFlash(
                'error',
                $e->getMessage()
            );  
        }
        
        return $this->redirect($this->generateUrl('foiv_its_monitoring_edit', array( 'id'=> $entity->getId() ) ) );
    }

    public function checkUserAccess(){
        if ( $this->isGranted('ROLE_FOIV')){
            return false;
        }
        return true;
    }

    public function initMessage(){
        return array("type"=>0, "text"=>"", "trace"=>"");
    }        
}