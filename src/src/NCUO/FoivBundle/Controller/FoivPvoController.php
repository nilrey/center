<?php

namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\FoivBundle\Entity\FoivPvo;
use App\NCUO\FoivBundle\Entity\FoivPvoPersons;
use App\NCUO\FoivBundle\Form\FoivPvoType;
use App\NCUO\FoivBundle\Entity\FoivPerson;

use App\NCUO\FoivBundle\Controller\ErrorResponseGenerator;
//use App\NCUO\FoivBundle\Entity\ORMObjectMetadata;

/**
 * FoivPvo controller.
 */
class FoivPvoController extends Controller
{

    /**
     * Lists all FoivPvo entities.
     *
     * @Route("/{foivid}/pvo", name="foivpvo")
     * @Method("GET")
     * @Template("ncuofoiv/FoivPvo/index.html.twig")
     */
    public function indexAction($foivid)
    {
        
        
        $em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);

        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        
        $entities = $em->getRepository('NCUOFoivBundle:FoivPvo')->findByFoiv($foiv);
        
        return array(
            'foiv' => $foiv,
            'entities' => $entities,
            'createURL'   => $this->generateUrl('foivpvo_new', array('foivid' => $foiv->getId() ) ),
        );
    }
    
    /*public function createAction_Old(Request $request, $foivid)
    {
        try
        {
         $em = $this->getDoctrine()->getManager();
         
        //создаем класc метаинформации об FoivPVO
       // $OrmMetaData = ORMObjectMetadata::Create($em, 'NCUOFoivBundle:FoivPvo');
        
        $entity = new FoivPvo();
        
        //получаем данные по конкретному контролу
        $data = $request->request->get(FoivPvo::getFullNameControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getFullNameControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getFullNameControlCaption(), FoivPvo::getFullNameControlID());
       }
        $entity->setName($data);
                
        //получаем данные по конкретному контролу
        $data = $request->request->get(FoivPvo::getShortNameControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getShortNameControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getShortNameControlCaption(), FoivPvo::getShortNameControlID());
       }
        $entity->setShortName($data);
        
        $data = $request->request->get(FoivPvo::getWebURLControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getWebURLControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getWebURLControlCaption(), FoivPvo::getWebURLControlID());
       }
        $entity->setWebsiteUrl($data);
        
        $data = $request->request->get(FoivPvo::getAddressControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getAddressControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getAddressControlCaption(), FoivPvo::getAddressControlID());
       }
        $entity->setAddress($data);
        
        $data = $request->request->get(FoivPvo::getFunctionsControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getFunctionsControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getFunctionsControlCaption(), FoivPvo::getFunctionsControlID());
       }
        $entity->setFunctions($data);
        
        
         $data = $request->request->get(FoivPvo::getDirectorControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getDirectorControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getDirectorControlCaption(), FoivPvo::getDirectorControlID());
       }
        $Director = $em->getRepository('NCUOFoivBundle:FoivPvoPersons')->find($data);
        if(!$Director)
        {
             throw $this->createNotFoundException('Руководитель  с нужным идентификатором не найден');
        }
        $entity->setDirector($Director);
        
        $data = $request->request->get(FoivPvo::getPhoneControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getPhoneControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getPhoneControlCaption(), FoivPvo::getPhoneControlID());
       }
       $entity->setPhone($data);
        
        $data = $request->request->get(FoivPvo::getEmailControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getEmailControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getEmailControlCaption(), FoivPvo::getEmailControlID());
       }
        $entity->setEmail($data);
        
        
         $data = $request->request->get(FoivPvo::getTypeControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getTypeControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getTypeControlCaption(), FoivPvo::getTypeControlID());
       }
        $Type = $em->getRepository('NCUOFoivBundle:FoivPvoTypes')->find($data);
        if(!$Type)
        {
             throw $this->createNotFoundException('Тип организации с нужным идентификатором не найден');
        }
        $entity->setType($Type);
      
        $Foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if(!$Foiv)
        {
             throw $this->createNotFoundException('ФОИВ  с нужным идентификатором не найден');
        }
        $entity->setFoiv($Foiv);
        
        $em->persist($entity);
        $em->flush();
       
             //отправляем ответ об успешном проведении операции + ссылку для редиректа на форму редактирования существующего объекта
             return ErrorResponseGenerator::getSuccessResponse($this->generateUrl('foivpvo_edit', array('foivid'=>$entity->getFoiv()->getId(), 'id'=> $entity->getId() ) ) );
        }
        catch(\Exception $ex)
        {
             return ErrorResponseGenerator::getInternalServerError($ex->getMessage());
        }
    }*/
	
	/**
     * Creates a new FoivPvo entity.
     *
     * @Route("/{foivid}/pvo/create", name="foivpvo_create")
     * @Method("POST")
     *
     */
	public function createAction(Request $request, $foivid)
	{

        $message = array( 'message_type' => 0, 'message_subj'  => "", 'message_text'  => "" );

        try{
            if( trim($request->request->get("name")) == ""){
                $error_field_name = "name";
            }elseif(trim($request->request->get("shortName")) == ""){
                $error_field_name = "shortName";
            }elseif(trim($request->request->get("address")) == ""){
                $error_field_name = "address";
            }else{
                $error_field_name = "";
            }
            if( !empty($error_field_name)) {
                 $this->addFlash(
                    'error_field_name',
                    $error_field_name
                );
            }else{
                $em = $this->getDoctrine()->getManager();
                //$entity = $em->getRepository('NCUOFoivBundle:FoivPvo')->find($id);
				$entity = new FoivPvo();
                $director = $em->getRepository('NCUOFoivBundle:FoivPvoPersons')->find($request->request->get( "director" ));
                $type = $em->getRepository('NCUOFoivBundle:FoivPvoTypes')->find($request->request->get( "type" ));
                $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find( $foivid);
                
                $entity->setName($request->request->get("name" ));
                $entity->setShortName($request->request->get("shortName" ));
                $entity->setWebsiteUrl($request->request->get("websiteUrl" ));
                $entity->setAddress($request->request->get( "address" ));
                $entity->setFunctions($request->request->get( "functions" ));
                $entity->setDirector($director);
                $entity->setEmail($request->request->get( "email" ));
                $entity->setPhone($request->request->get( "phone" ));
                $entity->setType( $type);
                $entity->setFoiv($foiv);
                
				$em->persist($entity);
                $em->flush();
				
				if($director != null)
				{
					$director->setFkFoivPvo($entity);
					$em->flush();
				}
				
				
				
                $message = array(
                    'message_type' => 2,
                    'message_subj'  => $this->container->getParameter('msg.created'),
                    'message_text'  => ""
                );
                
            }
            
        }catch(\Exception $e){
                $message = array(
                    'message_type' => 1,
                    'message_subj'  => $this->container->getParameter('msg.error.upd')." ".$e->getMessage(),
                    'message_text'  => $e->getTraceAsString()
                );
           
        }
         
		return new RedirectResponse($this->generateUrl('foivpvo_edit', array('foivid' => $entity->getFoiv()->getId(), 'id' => $entity->getId() ) ));
		
		//$typeList = $em->getRepository('NCUOFoivBundle:FoivPvoTypes')->findAll();
        //$personsList = $em->getRepository('NCUOFoivBundle:FoivPvoPersons')->findBy( array('fkFoivPvo'=>$id) );
		/*
		return	array(
            "foiv" => $entity->getFoiv(),
            "entity" => $entity,
            "message" => $message,
            "typeList"  => $typeList,
            "personsList"  => $personsList,
            'BackURL' => $this->generateUrl('foivpvo', array('foivid' => $entity->getFoiv()->getId() ) ),
            'ActionURL' => $this->generateUrl('foivpvo_edit', array('foivid' => $entity->getFoiv()->getId(), 'id' => $entity->getId() ) ),
            'urlNewPerson'   => $this->generateUrl('foiv_pvo_add_person', array('foivid' => $entity->getFoiv()->getId(), 'id' => $entity->getId() ) ),
            );
		*/
		
    }    

    /**
     * Creates a form to create a FoivPvo entity.
     *
     * @param FoivPvo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(FoivPvo $entity)
    {
        $form = $this->createForm(new FoivPvoType(), $entity, array(
            'action' => $this->generateUrl('foivpvo_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new FoivPvo entity.
     *
     * @Route("/{foivid}/pvo/new", name="foivpvo_new")
     * @Method("GET")
     * @Template("ncuofoiv/FoivPvo/new.html.twig")
     */
    public function newAction($foivid)
    { 
        $em = $this->getDoctrine()->getManager();
        
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv)
        {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        
        //получаем все типы организаций с отсортировкой
		 $typeList = $em->getRepository('NCUOFoivBundle:FoivPvoTypes')->findAll();
        
        //список всех руковдителей для новой организации пока пустой
		$personsList = null;
        
        //динамически формируем URL для возврата к списку
        $URL            = $this->generateUrl('foivpvo', array('foivid' => $foiv->getId()));
        
        //динамически формируем URL для  выполнения операции сохранения контакта в БД
        $ActionURL = $this->generateUrl('foivpvo_create', array('foivid' => $foiv->getId()));        
							 
		return array(
            "foiv" => $foiv,
            "typeList"  => $typeList,
            "personsList"  => $personsList,
            'urlBack' => $this->generateUrl('foivpvo', array('foivid' => $foiv->getId())),
            'ActionURL' => $ActionURL,
            'urlNewPerson'   => $this->generateUrl('foiv_pvo_add_person', array('foivid' => $foiv->getId(), 'id' => 0 ))
		);
        

    }

    /*
    * Получение все элементов из таблицы с последующей их сортировкой
    * 
    *  em:                          Doctrine manager
    *  RepositoryName:   имя репозитория для извлечения данных
    * SortingFieldName:  имя поля таблицы по котрому ведется сортировка
    * ExceptionText:         текст сообщения для генерации исключения в слукае, если данные не будут найдены
    *
    * Возврат:                  отсортированный список табличных объектов
    */
    private function getAllItems($em, $RepositoryName, $SortingFieldName, $ExceptionText)
    {
        $repository = $em->getRepository($RepositoryName);
        
        //если указано поле , по котрому надо сортировать - соритируем
        //в противном случае берем все данные без сортировки (как есть)
        if($SortingFieldName)
        {
            $all_items = $repository->findBy(array(), array( $SortingFieldName =>'ASC'));
        }
        else
        {
            $all_items = $repository->findAll();
        }
        
        if(!$all_items)
        {
            throw $this->createNotFoundException($ExceptionText);
        }
        
        return $all_items;
    }
    
    /*
    * Формирование ассоциатитвного массива по ФОИВ
    *  SourceFoivs             исходные ФОИВ-данные
    *
    * Возврат:                 ассоциативный массив (ключ = идентификатор ФОИВ, значение название ФОИВ  )
    */
    private function createFoivList( $SourceFoivs)
    {
        $output_data = array();
        
        foreach ($SourceFoivs as $foiv)
        {
                $output_data[$foiv->getId()] = $foiv->getName();
        }
     
        return $output_data;
    }
    
    /*
    * Формирование ассоциатитвного массива по ФИО руководителей
    * 
    *  SourceFoivs:   исходные данные по ФИО руководителей
    * UsingDirectiveText: признак того, что необходимо использовать в контроле эелемнт с  текст типа "Выберите..."
    *                                   (true - директивный текст необходимо отображать в списке  / false - директивный текст использовать не нужно)
    * Возврат:         ассоциативный массив (ключ = идентификатор руководителя, значение  = ФИО руководителя  )
    */
    private function createDirectorList($SourceDirectors, $UsingDirectiveText = false )
    {
         $output_data = array();
        
        foreach ($SourceDirectors as $director)
        {
             //если надо, то используем директивный текст в combobox'e
               if($UsingDirectiveText === true)
               {
                $output_data[-1] = "Выберите руководителя";
               }
               
               $output_data[$director->getId()] = $director->getFio();
        }
        
        return $output_data;
    }
    
    /*
    * Формирование ассоциатитвного массива по типам организаций
    * 
    *  SourceFoivs:   исходные данные по типам организаций
    * UsingDirectiveText: признак того, что необходимо использовать в контроле эелемнт с  текст типа "Выберите..."
    *                                   (true - директивный текст необходимо отображать в списке  / false - директивный текст использовать не нужно)
    * Возврат:         ассоциативный массив (ключ = идентификатор типа, значение  = тип организации  )
    */
    private function createPVOTypeList($SourcePVOTypes, $UsingDirectiveText = false)
    {
         $output_data = array();
        
        foreach ($SourcePVOTypes as $PVO_Type)
        {
               //если надо, то используем директивный текст в combobox'e
               if($UsingDirectiveText === true)
               {
                     $output_data[-1] = "Выберите тип организации";
               }
                $output_data[$PVO_Type->getId()] = $PVO_Type->getType();
        }
        
        return $output_data;
    }
    
    /**
     * Finds and displays a FoivPvo entity.
     *
     * @Route("/pvo/{id}", name="foivpvo_view")
     * @Method("GET")
     * @Template("ncuofoiv/FoivPvo/view.html.twig")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NCUOFoivBundle:FoivPvo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FoivPvo entity.');
        }
        
       $foiv = $entity->getFoiv();
      
        return array(
            'foiv'        => $foiv,
            'entity'      => $entity,
            'urlBack'     => $this->generateUrl('foivpvo', array('foivid' => $entity->getFoiv()->getId())),
            'urlEdit' => $this->generateUrl('foivpvo_edit', array('foivid' => $entity->getFoiv()->getId(), 'id' => $entity->getId() ) ),
        );
    }

    /**
     * Displays a form to edit an existing FoivPvo entity.
     *
     * @Route("/{foivid}/pvo/{id}/edit", name="foivpvo_edit")
     * @Method("GET")
     * @Template("ncuofoiv/FoivPvo/edit.html.twig")
     */
    public function editAction(Request $request, $foivid, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        
         $entity = $em->getRepository('NCUOFoivBundle:FoivPvo')->find($id);
         //динамически формируем URL для  выполнения операции обновления объекта в БД
        $ActionURL = $this->generateUrl('foivpvo_update', array('foivid' => $entity->getFoiv()->getId(), 'id'=>$id));
         
        if (!$entity )
        {
            throw $this->createNotFoundException('Unable to find FoivPvo entity.');
        }
                
        $typeList = $em->getRepository('NCUOFoivBundle:FoivPvoTypes')->findAll();
        
        
        //получаем всех руковдителей  для выбранной организации
        $personsList = $em->getRepository('NCUOFoivBundle:FoivPvoPersons')->findBy( array('fkFoivPvo'=>$id) );
        $this->get('logger')->debug("personsList count: ".count($personsList));
        
         //динамически формируем URL для возврата к списку
        $URL  = $this->generateUrl('foivpvo', array('foivid' => $entity->getFoiv()->getId()));
        
       
        return array(
            "foiv" => $entity->getFoiv(),
            "entity" => $entity,
            "typeList"  => $typeList,
            "personsList"  => $personsList,
            'urlBack' => $this->generateUrl('foivpvo', array('foivid' => $entity->getFoiv()->getId())),
            'urlView' => $this->generateUrl('foivpvo_view', array('foivid' => $entity->getFoiv()->getId(), 'id' => $entity->getId() )),
            'ActionURL' => $ActionURL,
            'urlNewPerson'   => $this->generateUrl('foiv_pvo_add_person', array('foivid' => $entity->getFoiv()->getId(), 'id' => $entity->getId() ) )
			
			
        );
    }

    /**
     * @Route("/{foivid}/pvo/{id}/newPerson", name = "foiv_pvo_add_person")
     * 
     * Create new pvo person
     */    
    public function newPerson(Request $request, $foivid, $id) {
        
        $resp = array('err_id' => NULL, 'res_msg' => NULL);

        try{
			
			if( trim($request->request->get("surname")) == ""){
                $error_field_name = "surname";
            }elseif( trim($request->request->get("name")) == ""){
                $error_field_name = "name";
            }elseif( trim($request->request->get("patronimyc")) == ""){
                $error_field_name = "patronimyc";
            }/*elseif
				if(trim($request->request->get("position")) == ""){
                $error_field_name = "position";
            }elseif(trim($request->request->get("phone")) == ""){
                $error_field_name = "phone";
            }*/
			else{
                $error_field_name = "";
            }
            if( !empty($error_field_name)) {
                    $resp['err_id']  = 2;
                    $resp['res_msg'] = 'Пожалуйста, заполните все обязательные поля!';
                    $resp['content']["id"] = $error_field_name;
            }else{
    
                $em = $this->getDoctrine()->getManager();
				//Демьянов А.Е. 09.02.2016 
				//Сначала создаем новую FOIV-персону и сохраняем ее в БД, 
				//а потом привязываем pvo-персону к новой персоне, 
				//т.к. pvo-сущность зависит от FoivPerson
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
				
                $entity = new FoivPvoPersons();
                /*$entity->setFio( $request->request->get("name") );
                $entity->setPosition($request->request->get("position"));
                $entity->setPhone($request->request->get("phone"));
                //$entity->setWebsiteUrl($request->request->get(''));
                $entity->setAddress($request->request->get('address'));
                $entity->setEmail($request->request->get('email'));*/
				if($id == 0)
				{
					$foiv = null;
				}
				else
				{
					$foiv = $em->getRepository('NCUOFoivBundle:FoivPvo')->find($id);
				}
				
                if($id != 0 && !$foiv)
                {
                    $resp['err_id']  = 1;
                    $resp['res_msg'] = 'ФОИВ  с нужным идентификатором не найден';
                }else{
            
                    $entity->setFkFoivPvo($foiv);
					
					//Демьянов А.Е. 09.02.2016 
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
                        $resp['res_msg'] = $this->container->getParameter('msg.error.upd');
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
    * Creates a form to edit a FoivPvo entity.
    *
    * @param FoivPvo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(FoivPvo $entity)
    {
        $form = $this->createForm(new FoivPvoType(), $entity, array(
            'action' => $this->generateUrl('foivpvo_update', array('foivid' => $entity->getFoiv()->getId(), 'id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing FoivPvo entity.
     *
     * @Route("/{foivid}/pvo/{id}/edit", name="foivpvo_update")
     * @Method("POST")
     * @Template("ncuofoiv/FoivPvo/edit.html.twig")
     */

    public function updateAction(Request $request, $foivid, $id){

        $message = array( 'message_type' => 0, 'message_subj'  => "", 'message_text'  => "" );

        try{
            if( trim($request->request->get("name")) == ""){
                $error_field_name = "name";
            }elseif(trim($request->request->get("shortName")) == ""){
                $error_field_name = "shortName";
            }elseif(trim($request->request->get("address")) == ""){
                $error_field_name = "address";
            }else{
                $error_field_name = "";
            }
            if( !empty($error_field_name)) {
                 $this->addFlash(
                    'error_field_name',
                    $error_field_name
                );
            }else{
                $em = $this->getDoctrine()->getManager();
                $entity = $em->getRepository('NCUOFoivBundle:FoivPvo')->find($id);
                $director = $em->getRepository('NCUOFoivBundle:FoivPvoPersons')->find($request->request->get( "director" ));
                $type = $em->getRepository('NCUOFoivBundle:FoivPvoTypes')->find($request->request->get( "type" ));
                $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find( $foivid);
                
                $entity->setName($request->request->get("name" ));
                $entity->setShortName($request->request->get("shortName" ));
                $entity->setWebsiteUrl($request->request->get("websiteUrl" ));
                $entity->setAddress($request->request->get( "address" ));
                $entity->setFunctions($request->request->get( "functions" ));
                $entity->setDirector($director);
                $entity->setEmail($request->request->get( "email" ));
                $entity->setPhone($request->request->get( "phone" ));
                $entity->setType( $type);
                $entity->setFoiv($foiv);
                
                $em->flush($entity);
				
				//Демьянов А.Е. 10.02.2016 
				//если возможно, то запоминаем ссылку на организацию
				if($director != null)
				{
					$director->setFkFoivPvo($entity);
					$em->flush();
				}
				
                $message = array(
                    'message_type' => 2,
                    'message_subj'  => $this->container->getParameter('msg.updated'),
                    'message_text'  => ""
                );
                
            }
            
        }catch(\Exception $e){
                $message = array(
                    'message_type' => 1,
                    'message_subj'  => $this->container->getParameter('msg.error.upd')." ".$e->getMessage(),
                    'message_text'  => $e->getTraceAsString()
                );
            //$this->addFlash(  
            //   'error_update',
            //   $e->getMessage()
            //);
        }

        $typeList = $em->getRepository('NCUOFoivBundle:FoivPvoTypes')->findAll();
        $personsList = $em->getRepository('NCUOFoivBundle:FoivPvoPersons')->findBy( array('fkFoivPvo'=>$id) );
        
        return array(
            "foiv" => $entity->getFoiv(),
            "entity" => $entity,
            "message" => $message,
            "typeList"  => $typeList,
            "personsList"  => $personsList,
            'BackURL' => $this->generateUrl('foivpvo', array('foivid' => $entity->getFoiv()->getId() ) ),
			'urlView' =>  $this->generateUrl('foivpvo_view', array('foivid' => $entity->getFoiv()->getId(), 'id' => $entity->getId())),
            'ActionURL' => $this->generateUrl('foivpvo_edit', array('foivid' => $entity->getFoiv()->getId(), 'id' => $entity->getId() ) ),
            'urlNewPerson'   => $this->generateUrl('foiv_pvo_add_person', array('foivid' => $entity->getFoiv()->getId(), 'id' => $entity->getId() ) ),
			'IsEditMode' => true
            );
    }    
    
	/*
    public function updateAction_old(Request $request, $foivid, $id)
    {
        try
        {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NCUOFoivBundle:FoivPvo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FoivPvo entity.');
        }
        
        //создаем класc метаинформации об FoivPVO
        $OrmMetaData = ORMObjectMetadata::Create($em, 'NCUOFoivBundle:FoivPvo');

        //сохраняем измененнные данные в объект
        $entity->setName($request->request->get("name" ));
        $entity->setShortName($request->request->get("shortName" ));
        $entity->setWebsiteUrl($request->request->get("websiteUrl" ));
        $entity->setAddress($request->request->get( "address" ));
        $entity->setFunctions($request->request->get( "functions" ));
        
        $DirectorID =   $request->request->get( "director" );
        $Director = $em->getRepository('NCUOFoivBundle:FoivPvoPersons')->find($DirectorID);

        //получаем данные по конкретному контролу
        $data = $request->request->get(FoivPvo::getFullNameControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getFullNameControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getFullNameControlCaption(), FoivPvo::getFullNameControlID());
       }
        $entity->setName($data);
                
        //получаем данные по конкретному контролу
        $data = $request->request->get(FoivPvo::getShortNameControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getShortNameControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getShortNameControlCaption(), FoivPvo::getShortNameControlID());
       }
        $entity->setShortName($data);
        
        $data = $request->request->get(FoivPvo::getWebURLControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getWebURLControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getWebURLControlCaption(), FoivPvo::getWebURLControlID());
       }
        $entity->setWebsiteUrl($data);
        
        $data = $request->request->get(FoivPvo::getAddressControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getAddressControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getAddressControlCaption(), FoivPvo::getAddressControlID());
       }
        $entity->setAddress($data);
        
        $data = $request->request->get(FoivPvo::getFunctionsControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getFunctionsControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getFunctionsControlCaption(), FoivPvo::getFunctionsControlID());
       }
        $entity->setFunctions($data);
        
        
         $data = $request->request->get(FoivPvo::getDirectorControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getDirectorControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getDirectorControlCaption(), FoivPvo::getDirectorControlID());
       }
        $Director = $em->getRepository('NCUOFoivBundle:FoivPvoPersons')->find($data);

        if(!$Director)
        {
             throw $this->createNotFoundException('Руководитель  с нужным идентификатором не найден');
        }
        $entity->setDirector($Director);
        
        $entity->setPhone($request->request->get( "phone" ));
        $entity->setEmail($request->request->get( "email" ));
        
        $TypeID =   $request->request->get( "type" );

        $data = $request->request->get(FoivPvo::getPhoneControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getPhoneControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getPhoneControlCaption(), FoivPvo::getPhoneControlID());
       }
       $entity->setPhone($data);
        
        $data = $request->request->get(FoivPvo::getEmailControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getEmailControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getEmailControlCaption(), FoivPvo::getEmailControlID());
       }
        $entity->setEmail($data);
        
         $data = $request->request->get(FoivPvo::getTypeControlID());
        //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
        if( $OrmMetaData->IsFieldMandatory(FoivPvo::getTypeControlName())  && $data == null )
        {
                //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                return ErrorResponseGenerator::getRequiredFieldError(FoivPvo::getTypeControlCaption(), FoivPvo::getTypeControlID());
       }
        $Type = $em->getRepository('NCUOFoivBundle:FoivPvoTypes')->find($data);
        if(!$Type)
        {
             throw $this->createNotFoundException('Тип организации с нужным идентификатором не найден');
        }
        $entity->setType($Type);
      
        //сохраняем в БД
        $em->flush();
       
        return  $this->redirect($this->generateUrl('foivpvo_edit', array('foivid'=>$entity->getFoiv()->getId(), 'id'=> $entity->getId() ) ) );
         //отправляем ответ об успешном проведении операции
         return ErrorResponseGenerator::getSuccessResponse();
        }
        catch(\Exception $ex)
        {
            //генерируем ответ об внутренней ошибке сервера
              return ErrorResponseGenerator::getInternalServerError($ex->getMessage());
        }
    }
	*/
    
    /**
     * Deletes a FoivPvo entity.
     *
     * @Route("/{foivid}/pvo/{id}/delete", name="foivpvo_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $foivid, $id)
    {
		
         $em = $this->getDoctrine()->getManager();
        
          //получаем из БД сам контактный объект
          $entity = $em->getRepository('NCUOFoivBundle:FoivPvo')->find($id);

            if (!$entity)
            {
                throw $this->createNotFoundException('Не найдена организация с нужным индетификатором');
            }
			
			//Демьянов А.Е. 10.02.2016
			//Принудительно обнуляем связь с директором (то есть с сущносттью FoivPVOPerson),
			//чтобы аннулировать одну их двух перекрестных связей. Иначе будут проблемы с удалением
			$entity->setDirector(null);
			$em->flush();
			
            try
			{
				//Демьянов А.Е. 10.02.2016
				//Ищем те PVO-персоны, которые связанны с данной организацией
				$PVOPersons = $em->getRepository('NCUOFoivBundle:FoivPvoPersons')->findByFkFoivPvo($entity->getId());
				if($PVOPersons != null )
				{
					
					$Person =  null;
					//удаляем все PVO-персоны, 
					//связанные с данной организацией
					foreach($PVOPersons as $CurrentPVOPerson)
					{
						$Person = $CurrentPVOPerson->getPerson();
						$em->remove($CurrentPVOPerson);
						$em->flush();
						$em->remove($Person);
						$em->flush();
					}
				}
				
                $em->remove($entity);
                $em->flush();
            }
			catch( \Exception $e )
			{
				$this->get('logger')->debug("error: ".$e->getMessage());
                $resp = array(
                    'err_id' => 1,
                    'res_msg'  => $e->getMessage(),
                    'error_explain'  => $e->getTraceAsString()
                );
                $response = new Response();
                $response->setContent( json_encode( $resp) );
        
                return $response;
            }
        
            return new Response(Response::HTTP_OK );
    }

    /**
     * Creates a form to delete a FoivPvo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('foivpvo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Удалить'))
            ->getForm()
        ;
    }
    
    /**
     * Imports FoivPvo entities.
     *
     * @Route("/{foivid}/pvo/import", name="foivpvo_import")
     * @Method("POST")
     * @Template("ncuofoiv/foivPvo/index.html.twig")
     */
    public function importAction($foivid)
    {   
        $em = $this->getDoctrine()->getManager();
        
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);

        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        
        $file = fopen($_FILES["importData"]["tmp_name"], "r");
        
        $count = 0;
        while(!feof($file)) {
            $pvo_array = fgetcsv($file, 0, ";");
            
            $pvo = new FoivPvo();
            
            if ($pvo_array[5]) {
                $director = new FoivPvoPersons();
                $director->setFio($pvo_array[5]);
                $director->setPosition($pvo_array[4]);
                $director->setPhone($pvo_array[6]);
                $director->setEmail($pvo_array[7]);
                
                $em->persist($director);
                
                $pvo->setDirector($director);
            }
            
            $pvo->setFoiv($foiv);
            $pvo->setShortName($pvo_array[0]);
            $pvo->setName($pvo_array[1]);
            $pvo->setWebsiteUrl($pvo_array[2]);
            $pvo->setAddress($pvo_array[3]);
            
            
            $pvo->setPhone($pvo_array[6]);
            $pvo->setEmail($pvo_array[7]);
            
            
            $em->persist($pvo);
            $em->flush();
            
            $count++;
        }
        
        fclose($file);
        
        $entities = $em->getRepository('NCUOFoivBundle:FoivPvo')->findByFoiv($foiv);

        return array(
            'foiv' => $foiv,
            'entities' => $entities,
            'msg' => "Успешно импортировано $count организация и предприятий",
        );
    }
}
