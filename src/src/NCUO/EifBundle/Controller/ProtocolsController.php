<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;
use App\NCUO\EifBundle\Entity\Protocol;

/**
 * Класс контроллера обработки протоколов информационно-технического сопряжения
 * ############################################################################
 * 
 */

class ProtocolsController extends BaseController {
 
    /**
     * @Route("/protocols", name = "eif_protocols")
     * @Template("ncuoeif/protocols.html.twig")
     * 
     * Страница списка протоколов источника
     */
    
    public function protocols(Request $request) {
        // Формируем контент страницы
        $context = array();
        $context['msg_service_error'] = $this->container->getParameter('ncuoeif.msg.service_error');
        
		$id_source = $this->checkUuid($request->query->get('id_source'));
		
        // Получаем входные параметры
        try {
			$em   = $this->getDoctrine();
			$user = $this->getUser();
									
			if ($this->isGranted('ROLE_FOIV')) {
				// Ищем источник по идентификатору и пользователю ФОИВ/РОИВ
				if ($user->getFoiv())
					$source = $em->getRepository('NCUOEifBundle:Source')->findOneBy(array('id_source' => $id_source, 'foiv' => $user->getFoiv()));
				else
					$source = $em->getRepository('NCUOEifBundle:Source')->findOneBy(array('id_source' => $id_source, 'roiv' => $user->getRoiv()));				
			} else {
				$source = $em->getRepository('NCUOEifBundle:Source')->find($id_source);
			}
			
            if (!$source)
                $context['err_msg'] = 'Источник не найден!';
            else
                $context['source'] = $source;
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $context['err_msg'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        // Результирующий массив
        return $context;
    }
    
    /**
     * @Route("/protocols__get_list", name = "eif_protocols__get_list")
     * 
     * Сервис получения списка протоколов источника
     */
    
    public function protocols__get_list(Request $request) {
        // Получаем входные параметры
        $row_start      = $request->request->get('start');
        $res_length     = $request->request->get('length');
        $search_str     = $request->request->get('search')['value'];
        
        $resp = array('draw' => $request->request->get('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array());
        
		$id_source = $this->checkUuid($request->request->get('id_source'));
		
        // Запрашиваем список в БД
        try {  
            $em   = $this->getDoctrine()->getManager();
            $user = $this->getUser();
						
			if ($this->isGranted('ROLE_FOIV')) {
				// Ищем источник по идентификатору и пользователю ФОИВ/РОИВ
				if ($user->getFoiv())
					$source = $em->getRepository('NCUOEifBundle:Source')->findOneBy(array('id_source' => $id_source, 'foiv' => $user->getFoiv()));
				else
					$source = $em->getRepository('NCUOEifBundle:Source')->findOneBy(array('id_source' => $id_source, 'roiv' => $user->getRoiv()));				
			} else {
				$source = $em->getRepository('NCUOEifBundle:Source')->find($id_source);
			}
			
            if (!$source) {
                $resp['error'] = 'Источник не найден!';
                return $this->createResponse($resp);
            }
            
            // Получаем список протоколов по источнику
            $res = $em->getRepository('NCUOEifBundle:Protocol')->getDataByBlocks($source, $row_start, $res_length, $search_str);

            // Формируем результат
            $resp['recordsTotal'] = $res['total_cnt'];
            $resp['recordsFiltered'] = $res['total_cnt'];

			if ($this->isGranted('ROLE_ADMIN'))
				$opts = '<i class="fa fa-pencil icon-link protocol_edit" title="Редактирование протокола"></i> <i class="fa fa-file-text-o icon-link to_files" title="Загрузка и просмотр файлов"></i> <i class="fa fa-table icon-link to_forms" title="Формы хранения данных"></i> <i class="fa fa-trash-o protocol_del icon-link" title="Удаление протокола"></i>';
			else
				$opts = '<i class="fa fa-info-circle icon-link protocol_info" title="Информация"></i> <i class="fa fa-file-text-o icon-link to_files" title="Загрузка и просмотр файлов"></i> <i class="fa fa-table icon-link to_forms" title="Формы хранения данных"></i>';
			
            foreach($res['data'] as $d) {
                array_push(
                    $resp['data'],
                    array(
                        $d->getIdProtocol(),
                        $d->getProtocolName(),
                        $d->getProtocolDescr(),
                        $d->getProtocolFileMimeType(),
                        $d->getProtocolSignDate()->format('d.m.Y'),
						($d->getEnableMigration() == 0) ? 'Выключена' : 'Включена',
                        $opts
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
     * @Route("/protocols__get", name = "eif_protocols__get")
     * 
     * Сервис получения протокола
     */
    
    public function protocols__get(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Получаем входные параметры
        $id_protocol = $this->checkUuid($request->query->get('id_protocol'));
        
        // Получаем запись из БД
        try {
            $em 	= $this->getDoctrine()->getManager();
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
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Протокол не найден!';
            } else {
                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
				
				if ($this->isGranted('ROLE_ADMIN')) {
					$resp['content'] = array(
						'protocol_name' => $protocol->getProtocolName(),
						'protocol_descr' => $protocol->getProtocolDescr(),
						'protocol_file_mime_type' => $protocol->getProtocolFileMimeType(),
						'protocol_sign_date' => $protocol->getProtocolSignDate()->format('d.m.Y'),
						'protocol_files_cnt' => count($protocol->getFiles()),
						'protocol_doc' => is_null($protocol->getProtocolDoc()) ? 0 : 1,
						'protocol_xml_xsd' => is_null($protocol->getProtocolXmlXsd()) ? 0 : 1,
						'enable_migration' => $protocol->getEnableMigration()
					);
				} else {
					$resp['content'] = array(
						'protocol_name' => $protocol->getProtocolName(),
						'protocol_descr' => $protocol->getProtocolDescr(),
						'protocol_file_mime_type' => $protocol->getProtocolFileMimeType(),
						'protocol_sign_date' => $protocol->getProtocolSignDate()->format('d.m.Y'),
						'protocol_doc' => is_null($protocol->getProtocolDoc()) ? 0 : 1
					);					
				}
            }
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
     * @Route("/protocols__save", name = "eif_protocols__save") 
     * 
     * Сервис сохранения протокола
     */
    
    public function protocols__save(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
               
        try {
            $em = $this->getDoctrine()->getManager();

            // Проверка входных параметров
            $id_protocol = $request->request->get('id_protocol');
            
            if (!($source = $em->getRepository('NCUOEifBundle:Source')->find($this->checkUuid($request->request->get('id_source'))))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Источник не найден!';
                
                return $this->createResponse($resp);
            }
            
            $protocol_name = trim($request->request->get('protocol_name'));
            if ($protocol_name == '') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Не задано наименование протокола!';
                
                return $this->createResponse($resp);                
            }
            
            $protocol_descr = $request->request->get('protocol_descr');
            
            $protocol_sign_date = trim($request->request->get('protocol_sign_date'));
            if ($protocol_sign_date == '') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Не задана дата подписания протокола!';
                
                return $this->createResponse($resp);                 
            }
            
            $protocol_file_mime_type = $request->request->get('protocol_file_mime_type');
            if ($protocol_file_mime_type == '') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Не задан тип файлов протокола!';
                
                return $this->createResponse($resp);                
            }
			
            $enable_migration = $request->request->get('enable_migration');
            if ($enable_migration != '0' && $enable_migration != '1') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задан флаг включения миграции файлов!';
                
                return $this->createResponse($resp);                
            }			
            
            // Сохранение
            if ($id_protocol == -1) {
                // Создание протокола
                $protocol = new Protocol();
                $protocol->setProtocolName($protocol_name);
                $protocol->setProtocolDescr($protocol_descr);
                $protocol->setProtocolFileMimeType($protocol_file_mime_type);
                $protocol->setProtocolSignDate(\DateTime::createFromFormat('d.m.Y', $protocol_sign_date));
				$protocol->setEnableMigration($enable_migration);

                if ($f = $request->files->get('protocol_doc')) {
                    $cont = $f->getMimeType() . ';' . $f->getClientOriginalName() . ';' . base64_encode(file_get_contents($f->getRealPath()));
                    $protocol->setProtocolDoc($cont);
                } else
                    $protocol->setProtocolDoc(null);
                
                if ($f = $request->files->get('protocol_xml_xsd')) {
                    $cont = $f->getMimeType() . ';' . $f->getClientOriginalName() . ';' . base64_encode(file_get_contents($f->getRealPath()));
                    $protocol->setProtocolXmlXsd($cont);
                } else
                    $protocol->setProtocolXmlXsd(null);                

                $protocol->setSource($source);

                $em->persist($protocol);
                $em->flush();

                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = 'Протокол успешно создан!';
            } else {
                $protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($this->checkUuid($id_protocol));

                if (!$protocol) {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'Протокол не найден!';
                } else {
                    // Сохраняем свойства протокола
                    $protocol->setProtocolName($protocol_name);
                    $protocol->setProtocolDescr($protocol_descr);
                    $protocol->setProtocolFileMimeType($protocol_file_mime_type);
                    $protocol->setProtocolSignDate(\DateTime::createFromFormat('d.m.Y', $protocol_sign_date));
					$protocol->setEnableMigration($enable_migration);

                    if ($request->request->get('protocol_doc_save') == 1) {
                        if ($f = $request->files->get('protocol_doc')) {
                            $cont = $f->getMimeType() . ';' . $f->getClientOriginalName() . ';' . base64_encode(file_get_contents($f->getRealPath()));
                            $protocol->setProtocolDoc($cont);
                        } else
                            $protocol->setProtocolDoc(null);
                    }
                    
                    if ($request->request->get('protocol_xml_xsd_save') == 1) {
                        if ($f = $request->files->get('protocol_xml_xsd')) {
                            $cont = $f->getMimeType() . ';' . $f->getClientOriginalName() . ';' . base64_encode(file_get_contents($f->getRealPath()));
                            $protocol->setProtocolXmlXsd($cont);
                        } else
                            $protocol->setProtocolXmlXsd(null);
                    }                    

                    $em->flush();

                    $resp['err_id'] = 0;
                    $resp['err_msg'] = 'Ok';
                    $resp['content'] = 'Протокол успешно обновлен!';
                }
            }   
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
     * @Route("/protocols__delete", name = "eif_protocols__delete") 
     * 
     * Сервис удаления протокола
     */
    
    public function protocols__delete(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Удаляем запись из БД
        try {
            $em = $this->getDoctrine()->getManager();
            
            if (!($protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($this->checkUuid($request->request->get('id_protocol'))))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Протокол не найден!';
            } else {            
                $em->remove($protocol);
                $em->flush();
                
                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = 'Протокол успешно удален!';
            }
        } catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';     
            
            if (strpos($ex->getMessage(), 'fk_files_id_protocol') !== false)
                $resp['content'] = 'Невозможно удалить протокол, т.к. по нему есть загруженные файлы!';
            else if (strpos($ex->getMessage(), 'fk_forms_id_protocol') !== false)
                $resp['content'] = 'Невозможно удалить протокол, т.к. по нему есть созданные формы!';
            else    
                $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);
        }
            
        return $this->createResponse($resp);
    }
    
    /**
     * @Route("/protocols__doc_download", name = "eif_protocols__doc_download")
     * 
     * Сервис скачивания документа протокола
     */
    
    public function protocols__doc_download(Request $request) {
		// Ответ на случай ошибки
        $resp = new Response('Ошибка загрузки файла!');
		$resp->headers->set('Content-Type', 'text/plain');
		$resp->headers->set('Content-Transfer-Encoding', 'binary');
		$resp->headers->set('Pragma', 'no-cache');	
		$resp->headers->set('Content-Disposition', 'attachment; filename="Ошибка.txt"');
        
		$id_protocol = $this->checkUuid($request->query->get('id_protocol'));
		
        try {
			$em 	= $this->getDoctrine();
			$user	= $this->getUser();
			
            // Получаем объект протокола
			if ($this->isGranted('ROLE_FOIV')) {
				// Ищем протокол по идентификатору и пользователю ФОИВ/РОИВ
				if ($user->getFoiv())
					$protocol = $em->getRepository('NCUOEifBundle:Protocol')->findByUser($id_protocol, 'FOIV', $user->getFoiv());
				else
					$protocol = $em->getRepository('NCUOEifBundle:Protocol')->findByUser($id_protocol, 'ROIV', $user->getRoiv());                    				
			} else {
				$protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($id_protocol);
			}
						
            if ($protocol) {
                $data = explode(';', $protocol->getProtocolDoc());
                
                $resp = new Response(base64_decode($data[2]));
                $resp->headers->set('Content-Type', $data[0]);
                $resp->headers->set('Content-Transfer-Encoding', 'binary');
                $resp->headers->set('Pragma', 'no-cache');
                $resp->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', addslashes($data[1])));
            }
        } catch (\Exception $ex) {
            // Логируем ошибку
            $this->log_e($ex);
        }
        
        return $resp;
    }  
    
    /**
     * @Route("/protocols__xml_xsd_download", name = "eif_protocols__xml_xsd_download") 
     * 
     * Сервис скачивания XSD файла протокола
     */
    
    public function protocols__xml_xsd_download(Request $request) {
		// Ответ на случай ошибки
        $resp = new Response('Ошибка загрузки файла!');
		$resp->headers->set('Content-Type', 'text/plain');
		$resp->headers->set('Content-Transfer-Encoding', 'binary');
		$resp->headers->set('Pragma', 'no-cache');	
		$resp->headers->set('Content-Disposition', 'attachment; filename="Ошибка.txt"');
        
        try {
            // Получаем объект протокола
            if (($protocol = $this->getDoctrine()->getRepository('NCUOEifBundle:Protocol')->find($this->checkUuid($request->query->get('id_protocol'))))) {
                $data = explode(';', $protocol->getProtocolXmlXsd());
                
                $resp = new Response(base64_decode($data[2]));
                $resp->headers->set('Content-Type', $data[0]);
                $resp->headers->set('Content-Transfer-Encoding', 'binary');
                $resp->headers->set('Pragma', 'no-cache');
                $resp->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', addslashes($data[1])));
            }
        } catch (\Exception $ex) {
            // Логируем ошибку
            $this->log_e($ex);
        }
        
        return $resp;
    }     
}