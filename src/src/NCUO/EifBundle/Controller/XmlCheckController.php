<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;
use App\NCUO\EifBundle\Entity\File;

/**
 * Класс контроллера проверки XML файлов
 * #####################################
 *  
 */

class XmlCheckController extends BaseController {
    
    /**
     * @Route("/xmlcheck", name = "eif_xmlcheck")
     * @Template("ncuoeif/xmlcheck.html.twig")
     * 
     * Страница проверки XML файлов
     */
    
    public function xmlcheck(Request $request) {
        // Формируем контент страницы
        $context = [];
        $context['msg_service_error'] = $this->container->getParameter('ncuoeif.msg.service_error');
        
        // Получаем входные параметры
        try {
            // Foo
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $context['err_msg'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        // Результирующий массив
        return $context;
    }
    
    /**
     * @Route("/xmlcheck__process", name = "eif_xmlcheck__process")
     * 
     * Сервис обработки файлов
     */    
    
    public function xmlcheck__process(Request $request) {
        $resp = ['err_id' => NULL, 'err_msg' => NULL, 'content' => NULL];
                
        // Получаем файлы
        $xml_file = $request->files->get('xml_file');
        if (is_null($xml_file)) {
            $resp['err_id'] = 1;
            $resp['err_msg'] = 'Service error';
            $resp['content'] = 'Не задан файл XML!';
            return $this->createResponse($resp);
        }
        
        $xsd_file = $request->files->get('xsd_file');
        
        $xslt_file = $request->files->get('xslt_file');
        
        // Обработка       
        libxml_use_internal_errors(true);
        
        // Загружаем файл
        $xml = new \DOMDocument();
        if (!$xml->load($xml_file->getRealPath())) {            
            $str_msg = "Ошибка загрузки XML файла!\n";
            foreach(libxml_get_errors() as $err) {
                $str_msg .= "[{$err->line}:{$err->column}] => {$err->message}";
            }            

            libxml_clear_errors();
            libxml_use_internal_errors(false);
            
            $resp['err_id'] = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = $str_msg;
            return $this->createResponse($resp);
        }
        
        // Если задан файл XSD, то проверяем валидность
        if (!is_null($xsd_file) && !$xml->schemaValidate($xsd_file->getRealPath())) {
            $str_msg = "Ошибка валидации XML файла по XSD!\n";
            foreach(libxml_get_errors() as $err) {
                $str_msg .= "[{$err->line}:{$err->column}] => {$err->message}";
            }            

            libxml_clear_errors();
            libxml_use_internal_errors(false);
            
            $resp['err_id'] = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = $str_msg;
            return $this->createResponse($resp);            
        }
        
        // Если задан файл XSLT, то выводим результат преобразования, иначе сам файл
        if (!is_null($xslt_file)) {
            // Загружаем XSLT документ
            $xslt = new \DOMDocument();
            if (!$xslt->load($xslt_file->getRealPath())) {            
                $str_msg = "Ошибка загрузки XSLT файла!\n";
                foreach(libxml_get_errors() as $err) {
                    $str_msg .= "[{$err->line}:{$err->column}] => {$err->message}";
                }            
    
                libxml_clear_errors();
                libxml_use_internal_errors(false);
                
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = $str_msg;
                return $this->createResponse($resp);
            }
            
            // Инициализируем XSLT процессор
            $xslt_proc = new \XSLTProcessor();
            if (!$xslt_proc->importStylesheet($xslt)) {
                $str_msg = "Ошибка инициализации XSLT преобразования!\n";
                foreach(libxml_get_errors() as $err) {
                    $str_msg .= "[{$err->line}:{$err->column}] => {$err->message}";
                }            
    
                libxml_clear_errors();
                libxml_use_internal_errors(false);
                
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = $str_msg;
                return $this->createResponse($resp);                
            }
            
            // Выполняем преобразование
            $xslt_content = "";
            if (false === ($xslt_content = $xslt_proc->transformToXml($xml))) {
                $str_msg = "Ошибка выполнения XSLT преобразования!\n";
                foreach(libxml_get_errors() as $err) {
                    $str_msg .= "[{$err->line}:{$err->column}] => {$err->message}";
                }            
    
                libxml_clear_errors();
                libxml_use_internal_errors(false);
                
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = $str_msg;
                return $this->createResponse($resp); 
            }
                        
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = $xslt_content;            
        } else {
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = $xml->saveXML();
        }                  
            
        libxml_clear_errors();
        libxml_use_internal_errors(false);            

        return $this->createResponse($resp);
    }
}