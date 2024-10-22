<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\FoivBundle\Controller\ErrorResponseGenerator;
use App\NCUO\FoivBundle\Entity\ORMObjectMetadata;
use App\NCUO\FoivBundle\Entity\FoivPerson;
use App\NCUO\FoivBundle\Entity\File;

/*
 *Контроллер по персонам 
*/
class PersonController extends Controller
{
 
     /**
     * Lists all persons entities.
     *
     * @Route("/persons", name="foiv_person")
     * @Method("GET")
     * @Template("ncuofoiv/Person/index.html.twig")
     */
    public function indexAction()
    {
         $em = $this->getDoctrine()->getManager();
         $repository = $em->getRepository('NCUOFoivBundle:FoivPerson');
          
          if($repository == null)
          {
             return new Response("Not found repository!");
          }
         // $this->get('logger')->debug('repository is found!');
          
          $data_list = $repository->findAll();
        
          
          $_SESSION['PERSON_BACK_URL']  = $this->generateUrl('foiv_person');
          return array("persons" => $data_list);
    }
    
     /**
     * Lists all persons entities.
     *
     * @Route("/persons/full_list", name="full_persons_list")
     * @Method("POST")
     * @Template("ncuofoiv/Person/index.html.twig")
     */
    public function getPersonsList()
    {
          $em = $this->getDoctrine()->getManager();
          $Persons = $em->getRepository('NCUOFoivBundle:FoivPerson')->findAll();
         if(!$Persons)
         {
            throw $this->createNotFoundException('Не удалось получить список персон');
         }
  
         $PersonsList = array();
         foreach($Persons as $person)
         {
               $PersonsList[] = $person->getAsArray();
         }
   
          return ErrorResponseGenerator::getSuccessResponseWithData($PersonsList);
    }
    /**
     * Displays a form to create a new  entity.
     *
     * @Route("/persons/new", name="person_new")
     * @Method("GET")
     * @Template("ncuofoiv/Person/edit.html.twig")
     */
    public function newAction()
    {
       $em = $this->getDoctrine()->getManager();
    
        //получаем список файлов
        $files_list = $this->getAllItems($em, 'NCUOFoivBundle:File',  null, 'Не удалось получить список файлов' );
                
         //динамически формируем URL для возврата к списку
        $URL  = $this->generateUrl('foiv_person');
        
        //динамически формируем URL для  выполнения операции сохранения контакта в БД
        $ActionURL = $this->generateUrl('person_create');
        //URL - mссылка для получение обновленного списка файлов
        $ReloadFilesListURL = $this->generateUrl('full_file_list');
        //URL-ссылка длдя получения информации о файле по его идентификатору
        $GettingFileURL = $this->generateUrl('get_file_by_id');
         
         return array( 
                              "PhotoFiles" => $this->createPhotoFileList($files_list, true),
                              "ActionTitle"=>"Создание новой персоны",
                              "person" => new FoivPerson(),
                              "ActionURL" => $ActionURL,
                              "RetunedURL"=>$URL,
                              "GettingFileURL" => $GettingFileURL,
                              "ReloadURL" =>$ReloadFilesListURL, 
                              "ScriptFileName" => "bundles/ncuofoiv/js/person.js", //URL-ссылка на JavaScript c обработчиком событий. 
                              "SaveFunctionName" => "OnSavePersonClick('create_data', 'msg_dialog','Персона успешно создана', 'Ошибка создания новой персоны')" //Описание имени обработчика создания и его парметр. 
                             );
    }
    
    
     /**
     * Creates a new person entity.
     *
     * @Route("/persons/create", name="person_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
          try
          {
            $em = $this->getDoctrine()->getManager();
            $result = $this->setDataToPerson($request, $em, new FoivPerson());
            
            if($result  instanceof JsonResponse)
            {
               return $result;
            }
            else
            if($result  instanceof \NCUO\FoivBundle\Entity\FoivPerson )
            {
               $entity =   $result;
            }
            else
            {
                    throw new \Exception("Ошибка обработки данных в процессе создания новой персоны");
            }
             
             $em->persist($entity);
             $em->flush();
         
          //отправляем ответ об успешном проведении операции + ссылку для редиректа на форму редактирования существующего объекта
		  return ErrorResponseGenerator::getSuccessResponseWithURLData($this->generateUrl('person_edit', array('id'=>$entity->getId())),array('id'=>$entity->getId()));
     
          }
          catch(\Exception $ex)
          {
               return ErrorResponseGenerator::getInternalServerError($ex->getMessage());
          }
          
    }

    private function setDataToPerson($request, $em,\NCUO\FoivBundle\Entity\FoivPerson $NewPerson)
    {
               //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getSurnameControlID());
               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getSurnameColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getSurnameControlCaption(), FoivPerson::getSurnameControlID());
              }
              $NewPerson->setSurname($data);
               
                //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getNameControlID());
               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getNameColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getNameControlCaption(), FoivPerson::getNameControlID());
              }
               $NewPerson->setName($data);
               
                //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getPatronymicControlID());
               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getPatronymicColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getPatronymicControlCaption(), FoivPerson::getPatronymicControlID());
              }
               $NewPerson->setPatronymic($data);
               
               //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getAddressControlID());
               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getAddressColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getAddressControlCaption(), FoivPerson::getAddressControlID());
              }
               $NewPerson->setAddress($data);

               
               //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getPhotoFileControlID());

               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getPhotoFileColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getPhotoFileControlCaption(), FoivPerson::getPhotoFileControlID());
               }
               
               //Пользователь выбрал уже существующий фотопортрет персоны?
               if($data != null)
               {
                    $PhotoFile = $em->getRepository('NCUOFoivBundle:File')->find($data);
                    $NewPerson->setPhotoFile($PhotoFile);
               }

              //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getPhoneControlID());
               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getPhoneColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getPhoneControlCaption(), FoivPerson::getPhoneControlID());
              }
               $NewPerson->setPhone($data);
               
               //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getWebsiteUrlControlID());
               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getWebsiteUrlColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getWebsiteUrlControlCaption(), FoivPerson::getWebsiteUrlControlID());
              }
               $NewPerson->setWebsiteUrl($data);
               
                //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getEmailControlID());
               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getEmailColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getEmailControlCaption(), FoivPerson::getEmailControlID());
              }
               $NewPerson->setEmail($data);
               
               
               //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getPositionControlID());
               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getPositionColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getPositionControlCaption(), FoivPerson::getPositionControlID());
              }
               $NewPerson->setPosition($data);
               
                 //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getBiographyControlID());
               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getBiographyColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getBiographyControlCaption(), FoivPerson::getBiographyControlID());
              }
               $NewPerson->setBiography($data);
			   
			   //получаем данные по конкретному контролу
               $data = $request->request->get(FoivPerson::getServiceControlID());
               //проверяем: является ли данное поле обязательным к заполнению и есть ли там данные
               if( FoivPerson::IsMandatory(FoivPerson::getServiceColumnName())  && $data == null )
               {
                       //генерируем ответ об ошибке (типа нужно заполнить данное поле)
                       return ErrorResponseGenerator::getRequiredFieldError(FoivPerson::getServiceControlCaption(), FoivPerson::getServiceControlID());
              }
               $NewPerson->setService($data); 
               
			   
               return $NewPerson;  
    }
    
    /**
     * Добавление фото в БД
     *
     * @Route("/persons/add_photo", name="add_photo")
     * @Method("POST")
     */
    public function AddPhotoAction()
    {
          //получаем данные в формате JSON и
          //предтсавляем их в видде ассоциативного массива
          $RAWData = json_decode($_POST["JSON_DATA"],true);
          if($RAWData != null)
          {
               //получаем оригинальное имя файла
               $original_file_name = $RAWData["file_name"];
               //получаем название файла
               $title              = $RAWData["title"];
               //получаем описание файла
               $decription         = $RAWData["decription"];
               //получаем тип файла
               $mime_type          = $RAWData["mime_type"];
               //получаем размер файла
               $size               = $RAWData["size"];
               //получаем расширение файла
               $file_ext           = $RAWData["file_ext"];
               //формируем хэшированное имя файла
               $hash_file_name     = md5(original_file_name.date("U")).".".$file_ext;
               //получаем содерожимое файла в формате Base64
               $raw                = $RAWData["data"];
               
               //получаем локальный путь к будущему файлу
               $localPath = $this->container->getParameter('ncuofoiv.path_img_persons')."/";
			   
               $uploadUrlDir = $localPath.$hash_file_name;

               //получаем полный путь к файлу 
               $uploadDir =  $this->getRequest()->server->get('DOCUMENT_ROOT').$uploadUrlDir;

				//Проверяем целевую папку на право записи файла
				$test_dir = $this->getRequest()->server->get('DOCUMENT_ROOT').$localPath;
			    if (!is_dir($test_dir) || !is_writable($test_dir)) 
			    {
					return ErrorResponseGenerator::getInternalServerError("Отсутсвуют права на запись файла в директорию");
  			    }

               //декодируем из формата Base64 в нормальный вид и сразу записываем в файл
               $result = file_put_contents($uploadDir, base64_decode($raw));
               if($result === FALSE)
               {
                    return ErrorResponseGenerator::getInternalServerError("Не удалось сохранить данные в файл '".$uploadDir."'");
               }
               
               //теперь записываем информацию в БД
               $em = $this->getDoctrine()->getManager();
               $PhotoFile =  new File();
               
               $PhotoFile->setName($hash_file_name);
               $PhotoFile->setTitle($title);
               $PhotoFile->setMimeType($mime_type);
               $PhotoFile->setExt($file_ext);
               $PhotoFile->setSize($size);
               $PhotoFile->setPath($localPath);
               $PhotoFile->setDescription($decription);
               $PhotoFile->setOriginalName($original_file_name);
               
               $em->persist($PhotoFile);
               $em->flush();
               //все нормально                             
               return ErrorResponseGenerator::getSuccessResponse();
          }
          else
          {
                return ErrorResponseGenerator::getInternalServerError("Не удалось получить данные в формате JSON");
               //$this->get('logger')->debug("RAWData NOT exist!");
          }
    }

    /**
     * Displays a form to edit a existing  entity.
     *
     * @Route("/persons/{id}/edit/", name="person_edit")
     * @Method("GET")
     * @Template("ncuofoiv/Person/edit.html.twig")
     */
    public function editAction($id)
    {
         $em = $this->getDoctrine()->getManager();
      
       $entity = $em->getRepository('NCUOFoivBundle:FoivPerson')->find($id);

        if (!$entity)
        {
            throw $this->createNotFoundException('Не удалось найти персону с идентификатором ID: '.$id);
        }
        
       //получаем список файлов
        $files_list = $this->getAllItems($em, 'NCUOFoivBundle:File',  null, 'Не удалось получить список файлов' );
                
         //динамически формируем URL для возврата к списку
         if( $_SESSION['PERSON_BACK_URL']  != null)
         {
                 $URL  = $_SESSION['PERSON_BACK_URL'];
         }
         else
         {
               $URL  = $this->generateUrl('foiv_person');
         }
        //динамически формируем URL для  выполнения операции сохранения контакта в БД
        $ActionURL = $this->generateUrl('person_update', array('id'=>$id));
         //URL - mссылка для получение обновленного списка файлов
        $ReloadFilesListURL = $this->generateUrl('full_file_list');
        
        //URL-ссылка длдя получения информации о файле по его идентификатору
        $GettingFileURL = $this->generateUrl('get_file_by_id');
        
         //используем SourceData для того, чтобы в шаблоне залить ПРЕДОПРЕДЕЛЕННЫМИ данными соответсвующий HTML-контрол (например combobox)
         return array(
                              "PhotoFiles" => $this->createPhotoFileList($files_list, true),
                              "ActionTitle"=>"Редактирование персоны",
                              "person" => $entity ,
                              "ActionURL" => $ActionURL,
                              "RetunedURL"=>$URL,
                              "ReloadURL" =>$ReloadFilesListURL,
                              "GettingFileURL" => $GettingFileURL,
                              "ScriptFileName" => "bundles/ncuofoiv/js/person.js", //URL-ссылка на JavaScript c обработчиком событий. 
                              "SaveFunctionName" => "OnSavePersonClick('create_data', 'msg_dialog','Персона успешно сохранена', 'Ошибка сохранениня персоны')" //Описание имени обработчика создания и его парметр. 
                             );
    }
    
      /**
     * Displays a form to edit a existing  entity.
     *
     * @Route("/persons/{id}/update/", name="person_update")
     * @Method("POST")
     */
    public function updateAction(Request $request, $id)
    {
          try
          {
           $em = $this->getDoctrine()->getManager();
            
           $entity = $em->getRepository('NCUOFoivBundle:FoivPerson')->find($id);

           if (!$entity)
           {
               throw $this->createNotFoundException('Unable to find person entity.');
           }
        
            $result = $this->setDataToPerson($request, $em, $entity);
     
            if($result  instanceof JsonResponse)
            {
			   $this->get('logger')->debug('result  is JsonResponse');
               return $result;
            }
            else
            if($result  instanceof \NCUO\FoivBundle\Entity\FoivPerson )
            {
               $entity =   $result;
            }
            else
            {
				    $this->get('logger')->debug('Ошибка обработки данных в процессе cохранения персоны');
                    throw new \Exception("Ошибка обработки данных в процессе cохранения персоны");
            }
             
             
             $em->flush();
         
          //отправляем ответ об успешном проведении операции + ссылку для редиректа на форму редактирования существующего объекта
          return ErrorResponseGenerator::getSuccessResponse();
     
          }
          catch(\Exception $ex)
          {
               return ErrorResponseGenerator::getInternalServerError($ex->getMessage());
          }
    }
    
     /**
     * Displays a form to edit a existing  entity.
     *
     * @Route("/persons/{id}/delete/", name="person_delete")
     * @Method("GET")
     */
    public function deleteAction($id)
    {
		try
		{
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NCUOFoivBundle:FoivPerson')->find($id);

            if (!$entity)
            {
                throw $this->createNotFoundException('Unable to find person entity.');
            }

            $em->remove($entity);
            $em->flush();
        

        return  new Response(Response::HTTP_OK);
		}
		catch(\Exception $ex)
	    {
		   //теперь отсылаем клиенту
		   return ErrorResponseGenerator::getInternalServerError($ex->getMessage());
	    }
    }
    
     /**
     * Displays a form about a existing  entity.
     *
     * @Route("/persons/{id}/show/", name="person_show")
     * @Method("GET")
     * @Template("ncuofoiv/Person/show.html.twig")
     */
    public function showAction($id)
    {
           $em = $this->getDoctrine()->getManager();
           $entity = $em->getRepository('NCUOFoivBundle:FoivPerson')->find($id);

            if (!$entity)
            {
                throw $this->createNotFoundException('Unable to find person entity.');
            }
          
           $_SESSION['PERSON_BACK_URL']  = $this->generateUrl('person_show', array('id'=>$id));
           $ActionURL =  $this->generateUrl('person_edit', array('id'=>$id));
           $URL =   $this->generateUrl('foiv_person');  
            return array(
                          "ActionTitle"=>"Подробности о персоне",
                          "person" => $entity ,
                          "ActionURL" => $ActionURL,
                          "ReturnedURL"=>$URL
                         );
    }
    
	/**
     * get contact by person ID 
     *
     * @Route("/person/get_by_id", name="get_person_by_id")
     * @Method("POST")
     */
	public function getContactByID()
	{
		try
		{
			$JSONData = json_decode($_POST["JSON_DATA"],true);
			
			if($JSONData == null)
            {
				  return ErrorResponseGenerator::getInternalServerError("Не удалось получить данные в формате JSON");
			}
          
         //получаем идентификатор контакта
         $person_id = $JSONData["person_id"];
         if($person_id == null)
         {
            return ErrorResponseGenerator::getInternalServerError("Не удалось получить идентификатор персоны");
         }
         
         $em = $this->getDoctrine()->getManager();
		 //получаем персону по ее идентификатору
		 $SelectedPerson = $em->getRepository('NCUOFoivBundle:FoivPerson')->find($person_id);
			
		if($SelectedPerson == null )
		{
			return ErrorResponseGenerator::getInternalServerError("Не найдена нужная персона");
		}
		
		return ErrorResponseGenerator::getSuccessResponseWithData($SelectedPerson->getAsArray());
        			
		}
		catch(\Exception $ex)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить информацию о персоне." + $ex->getMessage());
		}
	}
	
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
        
        /*if(!$all_items)
        {
            throw $this->createNotFoundException($ExceptionText);
        }*/
        
        return $all_items;
    }
    
    private function createPhotoFileList($FilesList, $UsingDirectiveText = false)
    {
        $output_data = array();
        
        foreach ($FilesList as $file)
        {
               //если надо, то используем директивный текст в combobox'e
               if($UsingDirectiveText === true)
               {
                     $output_data[-1] = "Выберите файл фото...";
               }
                $output_data[$file->getId()] = $file->getTitle();
        }
     
        return $output_data;
    }
    
}
?>