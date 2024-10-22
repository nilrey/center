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

class FileController extends Controller
{
    /**
     * Lists all files entities.
     *
     * @Route("/file/full_list", name="full_file_list")
     * @Method("POST")
     */
    public function getFileList()
    {
          $em = $this->getDoctrine()->getManager();
          $Files = $em->getRepository('NCUOFoivBundle:File')->findAll();
         if(!$Files)
         {
            return ErrorResponseGenerator::getInternalServerError('Не удалось получить список файлов');
         }
  
         $FilesList = array();
         foreach($Files as $file)
         {
               $FilesList[] = $file->getAsArray();
         }
   
         return ErrorResponseGenerator::getSuccessResponseWithData($FilesList);
    }
    
     /**
     * get file by ID 
     *
     * @Route("/file/get_by_id", name="get_file_by_id")
     * @Method("POST")
     */
    public function getFileByID()
    {
      try
      {
         $JSONData = json_decode($_POST["JSON_DATA"],true);
          
         if($JSONData == null)
         {
              return ErrorResponseGenerator::getInternalServerError("Не удалось получить данные в формате JSON");
         }
          
         //получаем идентификатор файла
         $file_id = $JSONData["file_id"];
         if($file_id == null)
         {
            return ErrorResponseGenerator::getInternalServerError("Не удалось получить идентификатор файла");
         }
         
         $em = $this->getDoctrine()->getManager();
         $File = $em->getRepository('NCUOFoivBundle:File')->find($file_id);
         if($File == null)
         {
            return ErrorResponseGenerator::getInternalServerError("Не удалось получить информацию о файле");
         }
  
         //отправляем информацию о файле
         return  ErrorResponseGenerator::getSuccessResponseWithData($File->getAsArray());
      }
      
      catch(\Exception $ex)
      {
            return ErrorResponseGenerator::getInternalServerError("Не удалось получить информацию о файле." + $ex->getMessage());
      }
    }
}
