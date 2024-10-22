<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use App\NCUO\EifBundle\Controller\DefaultController;
use App\NCUO\EifBundle\Entity\File;

/**
 * Класс контроллера выгрузки файлов в Капитан
 * ###########################################
 */

class FilesCapiDumpController extends BaseController {
    
    /**
     * @Route("/files__capidump", name = "eif_files__capidump")
     * 
     * Сервис выгрузки файлов в Капитан
     */
    
    public function files__capidump(Request $request) {
        $resp = ['err_id' => NULL, 'err_msg' => NULL, 'content' => NULL];
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Получаем список файлов для выгрузки в Капитан
            $file_list = $em->getRepository('NCUOEifBundle:File')->getFileCapiDumpList();           			           
			
			$files_dir		= $this->container->getParameter('ncuoeif.file_upload_dir');
			$capi_dir_out 	= $this->container->getParameter('ncuoeif.file_capi_dir_out');
			
			$f_cnt = count($file_list);
			
			// Обрабатываем файлы
			foreach($file_list as $file) {
				// Копируем сжатый файл из каталога загрузки в каталог Капитана с именем: ИД Протокола_ИД Файла_Размер файла_BASE64(имя файла).gz
				copy(
					$files_dir . '/' . $file->getIdFile() . '.gz',
					$capi_dir_out . '/' . $file->getProtocol()->getIdProtocol() . '_' . $file->getIdFile() . '_' . $file->getFileDataSizeb() . '_' . base64_encode($file->getFileName()) . '.gz'
				);
				
				// Меняем флаг файла
				$file->setMigrationFlag(1);
				$file->setMigrationDate(new \DateTime('now'));
			}
			$em->flush();
			
            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';            
            $resp['content'] = sprintf('Файлов успешно выгружено в Капитан: %d', $f_cnt);
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