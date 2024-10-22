<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use App\NCUO\EifBundle\Controller\DefaultController;
use App\NCUO\EifBundle\Entity\File;

// require("file_transfer_pack.php");

/**
 * Класс контроллера загрузки файлов извне
 * #######################################
 */

class FilesExtUploadController extends BaseController {
    
    /**
     * @Route("/files__ext_upload", name = "eif_files__ext_upload")
     * @Method("POST")
     * 
     * Сервис внешней загрузки файлов
     */
    
    public function files__ext_upload(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);        
        
        try {
            $em = $this->getDoctrine()->getManager();

            $this->log_s('... + files__ext_upload : id_source = [' . $request->request->get('id_source') . '], id_protocol = [' . $request->request->get('id_protocol') . ']');                        
            // Получаем источник
            $id_source = $this->checkUuid($request->request->get('id_source'));
            if (is_null($id_source) || ($id_source == '') || !($source = $em->getRepository('NCUOEifBundle:Source')->find($id_source))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задан идентификатор источника!';
                $this->log_s('... - files__ext_upload: bad id_source: [' . $id_source . ']' );
                return $this->createResponse($resp);
            }

            // Получаем протокол
            $id_protocol = $this->checkUuid($request->request->get('id_protocol'));
            if (is_null($id_protocol) || ($id_protocol == '') || !($protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($id_protocol))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задан идентификатор протокола!';
                $this->log_s('... - files__ext_upload: bad id_protocol: [' . $id_protocol . ']' );				
                return $this->createResponse($resp);
            }
            
            // Проверяем, что протокол принадлежит источнику
            if ($protocol->getSource()->getIdSource() != $source->getIdSource()) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Ошибка соответствия протокола и источника!';
                $this->log_s('... - files__ext_upload: Ошибка соответствия протокола и источника!' );				
                return $this->createResponse($resp);                
            }

            // Проверяем полученный файл   
            $upl_file = $request->files->get('file');        // UploadedFile object
            if (is_null($upl_file)) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задан файл!';
                $this->log_s('... - files__ext_upload: Неверно задан файл!' );				
                return $this->createResponse($resp);                 
            }
            
            $upl_file_name = $upl_file->getClientOriginalName();
            if ($upl_file->getMimeType() != $protocol->getProtocolFileMimeType()) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = "Формат файла [{$upl_file_name}] не соответствует формату файлов протокола!";
                $this->log_s("... - files__ext_upload: Формат файла [{$upl_file_name}] не соответствует формату файлов протокола!" );				
                return $this->createResponse($resp);                                 
            }
            $this->log_s('... . files__ext_upload : upl_file_name = [' . $upl_file_name . '], size = [' . $upl_file->getClientSize() . ']');                        
            
            // Сохраняем полученный файл   
            $file = new File();
            $file->setFileName($upl_file_name);
            $file->setFileDataSizeb($upl_file->getClientSize());
            $file->setFileUploadDate(new \DateTime('now'));
            $file->setFileStatus($em->getRepository('NCUOEifBundle:FileStatus')->findOneBy(array('status_name' => 'В очереди на импорт')));
            $file->setMigrationFlag(0);
            $file->setMigrationDate(null);
            $file->setProtocol($protocol);
                
            $em->persist($file);
            $em->flush();

            $PROTOCOL_ID_GIS_RZD = '2bc2a8a4-6e93-45dd-9a3e-c796bae8ad13';
            $gisFtpDir = '/home/ftp_gis/gis_rzd';

            // Файлы от ГИС РДЖ выгружаем в ГИС вне очереди, сразу после загрузки            
            if(($id_protocol == $PROTOCOL_ID_GIS_RZD) 
                && (( $upl_file_name == 'curPos.xml') || ( $upl_file_name == 'Emergency.xml')) )
            {
                $this->log_s('ГИС РДЖ: файл ' . $upl_file_name );
                $tmpCopyFileName = $upl_file->getRealPath() . '.tmp';
                $gisUnloadFileName = $gisFtpDir . '/' . $upl_file_name;
                copy($upl_file->getRealPath(), $tmpCopyFileName); 
                rename($tmpCopyFileName, $gisUnloadFileName);
                $this->log_s('ГИС РДЖ: ' . $upl_file_name . ' выгружен в ' . $gisUnloadFileName);
            }

            
            // Перемещаем файл из временной директории в хранилище с архивированием
            $gzFileName = $this->container->getParameter('ncuoeif.file_upload_dir') . '/' . $file->getIdFile() . '.gz'; 
            $gz = gzopen($gzFileName, 'w9');
            gzwrite($gz, file_get_contents($upl_file->getRealPath()));
            gzclose($gz);
            unlink($upl_file->getRealPath());

            // Упаковываем копию файла для передачи в закрытый контур
            //$transferDir = '/home/transfer';
            //file_transfer_pack($file, $transferDir, $gzFileName);
            //$this->log_s('файл ' . $gzFileName . ' передан в ' . $transferDir);

            
            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Файл успешно загружен!';
        } catch (\Exception $ex) {
            // Логируем ошибку
            $this->log_s('... - files__ext_upload: Error detected');
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        return $this->createResponse($resp);
    }       
    
}