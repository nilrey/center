<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use App\NCUO\EifBundle\Controller\DefaultController;
use App\NCUO\EifBundle\Entity\File;

/**
 * Класс контроллера загрузки файлов из Капитана
 * #############################################
 */

class FilesCapiLoadController extends BaseController {
    
    /**
     * @Route("/files__capiload", name = "eif_files__capiload")
     * 
     * Сервис загрузки файлов из Капитана
     */
    
    public function files__capiload(Request $request) {
        $resp = ['err_id' => NULL, 'err_msg' => NULL, 'content' => NULL];
        
        try {
            $em = $this->getDoctrine()->getManager();

			$files_dir		= $this->container->getParameter('ncuoeif.file_upload_dir');
			$capi_dir_in 	= $this->container->getParameter('ncuoeif.file_capi_dir_in');
			
			$prot_rep		= $em->getRepository('NCUOEifBundle:Protocol');
			$prot_file		= $em->getRepository('NCUOEifBundle:File');
			$status_q		= $em->getRepository('NCUOEifBundle:FileStatus')->findOneBy(array('status_name' => 'В очереди на импорт'));

			// Ищем файлы в каталоге Капитана
			$files_list = scandir($capi_dir_in);
			$load_cnt	= 0;
			
			foreach($files_list as $file_item) {
				// Пропускаем текущий каталог и родительский
				if ($file_item == '.' || $file_item == '..')
					continue;
				
				// Проверяем, если последнее время изменения блоков файла менее, чем 5 секунд назад,
				// то возможно файл еще записывается. Пропускаем до следующей иттерации.
				if (time() - filemtime($file_item) < 5)
					continue;
				
				// Получаем параметры файла
				$file_params = explode('_', $file_item);
				
				$id_protocol = $this->checkUuid($file_params[0]);
				$id_file	 = $this->checkUuid($file_params[1]);
				$file_sizeb  = $file_params[2];
				$file_name	 = base64_decode(substr($file_params[3], 0, -3));
							
				// Сохраняем файл
				if ($protocol = $prot_rep->find($id_protocol)) {
					if (!$prot_file->find($id_file) && ($id_file != '00000000-0000-0000-0000-000000000000')) {
						// Переименовываем файл и помещаем в каталог загрузки
						rename($capi_dir_in . '/' . $file_item, $files_dir . '/' . $id_file . '.gz');					
					
						// БД
						$file = new File();
						$em->getClassMetaData(get_class($file))->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
						$file->setIdFile($id_file);
						$file->setFileName($file_name);
						$file->setFileDataSizeb($file_sizeb);
						$file->setFileUploadDate(new \DateTime('now'));
						$file->setFileStatus($status_q);
						$file->setMigrationFlag(0);
						$file->setMigrationDate(null);
						$file->setProtocol($protocol);
							
						$em->persist($file);
						$em->flush();		

						++$load_cnt;
					}
				}
			}
			
            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';            
            $resp['content'] = sprintf('Файлов успешно загружено из Капитана: %d', $load_cnt);
        } catch (\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        return $this->createResponse($resp);        
    }
}