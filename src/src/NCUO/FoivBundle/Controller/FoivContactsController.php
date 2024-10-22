<?php


namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\NCUO\FoivBundle\Entity\FoivContacts;
use App\NCUO\FoivBundle\Form\FoivContactsType;
use App\NCUO\FoivBundle\Entity\FoivPerson;

use App\NCUO\FoivBundle\Controller\ErrorResponseGenerator;
use App\NCUO\FoivBundle\Entity\ORMObjectMetadata;

use Doctrine\ORM\EntityRepository;
/**
 * FoivContacts controller.
 *
 *
 */
class FoivContactsController extends Controller
{

    /**
     * Lists all FoivContacts entities.
     *
     * @Route("/{foivid}/foivcontacts", name="foiv_contacts_list")
     * @Method("GET")
    * @Template("ncuofoiv/foivContacts/index.html.twig")
     */
    public function indexAction($foivid)
    {
      
        $em = $this->getDoctrine()->getManager();
          
        try{
                $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
                
                if (!$foiv) {
                    throw $this->createNotFoundException('Unable to find Foiv entity.');
                }
                
                
                $entities = $em->getRepository('NCUOFoivBundle:FoivContacts')->findByFoiv($foiv);
         
                return array(
                    'foiv' => $foiv,
                    'entities' => $entities,
                );
        }
        catch(Exception $e)
        {
            return new Response('Fatal error: ' . $e->getMessage());
        }
    }
    
	
    /**
     * Процедура сохранения нового контакта в БД
     *
     * @Route("{foivid}/foivcontacts/create", name="foivcontacts_create")
     * @Method("POST")
     * 
     */
    public function createAction(Request $request, $foivid)
    {
        try
        {
       
        $em = $this->getDoctrine()->getManager();
       
        //получаем объект ФОИВ по его ID
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if($foiv == null)
        {
             throw new \Exception("Не найден ФОИВ-объект с идентификатором". $foivid);
        }
       
         $entity = null;
		
         $Result =  $this->setData($request, $em);
		// $this->get('logger')->debug("after calling setData(...)");
		//Если результат является JsonResponse, 
		//то скорее всего это ответ с сообщением об ошибке
		if($Result  instanceof JsonResponse)
		{
		   return $Result;
		}
		else
		//если результат являетсясущностью  нового контакта, то его надо потом сохранить
		if($Result  instanceof \NCUO\FoivBundle\Entity\FoivContacts )
		{
		   $entity =   $Result;
		}
		else
		{
				throw new \Exception("Ошибка обработки данных в процессе cохранения персоны");
		}
       
        $entity->setFoiv($foiv);
         //сохраняем в БД
        $em->persist($entity);
        $em->flush();

        //отправляем ответ об успешном проведении операции + ссылку для редиректа на форму редактирования существующего объекта
         return ErrorResponseGenerator::getSuccessResponse($this->generateUrl('foivcontacts_edit', array('id' => $entity->getId())));
        }
        catch(\Exception $ex)
        {
             return ErrorResponseGenerator::getInternalServerError($ex->getMessage());
        }
    
    }
     
         
    
    /**
     * Отображение формы для создания нового контакта
     *
     * @Route("{foivid}/foivcontacts/new", name="foivcontactsl_new")
     * @Method("GET")
     * @Template("ncuofoiv/foivContacts/edit.html.twig")
     */
    public function newAction($foivid)
    {
       
          $em = $this->getDoctrine()->getManager();
        
        //проверяем: есть ли номер текущего ФОИВ - идентификатора?
        if (!isset($foivid))
        {
                throw $this->createNotFoundException('Не задан текущий ФОИВ-идентификатор');
        }
        
         $foiv =  $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv)
        {
            throw $this->createNotFoundException('Не удалось получить ФОИВ с идентификатором c номером '.$foivid);
        }
         
        //динамически формируем URL для возврата к списку
        $URL            = $this->generateUrl('foiv_contacts_list', array('foivid' => $foivid));
        
        //динамически формируем URL для  выполнения операции сохранения контакта в БД
        $ActionURL = $this->generateUrl('foivcontacts_create', array('foivid'=>$foiv->getId()));
        return array("foiv"=> $foiv,
					 "IsCreateAction" => true,
   		  			 "ActionTitle" => "Создание нового контакта", 			   //Заголовок 
				     "ActionURL" => $ActionURL,                                //URL-ссылка  для отправки запроса на сервре
				     "RetunedURL"=>$URL,                                       //URL-ссылка  для редиректа на другую страницу
				     "ScriptFileName" => "bundles/ncuofoiv/js/foivContacts.js", //URL-ссылка на JavaScript c обработчиком событий. 
				     "SaveFunctionName" => "OnSaveContactClick('create_data', 'msg_dialog','Контакт успешно создан', 'Ошибка создания контакта')" //Описание имени обработчика создания и его парметр. 
                             );
    }

      private function createContactsList($Persons, $UsingDirectiveText = false)
      {
          $output_data = array();
        
        foreach ($Persons as $person)
        {
                if( $UsingDirectiveText === true)
                {
                     $output_data[-1] = "Выберите персону...";    
                }
                
                $output_data[$person->getId()] = $person->getFIO();
        }
     
        return $output_data;
      }
	  
    /**
     * Finds and displays a FoivContacts entity.
     *
     * @Route("/foivcontacts/{id}", name="foivcontacts_show")
     * @Method("GET")
     * @Template("ncuofoiv/foivContacts/show.html.twig")
     */
    public function showAction($id)
    {
        //проверяем: есть ли номер текущего ФОИВ - идентификатора?
        if (!isset($_SESSION['FOIV_ID']))
        {
               throw $this->createNotFoundException('Не задан текущий ФОИВ-идентификатор');
        }
        
        //используем ФОИВ - идентификатора для внедрения в шаблон
        $foivid = $_SESSION['FOIV_ID'];
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NCUOFoivBundle:FoivContacts')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FoivContacts entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'foivid' => $foivid,
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Отображение формы для редактирования существующего контакта
     *
     * @Route("/foivcontacts/{id}/edit", name="foivcontacts_edit")
     * @Method("GET")
     * @Template("ncuofoiv/foivContacts/edit.html.twig")
     */
    public function editAction($id)
    {
        
        //динамически формируем URL для  выполнения операции обновления контакта в БД
        $ActionURL = $this->generateUrl('foivcontacts_update', array('id'=>$id));

        //пытаемся получить информацию о выбранном контакте        
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NCUOFoivBundle:FoivContacts')->find($id);
        if (!$entity)
        {
            throw $this->createNotFoundException('Unable to find FoivContacts entity.');
        }

        $foiv = $entity->getFoiv();
        //динамически формируем URL для возврата к списку
        $URL            = $this->generateUrl('foiv_contacts_list', array('foivid' => $foiv->getId() ));
                
        /*$Persons = $em->getRepository('NCUOFoivBundle:FoivPerson')->findAll();
		if(!$Persons)
		{
			throw $this->createNotFoundException('Не удалось получить список персон');
		}*/
       // $this->get('logger')->debug("Service person's: ".$entity->getPerson()->getService());
		
        return array("foiv" => $foiv,
					 "IsCreateAction" => false,
					 "SelectedContact" => $entity,
					 "ActionTitle" => "Редактирование контакта",
					 "ActionURL" => $ActionURL,
					 "RetunedURL"=>$URL,
					 "ScriptFileName" => "bundles/ncuofoiv/js/foivContacts.js", //URL-ссылка на JavaScript c обработчиком событий. 
					 "SaveFunctionName" => "OnSaveContactClick('create_data', 'msg_dialog','Контакт успешно изменен', 'Ошибка изменения контакта')" //Описание имени обработчика создания и его парметр. 
                    );
    
    
    }

  
    /**
     * Процедура сохранения измененного контакта
     *
     * @Route("/foivcontacts/{id}/update", name="foivcontacts_update")
     * @Method("POST")
     */
    public function updateAction(Request $request,$id)
    {
        try
        {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NCUOFoivBundle:FoivContacts')->find($id);
        if (!$entity)
        {
            throw $this->createNotFoundException('Unable to find FoivContacts entity.');
        }
        
           $Result =  $this->setData($request, $em,  $entity);
		   //Если результат является JsonResponse, 
		   //то скорее всего это ответ с сообщением об ошибке
           if($Result  instanceof JsonResponse)
            {
               return $Result;
            }
            else
            if($Result  instanceof \NCUO\FoivBundle\Entity\FoivContacts )
            {
				//Ничего не делаем
               
            }
            else
            {
                    throw new \Exception("Ошибка обработки данных в процессе cохранения персоны");
            }
       
        
        //сохраняем в БД
        $em->flush();
     
         //отправляем ответ об успешном проведении операции
         return ErrorResponseGenerator::getSuccessResponse();
        }
        catch(\Exception $ex)
        {
            //генерируем ответ об внутренней ошибке сервера
              return ErrorResponseGenerator::getInternalServerError($ex->getMessage());
        }
     }
        

     
    /**
     * Deletes a FoivContacts entity.
     *
     * @Route("/foivcontacts/{id}/delete", name="foivcontacts_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
      
	    try
		{
          $em = $this->getDoctrine()->getManager();
        
          //получаем из БД сам контактный объект
          $entity = $em->getRepository('NCUOFoivBundle:FoivContacts')->find($id);

            if (!$entity)
            {
                throw $this->createNotFoundException('Unable to find FoivContacts entity.');
            }
			
			//заранее получаем идентификатор с связанной сущностью FoivPerson
			$PersonID = $entity->getPerson()->getId();
			
			if($PersonID == null)
			{
				//генерируем ответ об внутренней ошибке сервера
				return ErrorResponseGenerator::getInternalServerError("Не удалось получить информацию об удаляемой персоне");
			}
			
            //удаляем контакт
            $em->remove($entity);
            $em->flush();
			
			//т.к. сущность FoivContacts связана с сущностью FoivPerson, то ее персону необходимо удалить
			//через переадресацию контроллера
        	$response = $this->forward('NCUOFoivBundle:Person:delete', array('id' => $PersonID));
			
			if($response instanceof JsonResponse)
			{
				//если это какая-то ошибка, то разбираемся в ней
				if(!$response->isOk())
				{
					//получаем JSON-данные
					$RAWData = json_decode($response->getContent(),true);
					//если нет никакого сообщения об ошибке или сообщение НЕ содержит код ошибки БД 23503 (ограничение по внешнему ключу), то отправляем ответ клиенту
					//Если сообщение содержит код ошибки БД 23503 - это значит, что у удаляемой сущности FoivPerson есть связи с сущностями из связанных таблиц
					//и эту сущность можно удалить только тогда, когда связанные с ней другие сущности будут заранее удалены. 
					//Поэтому мы эту ошибку игнорируем и не оповоещаем об ней клиента.
					if($RAWData['message'] == null || strpos($RAWData['message'],"SQLSTATE[23503]", 0) === false)
					{
							return $response;
					}
				}
			}
			
			return new Response(Response::HTTP_OK );
		}
		catch(\Exception $ex)
        {
            //генерируем ответ об внутренней ошибке сервера
            return ErrorResponseGenerator::getInternalServerError($ex->getMessage());
        }
    }
	
  /*
   * Проверка всех полей с введенными данными
   * 
   * Request - Объект запроса
   * Возврат: - JsonResponse в случае неудачи в ходе проверки
   *		  - null в случае успешной проверки всех полей
   * 
   * Примечание: т.к. сущность FoivContacts связана с сущностью FoivPerson,
   *			 то для проверки своих полей используются проверочные функции от FoivPerson
   */
	private function checkInputData($Request)
	{
		
		$data = $Request->request->get(FoivPerson::getSurnameControlID());
		if(FoivContacts::IsMandatory(FoivPerson::getSurnameColumnName()) && $data == null)
		{
			return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getSurnameControlCaption(), FoivPerson::getSurnameControlID());
		}
		$Surname = $data;
		
		$data = $Request->request->get(FoivPerson::getNameControlID());
    	//проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
	    if( FoivPerson::IsMandatory(FoivPerson::getNameColumnName())  && $data == null )
	    {
		    //генерируем ответ об ошибке (типа нужно заполнить данное поле)
		    return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getNameControlCaption(), FoivPerson::getNameControlID());
	    }
		$Name = $data;
	
		$data = $Request->request->get(FoivPerson::getPatronymicControlID());
  	    //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
	    if( FoivPerson::IsMandatory(FoivPerson::getPatronymicColumnName())  && $data == null )
	    {
		    //генерируем ответ об ошибке (типа нужно заполнить данное поле)
		    return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getPatronymicControlCaption(), FoivPerson::getPatronymicControlID());
	    }	
		$Patronymic = $data;
		
		$data = $Request->request->get(FoivPerson::getAddressControlID());
  	    //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
	    if( FoivPerson::IsMandatory(FoivPerson::getAddressColumnName())  && $data == null )
	    {
			   //генерируем ответ об ошибке (типа нужно заполнить данное поле)
			   return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getAddressControlCaption(), FoivPerson::getAddressControlID());
	    }
		
		$data = $Request->request->get(FoivPerson::getEmailControlID());
 	    //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
	    if( FoivPerson::IsMandatory(FoivPerson::getEmailColumnName())  && $data == null )
	    {
			   //генерируем ответ об ошибке (типа нужно заполнить данное поле)
			   return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getEmailControlCaption(), FoivPerson::getEmailControlID());
	    }
		
		$data = $Request->request->get(FoivPerson::getPhoneControlID());
  	    //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
	    if( FoivPerson::IsMandatory(FoivPerson::getPhoneColumnName())  && $data == null )
	    {
			   //генерируем ответ об ошибке (типа нужно заполнить данное поле)
			   return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getPhoneControlCaption(), FoivPerson::getPhoneControlID());
	    }
		
		$data = $Request->request->get(FoivPerson::getPositionControlID());
  	    //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
	    if( FoivPerson::IsMandatory(FoivPerson::getPositionColumnName())  && $data == null )
	    {
			   //генерируем ответ об ошибке (типа нужно заполнить данное поле)
			   return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getPositionControlCaption(), FoivPerson::getPositionControlID());
	    }
		$Position = $data;
		
		
		$data = $Request->request->get(FoivPerson::getServiceControlID());
  	    //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
	    if( FoivPerson::IsMandatory(FoivPerson::getServiceColumnName())  && $data == null )
	    {
			   //генерируем ответ об ошибке (типа нужно заполнить данное поле)
			   return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getServiceControlCaption(), FoivPerson::getServiceControlID());
	    }
		$Service = $data;
		
		//проверяем на дублирование
		$Repository = $this->getDoctrine()->getRepository("NCUOFoivBundle:FoivContacts");
		if($Repository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivContacts");
		}
		//получаем идентификатор редактируемой персоны (если существует)
		$PersonID  = $Request->request->get("PERSON_ID");
		//Проверяем запись на дублирование
		if($Repository->checkDuplicate($Surname, $Name, $Patronymic, $Position, $Service, $PersonID) == TRUE)
		{
			return ErrorResponseGenerator::getDuplicateDataError();
		}
		
		return null;
		
	}
	
   /*
   * Установление данных в объект Фоив-контакта
   * 
   * Request - Объект запроса
   * em - Doctrine-менеджер
   * FoivContact - объект ФОИВ-контакта (null если создается новый объект)
   * Возврат: - JsonResponse в случае неудачи в ходе проверки функцией checkInputData
   *		  - FoivContact в остальных случаях
   */
   private function setData($Request, $em,  $FoivContact = null)
   {
		//Осуществляем проверку по полям с введенными данными
        $Result = $this->checkInputData($Request);
		//если в результате проверки получен "ответ" - отправляем ответ об ошибке браузеру
		if($Result != null)
		{
			return $Result;
		}
		
		
		$Person = null;
		//Если ФОИВ-контакт отсутсвует - значит надо создать новый
        if($FoivContact == null)
        {
            $FoivContact = new FoivContacts();
			$Person = new FoivPerson();

			//осуществляем переадресацию вызова контроллера
			$response = $this->forward('NCUOFoivBundle:Person:create', array('Request' => $Request));
						
			if($response  instanceof JsonResponse)
			{
				//Полученный JSON-ответ от другого контроллера преобразуем в понятный массив 
				$RAWData = json_decode($response->getContent(),true);
				if($RAWData != null)
				{
					//Пытаемся получить идентификатор новой персоны
					$PersonID = $RAWData["data"]["id"];
				}
				else
				{
					//Ошибка: нет информации об новой персоне
					return ErrorResponseGenerator::getInternalServerError("Не удалось создать персону. Нет полной информации об персоне");
				}
			}
			else
			{
				//Ошибка: другой контроллер не смог создать новую персону
				return ErrorResponseGenerator::getInternalServerError("Не удалось создать персону");
			}
			
			//по идентификатору новой персоны получаем саму персону из БД
			$Person = $em->getRepository('NCUOFoivBundle:FoivPerson')->find($PersonID);
            if($Person == null)
			{
				return ErrorResponseGenerator::getInternalServerError("Не удалось найти нужную персону с идентификатором ".$PersonID );
			}
			
			$FoivContact->setPerson($Person);
        }
		//ФОИВ-контакт существует - тогда просто изменяем значения полей персоны (FoivPerson)
		else
		{
			//осуществляем переадресацию вызова контроллера
			$response = $this->forward('NCUOFoivBundle:Person:update', array('Request' => $Request, 'id' => $FoivContact->getPersonId()));

			if($response  instanceof JsonResponse)
			{
				//все в порядке?
				if(!$response->isOk())
				{
					$RAWData = json_decode($response->getContent(),true);
					$ErrorMessage = "Ошибка обработки данных в процессе cохранения персоны!";
					if($RAWData['message'] != null)
					{
						$ErrorMessage = $ErrorMessage." ".$RAWData['message'];
					}
					
					throw new \Exception($ErrorMessage);
				}
			}
			else
            {
                    throw new \Exception("Ошибка обработки данных в процессе cохранения персоны");
            }
			
		}
                
        return $FoivContact;
   }
    /**
     * Creates a form to delete a FoivContacts entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('foivcontacts_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
