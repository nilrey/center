<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\FoivBundle\Entity\File;
use App\NCUO\FoivBundle\Entity\Convention;
/**
 * Conventions controller.
  */
class ConventionsController extends Controller
{
     /**
     * Lists all Foiv entities.
     *
     * @Route("/{id}/conventions", name="conventions")
     * @Method("GET")
     * @Template("ncuofoiv/Conventions/index.html.twig")
     */
    public function indexAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($id);

        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }

		$Conventions = $em->getRepository('NCUOFoivBundle:Convention')->findAll();
		if($Conventions != null)
		{
			$this->get('logger')->debug("Conventions is exist");
		}
		else
		{
			$this->get('logger')->debug("Conventions is NOT exist");
		}
		
        return array(
            'foiv'      => $foiv,
			'conventions' => $Conventions,
			'CreateURL' => $this->generateUrl('new_convention', array('id' => $foiv->getId() ) )
			
			
        );
      
    }
	
	 /**
     * Lists all Foiv entities.
     *
     * @Route("/{id}/new_convention", name="new_convention")
     * @Method("GET")
     * @Template("ncuofoiv/Conventions/edit.html.twig")
     */
	public function createAction($id)
	{
		$this->get('session')->getFlashBag()->clear();
		$em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($id);

        if (!$foiv) 
		{
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
		
		

		 return array(
            'foiv'      => $foiv,
			'ActionURL' => $this->generateUrl('save_convention', array('foivid' => $foiv->getId(), 'convid' => 0)),
			'urlBack'   => $this->generateUrl('conventions', array('id' => $foiv->getId()))
        );
	}
	
	/**
     * edit convention
     *
     * @Route("/{foivid}/convention/{convid}/edit", name="edit_convention")
     * @Method("GET")
     * @Template("ncuofoiv/Conventions/edit.html.twig")
     */
	public function editAction($foivid, $convid)
	{
		//$this->get('session')->getFlashBag()->clear();
		$em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);

        if (!$foiv) 
		{
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
		
		$convention = $em->getRepository('NCUOFoivBundle:Convention')->find($convid);

        if (!$convention) 
		{
            throw $this->createNotFoundException('Unable to find convention entity.');
        }
		
		

		 return array(
            'foiv'      => $foiv,
			'entity' => $convention,
			'ActionURL' => $this->generateUrl('save_convention', array('foivid' => $foiv->getId(), 'convid' => $convid)),
			'urlBack'   => $this->generateUrl('conventions', array('id' => $foiv->getId()))
        );
	}
	
	/**
     * saving convention
     *
     * @Route("/{foivid}/save_convention/{convid}", name="save_convention")
     * @Method("POST")
     
     */
	public function saveConvention(Request $request, $foivid, $convid)
	{
	  // $this->get('session')->getFlashBag()->clear();
	   $DateSign = $request->request->get('date_sign');
	   $Title 	 = $request->request->get('title'); 
	   $MaxFileSize = $request->request->get('MAX__FILE_SIZE');
	 	   
	    $em = $this->getDoctrine()->getManager();
		
	    $NewFile = null;
		
		//признак создания новго соглашения 
		//(TRUE-создать новое соглашение /FALSE- редактировать выбранное соглашение)
	    $IsCreateConvention = FALSE;
		if($convid == (int)0)
		{
			$IsCreateConvention = TRUE;
		}
		
		//признак удаления устаревшего файла во время редактирования выбранного соглашения
		//(TRUE - текущий файл НАДО удалить/ FALSE - текущий фай НЕ удалять)
		$NeedDeleteObsoleteFile = false;
	    try
		{
			//перемещаем выгруженный файл в хранилище
			$NewFile = $this->moveFileToStorage('filename', $MaxFileSize, $IsCreateConvention);
			if( $NewFile === NULL || !($NewFile instanceof File))
			{
				//генерируем исключение в том, случае, если создается новое соглашение
				if($IsCreateConvention)
				{
					$this->addFlash(
					'notice',
					"Ошибка: Не удалось загрузить файл"
					); 
					$this->get('logger')->debug("Ошибка: Не удалось загрузить файл");
					return new Response( "Ошибка: Не удалось загрузить файл", Response::HTTP_INTERNAL_SERVER_ERROR );		
				}
			}
			else
			{
				//сохраняем информацию о новом файле в БД
				$NewFile->setTitle($Title);
				$NewFile->setDescription("Файл соглашения");
				
				$em->persist($NewFile);
				$em->flush();
				//устаревший файл необходимо заменпть через удаление онного
				$NeedDeleteObsoleteFile = true;
			}
						
			$Conv = null;
			
			
			if($IsCreateConvention === TRUE)
			{//создаем новую сущность соглашения
			   $Conv = new Convention();
			}
			else
			{ 
				$this->get('logger')->debug("IsCreateConvention === FALSE");
				//пытаемся загрузить существующую сущность соглашения
				$Conv = $em->getRepository('NCUOFoivBundle:Convention')->find($convid);
				if(!$Conv)
				{
					$ErrorMsg = "Ошибка: Не удалось сохранить изменния. Не найдено информация о текущем соглашении";
					$this->get('logger')->debug($ErrorMsg." с идентификатором ".$convid);
					$this->addFlash('notice', $ErrorMsg);
					return new Response($ErrorMsg , Response::HTTP_INTERNAL_SERVER_ERROR );	
				}
			}
			
			$Conv->setTitle($Title);
			$Conv->setDateSigning($DateSign);
			$OldFile = null;
			if(!$IsCreateConvention && $NeedDeleteObsoleteFile)
			{
				//запоминаем информацию о старом файле
				$OldFile = $Conv->getFile();
			}
			
			//если есть новый загруженный файл, 
			//то фиксируем в сущности соглашения
			if($NewFile !== null && $NewFile instanceof File)
			{
				
				//устанавливаем связь соглашения с новым файлом
				$Conv->setFile($NewFile);
			}
			
			
			if($IsCreateConvention)
			{	//для новой сущности
				$em->persist($Conv);
			}
			
            $em->flush();
			$convid = $Conv->getId();
			
			//если это просто замена файла соглашеня во время редактирования,
			// то удаляем устаревший файл, если это необходимо
			if(!$IsCreateConvention && $NeedDeleteObsoleteFile)
			{
				try
				{
					//удаляем файл из хранилища
					$this->removeFileFromStorage($OldFile);
								
					//удаляем запись из БД
					$em->remove($OldFile);
					$em->flush();
				}
				catch(\Exception $ex)
				{
					$this->addFlash('notice', $ex->getMessage()); 
					return new Response( $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR );				
				}
			}
		}
		catch(\Exception $ex)
		{
			//осуществляем откат - удаляем загруженный файл
			$this->removeFileFromStorage($NewFile);
			
			$this->addFlash('notice', $ex->getMessage()); 
			return new Response( $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR );		
		}
		
		if($IsCreateConvention)
		{
			$this->addFlash('notice','Запись успешно создана!' ); 
		}
		else
		{
			$this->addFlash('notice','Запись успешно изменена!' ); 
		}
		
		return new RedirectResponse($this->generateUrl('edit_convention', array('foivid'=> $foivid , 'convid' => $convid)));
	}
	
	
	
	private function removeFileFromStorage($NewFile)
	{
		try
		{
			if($NewFile !== NULL || $NewFile instanceof File)
			{
				$FullPath = $NewFile->getPath().$NewFile->getName();
				unlink($FullPath);
			}
		}
		catch(\Exception $ex)
		{
			throw $ex;
		}
	}
	
	private function moveFileToStorage($FileFieldName, $MaxFileSize, $IsCreateConvention = FALSE)
	{
		    // проверяем, что файл загружался
		    if(isset($_FILES[$FileFieldName]) && $_FILES[$FileFieldName]['error'] != 4)
		    {	
				// проверяем, что файл загрузился без ошибок
				if($_FILES[$FileFieldName]['error'] != 1 && $_FILES['filename']['error'] != 0)
				{
				  $error = $_FILES[$FileFieldName]['error'];
				  $ErroMsg = 'Ошибка: Файл загрузился с ошибками'.' Код ошибки: '.$error;
				  throw new \Exception($ErroMsg);
				}
				else
				{
					  // файл загружен на сервер

					  // проверяем файл на максимальный размер
					  $filesize = $_FILES[$FileFieldName]['size'];
					  
					  if($filesize > $MaxFileSize)
					  {
						
						$ErroMsg = 'Ошибка: Размер загруженного файла '.$filesize.' больше допустимого (50 Мб)';
						throw new \Exception($ErroMsg);
					  }
					  else
					  {
						$NewFile = new File();
						$filename = $_FILES[$FileFieldName]['name'];
						$NewFile->setOriginalName($filename);
						
						$PointPos = strrpos($filename, '.');
						$FileExt = null;
						if($PointPos !== FALSE)
						{
							$FileExt = substr($filename, $PointPos + 1 , strlen($filename) - $PointPos - 1);
							if($FileExt !== FALSE || $FileExt !== NULL)
							{
								$NewFile->setExt($FileExt);
							}
						}
						 //формируем хэшированное имя файла
						$hash_file_name     = md5($filename.date("U")).".".$FileExt;
						$NewFile->setName($hash_file_name);
						
					
						$tempfilepath = $_FILES[$FileFieldName]['tmp_name'];
						
						$filetype = $_FILES[$FileFieldName]['type'];
						if($filetype == null ||	$filetype == '')
						{
							$filetype = 'unknown/unknown';
						}
						$NewFile->setMimeType($filetype);
						
						$filesize = $_FILES[$FileFieldName]['size'];
						$NewFile->setSize($filesize);
					
						
						$TargetDir = $this->container->getParameter('ncuofoiv.file_upload_conventions');
						$NewFile->setPath($TargetDir.'/');
					
						//проверяем директорию на существование и на права
						if (!is_dir($TargetDir) || !is_writable($TargetDir)) 
						{
							throw new \Exception("Ошибка: Отсутсвуют права на запись файла в директорию"); 
						}
												
						$Result = move_uploaded_file($tempfilepath, $TargetDir.'/'.$hash_file_name);
						if($Result == FALSE)
						{
							throw new \Exception("Ошибка: не удалось переместить файл");
						}
						 
					  }
					  
					  
					  return $NewFile;
				}
			}
			else if($IsCreateConvention )
			{
				  $error = $_FILES[$FileFieldName]['error'];
				  $ErroMsg = 'Ошибка: Файл не загружался.'.' Код ошибки: '. $error;
				  throw new \Exception($ErroMsg);
			}
			
		
			return null;
	  
	   
	}  

	 /**
     * Displays a form to edit a existing  entity.
     *
     * @Route("/convention/{id}/delete/", name="delete_convention")
     * @Method("POST")
     */
	public function deleteAction(Request $request, $id)
	{
		try
		{
            $em = $this->getDoctrine()->getManager();
            $conv = $em->getRepository('NCUOFoivBundle:Convention')->find($id);

            if (!$conv)
            {
				$this->get('logger')->debug('Не удалось найти соглашение с идентификатором '.$id);
                throw $this->createNotFoundException('Unable to find convention entity.');
            }
			
			$file = $conv->getFile();
			if(!$file)
			{
				$this->get('logger')->debug('Не удалось найти файл, связанный с соглашеним');
                throw $this->createNotFoundException('Unable to get file entity.');
            }	

			//запоминаем путь к файлу
			$FullPath = $file->getPath().$file->getName();
				
			$em->remove($file);
			$em->flush();
			
			
			unlink($FullPath);
			
        $this->addFlash('notice','Запись успешно удалена!' ); 

        return  new Response(Response::HTTP_OK);
		}
		catch(\Exception $ex)
	    {
		   $this->addFlash('notice','Ошибка удаления записи:'.$ex->getMessage() ); 
		   //теперь отсылаем клиенту
		   return ErrorResponseGenerator::getInternalServerError($ex->getMessage());
	    }
	}
	
	/**
     * Lists all Foiv entities.
     *
     * @Route("/{foivid}/convention/{convid}/view", name="view_convention")
     * @Method("GET")
     * @Template("ncuofoiv/Conventions/view.html.twig")
     */
	public function viewAction($foivid, $convid)
	{
			$em = $this->getDoctrine()->getManager();
			
			$foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
			if (!$foiv)
            {
				$this->get('logger')->debug('Не удалось найти ФОИВ с идентификатором '.$foivid);
                throw $this->createNotFoundException('Unable to find foiv entity.');
				
            }
			
            $conv = $em->getRepository('NCUOFoivBundle:Convention')->find($convid);

            if (!$conv)
            {
				$this->get('logger')->debug('Не удалось найти соглашение с идентификатором '.$convid);
                throw $this->createNotFoundException('Unable to find convention entity.');
				
            }
			
			return array(
            'foiv'      => $foiv,
			'entity' 	=> $conv,
			'urlEdit'   => $this->generateUrl('edit_convention', array('foivid' => $foiv->getId(), 'convid' => $convid)),
			'urlBack'   => $this->generateUrl('conventions', array('id' => $foiv->getId()))
        );
	}
}	

?>