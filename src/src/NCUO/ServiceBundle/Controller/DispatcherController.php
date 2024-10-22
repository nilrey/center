<?php

namespace App\NCUO\ServiceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;
use App\NCUO\ServiceBundle\Entity\Service;

/**
 * Класс контроллера диспетчера сервисов
 * #####################################
 * 
 */

class DispatcherController extends BaseController {
    
    /**
     * @Route("/scheduler", name = "service__scheduler")
     * 
     * Веб-сервис проверки расписания наступления регламента сервисов (обычно вызывается из cron)
     */
    
    public function scheduler(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Выбираем сервисы у которых включен регламент
            $srv_lst = $em->getRepository('NCUOServiceBundle:Service')->getSchedServices();
           
            foreach($srv_lst as $srv) {
				// Текущая дата
				$cur_date = \DateTime::createFromFormat('d.m.Y H:i', (new \DateTime('now'))->format('d.m.Y H:i'));
				
				// Дата след выполнения, интервалы выполнения и автоконтроля
                $sched_next_date 			= $srv->getSchedNextDate();
                $sched_interval_min 		= $srv->getSchedIntervalMin();
				$autocontrol_interval_min 	= $srv->getAutocontrolIntervalMin();
				
				$sched_next_date = clone $sched_next_date;
				$d_diff = $sched_next_date->getTimestamp() - $cur_date->getTimestamp();
				
				// Если дата следующего выполнения меньше текущей даты
				if ($d_diff < 0) {
					$srv->setSchedNextDate($cur_date->add(new \DateInterval("PT${autocontrol_interval_min}M")));
					$em->flush();					
				}
				else if ($d_diff == 0) {
					// Если дата следующего выполнения равна текущей даты - регламент наступил
					$srv->setSchedNextDate($sched_next_date->add(new \DateInterval("PT${sched_interval_min}M")));
					$em->flush();
					
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $this->getRequest()->getSchemeAndHttpHost() . $this->generateUrl('service__dispathcer'));
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_POSTFIELDS, "id_service=" . $srv->getIdService());
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_TIMEOUT, 2);
					curl_setopt($curl, CURLOPT_USERPWD, $this->container->getParameter('ncuoservice.basicauth')); // Под АстраЛинукс Basic авторизация    
					curl_exec($curl);
					curl_close($curl);   					
					
				}         
            }
            
            //
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Итерация регламента выполнена!';             
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
     * @Route("/dispatcher", name = "service__dispathcer")
     * 
     * Веб-сервис выполнения команды сервиса
     */
    
    public function dispatcher(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Получаем сервис
            $id_service = $this->checkUuid($request->request->get('id_service'));
            
            if (!($service = $em->getRepository('NCUOServiceBundle:Service')->find($id_service))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Сервис не найден!';
                
                return $this->createResponse($resp);
            }
            
            // Проверяем статус сервиса
            if ($service->getServiceStatus()->getStatusName() == 'Выполняется') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Сервис уже выполняется!';
                
                return $this->createResponse($resp);                
            }
            
            // Устанавливаем дату последнего запуска, статус
            $service->setLastStartDate(new \DateTime('now'));
            $service->setLastFinishDate(null);
            $service->setLastOutput(null);
            $service->setServiceStatus($em->getRepository('NCUOServiceBundle:ServiceStatus')->findOneBy(array('status_name' => 'Выполняется')));            
            $em->flush();
            
            // Запускаем процесса командной оболочки
            $cmd_out = shell_exec($service->getShellCmd());
            
            // Устанавливаем статус
            $service->setServiceStatus($em->getRepository('NCUOServiceBundle:ServiceStatus')->findOneBy(array('status_name' => 'Остановлен')));
            $service->setLastFinishDate(new \DateTime('now'));
            $service->setLastOutput($cmd_out);
            $em->flush();
            
            //
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Сервис выполнен!';             
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
     * @Route("/pkill_exec", name = "service__pkill_exec")
     * 
     * Веб-сервис принудительного завершения сервиса (выполнение pkill)
     */  	
	 
    public function pkill_exec(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Завершаем сервис
        try {
            $em = $this->getDoctrine()->getManager();
            
            if (!($service = $em->getRepository('NCUOServiceBundle:Service')->find($this->checkUuid($request->request->get('id_service'))))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Сервис не найден!';
                
                return $this->createResponse($resp);
            }
			
            if ($service->getServiceStatus()->getStatusName() != 'Выполняется') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Сервис не запущен!';
                
                return $this->createResponse($resp);                
            }

            // Если нет процесса по данному сервису, то просто меняем статус
			// Т.е. grep возвращает две строки: одна для sh -c (shell_exec), другая для самого процесса grep			
			if (intval(shell_exec('ps -ef | grep ' . $service->getPkillPattern() . ' | wc -l')) == 2) {
				$service->setServiceStatus($em->getRepository('NCUOServiceBundle:ServiceStatus')->findOneBy(array('status_name' => 'Остановлен')));
				$em->flush();
			}
			else {
				// Иначе убиваем процесс
				shell_exec('pkill -9 -f ' . $service->getPkillPattern());
			}
            
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Сервис успешно завершен!';
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