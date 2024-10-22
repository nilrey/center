<?php

namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/*
 *Класс, генерирующий ответы об ошибках от сервера клиенту с набором параметров (в формате JSON)
*/
class ErrorResponseGenerator
{
    /*
     *Генерация ответа об ошибке типа "Необходимо заполнить поле"
     *
     *ControlCaption - название заголовка того поля, в котором необходимо ввести данные
     *ControlID - идентификатор контрола
     *
     *Возврат: ответный объект с JSON-данными
     */
    public  static function getRequiredFieldError($ControlCaption, $ControlID)
    {
                $Response =  new JsonResponse();
				$Response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                $Response->setData(array("code" => Response::HTTP_INTERNAL_SERVER_ERROR,
                                                                "error_type"=>"field mandatory",
                                                                "message" => "Необходимо заполнить поле '". $ControlCaption."'",
                                                                "control_id" => $ControlID 
                                                                ));
                return $Response;
    }
	
	/*
     *Генерация ответа об ошибке типа "Данные дублируются"
     *
     *Возврат: ответный объект с JSON-данными
     */
	public  static function getDuplicateDataError()
    {
                $Response =  new JsonResponse();
				$Response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                $Response->setData(array("code" => Response::HTTP_INTERNAL_SERVER_ERROR,
                                                                "error_type"=>"duplicate data",
                                                                "message" => "Дублируются данные"
                                                                ));
                return $Response;
    }
    
     /*
     *Генерация ответа об успешной операции
     *
     *URL - URL-ссылка для редиректа(если это необходимо)
     *
     *Возврат: ответный объект с JSON-данными
     */
    public static function getSuccessResponse($URL = null)
    {
        $Response =  new JsonResponse();
		$Response->setStatusCode(Response::HTTP_OK);
		
        if($URL != null)
        {
            $Response->setData(array("code" => Response::HTTP_OK, "url"=>$URL));
        }
        else
        {
            $Response->setData(array("code" => Response::HTTP_OK));
        }
        return $Response;
    }
    
	 /*
     *Генерация ответа об успешной операции с набором данных
     *
     *Data - набор данных для передачи клиенту(если это необходимо)
     *
     *Возврат: ответный объект с JSON-данными
     */
    public static function getSuccessResponseWithData($Data = null)
    {
        $Response =  new JsonResponse();
        if($Data != null)
        {
            $Response->setData(array("code" => Response::HTTP_OK, "data"=>$Data));
        }
        else
        {
            $Response->setData(array("code" => Response::HTTP_OK));
        }
        return $Response;
    }
	
	 /*
     *Генерация ответа об успешной операции с набором данных и с URL-ссылкой для редиректа
     *
	 *URL - URL-ссылка для редиректа
     *Data - набор данных для передачи клиенту(если это необходимо)
     *
     *Возврат: ответный объект с JSON-данными
     */
	public static function getSuccessResponseWithURLData($URL, $Data)
    {
        $Response =  new JsonResponse();
		
        if($Data != null)
        {
            $Response->setData(array("code" => Response::HTTP_OK, "url"=>$URL, "data"=>$Data));
        }
        else
        {
            $Response->setData(array("code" => Response::HTTP_OK));
        }
        return $Response;
    }
    
     /*
     *Генерация ответа об внутренней ошибке сервера
     *
     *AdditionalMessage - дополнительное сообщение об ошибке (если имеется)
     *
     *Возврат: ответный объект с JSON-данными
     */
    public static function getInternalServerError($AdditionalMessage = null)
    {
             $Response =  new JsonResponse();
			 $Response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			 
             if($AdditionalMessage != null)
             {
                $Response->setData(array("code" => Response::HTTP_INTERNAL_SERVER_ERROR, "message"=>$AdditionalMessage));
             }
             else
             {
                $Response->setData(array("code" => Response::HTTP_INTERNAL_SERVER_ERROR));
             }
             return $Response;
    }
}
?>