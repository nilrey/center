<?php

namespace App\NCUO\ServiceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;
use App\NCUO\ServiceBundle\Entity\Service;

/**
 * Класс контроллера списка сервисов
 * ################################
 * 
 */

class ServicesController extends BaseController {
 
    /**
     * @Route("/services", name = "service_services")
     * @Template("ncuoservice/services.html.twig")
     * 
     * Страница списка сервисов
     */
    
    public function services(Request $request) {
        // Формируем контент страницы
        $context = [];
        $context['msg_service_error'] = 'err test 1'; //$this->container->getParameter('ncuoservice.msg.service_error');                    
        
		// Серверное время
		$context['srv_time'] = (new \DateTime('now'))->format('d.m.y H:i:s');
		
        // Результирующий массив
        return $context;
    }
	
	/**
     * @Route("/services__srv_time", name = "service_services__srv_time")
     * 
     * Веб-сервис получения серверного времени
     */
	 
	public function services__srv_time(Request $request) {
		$resp = ['srv_time' => (new \DateTime('now'))->format('d.m.y H:i:s')];
	
		return $this->createResponse($resp);
	}
    
    /**
     * @Route("/services__get", name = "service_services__get")
     * 
     * Веб-сервис получения сервиса
     */
    
    public function services__get(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Получаем входные параметры
        $id_service = $this->checkUuid($request->query->get('id_service'));
        
        // Получаем запись из БД
        try {
            if (!($service = $this->getDoctrine()->getRepository('NCUOServiceBundle:Service')->find($id_service))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Регламентная задача не найдена!';
                
                return $this->createResponse($resp);
            }
            
            $service_sched_next_date = $service->getSchedNextDate();
            if ($service_sched_next_date) {
                $service_sched_next_date_d = $service_sched_next_date->format('d.m.Y');
                $service_sched_next_date_h = $service_sched_next_date->format('H');
                $service_sched_next_date_m = $service_sched_next_date->format('i');
            } else {
                $service_sched_next_date_d = '';
                $service_sched_next_date_h = '00';
                $service_sched_next_date_m = '00';
            }
            
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = [
                'service_name' => $service->getServiceName(),
                'service_descr' => $service->getServiceDescr(),
                'service_shell_cmd' => $service->getShellCmd(),
                'service_pkill_pattern' => $service->getPkillPattern(),
                'service_sched_interval_min' => $service->getSchedIntervalMin(),
				'service_autocontrol_interval_min' => $service->getAutocontrolIntervalMin(),
                'service_sched_next_date_d' => $service_sched_next_date_d,
                'service_sched_next_date_h' => $service_sched_next_date_h,
                'service_sched_next_date_m' => $service_sched_next_date_m,
                'service_last_output' => $service->getLastOutput()
            ];
        } catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = sprintf($this->container->getParameter('ncuoservice.msg.exception'), $err_log_id);
        }
            
        return $this->createResponse($resp);           
    }    
    
    /**
     * @Route("/services__get_list", name = "service_services__get_list")
     *
     * Веб-сервис получения списка сервисов
     */
    
    public function services__get_list(Request $request) {
        // Получаем входные параметры
        $row_start      = $request->request->get('start');
        $res_length     = $request->request->get('length');   
        $search_str     = $request->request->get('search')['value'];
        
        $resp = array('draw' => $request->request->get('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array());
        /*
        // Получаем данные в БД
        try {      
            $res = $this->getDoctrine()->getRepository('NCUOServiceBundle:Service')->getDataByBlocks($row_start, $res_length, $search_str);    
                
            // Формируем результат
            $resp['recordsTotal'] = $res['total_cnt'];
            $resp['recordsFiltered'] = $res['total_cnt'];

            foreach($res['data'] as $d) {
                $service_status = $d->getServiceStatus()->getStatusName();
                if ($service_status == 'Остановлен')
                    $service_opts = '<i class="fa fa-pencil icon-link service_edit" title="Редактирование регламентной задачи"></i> <i class="fa fa-play service_manual_start icon-link" title="Запуск регламентной задачи"></i> <i class="fa fa-trash-o service_del icon-link" title="Удаление регламентной задачи"></i>';
                else if ($service_status == 'Выполняется')
                    $service_opts = '<i class="fa fa-times icon-link service_pkill" title="Принудительное завершение регламентной задачи"></i>';
                
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
                
                array_push(
                    $resp['data'],
                    array(
                        $d->getIdService(),
                        $d->getServiceName(),
                        $service_status,
                        $last_start_date,
                        $last_finish_date,
                        $interval,
                        $service_opts
                    )
                );
            }
        } catch (\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['error'] = sprintf($this->container->getParameter('ncuoservice.msg.exception'), $err_log_id);
        }
        */

        $where = "";
        if(!empty($search_str)){
            $where .= " WHERE service_name LIKE '%".$search_str."%' ";
        }

        $conn = $this->getDoctrine()->getManager()->getConnection();

        $stmt = $conn->prepare("SELECT count(*) as cnt FROM service.services {$where}");
        $stmt->execute();        
        $cnt = $stmt->fetchAll();

        $stmt = $conn->prepare("SELECT * FROM service.services {$where} ORDER BY service_name ASC LIMIT {$res_length} OFFSET {$row_start} ");
        $stmt->execute();        
        $arItems = $stmt->fetchAll();
        
        $resp['recordsTotal'] = $cnt[0]['cnt'];
        $resp['recordsFiltered'] = $cnt[0]['cnt'];
        $resp['row_start'] = $row_start;
        $resp['res_length'] = $res_length;
        
        if( count($arItems) > 0 )
        {
            foreach( $arItems as &$arItem )
            {
                if ($arItem['id_status'] == '21fc5358-9a84-4524-8d79-8c523cf2ae2b') { // Остановлен 
                    $service_opts = '<!--<i class="fa fa-pencil icon-link service_edit" title="Редактирование регламентной задачи"></i> <i class="fa fa-play service_manual_start icon-link" title="Запуск регламентной задачи"></i> --><i class="fa fa-trash-o service_del icon-link" title="Удаление регламентной задачи"></i>';
                    $arItem['status_name'] = 'Остановлен';
                }else if ($arItem['id_status'] == 'fac4b7d1-24d9-49cb-91ba-da6b4237896b'){ // Выполняется
                    $service_opts = '<i class="fa fa-times icon-link service_pkill" title="Принудительное завершение регламентной задачи"></i>';
                    $arItem['status_name'] = 'Выполняется';
                }
        
                if ($arItem['sched_interval_min'] == 0)
                    $interval = 'Регламент выключен';
                else
                    $interval = $arItem['sched_next_date'];
                    
                array_push(
                    $resp['data'],
                    array(
                        $arItem['id_service'],
                        $arItem['service_name'],
                        $arItem['status_name'],
                        $arItem['last_start_date'],
                        $arItem['last_finish_date'],
                        $interval,
                        $service_opts
                    )
                );
            }
        }



        return $this->createResponse($resp);
    }
    
    /**
     * @Route("/services__save", name = "service_services__save")
     * 
     * Веб-сервис сохранения сервиса
     */
    
    public function services__save(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Проверка входных параметров
            $id_service = $request->request->get('id_service');            
            
            $service_name = trim($request->request->get('service_name'));
            if ($service_name == '') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Не задано наименование регламентной задачи!';
                
                return $this->createResponse($resp);
            }
            
            $service_descr = $request->request->get('service_descr');
            
            $service_shell_cmd = trim($request->request->get('service_shell_cmd'));
            if ($service_shell_cmd == '') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Не задана команда выполнения для регламентной задачи!';
                
                return $this->createResponse($resp);
            }
            
            $service_pkill_pattern = trim($request->request->get('service_pkill_pattern'));
            if ($service_pkill_pattern == '') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Не задана метка команды принудительного завершения регламентной задачи!';
                
                return $this->createResponse($resp);
            }            
            
            $service_sched_interval_min = trim($request->request->get('service_sched_interval_min'));
            if (!is_numeric($service_sched_interval_min) || (intval($service_sched_interval_min) < 0)) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задан интервал запуска регламентной задачи!';
                
                return $this->createResponse($resp);
            }
            $service_sched_interval_min = intval($service_sched_interval_min);
            
            if ($service_sched_interval_min != 0) {
				$service_autocontrol_interval_min = trim($request->request->get('service_autocontrol_interval_min'));
				if (!is_numeric($service_autocontrol_interval_min) || (intval($service_autocontrol_interval_min) <= 0)) {
					$resp['err_id'] = 1;
					$resp['err_msg'] = 'Service error';
					$resp['content'] = 'Неверно задан интервал автоконтроля!';
					
					return $this->createResponse($resp);
				}				
			
                if (($service_sched_next_date = \DateTime::createFromFormat('d.m.Y H:i', $request->request->get('service_sched_next_date'))) === false) {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'Неверно задана дата следующего регламентного выполнения!';
                    
                    return $this->createResponse($resp);                    
                }
            }
            
            // Сохранение
            if ($id_service == -1) {
                // Создание источника
                $service = new Service();
                $service->setServiceName($service_name);
                $service->setServiceDescr($service_descr);
                $service->setServiceCreateDate(new \DateTime('now'));
                $service->setServiceStatus($em->getRepository('NCUOServiceBundle:ServiceStatus')->findOneBy(array('status_name' => 'Остановлен')));
                                
                $service->setSchedIntervalMin($service_sched_interval_min);
                if ($service_sched_interval_min != 0) {
					$service->setAutocontrolIntervalMin($service_autocontrol_interval_min);
                    $service->setSchedNextDate($service_sched_next_date);   // Если регламент задан, то устанавливаем которую передали
				}
                                
                $service->setShellCmd($service_shell_cmd);
                $service->setPkillPattern($service_pkill_pattern);
                    
                $em->persist($service);
                $em->flush();
                
                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = 'Регламентная задача успешно создана!';
            } else {
                // Редактирование сервиса
                if (!($service = $em->getRepository('NCUOServiceBundle:Service')->find($this->checkUuid($id_service)))) {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'Регламентная задача не найдена!';
                    
                    return $this->createResponse($resp);
                }
                
                if ($service->getServiceStatus()->getStatusName() == 'Выполняется') {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'Невозможно редактировать выполняющуюся регламентную задачу!';
                    
                    return $this->createResponse($resp);                
                }                
                
                //
                $service->setServiceName($service_name);
                $service->setServiceDescr($service_descr);
                
                $service->setSchedIntervalMin($service_sched_interval_min);
                if ($service_sched_interval_min == 0) {
                    $service->setAutocontrolIntervalMin(null);
					$service->setSchedNextDate(null);   // Если регламент не задан, дата следующего выполнения пустая
				}
                else {
					$service->setAutocontrolIntervalMin($service_autocontrol_interval_min);
                    $service->setSchedNextDate($service_sched_next_date);   // Если регламент задан, то устанавливаем которую передали
				}
                    
                $service->setShellCmd($service_shell_cmd);
                $service->setPkillPattern($service_pkill_pattern);
                        
                $em->flush();
                
                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = 'Регламентная задача успешно обновлена!';
            }           
        } catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = sprintf($this->container->getParameter('ncuoservice.msg.exception'), $err_log_id);
        }
            
        return $this->createResponse($resp);
    }
    
    /**
     * @Route("/services__delete", name = "service_services__delete")
     * 
     * Веб-сервис удаления сервиса
     */    
    
    public function services__delete(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Удаляем запись из БД
        try {
            $em = $this->getDoctrine()->getManager();
            
            if (!($service = $em->getRepository('NCUOServiceBundle:Service')->find($this->checkUuid($request->request->get('id_service'))))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Регламентная задача не найдена!';
                
                return $this->createResponse($resp);
            }
            
            if ($service->getServiceStatus()->getStatusName() == 'Выполняется') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Невозможно удалить выполняющуюся регламентную задачу!';
                
                return $this->createResponse($resp);                
            }
            
            // 
            $em->remove($service);
            $em->flush();
            
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Регламентная задача успешно удалена!';
        } catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = sprintf($this->container->getParameter('ncuoservice.msg.exception'), $err_log_id);            
        }
            
        return $this->createResponse($resp);        
    }
    
    /**
     * @Route("/services__manual_start", name = "service_services__manual_start")
     * 
     * Веб-сервис запуска сервиса вручную
     */    
    
    public function services__manual_start(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {            
            // Отправляем запрос в диспетчер выполнения
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->getRequest()->getSchemeAndHttpHost() . $this->generateUrl('service__dispathcer'));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "id_service=" . $request->request->get('id_service'));            
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 2);
            curl_setopt($curl, CURLOPT_USERPWD, $this->container->getParameter('ncuoservice.basicauth')); // Под АстраЛинукс Basic авторизация    
            curl_exec($curl);
            curl_close($curl);
            
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Запрос на запуск отправлен в диспетчер регламентных задач!';                
        } catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = sprintf($this->container->getParameter('ncuoservice.msg.exception'), $err_log_id);            
        }
            
        return $this->createResponse($resp);        
    }
    
    /**
     * @Route("/services__pkill", name = "service_services__pkill")
     * 
     * Веб-сервис принудительного завершения сервиса (прием от веб-интерфейса)
     */    
    
    public function services__pkill(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            // Отправляем запрос в сервис завершения
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->getRequest()->getSchemeAndHttpHost() . $this->generateUrl('service__pkill_exec'));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "id_service=" . $request->request->get('id_service'));            
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 2);
            curl_setopt($curl, CURLOPT_USERPWD, $this->container->getParameter('ncuoservice.basicauth')); // Под АстраЛинукс Basic авторизация    
            curl_exec($curl);

            curl_close($curl);
            
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Запрос на завершение принят!';
        } catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = sprintf($this->container->getParameter('ncuoservice.msg.exception'), $err_log_id);            
        }
            
        return $this->createResponse($resp);        
    }    	   
}