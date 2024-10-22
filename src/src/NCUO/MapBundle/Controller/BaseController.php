<?php

namespace App\NCUO\MapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Базовый класс контроллера, содержащий в себе общие переменные и методы, используемые каждым конкретным контроллером
 * ###################################################################################################################
 */

class BaseController extends Controller {
                
    /**
     * Функция генерация ответа на запрос
     * ##################################
     * 
     * Вход:
     *  &resp   - array     - ссылка на массив с данными
     *  type    - string    - тип формата вывода (json/xml)
     *  tmpl    - string    - файл шаблона для XML
     *       
     * Выход:
     *  JSON/XML ответ
     * 
     */
    
    protected function createResponse(&$resp, $type = '', $tmpl = '') {                
        $content = NULL;
        
        if ($type == 'xml' && $tmpl == '')
            $content = $resp;
        else if ($type == 'xml' && $tmpl != '')
            $content = $this->renderView($tmpl, $resp);
        else
            $content = json_encode($resp, JSON_UNESCAPED_UNICODE);
        
        $r = new Response($content);
        $r->headers->set('Content-Type', ($type == 'xml') ? 'application/xml; charset=UTF-8' : 'application/json; charset=UTF-8');

        return $r;         
    }                
    
    /**
     * Функция логирования
     * ###################
     * 
     * Вход:
     *  msg     - string    - сообщение для лога 
     *
     * Выход:
     *  log_id  - string    - идентификатор записи в логе
     * 
     */
    
    protected function log_s($msg) {
        $log_id = $this->getLogId();
        
        // LOG_ID - идентификатор записи лога
        // LOG_THREAD_ID - идентификатор потока, записывающего в лог      
        
        $this->get('logger')->debug(sprintf('[LOG_ID = %s] >>> %s', $log_id, $msg));
        
        return $log_id;
    }    
    
    /**
     * Функция логирования
     * ###################
     * 
     * Вход:
     *  ex     - Exception    - исключение для лога 
     *
     * Выход:
     *  err_log_id  - string    - идентификатор ошибки в логе
     * 
     */    
    
    protected function log_e($ex) {
        return $this->log_s('[Exception class: ' . get_class($ex) . '] ' . $ex->getMessage() . ' ' . $ex->getTraceAsString());
    }
        
    /**
     * Функция генерирования номера записи в логе
     * ##########################################
     * 
     * Выход:
     *  Номер записи - string
     */
    
    protected function getLogId() {
        return date('Ymd:His');
    }
}
