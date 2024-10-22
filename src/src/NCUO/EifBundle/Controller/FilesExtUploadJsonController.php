<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use App\NCUO\EifBundle\Controller\DefaultController;
use App\NCUO\EifBundle\Entity\File;

/**
 * Класс контроллера загрузки файлов извне
 * #######################################
 */

class FilesExtUploadJsonController extends BaseController {
        
    /**
     * @Route("/files__ext_upload_json", name = "eif_files__ext_upload_json")
     * @Method("POST")
     * 
     * Сервис внешней загрузки файлов в формате JSON
     */
    
    public function files__ext_upload_json(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);        
        
        try {
            $em = $this->getDoctrine()->getManager();
            
             $this->log_s('... + files__ext_upload_json : id_source = [' . $request->request->get('id_source') . '], id_protocol = [' . $request->request->get('id_protocol') . ']');
            // Получаем источник
            $id_source = $this->checkUuid($request->request->get('id_source'));
            if (is_null($id_source) || ($id_source == '') || !($source = $em->getRepository('NCUOEifBundle:Source')->find($id_source))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задан идентификатор источника!';
                return $this->createResponse($resp);
            }

            // Получаем протокол
            $id_protocol = $this->checkUuid($request->request->get('id_protocol'));
            if (is_null($id_protocol) || ($id_protocol == '') || !($protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($id_protocol))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задан идентификатор протокола!';
                return $this->createResponse($resp);
            }
            
            // Проверяем, что протокол принадлежит источнику
            if ($protocol->getSource()->getIdSource() != $source->getIdSource()) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Ошибка соответствия протокола и источника!';
                return $this->createResponse($resp);                
            }
            
            // Получаем имя файла
            $file_name = trim($request->request->get('file_name'));
            if (is_null($file_name) || ($file_name == '')) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задано имя файла!';
                return $this->createResponse($resp);
            }            

            // Получаем поле JSON строки   
            $json_data_str = trim($request->request->get('json_data'));
            if (is_null($json_data_str) || ($json_data_str == '')) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задана JSON строка - пустая!';
                return $this->createResponse($resp);
            }
                       
            // Конвертируем JSON в XML            
            $json_data = json_decode($json_data_str, true);
            if (is_null($json_data)) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задана JSON строка!';
                $this->log_s('Неверно задана JSON строка!\n' . '<' . $json_data_str . '>');
                //$resp['content'] = 'Неверно задана JSON строка!\n' . '<' . (is_null($json_data_str) ? 'null!!!' : $json_data_str) . '>';
                //$resp['content'] = 'Неверно задана JSON строка! qqq';
                return $this->createResponse($resp);                
            }
            
            $xml_data = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data></data>');
            $this->array_to_xml($json_data, $xml_data);
            
            // Сохраняем полученный файл   
            $file = new File();
            $file->setFileName($file_name);
            $file->setFileDataSizeb(strlen($json_data_str));
            $file->setFileUploadDate(new \DateTime('now'));
            $file->setFileStatus($em->getRepository('NCUOEifBundle:FileStatus')->findOneBy(array('status_name' => 'В очереди на импорт')));
            $file->setMigrationFlag(0);
            $file->setMigrationDate(null);
            $file->setProtocol($protocol);
                
            $em->persist($file);
            $em->flush();
            
            // Перемещаем файл из временной директории в хранилище с архивированием
            $gz = gzopen($this->container->getParameter('ncuoeif.file_upload_dir') . '/' . $file->getIdFile() . '.gz', 'w9');
            gzwrite($gz, $xml_data->asXML());
            gzclose($gz);

            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Файл успешно загружен!';
        } catch (\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        return $this->createResponse($resp);
    }       
    
    /**
     * Функция конвертации массива в XML
     */    
    
    private function array_to_xml($data, &$xml_data) {
        foreach($data as $key => $value ){
            if (is_numeric($key)) {
                $key = 'item'; // Чтобы не было узлов <0>..</0>, <1>...</1>
            }            
                
            if (is_array($value)) {                
                $subnode = $xml_data->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild($key, $value);
            }
        }
    }        
    
}