<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\FoivBundle\Form\FoivType;
use App\NCUO\FoivBundle\Entity\FoivDepartments;
use App\NCUO\FoivBundle\Entity\FoivDptPersons;
use App\NCUO\FoivBundle\Entity\FoivPerson;

/**
 * Controller.
  */
class DepartmentsController extends Controller
{
     /**
     * Lists all Foiv entities.
     *
     * @Route("/{foivid}/departments", name="departments")
     * @Method("GET")
     * @Template("ncuofoiv/Departments/index.html.twig")
     */
    public function indexAction($foivid)
    {
		
        $em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);

        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        return array(
            'foiv'      => $foiv,
            'entities'  => $foiv->getFoivDepartments(),
            'createURL'   => $this->generateUrl('foiv_department_new', array('foivid' => $foiv->getId() ) ),
        );
        
    }
   
    /**
     * Displays a form to view Departments entity.
     *
     * @Route("/{foivid}/departments/{id}/view", name="foiv_department_view")
     * @Method("GET")
     * @Template("ncuofoiv/Departments/view.html.twig")
     */
    public function viewRecord($foivid, $id)
    {
		
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        
        if (!$foiv) {
            throw $this->createNotFoundException($this->container->getParameter('msg.error.notFoundFoiv'));
        }
        //$entity = $em->getRepository('NCUOFoivBundle:FoivPersons')->find( $id );
         $entity = $em->getRepository('NCUOFoivBundle:FoivDepartments')->find( $id );
		
		//Демьянов А.Е. 03.02.2016
		//Получаем список персон для департамента, связанному с конкретным ФОИВом
		$personsList = $em->getRepository('NCUOFoivBundle:FoivDptPersons')->findBy( array('fkFoiv'=>$foivid) );
		
        return array(
            'foiv'      => $foiv,
            'entity'    => $entity,
			'personsList'  => $personsList,
            'urlEdit'   => $this->generateUrl('foiv_department_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
            'urlBack'   => $this->generateUrl('departments', array('foivid' => $foiv->getId() ) ),
        );
    }
 
    /**
     * Get form of new entity.
     *
     * @Route("/{foivid}/departments/new", name="foiv_department_new")
     * @Method("GET")
     * @Template("ncuofoiv/Departments/edit.html.twig")
     */
    
    public function newRecordForm(Request $request, $foivid)
    {
        $message = array(
            'message_error' => false,
            'message_text'  => '',
            'error_explain' => ''
        );

        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository("NCUOFoivBundle:Foiv")->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        
        $em = $this->getDoctrine()->getManager();
        $entity = new FoivDepartments();
        
        $em = $this->getDoctrine()->getManager();
        $dept_type = $em->getRepository('NCUOFoivBundle:DepartmentTypes')->findAll();
        // get foiv supervisors
        $em = $this->getDoctrine()->getManager();
        $dept_supervisor = $em->getRepository('NCUOFoivBundle:FoivDptPersons')->findBy( array('fkFoiv'=>$foivid) );

		
        return array(
            'foiv'      => $foiv,
            'message'   => $message,
            'entity'    => $entity,
            'dept_type' => $dept_type,
            'dept_supervisor'   => $dept_supervisor,
            'createDeptURL'   => $this->generateUrl('foiv_department_new', array('foivid' => $foiv->getId() ) ),
			'urlNewPerson' 	=> $this->generateUrl('foiv_departments_add_person', array('foivid' => $foiv->getId() ) ),
            'urlBack'   => $this->generateUrl('departments', array('foivid'=> $foiv->getId())),
            'ActionURL' => $this->generateUrl('foiv_department_create', array('foivid' => $foiv->getId() ) ),
        );
    }
        
    /**
     * Save new entity.
     *
     * @Route("/{foivid}/departments/new", name="foiv_department_create")
     * @Method("POST")
     * @Template("ncuofoiv/Departments/edit.html.twig")
     */
       
    public function newRecordCreate(Request $request, $foivid)
    {
        $message = array(
            'message_error' => false,
            'message_text'  => '',
            'error_explain' => ''
        );

        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository("NCUOFoivBundle:Foiv")->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        
        $em = $this->getDoctrine()->getManager();
        $entity = new FoivDepartments();
        //$entity = $this->setDataToEntity($request, $em, $entity );
        

        $entity->setName($request->request->get("name"));
        $entity->setFunctions($request->request->get("functions"));        
        
        $deptSupervisor = $em->getRepository('NCUOFoivBundle:FoivDptPersons')->find( $request->request->get("supervisor") );
        $entity->setSupervisor( $deptSupervisor );
        
        $deptType = $em->getRepository('NCUOFoivBundle:DepartmentTypes')->find($request->request->get("type"));
        $entity->setType($deptType);
        
        $entity->setFoiv($foiv);
        
            //сохраняем в БД
        try{
            $em->persist($entity);
            $em->flush();

            $this->addFlash(
                'notice',
                'Запись успешно создана!'
            );                        
            
        }catch(Exception $e){
            $message = array(
                'message_error' => true,
                'message_text'  => $e->getMessage(),
                'error_explain'  => $e->getTraceAsString()
            );
        }
        
        return $this->redirect($this->generateUrl('foiv_department_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ) );
        
        //return array(
        //    'foiv'      => $foiv,
        //    'message'   => $message,
        //    'entity'    => $entity,
        //    'createDeptURL'   => $this->generateUrl('foiv_department_new', array('foivid' => $foiv->getId() ) ),
        //    'BackURL' =>$this->generateUrl('departments', array('foivid'=> $foiv->getId())),
        //    'ActionURL' => $this->generateUrl('foiv_department_create', array('foivid' => $foiv->getId() ) ),
        //);
    }
    
	  /**
     * @Route("/{foivid}/departments/newPerson", name = "foiv_departments_add_person")
     * 
     * Create new department person
     */    
    public function newPerson(Request $request, $foivid) 
	{
		
		 $resp = array('err_id' => NULL, 'res_msg' => NULL);
		 
		try{
			if( trim($request->request->get("surname")) == ""){
                $error_field_name = "surname";
            }
            elseif( trim($request->request->get("name")) == ""){
                $error_field_name = "name";
            }
			elseif( trim($request->request->get("patronimyc")) == ""){
                $error_field_name = "patronimyc";
            }
			/*elseif(trim($request->request->get("position")) == ""){
                $error_field_name = "position";
            }elseif(trim($request->request->get("phone")) == ""){
                $error_field_name = "phone";
            }*/
			else
			{
                $error_field_name = "";
            }
			
            if( !empty($error_field_name)) {
                    $resp['err_id']  = 2;
                    $resp['res_msg'] = 'Пожалуйста заполните требуемые поля!';
                    $resp['content']["id"] = $error_field_name;
            }else{
    
                $em = $this->getDoctrine()->getManager();
				
				//$this->get('logger')->debug("start fiil fields of FoivPerson");
				//Демьянов А.Е. 04.02.2016 
				//Сначала создаем новую FOIV-персону и сохраняем ее в БД, 
				//а потом привязываем department-персону к новой персоне, 
				//т.к. сущность department'а зависит от FoivPerson
				$NewPerson = new FoivPerson();
				$NewPerson->setSurname($request->request->get("surname"));
				$NewPerson->setName($request->request->get("name"));
				$NewPerson->setPatronymic($request->request->get("patronimyc"));
				
				$NewPerson->setPosition($request->request->get("position"));
				$NewPerson->setPhone($request->request->get("phone"));
				$NewPerson->setEmail($request->request->get('email'));
				$NewPerson->setWebsiteUrl($request->request->get('weburl'));
				
				/*
				//Временно по этому полю пока не устанавливаем значене
				$NewPerson->setPhotoFile($request->request->get('photo'));
				*/
				
				$em->persist($NewPerson);
                $em->flush();
				//$this->get('logger')->debug("new FoivPerson saved to DB");
                $entity = new FoivDptPersons();
                
                $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
                
                if (!$foiv) {
                    $resp['err_id']  = 1;
                    $resp['res_msg'] = 'Foiv not found';
                }else{
					
					$entity->setFkFoiv( $foiv );
					//$this->get('logger')->debug("Foiv saved to FoivDptPerson");
					
					//Демьянов А.Е. 04.02.2016 
					$entity->setPerson($NewPerson);
					//$this->get('logger')->debug("FoivPerson saved to FoivDptPerson");
					
                    $em->persist($entity);
                    $em->flush();
					//$this->get('logger')->debug("FoivDptPerson saved to DB");
					
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
  
    /**
      * Get edit form of entity.
      *
      * @Route("/{foivid}/departments/{id}/edit", name="foiv_department_edit")
      * @Method("GET")
      * @Template("ncuofoiv/Departments/edit.html.twig")
      */
     
     public function editRecordForm(Request $request, $foivid, $id)
     {
         $message = array(
             'message_error' => false,
             'message_text'  => '',
             'error_explain' => ''
         );
    
         $em = $this->getDoctrine()->getManager();
         $foiv = $em->getRepository("NCUOFoivBundle:Foiv")->find($foivid);
         if (!$foiv) {
             throw $this->createNotFoundException('Unable to find Foiv entity.');
         }
         
         $entity = $em->getRepository('NCUOFoivBundle:FoivDepartments')->find( $id );

         // get all department types
         $dept_type = $em->getRepository('NCUOFoivBundle:DepartmentTypes')->findAll();
         // get foiv supervisors
         $dept_supervisor = $em->getRepository('NCUOFoivBundle:FoivDptPersons')->findBy( array('fkFoiv'=>$foivid) );

         return array(
            'foiv'      => $foiv,
            'message'   => $message,
            'entity'    => $entity,
            'dept_type' => $dept_type,
            'dept_supervisor' => $dept_supervisor,
			'urlNewPerson' 	=> $this->generateUrl('foiv_departments_add_person', array('foivid' => $foiv->getId() ) ),
            //'createDeptURL'   => $this->generateUrl('foiv_department_new', array('foivid' => $foiv->getId() ) ),
            'urlBack' =>$this->generateUrl('departments', array('foivid'=> $foiv->getId())),
            'ActionURL' => $this->generateUrl('foiv_department_update', array('foivid' => $foiv->getId(), 'id' => $entity->getId() ) ),
            'urlView'   => $this->generateUrl('foiv_department_view', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
         );
     }
    
    /**
     * Update data of Department entity
     * @Route("/{foivid}/departments/{id}/edit", name="foiv_department_update")
     * @Method("POST")
     * @Template("ncuofoiv/Departments/edit.html.twig")
     */
    
    public function editRecordUpdate(Request $request, $foivid, $id){
        $message = array(
            'message_error' => false,
            'message_text'  => '',
            'error_explain' => ''
        );
        $arResult = array();
        try{

            
            $em = $this->getDoctrine()->getManager();
            $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
            if( !$foiv ){
                throw $this->createNotFoundException('Unable to find Foiv entity.');
            }

            $entity = $em->getRepository('NCUOFoivBundle:FoivDepartments')->find( $id );
            
            
            $entity = $this->setDataToEntity($request, $em, $entity );
            
            $deptSupervisor = $em->getRepository('NCUOFoivBundle:FoivDptPersons')->find( $request->request->get("supervisor") );
            $entity->setSupervisor( $deptSupervisor );
            
            $deptType = $em->getRepository('NCUOFoivBundle:DepartmentTypes')->find($request->request->get("type"));
            $entity->setType($deptType);
            
            $entity->setFoiv($foiv);
            
            //$entity->setName($request->request->get("name"));
            //сохраняем в БД
            //$em->persist($entity);
            $em->flush();

            $message['message_text'] = 'Данные успешно сохранены.';
            
            $em = $this->getDoctrine()->getManager();
            $arResult['dept_type'] = $em->getRepository('NCUOFoivBundle:DepartmentTypes')->findAll();
            // get foiv supervisors
            $em = $this->getDoctrine()->getManager();
            $arResult['dept_supervisor'] = $em->getRepository('NCUOFoivBundle:FoivDptPersons')->findBy( array('fkFoiv'=>$foivid) );

        }catch(Exception $e ){
            $message = array(
                'message_error' => true,
                'message_text'  => $e->getMessage(),
                'error_explain'  => $e->getTraceAsString()
            );
        }
        
        $arResult['foiv']       = $foiv;
        $arResult['entity']     = $entity;
        $arResult['message']    = $message;
        $arResult['urlBack']    = $this->generateUrl('departments', array('foivid'=> $foiv->getId()));
        $arResult['ActionURL']  = $this->generateUrl('foiv_department_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) );
        $arResult['urlView']   = $this->generateUrl('foiv_department_view', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) );
        
        return $arResult;
    }
  
    /**
     * Delete Department entity.
     *
     * @Route("/{foivid}/departments/{id}/delete", name="foiv_department_delete")
     * @Method("POST")
     * @Template("ncuofoiv/Departments/index.html.twig")
     */
    public function deleteAction(Request $request, $id)
    {
         $em = $this->getDoctrine()->getManager();
        
          //получаем из БД сам контактный объект
          $entity = $em->getRepository('NCUOFoivBundle:FoivDepartments')->find($id);

            if (!$entity)
            {
                throw $this->createNotFoundException('Не найдена запись с нужным индетификатором');
            }

            //удаляем запись из БД
            $em->remove($entity);
            $em->flush();
        
            return new Response(Response::HTTP_OK );
        //return $this->redirect($this->generateUrl('departments', array('foivid'=>$foiv->getId()  ) ) );
      
    }
    
    
    /*
     *Установка данных в ФОИВ-объект
     *
     * request - объект запроса
     * em - ORM-объект
     * entity - рабочий объект, в котрый заливаются данные
     */
    private function setDataToEntity(Request $request,  $em, \NCUO\FoivBundle\Entity\FoivDepartments $entity)
    {
        $entity->setName($request->request->get("name"));
        $entity->setFunctions($request->request->get("functions"));
        //$supervisorId = $request->request->get(Foiv::getSuperFoivControlID());
        //$supervisor = $em->getRepository('NCUOFoivBundle:Foiv')->find($supervisorId);
        return $entity;  
    }
}
?>