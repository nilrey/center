<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\FoivBundle\Entity\File;
use App\NCUO\FoivBundle\Form\FoivType;
use App\NCUO\FoivBundle\Entity\FoivPersons;
use App\NCUO\FoivBundle\Entity\FoivPerson;

/**
 * Supervisory controller.
  */
class SupervisoryController extends Controller
{
    const DEF_PERSON_PHOTO_TITLE = "Фото руководителя";
    const DEF_PERSON_PHOTO_DESC = "Фото руководителя: ";
     /**
     * Lists all entities.
     *
     * @Route("/{id}/supervisory", name="supervisory")
     * @Method("GET")
     * @Template("ncuofoiv/Supervisory/index.html.twig")
     */
    public function indexAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($id);

        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
		
		//Демьянов А.Е. 25.03.2016 
		//получаем списки руководителей текущего ФОИВа, 
		//заранее отсортированные по весу и ФИО
		$query = $em->createQuery(
				   'SELECT p  FROM NCUOFoivBundle:FoivPersons p JOIN p.fkFoiv f
					WHERE f.id = :fid 
					ORDER BY p.weight ASC, p.fio asc'
					)->setParameter('fid', $id);
		$persons = $query->getResult();
		
		
		
        //$persons = [];        
        /*foreach($foiv->getFoivPersons() as $f) 
		{
            if ($f->getFio() != '')
                $persons[] = $f;
        }*/
        
        return array(
            'foiv'      => $foiv,
            'entities'  => $persons,
            'createURL'   => $this->generateUrl('foiv_supervisory_edit', array('foivid' => $foiv->getId(), "id" => 0 ) ),
        );
       //return $this->render("ncuofoiv/Supervisory/index.html.twig");
    }
    
        
    /**
     * View edit form
     *
     * @Route("/{foivid}/supervisory/{id}/edit", name="foiv_supervisory_edit")
     * @Method("GET")
     * @Template("ncuofoiv/Supervisory/edit.html.twig")
     */
    public function editRecord(Request $request, $foivid, $id)
    {
        $message = array(
            "message_error" => false,
            "message_text"  => "",
        );
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository("NCUOFoivBundle:Foiv")->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        
        //$entity = new FoivPvo();
        //
        //$entity->setName($request->request->get(FoivPvo::getFullNameControlID()));
        //
        //if($foivid == 0){
        //    $em->persist($entity);
        //}
        //$em->flush();
            
//        return $this->redirect($this->generateUrl('foivpvo', array('foivid' => $foivid)));

        $em = $this->getDoctrine()->getManager();
        if(intval($id) > 0 ){
            $entity = $em->getRepository('NCUOFoivBundle:FoivPersons')->find( $id );
            $entityId = $entity->getId();
        }else{
            $entity = new FoivPersons();
            $entityId = 0;
        }
        
        $supervisor = $em->getRepository('NCUOFoivBundle:FoivPersons')->findBy( array('fkFoiv'=>$foivid), array("fio"=>"asc") );
        
        setlocale( LC_TIME, 'ru_RU', 'russian' );
                
        return array(
            'foiv'      => $foiv,
            'message'   => $message,
            'entity'    => $entity,
            'supervisor'=> $supervisor,
            'image'     => "", 
            'urlBack'   => $this->generateUrl('supervisory', array('id' => $foiv->getId() ) ),
            'ActionURL' => $this->generateUrl('foiv_supervisory_save', array('foivid' => $foiv->getId(), 'id' =>$entityId  ) ),
            'urlView'   => $this->generateUrl('foiv_supervisory_view', array('foivid'=>$foiv->getId(), 'id'=> $entityId ) ),
			'urlNewPerson' => $this->generateUrl('foiv_supervisory_add_person', array('foivid'=>$foiv->getId() ) )
        );
    }
	
	 /**
     * @Route("/{foivid}/supervisory/newPerson", name = "foiv_supervisory_add_person")
     * 
     * Create new supervisor person
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
				
				
				//Сначала создаем новую персону и сохраняем ее в БД, 
				//а потом привязываем foiv-персону к новой персоне, 
				//т.к. сущность FoivPersons зависит от FoivPerson
				$NewPerson = new FoivPerson();
				$NewPerson->setSurname($request->request->get("surname"));
				$NewPerson->setName($request->request->get("name"));
				$NewPerson->setPatronymic($request->request->get("patronimyc"));
				
				$NewPerson->setPosition($request->request->get("position"));
				$NewPerson->setPhone($request->request->get("phone"));
				$NewPerson->setEmail($request->request->get('email'));
				$NewPerson->setWebsiteUrl($request->request->get('weburl'));
				$NewPerson->setAddress($request->request->get('address'));
				
				/*
				//Временно по этому полю пока не устанавливаем значене
				$NewPerson->setPhotoFile($request->request->get('photo'));
				*/
				
				$em->persist($NewPerson);
                $em->flush();
				
                $entity = new FoivPersons();
				
				//Демьянов А.Е. 23.03.2016 
				//Задаем вес персоны
				$entity->setWeight($request->request->get("weight"));
		
				//Демьянов А.Е. 23.03.2016 
				//Определяем признак отображения на главной странице
				if($request->request->get('show_flag') == "on")
				{
					$entity->setShowed(TRUE);
				}
				else
				{
					$entity->setShowed(FALSE);
				}
                
                $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
                
                if (!$foiv) {
                    $resp['err_id']  = 1;
                    $resp['res_msg'] = 'Foiv not found';
                }else{
					
					$entity->setFkFoiv( $foiv );
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
        }catch(\Exception $e){
			
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
     * Creates a new entity or edit exist
     *
     * @Route("/{foivid}/supervisory/{id}/edit", name="foiv_supervisory_save")
     * @Method("POST")
     * @Template("ncuofoiv/Supervisory/edit.html.twig")
     */
    public function saveRecord(Request $request, $foivid, $id)
    {
		
        $message = array( "message_set" => false, "message_error" => false, "message_type" => 0, "message_text"  => "" );
        
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository("NCUOFoivBundle:Foiv")->find($foivid);
        
       // $em = $this->getDoctrine()->getManager();
		
		$entity = null;
        if(intval($id) > 0 )//Демьянов А.Е. 17.02.2016 - для обновления существующей записи
		{
		
            $entity = $em->getRepository('NCUOFoivBundle:FoivPersons')->find( $id );
            $entityId = $entity->getId();
			
			$entity->setSurname($request->request->get("surname"));
			$entity->setName($request->request->get("name"));
			$entity->setPatronymic($request->request->get("patronimyc"));
			
			$entity->setPosition($request->request->get("position") );
			$supervisor = $em->getRepository('NCUOFoivBundle:FoivPersons')->find( $request->request->get("supervisor") );
			$entity->setSupervisor( $supervisor );
			
			$entity->setAddress($request->request->get("address") );
			$entity->setPhone($request->request->get("phone") );
			$entity->setEmail($request->request->get("email") );
			$entity->setWebsiteUrl($request->request->get("websiteUrl") );        
			
			
			
			
			
        }
		else //Демьянов А.Е. 17.02.2016 - при создании новой записи
		{
			
			//создаем персону для общей персонализированной таблицы (dict_person)
			$NewPerson = new FoivPerson();
			$NewPerson->setSurname($request->request->get("surname"));
			$NewPerson->setName($request->request->get("name"));
			$NewPerson->setPatronymic($request->request->get("patronimyc"));
			
			$NewPerson->setPosition($request->request->get("position"));
			$NewPerson->setAddress($request->request->get("address"));
			$NewPerson->setPhone($request->request->get("phone"));
			$NewPerson->setEmail($request->request->get("email"));
			$NewPerson->setWebsiteUrl($request->request->get("websiteUrl"));
			$em->persist($NewPerson);
			$em->flush();
			
			//создаем конкретную ФОИВ-персону (таблица dict_foiv_persons)
            $entity = new FoivPersons();
            $entityId = 0;
			$Supervisor = $em->getRepository('NCUOFoivBundle:FoivPersons')->find( $request->request->get("supervisor") );
			$entity->setSupervisor($Supervisor);
			
			$entity->setPerson($NewPerson);
			
		}
        
		//Демьянов А.Е. 23.03.2016 
		//Задаем вес персоны
		//$this->get('logger')->debug("weight:".$request->request->get("weight"));
		$entity->setWeight($request->request->get("weight"));
		
        //Демьянов А.Е. 23.03.2016 
		//Определяем признак отображения на главной странице
		if($request->request->get("show_flag") == "on")
		{
			$entity->setShowed(TRUE);
		}
		else
		{
			$entity->setShowed(FALSE);
		}
		
        $personPhoto = "";
        $old_file="";
        if($request->getMethod() == 'POST'){
            try{
                $personPhoto = $request->files->get("photoUrl");
                if( $personPhoto instanceof UploadedFile && $personPhoto->getError() == "0"){
                    
                    $uploadUrlDir = $this->container->getParameter('ncuofoiv.path_img_persons')."/";
                    $uploadDir =  $this->getRequest()->server->get('DOCUMENT_ROOT').$uploadUrlDir;
                    
                    // PREPARE FILE METADATA
                    //$em = $this->getDoctrine()->getManager();
                    $entityFile = $entity->getPhotoId();
                        $isNewFile = false;
                    if( get_class($entityFile) === "NCUO\FoivBundle\Controller\SupervisoryController"  ){
                        $entityFile = new File();
                        $isNewFile = true;
                    }else{
                        $old_file = $uploadDir.$entityFile->getName();
                    }
                    $entityFile->setOriginalName( $personPhoto->getClientOriginalName() );
                    $entityFile->setTitle( $entity->getFio() );
                    $entityFile->setMimeType( $personPhoto->getMimeType() );
                    $entityFile->setName( md5($personPhoto->getClientOriginalName() . date("U")).".". $personPhoto->getClientOriginalExtension() );
                    $entityFile->setExt(  $personPhoto->getClientOriginalExtension() );
                    $entityFile->setSize( $personPhoto->getClientSize() );
                    $entityFile->setMimeType( $personPhoto->getClientMimeType() );
                    $entityFile->setPath( $uploadUrlDir );
                    $description = self::DEF_PERSON_PHOTO_DESC. $entity->getFio();
                    $entityFile->setDescription( $description );
                    
                    if( intval($entityFile->getId()) < 1 ){
                        $em->persist($entityFile);
                    }
                    $em->flush($entityFile);
                    
                    $personPhoto->move($uploadDir , $entityFile->getName() );
                    
                    if( $personPhoto->getError() !== UPLOAD_ERR_OK ){
                        $message["message_set"] = true;
                        $message["message_error"] = true;
                        $message["message_type"] = 1;
                        $message["message_text"] = $this->container->getParameter('msg.error.upd').$personPhoto->getError();
                    }else{
                        //$message["message_set"] = true;
                        //$message["message_error"] = false;
                        //$message["message_type"] = 2;
                        //$message["message_text"] = $this->container->getParameter('msg.updated');
                        try{
                            $entity->setPhotoId( $entityFile );
                            if(file_exists( $old_file )){
                                unlink($old_file);
                            }
                        }catch(\Exception $e){
                             $message = array(
                                "message_error" => true,
                                "message_text"  => $e->getMessage(),
                            );
                        }
                    }
                }
            
            }catch( \Exception $e){
                $message = array(
                    "message_set" => true,
                    "message_error" => true,
                    "message_type" => 1,
                    "message_text"  => $e->getMessage(),
                );
            }
            
            //$current = var_export($personPhoto, true);
        }
        
        //сохраняем в БД
        if(intval($id) == 0 ){ // new Record
			
            $entity->setFkFoiv($foiv );
            try{
				
                $em->persist($entity);
                $em->flush();
                $entityId = $entity->getId();
		
				
                $this->addFlash(
                    'notice',
                    'Запись успешно создана!'
                );                        
                                
                // goto edit page
                return $this->redirect($this->generateUrl('foiv_supervisory_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ) );
            }catch( \Exception $e){
                $message = array(
                    "message_error" => false,
                    "message_text"  => "Ошибка сохранения. ".$e->getMessage(),
                );
            }
        }
            
        try{
            $em->flush();
			
                if( !$message["message_set"]){
                    $message = array(
                       "message_error" => false,
                       "message_text"  => 'Данные успешно сохранены.',
                    );
                }
        }catch( \Exception $e){
                $message = array(
                    "message_error" => false,
                    "message_text"  => "Ошибка сохранения. ".$e->getMessage(),
                );
        }
        
        $supervisor = $em->getRepository('NCUOFoivBundle:FoivPersons')->findBy( array('fkFoiv'=>$foivid), array("fio"=>"asc") );
        
        return array(
            'foiv'      => $foiv,
            'message'   => $message,
            'entity'    => $entity,
            'supervisor'=> $supervisor,
            'image'     => "", //$old_file ,
            'urlBack'   => $this->generateUrl('supervisory', array('id' => $foiv->getId() ) ),
            'ActionURL' => $this->generateUrl('foiv_supervisory_edit', array('foivid' => $foiv->getId(), 'id' => $entity->getId() ) ),
            'urlView'   => $this->generateUrl('foiv_supervisory_view', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
			'urlNewPerson' => $this->generateUrl('foiv_supervisory_add_person', array('foivid'=>$foiv->getId() ) )
        );
 
        //$file = '/var/www/Projects/ncuo-cms/app/logs/new.log';
        //file_put_contents($file, $current);
        

   }
  
   
    /**
     * Displays a form to view Supervisory entity.
     *
     * @Route("/{foivid}/supervisory/{id}/view", name="foiv_supervisory_view")
     * @Method("GET")
     * @Template("ncuofoiv/Supervisory/view.html.twig")
     */
    public function viewRecord($foivid, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        
        if (!$foiv) {
            throw $this->createNotFoundException($this->container->getParameter('msg.error.notFoundFoiv'));
        }
        $entity = $em->getRepository('NCUOFoivBundle:FoivPersons')->find( $id );
		
		$this->get('logger')->debug("entity->getAddress(): ".print_r($entity->getAddress(), true));
		$this->get('logger')->debug("entity->getPerson()->getAddress(): ".print_r($entity->getPerson()->getAddress(), true));
        return array(
            'foiv'      => $foiv,
            'entity'    => $entity,
            'urlEdit'   => $this->generateUrl('foiv_supervisory_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
            'urlBack'   => $this->generateUrl('supervisory', array('id' => $foiv->getId() ) ),
        );
    }
   
    /**
     * Delete Supervisory entity.
     *
     * @Route("/{foivid}/supervisory/{id}/delete", name="foiv_supervisory_delete")
     * @Method("POST")
     * @Template("ncuofoiv/Supervisory/edit.html.twig")
     */
    public function deleteAction(Request $request, $id)
    {
         $em = $this->getDoctrine()->getManager();
        
          //получаем из БД сам контактный объект
          $entity = $em->getRepository('NCUOFoivBundle:FoivPersons')->find($id);

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

}
?>