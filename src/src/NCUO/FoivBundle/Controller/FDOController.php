<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\FoivBundle\Entity\FoivFdo;
use App\NCUO\FoivBundle\Entity\FoivFdoPersons;
use App\NCUO\FoivBundle\Controller\Message;
use App\NCUO\FoivBundle\Entity\FoivPerson;

/**
 * FDO controller.
  */
class FDOController extends Controller
{
     /**
     * Lists all Foiv entities.
     *
     * @Route("/{foivid}/fdo", name="fdo")
     * @Method("GET")
     * @Template("ncuofoiv/FDO/index.html.twig")
     */
     
    public function indexAction($foivid)
    {
        $message = $this->initMessage();
        $resp = array();
        $em = $this->getDoctrine()->getManager();
        try{
            $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
    
            if (!$foiv) {
                throw $this->createNotFoundException($this->container->getParameter('msg.error.notFoundFoiv'));
            }
            $entities = $em->getRepository('NCUOFoivBundle:FoivFdo')->findBy( array("foivFk" => $foivid ), array("name" => "asc"));

            $resp['foiv'] = $foiv;
            $resp['entities'] = $entities;
        }catch(\Exception $e){
            $message["type"] = 1;
            $message["text"] = $e->getMessage();
            $message["trace"] = $e->getTraceAsString();
        }
        
        $resp['message'] = $message;
        if(!$foiv){
            $resp['createURL'] = "";
        }else{
            $resp['createURL'] = $this->generateUrl('foiv_fdo_new', array('foivid' => $foiv->getId() ) );
        }
        
        return $resp;
        
        //array(
        //    'foiv' => $foiv,
        //    'entities' => $entities,
        //    'message' => $message,
        //    'createURL'   => $this->generateUrl('foiv_fdo_new', array('foivid' => $foiv->getId() ) ),
        //);
      
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/fdo/{id}/view", name="foiv_fdo_view")
     * @Method("GET")
     * @Template("ncuofoiv/FDO/view.html.twig")
     */
    public function viewRecord($foivid, $id)
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
                throw $this->createNotFoundException($this->container->getParameter('msg.error.notFoundFoiv'));
        }
        $entity = $em->getRepository('NCUOFoivBundle:FoivFdo')->find($id);
        
        $personsList = $em->getRepository('NCUOFoivBundle:FoivFdoPersons')->findBy( array('fkFoiv'=>$foivid), array('fio'=>'asc') );
        
        return array(
            'foiv' => $foiv,
            'entity' => $entity,
            'message' => $message,
            'personsList'  => $personsList,
            'urlCreate'   => $this->generateUrl('foiv_fdo_edit', array('foivid'=>$foiv->getId(), 'id'=> 0 ) ),
            'urlBack'   => $this->generateUrl('fdo', array('foivid' => $foiv->getId() ) ),
            'urlEdit'   => $this->generateUrl('foiv_fdo_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/fdo/0/edit", name="foiv_fdo_new")
     * @Method("GET")
     * @Template("ncuofoiv/FDO/edit.html.twig")
     */
    public function newForm($foivid)
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException($this->container->getParameter('msg.error.notFoundFoiv'));
        }
        $entity = new FoivFdo();
        
        $personsList = $em->getRepository('NCUOFoivBundle:FoivFdoPersons')->findBy( array('fkFoiv'=>$foivid), array('fio'=>'asc') );
        
        return array(
            'foiv' => $foiv,
            'entity' => $entity,
            'message' => $message,
            'personsList'  => $personsList,
            'urlNewPerson'   => $this->generateUrl('foiv_fdo_add_person', array('foivid' => $foiv->getId(), 'id' => 0 ) ),
            'urlCreate'   => $this->generateUrl('foiv_fdo_new', array('foivid' => $foiv->getId() ) ),
            'urlAction'   => $this->generateUrl('foiv_fdo_create', array('foivid' => $foiv->getId() ) ),
            'urlBack'   => $this->generateUrl('fdo', array('foivid' => $foiv->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/fdo/0/edit", name="foiv_fdo_create")
     * @Method("POST")
     * @Template("ncuofoiv/FDO/edit.html.twig")
     */
    public function createNewRecord(Request $request, $foivid)
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
				
		
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException($this->container->getParameter('msg.error.notFoundFoiv'));
        }
        $supervisor = $em->getRepository('NCUOFoivBundle:FoivFdoPersons')->find( $request->request->get("supervisor") );
        $entity = new FoivFdo();
        
        $entity->setName($request->request->get("name" ));
        $entity->setShortName($request->request->get("shortName" ));
        $entity->setWebsite($request->request->get("website" ));
        $entity->setAddress($request->request->get("address"));
        $entity->setPhone($request->request->get("phone"));
        $entity->setEmail($request->request->get("email"));
        $entity->setSupervisorFk($supervisor);
        $entity->setFoivFk($foiv);


        try{
            $em->persist($entity);
            $em->flush();

            $this->addFlash(
                'notice',
				'Запись создана'
                //'Record created! Record #'.$entity->getId()
            );  
            
        }catch(Exception $e){
            $message = array(
                'message_error' => true,
                'message_text'  => $e->getMessage(),
                'error_explain'  => $e->getTraceAsString()
            );
        }
        
        return $this->redirect($this->generateUrl('foiv_fdo_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ) );
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/fdo/{id}/edit", name="foiv_fdo_edit")
     * @Method("GET")
     * @Template("ncuofoiv/FDO/edit.html.twig")
     */
    public function editForm($foivid, $id)
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException($this->container->getParameter('msg.error.notFoundFoiv'));
        }
        $entity = $em->getRepository('NCUOFoivBundle:FoivFdo')->find($id);
        
        $personsList = $em->getRepository('NCUOFoivBundle:FoivFdoPersons')->findBy( array('fkFoiv'=>$foivid), array('fio'=>'asc') );
        
        return array(
            'foiv' => $foiv,
            'entity' => $entity,
            'message' => $message,
            'personsList'  => $personsList,
            'urlNewPerson'   => $this->generateUrl('foiv_fdo_add_person', array('foivid' => $entity->getFoivFk()->getId(), 'id' => $entity->getId() ) ),
            'createURL'   => $this->generateUrl('foiv_fdo_edit', array('foivid'=>$foiv->getId(), 'id'=> 0 ) ),
            'urlAction'   => $this->generateUrl('foiv_fdo_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
            'urlBack'   => $this->generateUrl('fdo', array('foivid' => $foiv->getId() ) ),
            'urlView'   => $this->generateUrl('foiv_fdo_view', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/fdo/{id}/edit", name="foiv_fdo_update")
     * @Method("POST")
     * @Template("ncuofoiv/FDO/edit.html.twig")
     */
    public function updateRecord(Request $request, $foivid, $id)
    {
        $message = $this->initMessage();
        $em = $this->getDoctrine()->getManager();
        try{
            $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
            if (!$foiv) {
                throw $this->createNotFoundException($this->container->getParameter('msg.error.notFoundFoiv'));
            }
            $supervisor = $em->getRepository('NCUOFoivBundle:FoivFdoPersons')->find( $request->request->get("supervisor") );
            $entity = $em->getRepository('NCUOFoivBundle:FoivFdo')->find($id);
            
            $entity->setName($request->request->get("name" ));
            $entity->setShortName($request->request->get("shortName" ));
            $entity->setWebsite($request->request->get("website" ));
            $entity->setAddress($request->request->get("address"));
            $entity->setPhone($request->request->get("phone"));
            $entity->setEmail($request->request->get("email"));
            $entity->setSupervisorFk($supervisor);
            $entity->setFoivFk($foiv);

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
        
        return $this->redirect($this->generateUrl('foiv_fdo_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ) );
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/fdo/{id}/delete", name="foiv_fdo_delete")
     * @Method("POST")
     * @Template("ncuofoiv/FDO/index.html.twig")
     */
    public function deleteRecord($id)
    {
        $message = $this->initMessage();

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NCUOFoivBundle:FoivFdo')->find($id);
        
            if (!$entity)
            {
                throw $this->createNotFoundException('Foiv not found');
            }
        try{
            $em->remove($entity);
            $em->flush();
            
            $resp['err_id']  = 0;
            $resp['res_msg'] = 'Запись удалена!';//'Record deleted!';
            
        }catch(Exception $e){
            $message = array(
                'message_error' => true,
                'message_text'  => $e->getMessage(),
                'error_explain'  => $e->getTraceAsString()
            );
        }
        
        //$response = new Response();
        //$response->setContent( json_encode( $message) );
        //
        //return $response;
        return new Response(Response::HTTP_OK );
    }

    /**
     * @Route("/{foivid}/fdo/{id}/newPerson", name = "foiv_fdo_add_person")
     * 
     * Create new fdo person
     */    
    public function newPerson(Request $request, $foivid, $id) {
        
        $message = $this->initMessage();
        $resp = array('err_id' => NULL, 'res_msg' => NULL);

        try{
			
			if( trim($request->request->get("surname")) == ""){
                $error_field_name = "surname";
            }else
			if( trim($request->request->get("name")) == ""){
                $error_field_name = "name";
            }else
			if( trim($request->request->get("patronimyc")) == ""){
                $error_field_name = "patronimyc";
            }
			/*if(trim($request->request->get("position")) == ""){
                $error_field_name = "position";
            }elseif(trim($request->request->get("phone")) == ""){
                $error_field_name = "phone";
            }*/
			else{
                $error_field_name = "";
            }
            if( !empty($error_field_name)) {
                    $resp['err_id']  = 2;
                    $resp['res_msg'] = 'Please, fill all requered fields!';
                    $resp['content']["id"] = $error_field_name;
            }else{
    
                $em = $this->getDoctrine()->getManager();
				
				//Демьянов А.Е. 29.01.2016 
				//Сначала создаем новую FOIV-персону и сохраняем ее в БД, 
				//а потом привязываем FDO-персону к новой персоне, 
				//т.к. сущность FDOPersons зависит от FoivPerson
				$NewPerson = new FoivPerson();
				$NewPerson->setSurname($request->request->get("surname"));
				$NewPerson->setName($request->request->get("name"));
				$NewPerson->setPatronymic($request->request->get("patronimyc"));
				
				$NewPerson->setPosition($request->request->get("position"));
				$NewPerson->setPhone($request->request->get("phone"));
				//временно не устанавливаем значение на три след. поля
				//$NewPerson->setWebsiteUrl(); 
				//$NewPerson->setPhotoUrl(); 
				//$NewPerson->setPhoto(); 
				$NewPerson->setAddress($request->request->get('address'));
				$NewPerson->setEmail($request->request->get('email'));
				$em->persist($NewPerson);
                $em->flush();
				
                $entity = new FoivFdoPersons();
                //$entity->setFio( $request->request->get("name") );
                //$entity->setPosition($request->request->get("position"));
                //$entity->setPhone($request->request->get("phone"));
                //$entity->setWebsiteUrl($request->request->get(''));
                //$entity->setAddress($request->request->get('address'));
                //$entity->setEmail($request->request->get('email'));
                $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
                if (!$foiv) {
                    $resp['err_id']  = 1;
                    $resp['res_msg'] = 'Foiv not found';
                }else{
            
                    $entity->setFkFoiv($foiv);
					//Демьянов А.Е. 29.01.2016 
					$entity->setPerson($NewPerson);
					
                    $em->persist($entity);
                    $em->flush();
                    if($entity->getId() > 0){
                        $resp['err_id']  = 0;
                        $resp['res_msg'] = $this->container->getParameter('msg.created');
                        $resp['content']["id"] = $entity->getId();
                        $resp['content']["name"] = $entity->getFio();
                    }else{
                        $resp['err_id']  = 1;
                        $resp['res_msg'] = $this->container->getParameter('msg.error.crt');
                    }
                }
            }
        }catch(Exception $e){
            $resp = array(
                'err_id' => 1,
                'res_msg'  => $e->getMessage(),
                'error_explain'  => $e->getTraceAsString()
            );
        }
        
        
        $response = new Response();
        $response->setContent( json_encode( $resp) );

        return $response;
    }

    public function initMessage(){
        return array("type"=>0, "text"=>"", "trace"=>"");
    }    

}
?>