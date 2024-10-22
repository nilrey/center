<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\FoivBundle\Controller\ErrorResponseGenerator;
use App\NCUO\FoivBundle\Entity\ORMObjectMetadata;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\FoivBundle\Entity\Systems;
use App\NCUO\FoivBundle\Form\FoivType;
use App\NCUO\PortalBundle\Entity\User;

use App\NCUO\EifBundle\Entity\Protocol;

/**
 * Foiv Systems controller.
  */
class FoivSystemsController extends Controller
{
    // Заказчик попросил убрать из доступа данный функционал - доступ уберем, функционал сохраним
    public function __construct()
    {
        header('Location: /');
        die();
    }


    /**
     * Displays a list of all Foiv systems
     *
     * @Route("/0/foiv_systems", name="foiv_systems")
     * @Method("GET")
     * @Template("ncuofoiv/FoivSystems/index.html.twig")
     */
    public function indexAction()
    {
        
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $stmt = $conn->prepare("SELECT * FROM eif_data.dict_systems ORDER BY name ASC");
        $stmt->execute();        
        $entities = $stmt->fetchAll();

        $stmt = $conn->prepare("SELECT * FROM eif.protocols ORDER BY protocol_name ASC");
        $stmt->execute();        
        $protocols = $stmt->fetchAll();

        $stmt = $conn->prepare("SELECT id, name FROM eif_data.dict_foiv ");
        $stmt->execute();        
        $foivs = $stmt->fetchAll();

        $stmt = $conn->prepare("SELECT * FROM eif_data.dict_status_yn ");
        $stmt->execute();        
        $status_yn = $stmt->fetchAll();

        $stmt = $conn->prepare("SELECT * FROM eif_data.dict_status_3_opt ");
        $stmt->execute();        
        $status_3_opt = $stmt->fetchAll();

        if( count($entities) > 0  && count($foivs) > 0  ) {
            foreach ($entities as &$arItem) {
                $arItem['foiv_name'] = "";
                $arItem['protocol_name'] = "";
                $arItem['is_integration_title'] = "";
                $arItem['vipnet_title'] = "";
                $arItem['protocol_its_title'] = "";
                $arItem['integration_spo_title'] = "";
                
                foreach ($foivs as $foiv) {
                    if( $arItem['foiv'] == $foiv['id'] ){
                        $arItem['foiv_name'] = trim($foiv['name']);
                    }
                }
                foreach ($status_yn as $tmp) {
                    if( intval($arItem['is_integration']) == $tmp['id'] ){
                        $arItem['is_integration_title'] = trim($tmp['name']);
                    }
                    if( intval($arItem['vipnet']) == $tmp['id'] ){
                        $arItem['vipnet_title'] = trim($tmp['name']);
                    }
                }
                foreach ($status_3_opt as $tmp) {
                    if( intval($arItem['protocol_its']) == $tmp['id'] ){
                        $arItem['protocol_its_title'] = trim($tmp['protocol_its']);
                    }
                    if( intval($arItem['integration_spo']) == $tmp['id'] ){
                        $arItem['integration_spo_title'] = trim($tmp['integration_spo']);
                    }
                }
                foreach ($protocols as $protocol) {
                    if( $arItem['protocol_id'] == $protocol['id_protocol'] ){
                        $arItem['protocol_name'] = trim($protocol['protocol_name']);
                    }
                }
            }
        }

        // $em = $this->getDoctrine()->getManager();
        // $entities = $em->getRepository('NCUOFoivBundle:Systems')->findBy(array(), array('name' => 'ASC'));
        // $protocols = $em->getRepository('NCUOEifBundle:Protocol')->findBy( array() , array("protocol_name" => "asc"));


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
          'urlCreate' => $this->generateUrl('foiv_systems_edit', array('id'=> 0 ) ),
          'protocols' => $protocols,
          'urlProtocol' => $urlProtocol,
        );
    }
    
    /**
     * Displays a form to create a new FoivSystems entity.
     *
     * @Route("/0/foiv_systems/{id}/view", name="foiv_systems_view")
     * @Method("GET")
     * @Template("ncuofoiv/FoivSystems/view.html.twig")
     */
    public function viewRecord($id)
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NCUOFoivBundle:Systems')->find($id);
        $foivList = $em->getRepository('NCUOFoivBundle:Foiv')->findAll();
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
            //'urlNewPerson'   => $this->generateUrl('foiv_systems_add_person', array( 'foivid' => $entity->getFoivFk()->getId() , 'id' => $entity->getId() ) ),
            'urlCreate'   => $this->generateUrl('foiv_systems_edit', array('id'=> 0 ) ),
            'urlEdit'   => $this->generateUrl('foiv_systems_edit', array('id'=> $entity->getId() ) ),
            'urlBack'   => $this->generateUrl('foiv_systems'),
        );
    }

    /**
     * Displays a form to create a new FoivSystems entity.
     *
     * @Route("/0/foiv_systems/0/edit", name="foiv_systems_new")
     * @Method("GET")
     * @Template("ncuofoiv/FoivSystems/edit.html.twig")
     */
    public function newForm()
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
        $entity = new Systems();
        $foivList = $em->getRepository('NCUOFoivBundle:Foiv')->findBy( array() , array("name" => "asc"));
        $protocols = $em->getRepository('NCUOEifBundle:Protocol')->findBy( array() , array("protocol_name" => "asc"));
        $statusYn = $em->getRepository('NCUOFoivBundle:StatusYn')->findAll();
        $status3Opt = $em->getRepository('NCUOFoivBundle:Status3Opt')->findAll();
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
            'statusYn'  => $statusYn,
            'status3Opt'  => $status3Opt,
            'urlProtocol' => $urlProtocol,
            'createURL'   => $this->generateUrl('foiv_systems_edit', array('id'=> 0 ) ),
            'urlAction'   => $this->generateUrl('foiv_systems_edit', array('id'=> 0 ) ),
            'urlBack'   => $this->generateUrl('foiv_systems'),
        );
    }
    
    /**
     * Displays a form to create a new FoivSystems entity.
     *
     * @Route("/0/foiv_systems/0/edit", name="foiv_systems_create")
     * @Method("POST")
     * @Template("ncuofoiv/FoivSystems/edit.html.twig")
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
          $isIntegration = $em->getRepository('NCUOFoivBundle:StatusYn')->find( intval($request->request->get("is_integration")) );
          $vipnet = $em->getRepository('NCUOFoivBundle:StatusYn')->find( intval($request->request->get("vipnet")) );
          $protocolIts = $em->getRepository('NCUOFoivBundle:Status3Opt')->find( intval($request->request->get("protocol_its")) );
          $integrationSpo = $em->getRepository('NCUOFoivBundle:Status3Opt')->find( intval($request->request->get("integration_spo")) );
          
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
          $entity->setIsIntegration( $isIntegration );
          $entity->setVipnet( $vipnet );
          $entity->setProtocolIts( $protocolIts );
          $entity->setIntegrationSpo( $integrationSpo );
  
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
        
        return $this->redirect($this->generateUrl('foiv_systems_edit', array( 'id'=> $entity->getId() ) ) );
    }
    

    /**
     * Displays a form to create a new FoivSystems entity.
     *
     * @Route("/0/foiv_systems/{id}/edit", name="foiv_systems_edit")
     * @Method("GET")
     * @Template("ncuofoiv/FoivSystems/edit.html.twig")
     */
    public function editForm($id)
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NCUOFoivBundle:Systems')->find($id);
        $foivList = $em->getRepository('NCUOFoivBundle:Foiv')->findBy( array() , array("name" => "asc"));
        $protocols = $em->getRepository('NCUOEifBundle:Protocol')->findBy( array() , array("protocol_name" => "asc"));
        $statusYn = $em->getRepository('NCUOFoivBundle:StatusYn')->findAll();
        $status3Opt = $em->getRepository('NCUOFoivBundle:Status3Opt')->findAll();
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
            'statusYn'  => $statusYn,
            'status3Opt'  => $status3Opt,
            //'urlNewPerson'   => $this->generateUrl('foiv_systems_add_person', array( 'foivid' => $entity->getFoivFk()->getId() , 'id' => $entity->getId() ) ),
            'createURL'   => $this->generateUrl('foiv_systems_edit', array('id'=> 0 ) ),
            'urlAction'   => $this->generateUrl('foiv_systems_edit', array('id'=> $entity->getId() ) ),
            'urlBack'   => $this->generateUrl('foiv_systems'),
            'urlView'   => $this->generateUrl('foiv_systems_view', array('id'=> $entity->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivSystems entity.
     *
     * @Route("/0/foiv_systems/{id}/edit", name="foiv_systems_update")
     * @Method("POST")
     * @Template("ncuofoiv/FoivSystems/edit.html.twig")
     */
    public function updateRecord(Request $request, $id)
    {
        try{
          $message = $this->initMessage();
          $em = $this->getDoctrine()->getManager();
          //if( intval($request->request->get("protocol_id")) > 0 ){
            $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find( $request->request->get("foiv") );
          //}
          if( strlen($request->request->get("protocol_id")) > 0 ){
            $protocol = $em->getRepository('NCUOEifBundle:Protocol')->find( $request->request->get("protocol_id") );
          }else{
            $protocol = null;
          }

          $isIntegration = $em->getRepository('NCUOFoivBundle:StatusYn')->find( intval($request->request->get("is_integration")) );
          $vipnet = $em->getRepository('NCUOFoivBundle:StatusYn')->find( intval($request->request->get("vipnet")) );
          $protocolIts = $em->getRepository('NCUOFoivBundle:Status3Opt')->find( intval($request->request->get("protocol_its")) );
          $integrationSpo = $em->getRepository('NCUOFoivBundle:Status3Opt')->find( intval($request->request->get("integration_spo")) );

          $entity = $em->getRepository('NCUOFoivBundle:Systems')->find($id);
          
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
          $entity->setIsIntegration( $isIntegration );
          $entity->setVipnet( $vipnet );
          $entity->setProtocolIts( $protocolIts );
          $entity->setIntegrationSpo( $integrationSpo );
  

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
        
        return $this->redirect($this->generateUrl('foiv_systems_edit', array( 'id'=> $entity->getId() ) ) );
    }

    /**
     * Displays a form to create a new FoivSystems entity.
     *
     * @Route("/0/foiv_systems/{id}/delete", name="foiv_systems_delete")
     * @Method("POST")
     * @Template("ncuofoiv/FoivSystems/index.html.twig")
     */
    public function deleteRecord($id)
    {
        $message = $this->initMessage();

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NCUOFoivBundle:Systems')->find($id);
        
            if (!$entity)
            {
              $resp['err_id']  = 1;
              $resp['res_msg'] = "Foiv not found";
                //throw $this->createNotFoundException('Foiv not found');
            }
        try{
            $em->remove($entity);
            $em->flush();
            
            $resp['err_id']  = 0;
            $resp['res_msg'] = 'Record deleted!';
            
        }catch(Exception $e){
          
            $resp['err_id']  = 1;
            $resp['res_msg'] = $e->getMessage();
        }
        
        //$response = new Response();
        //$response->setContent( json_encode( $message) );
        //
        //return $response;
        return new Response(Response::HTTP_OK );
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