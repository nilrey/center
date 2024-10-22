<?php

namespace App\NCUO\MapBundle\Controller\Adm;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\MapBundle\Controller\BaseController;
use App\NCUO\MapBundle\Entity\MetaFunction;


/**
 * Класс контроллера списка сервисов
 * ################################
 * 
 */

class MapController extends BaseController {

/***********************************************************************************************************************/	 
     /**
	 * @Route("/adm/map", name = "map_base_adm")
	 * @Template("ncuomap/adm/map.html.twig")
	 *
	 * Страница списка карт
	 */
    public function mapListUnload(Request $request) {
        // Формируем контент страницы
        $context = [];
        $context['content'] = 'err test'; //$this->container->getParameter('ncuomap.msg.exception');                    
        
        // Результирующий массив
        return $context;
    }
    
/***********************************************************************************************************************/		
    /**
     * @Route("/adm/map_adm_get", name = "map_adm__get")
     * 
     * Получение конкретной выгрузки
     */
    public function map_adm_get(Request $request) {
		$resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Получаем входные параметры
        $map_id = $request->query->get('id_unload');
		
        // Получаем запись из БД
        try {
            if (!($map = $this->getDoctrine()->getRepository('NCUOMapBundle:MetaFunction')->find($map_id))) {
		        $resp['err_id' ] = 1;
                $resp['err_msg'] = 'Map error';
                $resp['content'] = 'Сервис не найден!';
                return $this->createResponse($resp);
            }
            
		    $resp['err_id' ] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = [
				'map_id'      => $map->getId(),
                'map_name'    => $map->getName(),
                'map_command' => $map->getCommand(),
				'map_created' => $map->getCreated()
            ];
        } catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 2;
            $resp['err_msg'] = 'Map error';            
            $resp['content'] = 'err test'; //sprintf($this->container->getParameter('ncuomap.msg.exception'), $err_log_id);
        }
        
        return $this->createResponse($resp);           
    }    
    
/***********************************************************************************************************************/		
    /**
     * @Route("/adm/map_adm_get_list", name = "map_adm__get_list")
     *
     * Список выгрузок
     */
    public function map_adm__get_list(Request $request) {
        // Получаем входные параметры
        $row_start      = $request->request->get('start');
        $res_length     = $request->request->get('length');        
        
        $resp = array('draw' => $request->request->get('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array());
		
        // Получаем данные в БД
        try {      
            $res = $this->getDoctrine()->getRepository('NCUOMapBundle:MetaFunction')->getDataByBlocks($row_start, $res_length);
            
            // Формируем результат
            $resp['recordsTotal'] = $res['total_cnt'];
            $resp['recordsFiltered'] = $res['total_cnt'];
			
            foreach($res['data'] as $d) {
				/*
                $service_status = $d->getServiceStatus()->getStatusName();
                if ($service_status == 'Остановлен')
                    $service_opts = '<i class="fa fa-pencil icon-link service_edit" title="Редактирование сервиса"></i> <i class="fa fa-play service_manual_start icon-link" title="Запуск сервиса"></i> <i class="fa fa-trash-o service_del icon-link" title="Удаление сервиса"></i>';
                else if ($service_status == 'Выполняется')
                    $service_opts = '';
                
                $interval = $d->getSchedIntervalMin();
                if ($interval == 0)
                    $interval = 'Регламент выключен';
                else
                    $interval = $d->getSchedNextDate()->format('d.m.Y H:i');
                
                $last_start_date = $d->getLastStartDate();
                if ($last_start_date)
                    $last_start_date = $last_start_date->format('d.m.Y H:i:s'); 
                
                $last_finish_date = $d->getLastFinishDate();
                if ($last_finish_date)
                    $last_finish_date = $last_finish_date->format('d.m.Y H:i:s');                    
                */
                
                array_push(
                    $resp['data'],
                    array(
                        $d->getId(),
                        $d->getName(),
                        $d->getCommand(),
                        $d->getCreated()->format('d.m.Y H:i:s'),
							'<i class="fa fa-pencil icon-link map_edit" title="Редактирование"></i>'.
							// '<i class="fa fa-play map_manual_start icon-link" title="Переконфигурировать"></i>'.
							// '<a href="' . $this->generateUrl('map_adm__download_example') . '?mid=' . $d->getId() . '"><i class="glyphicon glyphicon-download map_download icon-link" title="Пример выгрузки"></i></a>'.
							// '<i class="fa fa-trash map_clear icon-link" title="Очистить выгруженные данные"></i>'.  
							'<i class="fa fa-trash-o map_del icon-link" title="Удалить"></i>'
							
                    )
                );
            }
        } catch (\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['error'] = 'err test'; //sprintf($this->container->getParameter('ncuomap.msg.exception'), $err_log_id);
        }
        
        return $this->createResponse($resp);
    }
    
/***********************************************************************************************************************/		
    /**
     * @Route("/adm/map_adm_save", name = "map_adm__save")
     * 
     * Сохранение данных о выгрузке в БД
     */
    public function map_adm__save(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
		
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Проверка входных параметров
            $map_id      = trim($request->request->get('map_id'));            
            $map_name    = trim($request->request->get('map_name'));
			$map_command = trim($request->request->get('map_command'));
			
			if ($map_command == '') {
                $resp['err_id' ] = 1;
                $resp['err_msg'] = 'map error';
                $resp['content'] = 'Не задана команда выполнения для геовыгрузки!';
                return $this->createResponse($resp);
            }
			
			if ($map_name == '') {
				$conn = $em->getConnection();
				try {
					$stmt = $conn->prepare("SELECT gis_unload.completeFormNameCMD('$map_command') as name;");
					$stmt->execute();
					
					if ($stmt->rowCount() == 0) {
						$resp['err_id' ] = 3;
						$resp['err_msg'] = 'map error';
						$resp['content'] = 'Ошибка выполнения хранимой процедуры!';
						return $this->createResponse($resp);
					}
					
					$res = $stmt->fetchAll();
					if (count($res) > 0) 
						$map_name = $res[0]['name'];
				} catch(\Exception $ex) {
					$resp['err_id' ] = 4;
					$resp['err_msg'] = 'map error';
					$resp['content'] = 'Ошибка выполнения хранимой процедуры!';
					return $this->createResponse($resp);
				}
			}
			         
            // Сохранение
            if ($map_id == -1) {
			    // Создание выгрузки
				$map = new MetaFunction();
				//$map->setId(new \String('uuid_generate_v4'));
                $map->setName($map_name);
			    $map->setCommand($map_command);
				//$map->setCreateDate(new \DateTime('now'));
				
                $em->persist($map);
                $em->flush();
				
				// TOOD: собственно создание выгрузки
                
                $resp['err_id' ] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = 'Выгрузка ГИС успешно создана!';
            }
			else {
                // Редактирование сервиса
                if (!($map = $em->getRepository('NCUOMapBundle:MetaFunction')->find($map_id))) {
                    $resp['err_id' ] = 5;
                    $resp['err_msg'] = 'map error';
                    $resp['content'] = 'Выгрузка ГИС не найдена!';
					
                    return $this->createResponse($resp);
                }
                
                $map->setName($map_name);
                $map->setCommand($map_command);
				$map->setCreateDate(new \DateTime());
				
                $em->flush();
				
				// TOOD: собственно создание выгрузки
                
                $resp['err_id' ] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = 'Сервис успешно обновлен!';
            }           
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id' ]  = 6;
            $resp['err_msg'] = 'map error';            
            $resp['content'] = 'err test'; //sprintf($this->container->getParameter('ncuomap.msg.exception'), $err_log_id);
        }
        
        return $this->createResponse($resp);
    }
	
/***********************************************************************************************************************/	
	/**
     * @Route("/adm/map_adm_exec", name = "map_adm__exec")
     * 
     * Генерация функции выгрузки
     */
    public function map_adm__exec(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
		
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Проверка входных параметров
			$command = trim($request->request->get('cmd'));
			
			if ($command == '') {
                $resp['err_id' ] = 1;
                $resp['err_msg'] = 'map error';
                $resp['content'] = 'Не задана команда выполнения для геовыгрузки!';
                return $this->createResponse($resp);
            }
			
			{
				$conn = $em->getConnection();
				try {
					$stmt = $conn->prepare("SELECT gis_unload.unload_form('$command') as result;");
					$stmt->execute();
					
					if ($stmt->rowCount() == 0) {
						$resp['err_id' ] = 3;
						$resp['err_msg'] = 'map error';
						$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>Нет данных';
						return $this->createResponse($resp);
					}
					
					/*
					$res = $stmt->fetchAll();
					if (count($res) > 0) 
						$map_name = $res[0]['result'];
						*/
				} catch(\Exception $ex) {
					
					/*
					$request->setRequestStatus($em->getRepository('NCUOReportBundle:RequestStatus')->findOneBy(array('status_name' => 'Ошибка')));
					$request->setFinishTimestamp(new \DateTime('now'));
					$request->setStatusMsg($ex->getMessage());
					*/
					
					$resp['err_id' ] = 4;
					$resp['err_msg'] = 'map error';
					$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>'.$ex->getMessage();
					return $this->createResponse($resp);
				}
			}
			
			$resp['err_id' ] = 0;
			$resp['err_msg'] = 'map error';
			$resp['content'] = 'Выгрузка успешно создана';
		
		} catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id' ] = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = 'err test'; //sprintf($this->container->getParameter('ncuomap.msg.exception'), $err_log_id);            
        }
		
		return $this->createResponse($resp);
    }
    
/***********************************************************************************************************************/		
    /**
     * @Route("/adm/map_adm_delete", name = "map_adm__delete")
     * 
     * Удаление выгрузки
     */    
    public function map_adm__delete(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Удаляем запись из БД
        try {
            $em = $this->getDoctrine()->getManager();
            
			$map_id      = trim($request->request->get('map_id'));
			
            if (!($unloadsrv = $em->getRepository('NCUOMapBundle:MetaFunction')->find($map_id))) {
                $resp['err_id' ] = 1;
                $resp['err_msg'] = 'map error';
                $resp['content'] = 'Сервис не найден!';
                
                return $this->createResponse($resp);
            }
                        
            // 
            $em->remove($unloadsrv);
            $em->flush();
            
            $resp['err_id' ] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Выгрузка успешно удалена!';                
        } catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Map error';            
            $resp['content'] = 'err test'; //sprintf($this->container->getParameter('ncuomap.msg.exception'), $err_log_id);            
        }
        
        return $this->createResponse($resp);        
    }
    
/***********************************************************************************************************************/		
    /**
     * @Route("/adm/map_adm_clear", name = "map_adm__clear")
     * 
     * Очистка данных о прежней выгрузке
     */    
    public function map_adm__clear(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
		
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Проверка входных параметров
			$command = trim($request->request->get('cmd'));
			
			if ($command == '') {
                $resp['err_id' ] = 1;
                $resp['err_msg'] = 'map error';
                $resp['content'] = 'Не задана команда выполнения для геовыгрузки!';
                return $this->createResponse($resp);
            }
			
			{
				$conn = $em->getConnection();
				try {
					$stmt = $conn->prepare("SELECT gis_unload.clearHist('$command') as result;");
					$stmt->execute();
					
					if ($stmt->rowCount() == 0) {
						$resp['err_id' ] = 3;
						$resp['err_msg'] = 'map error';
						$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>Нет данных';
						return $this->createResponse($resp);
					}
					
					/*
					$res = $stmt->fetchAll();
					if (count($res) > 0) 
						$map_name = $res[0]['result'];
						*/
				} catch(\Exception $ex) {
					
					/*
					$request->setRequestStatus($em->getRepository('NCUOReportBundle:RequestStatus')->findOneBy(array('status_name' => 'Ошибка')));
					$request->setFinishTimestamp(new \DateTime('now'));
					$request->setStatusMsg($ex->getMessage());
					*/
					
					$resp['err_id' ] = 4;
					$resp['err_msg'] = 'map error';
					$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>'.$ex->getMessage();
					return $this->createResponse($resp);
				}
			}
			
			$resp['err_id' ] = 0;
			$resp['err_msg'] = 'map error';
			$resp['content'] = 'Выгрузка успешно создана';
		
		} catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id' ] = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = 'err test'; //sprintf($this->container->getParameter('ncuomap.msg.exception'), $err_log_id);            
        }
		
		return $this->createResponse($resp);
    }
    
/***********************************************************************************************************************/	
	/**
     * @Route("/adm/map_adm_add_srv", name = "map_adm__add_service")
     * 
     * Добавление сервиса выгрузки
     */
    public function map_adm__add_service(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
		
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Проверка входных параметров
			$command = trim($request->request->get('cmd'));
			
			if ($command == '') {
                $resp['err_id' ] = 1;
                $resp['err_msg'] = 'map error';
                $resp['content'] = 'Не задана команда выполнения для геовыгрузки!';
                return $this->createResponse($resp);
            }
			
			{
				$conn = $em->getConnection();
				try {
					$stmt = $conn->prepare("SELECT gis_unload.createService('$command') as result;");
					$stmt->execute();
					
					if ($stmt->rowCount() == 0) {
						$resp['err_id' ] = 3;
						$resp['err_msg'] = 'map error';
						$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>Нет данных';
						return $this->createResponse($resp);
					}
					
					/*
					$res = $stmt->fetchAll();
					if (count($res) > 0) 
						$map_name = $res[0]['result'];
						*/
				} catch(\Exception $ex) {
					
					/*
					$request->setRequestStatus($em->getRepository('NCUOReportBundle:RequestStatus')->findOneBy(array('status_name' => 'Ошибка')));
					$request->setFinishTimestamp(new \DateTime('now'));
					$request->setStatusMsg($ex->getMessage());
					*/
					
					$resp['err_id' ] = 4;
					$resp['err_msg'] = 'map error';
					$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>'.$ex->getMessage();
					return $this->createResponse($resp);
				}
			}
			
			$resp['err_id' ] = 0;
			$resp['err_msg'] = 'map error';
			$resp['content'] = 'Выгрузка успешно создана';
		
		} catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id' ] = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = 'err test'; //sprintf($this->container->getParameter('ncuomap.msg.exception'), $err_log_id);            
        }
		
		return $this->createResponse($resp);
    }
	
/***********************************************************************************************************************/		
    /**
     * @Route("/adm/map__recreate_unloads", name = "map_adm__recreate_unloads")
     * 
     * пересоздать выгрузки
     */
    public function map__recreate_unloads(Request $request) {
		  $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
		
        try {
            $em = $this->getDoctrine()->getManager();
            
			{
				$conn = $em->getConnection();
				try {
					$stmt = $conn->prepare("SELECT id, name, gis_unload.unload_form(command) as result FROM gis_unload.meta_unload");
					$stmt->execute();
					
					if ($stmt->rowCount() == 0) {
						$resp['err_id' ] = 3;
						$resp['err_msg'] = 'map error';
						$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>Нет данных';
						return $this->createResponse($resp);
					}
					
					/*
					 
					 тут вставить отображение таблицы успешности создания выгрузок (со скролом контента)
					 
					$res = $stmt->fetchAll();
					if (count($res) > 0) 
						$map_name = $res[0]['result'];
						*/
				} catch(\Exception $ex) {
					
					/*
					$request->setRequestStatus($em->getRepository('NCUOReportBundle:RequestStatus')->findOneBy(array('status_name' => 'Ошибка')));
					$request->setFinishTimestamp(new \DateTime('now'));
					$request->setStatusMsg($ex->getMessage());
					*/
					
					$resp['err_id' ] = 4;
					$resp['err_msg'] = 'map error';
					$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>'.$ex->getMessage();
					return $this->createResponse($resp);
				}
			}
			
			$resp['err_id' ] = 0;
			$resp['err_msg'] = 'map error';
			$resp['content'] = 'Выгрузка успешно создана';
		
		} catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id' ] = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = 'err test'; //sprintf($this->container->getParameter('ncuomap.msg.exception'), $err_log_id);            
        }
		
		return $this->createResponse($resp);
    }

/***********************************************************************************************************************/	
	/**
     * @Route("/adm/map__recreate_services", name = "map_adm__recreate_services")
     * 
     * пересоздать сервиса выгрузки
     */
    public function map__recreate_services(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
		
        try {
            $em = $this->getDoctrine()->getManager();
            
			{
				$conn = $em->getConnection();
				try {
					$stmt = $conn->prepare("SELECT id, name, gis_unload.createService(command) as result FROM gis_unload.meta_unload;");
					$stmt->execute();
					
					if ($stmt->rowCount() == 0) {
						$resp['err_id' ] = 3;
						$resp['err_msg'] = 'map error';
						$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>Нет данных';
						return $this->createResponse($resp);
					}
					
					/*
					$res = $stmt->fetchAll();
					if (count($res) > 0) 
						$map_name = $res[0]['result'];
						*/
				} catch(\Exception $ex) {
					
					/*
					$request->setRequestStatus($em->getRepository('NCUOReportBundle:RequestStatus')->findOneBy(array('status_name' => 'Ошибка')));
					$request->setFinishTimestamp(new \DateTime('now'));
					$request->setStatusMsg($ex->getMessage());
					*/
					
					$resp['err_id' ] = 4;
					$resp['err_msg'] = 'map error';
					$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>'.$ex->getMessage();
					return $this->createResponse($resp);
				}
			}
			
			$resp['err_id' ] = 0;
			$resp['err_msg'] = 'map error';
			$resp['content'] = 'Выгрузка успешно создана';
		
		} catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id' ] = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = 'err test'; //sprintf($this->container->getParameter('ncuomap.msg.exception'), $err_log_id);            
        }
		
		return $this->createResponse($resp);
    }
	
/***********************************************************************************************************************/

	 /**
     * @Route("/adm/map__download_example", name = "map_adm__download_example")
     * 
     * скачивание примера выгрузки файла
     */
    
    public function map__download_example(Request $request) {
		$map_id = trim($request->query->get('mid'));
		
		// Ответ на случай ошибки
        $resp = new Response('Ошибка загрузки файла!');
		$resp->headers->set('Content-Type', 'text/plain');
		$resp->headers->set('Content-Transfer-Encoding', 'binary');
		$resp->headers->set('Pragma', 'no-cache');	
		$resp->headers->set('Content-Disposition', 'attachment; filename="Ошибка.txt"');
		
        try {
			$em = $this->getDoctrine()->getManager();
			$conn = $em->getConnection();
			
			/////////////////////////////////////////////////
			// 1. получить имя выгружаемого файла
			$tmp_file_name = "";
			$tmp_out_name  = "";
			$tmp_fn_name   = "";
			try {
				$stmt = $conn->prepare(
					"SELECT ".
					"	replace(uuid_generate_v4()::text, '-', '') as FILE, ". 
					"	replace(replace(replace(NAME, '/', '__'), '/', '__'), '\"', '') as NAME, ".
					"	'gis_unload.\\\"fn_unload__' || gis_unload.hash_value(gis_unload.hash_init(command), 'main') || '\\\"' as FUN ". 
					"FROM gis_unload.meta_unload ".
					"WHERE id = '$map_id'"
									   );
				$stmt->execute();
				
				if ($stmt->rowCount() == 0) {
					$resp['err_id' ] = 3;
					$resp['err_msg'] = 'map error';
					$resp['content'] = 'Ошибка получения имени хранимой процедуры! <br/>Нет данных';
					return $this->createResponse($resp);
				}
				$res = $stmt->fetchAll();
				if (count($res) == 0) {
					$resp['err_id' ] = 4;
					$resp['err_msg'] = 'map error';
					$resp['content'] = 'Нет данных для данной выгрузки';
					return $this->createResponse($resp);
				}

				$line = $res[0];				
				$tmp_file_name = $line{'file'};
				$tmp_out_name  = $line{'name'};
				$tmp_fn_name   = $line{'fun'};
			} catch(\Exception $ex) {
				$resp['err_id' ] = 4;
				$resp['err_msg'] = 'map error';
				$resp['content'] = 'Ошибка выполнения хранимой процедуры! <br/>'.$ex->getMessage();
				return $this->createResponse($resp);
			}
				
			/////////////////////////////////////////////////	
			// 2. сделать выгрузку
			// 3. сменить стиль
			
			// -100- это количество выгружаемых записей
			
			$cmd = "php ./cache/gisUnload_ex.php $tmp_fn_name 100 $tmp_file_name";
			$output = [];
			$result;
			exec($cmd, $output, $result);
			
			/////////////////////////////////////////////////
			// 4. отправить данные
			
			$value = file_get_contents("./cache/$tmp_file_name");

            $resp = new Response($value);
			$resp->setStatusCode(Response::HTTP_OK);
            $resp->headers->set('Content-Type', 'text/xml');
            $resp->headers->set('Content-Transfer-Encoding', 'binary');
			//$resp->headers->set('Content-Transfer-Encoding', 'text');
            $resp->headers->set('Pragma', 'no-cache');
			
			
            $resp->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', "$tmp_out_name.xml"));
			//$resp->setContent($value);
			
			
			/////////////////////////////////////////////////	
			// 5. удалить файл
			
			unlink("./cache/$tmp_file_name"); // удалить временный файл
			
        } catch(\Exception $ex) {
            // Логируем ошибку
            $this->log_e($ex);                        
        }
        
        return $resp;
    }

	
/***********************************************************************************************************************/		
}
