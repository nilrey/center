<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;
use App\NCUO\EifBundle\Entity\File;

/**
 * Класс контроллера обработки файлов
 * ##################################
 * 
 */

class FilesController extends BaseController {
    
    /**
     * @Route("/files", name = "eif_files")
     * @Template("ncuoeif/files.html.twig")
     * 
     * Страница списка файлов протокола
     */
    
    public function files(Request $request) {
        // Формируем контент страницы
        $context = array();
        $context['msg_service_error'] = $this->container->getParameter('ncuoeif.msg.service_error');
        
		$id_protocol = $this->checkUuid($request->query->get('id_protocol'));
		
        // Получаем входные параметры
        try {
            $em   = $this->getDoctrine()->getManager();
            $user = $this->getUser();
			
			if ($this->isGranted('ROLE_FOIV')) {
				// Ищем протокол по идентификатору и пользователю ФОИВ/РОИВ
				if ($user->getFoiv())
					$protocol = $em->getRepository('NCUOEifBundle:Protocol')->findByUser($id_protocol, 'FOIV', $user->getFoiv());
				else
					$protocol = $em->getRepository('NCUOEifBundle:Protocol')->findByUser($id_protocol, 'ROIV', $user->getRoiv());				
			} else {
				$protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($id_protocol);
			}
			
            if (!$protocol) {
                $context['err_msg'] = 'Протокол не найден!';
                return $context;
            }            

            $context['source']          = $protocol->getSource();
            $context['protocol']        = $protocol;
            $context['file_statuses']   = $em->getRepository('NCUOEifBundle:FileStatus')->getList();
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $context['err_msg'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        // Результирующий массив
        return $context;
    }
    
    /**
     * @Route("/files__get_list", name = "eif_files__get_list")
     * 
     * Сервис получения списка файлов протокола
     */
    
    public function files__get_list(Request $request) {
        // Получаем входные параметры
        $row_start      = $request->request->get('start');
        $res_length     = $request->request->get('length');
        $search_str     = $request->request->get('search')['value'];
        
        $resp = array('draw' => $request->request->get('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array());
        
		$id_protocol = $this->checkUuid($request->request->get('id_protocol'));
		
        // Запрашиваем список в БД
        try {
			$em		= $this->getDoctrine();
			$user	= $this->getUser();
			
			if ($this->isGranted('ROLE_FOIV')) {
				// Ищем протокол по идентификатору и пользователю ФОИВ/РОИВ
				if ($user->getFoiv())
					$protocol = $em->getRepository('NCUOEifBundle:Protocol')->findByUser($id_protocol, 'FOIV', $user->getFoiv());
				else
					$protocol = $em->getRepository('NCUOEifBundle:Protocol')->findByUser($id_protocol, 'ROIV', $user->getRoiv());				
			} else {
				$protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($id_protocol);
			}			
			
            if (!$protocol) {
                $resp['error'] = 'Протокол не найден!';
                return $this->createResponse($resp);
            }
            
            $res = $this->getDoctrine()->getRepository('NCUOEifBundle:File')->getDataByBlocks($protocol, $row_start, $res_length, $search_str);

            // Формируем результат
            $resp['recordsTotal'] = $res['total_cnt'];
            $resp['recordsFiltered'] = $res['total_cnt'];

            foreach($res['data'] as $d) {
				$file_status  = $d->getFileStatus()->getStatusName();
				$opt_download = '<a href="' . $this->generateUrl('eif_files__download') . '?id_file=' . $d->getIdFile() .'"><i class="fa fa-download icon-link " title="Загрузить файл"></i></a>';
				$opt_delete	  = '<i class="fa fa-trash-o icon-link file_delete" title="Удаление файла"></i>';
				
				$err = str_replace('"', "'", $d->getStatusMsg());
				$opt_err_view = "<i class=\"fa fa-exclamation-circle icon-link file_view_error\" data-descr=\"{$err}\" title=\"Просмотреть описание ошибки\"></i>";
				
                // Определяем статус файла и опции
				$file_opts = $opt_download;
				if ($this->isGranted('ROLE_ADMIN')) {
					if ($migr_flag = $d->getMigrationFlag() == 1) {
						$file_opts .= '<i class="fa fa-flag icon-link file_clear_migration_flag" title="Сбросить флаг выгрузки в Капитан"></i>';
					}				
					
					$file_opts .= '<i class="fa fa-chain-broken icon-link file_set_status" title="Установить статус файла"></i>';					
					if ($file_status == 'Загружен') {
						$file_opts .= $opt_delete;
					} else if ($file_status == 'Ошибка') {
						$file_opts .= $opt_delete . $opt_err_view;
					}
				} else if ($this->isGranted('ROLE_FOIV')) {
					if ($file_status == 'Загружен') {
						$file_opts .= $opt_delete;
					} else if ($file_status == 'Ошибка') {
						$file_opts .= $opt_delete . $opt_err_view;
					}					
				} else {
					if ($file_status == 'Ошибка') {
						$file_opts .= $opt_err_view;
					}					
				}
                
                array_push(
                    $resp['data'],
                    array(
                        $d->getIdFile(),                        
                        $d->getFileName(),
                        number_format($d->getFileDataSizeb() / 1024, 2),
                        $d->getFileUploadDate()->format('d.m.Y H:i:s'),
						$migr_flag ? $d->getMigrationDate()->format('d.m.Y H:i:s') : 'Нет',
                        $file_status,
                        $file_opts
                    )
                );
            }
        } catch (\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['error'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);
        }
        
        return $this->createResponse($resp);
    }
    
    /**
     * @Route("/files__upload", name = "eif_files__upload") 
     * 
     * Сервис загрузки файлов
     */
    
    public function files__upload(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Получаем входные параметры        
        $id_protocol = $this->checkUuid($request->request->get('id_protocol'));
        
        // Сохраняем файл на диск, метаданные в БД
        try {  
            $em 	= $this->getDoctrine()->getManager();
            $user   = $this->getUser();
			
			if ($this->isGranted('ROLE_FOIV')) {
				// Ищем протокол по идентификатору и пользователю ФОИВ/РОИВ
				if ($user->getFoiv())
					$protocol = $em->getRepository('NCUOEifBundle:Protocol')->findByUser($id_protocol, 'FOIV', $user->getFoiv());
				else
					$protocol = $em->getRepository('NCUOEifBundle:Protocol')->findByUser($id_protocol, 'ROIV', $user->getRoiv());				
			} else {
				$protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($id_protocol);
			}			
			
            if (!$protocol) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Протокол не найден!';
                
                return $this->createResponse($resp);
            }
            
            // UploadedFile object
            $upl_file = $request->files->get('file');        
            if (is_null($upl_file)) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Не найден загруженный файл!';
                
                return $this->createResponse($resp);                
            }    
            
            $upl_file_name = $upl_file->getClientOriginalName();
            if ($upl_file->getMimeType() != $protocol->getProtocolFileMimeType()) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = "Формат файла [{$upl_file_name}] не соответствует формату файлов протокола!";
                
                return $this->createResponse($resp);                                 
            }    
            
            // Создаем файл протокола
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

            // Перемещаем файл из временной директории в хранилище с архивированием
            $gz = gzopen($this->container->getParameter('ncuoeif.file_upload_dir') . '/' . $file->getIdFile() . '.gz', 'w9');
            gzwrite($gz, file_get_contents($upl_file->getRealPath()));
            gzclose($gz);
            unlink($upl_file->getRealPath());

            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Файл успешно добавлен!';
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
     * @Route("/files__delete", name = "eif_files__delete") 
     * 
     * Сервис удаления файла
     */
    
    public function files__delete(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            $em   = $this->getDoctrine()->getManager();
            $user = $this->getUser();
			
			$id_file = $this->checkUuid($request->request->get('id_file'));
			
			if ($this->isGranted('ROLE_FOIV')) {
				// Ищем файл по идентификатору и пользователю ФОИВ/РОИВ
				if ($user->getFoiv())
					$file = $em->getRepository('NCUOEifBundle:File')->findByUser($id_file, 'FOIV', $user->getFoiv());
				else
					$file = $em->getRepository('NCUOEifBundle:File')->findByUser($id_file, 'ROIV', $user->getRoiv());  				
			} else {
				$file = $em->getRepository('NCUOEifBundle:File')->find($id_file);
			}
			
            if (!$file) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Файл не найден!';
                
                return $this->createResponse($resp);
            }

            // Проверка на статус файла при удалении
            if (in_array($file->getFileStatus()->getStatusName(), array('Импортирование', 'Импортирован в формы данных'))) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Файл с данным статусом не может быть удален!';
                
                return $this->createResponse($resp);                
            }
            
            // Удаляем файл с диска
            $file_path = $this->container->getParameter('ncuoeif.file_upload_dir') . '/' . $file->getIdFile() . '.gz';
            unlink($file_path);

            // Удаляем метаданные из БД
            $em->remove($file);
            $em->flush();                

            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Файл успешно удален!';                
        } catch (\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);                        
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            
            if (strpos($ex->getMessage(), 'Foreign key violation') !== false)
                $resp['content'] = 'Невозможно удалить файл, т.к. по нему есть загруженные данные в формы! Сначала необходимо удалить данные форм!';
            else    
                $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        return $this->createResponse($resp);
    }
    
    /**
     * @Route("/files__get_status", name = "eif_files__get_status") 
     *
     * Сервис получения статуса файла
     */
    
    public function files__get_status(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            $em = $this->getDoctrine()->getManager();
                        
            if (!($file = $em->getRepository('NCUOEifBundle:File')->find($this->checkUuid($request->query->get('id_file'))))) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Файл не найден!';
                
                return $this->createResponse($resp);
            }                
            
            if (!($status = $file->getFileStatus())) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Статус не найден!';
                
                return $this->createResponse($resp);                                    
            }
            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = array('file_id_status' => $status->getIdStatus());
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);                        
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';                        
            $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);             
        }
        
        return $this->createResponse($resp);        
    }
    
    /**
     * @Route("/files__set_status", name = "eif_files__set_status") 
     * 
     * Сервис установки статуса файла
     */
    
    public function files__set_status(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        $id_status = $this->checkUuid($request->request->get('id_status'));
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            if ($id_status == -1) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Необходимо задать новый статус для файла!';
                
                return $this->createResponse($resp);                
            }
            
            if (!($status = $em->getRepository('NCUOEifBundle:FileStatus')->find($id_status))) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Статус не найден!';
                
                return $this->createResponse($resp);
            }             
            
            if (!($file = $em->getRepository('NCUOEifBundle:File')->find($this->checkUuid($request->request->get('id_file'))))) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Файл не найден!';
                
                return $this->createResponse($resp);
            }                
            
            $file->setFileStatus($status);
            $file->setStatusMsg(NULL);
            
            $em->flush();
            
            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Статус успешно изменен!';
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);                        
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';                        
            $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);             
        }
        
        return $this->createResponse($resp);
    }
	
	/**
     * @Route("/files__clear_migration_flag", name = "eif_files__clear_migration_flag") 
     * 
     * Сервис сброса флага миграции
     */
    
    public function files__clear_migration_flag(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
               
        try {
            $em = $this->getDoctrine()->getManager();                       
            
            if (!($file = $em->getRepository('NCUOEifBundle:File')->find($this->checkUuid($request->request->get('id_file'))))) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Файл не найден!';
                
                return $this->createResponse($resp);
            }                
            
            $file->setMigrationFlag(0);
			$file->setMigrationDate(null);
            
            $em->flush();
            
            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Флаг выгрузки в Капитан успешно сброшен!';
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);                        
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';                        
            $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);             
        }
        
        return $this->createResponse($resp);
    }	
    
    /**
     * @Route("/files__download", name = "eif_files__download")
     * 
     * Сервис скачивания файла
     */
    
    public function files__download(Request $request) {
		// Ответ на случай ошибки
        $resp = new Response('Ошибка загрузки файла!');
		$resp->headers->set('Content-Type', 'text/plain');
		$resp->headers->set('Content-Transfer-Encoding', 'binary');
		$resp->headers->set('Pragma', 'no-cache');	
		$resp->headers->set('Content-Disposition', 'attachment; filename="Ошибка.txt"');
        
		$id_file = $this->checkUuid($request->query->get('id_file'));
		
        try {
            $em 	= $this->getDoctrine()->getManager();                    
            $user	= $this->getUser();
			
			if ($this->isGranted('ROLE_FOIV')) {
				// Ищем файл по идентификатору и пользователю ФОИВ/РОИВ
				if ($user->getFoiv())
					$file = $em->getRepository('NCUOEifBundle:File')->findByUser($id_file, 'FOIV', $user->getFoiv());
				else
					$file = $em->getRepository('NCUOEifBundle:File')->findByUser($id_file, 'ROIV', $user->getRoiv());  				
			} else {
				$file = $em->getRepository('NCUOEifBundle:File')->find($id_file);
			}			
			
            if ($file) {
                $resp = new Response(implode(gzfile($this->container->getParameter('ncuoeif.file_upload_dir') . '/' . $file->getIdFile() . '.gz')));
                $resp->headers->set('Content-Type', $file->getProtocol()->getProtocolFileMimeType());
                $resp->headers->set('Content-Transfer-Encoding', 'binary');
                $resp->headers->set('Pragma', 'no-cache');
                $resp->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', addslashes($file->getFileName())));
            }                            
        } catch(\Exception $ex) {
            // Логируем ошибку
            $this->log_e($ex);                        
        }
        
        return $resp;
    }    
}