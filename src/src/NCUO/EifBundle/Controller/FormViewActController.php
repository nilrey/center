<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;

/**
 * Класс контроллера просмотра формы
 * #################################
 * 
 */

class FormViewActController extends BaseController {
    
    /**
     * @Route("/form_view_act", name = "eif_form_view_act")
     * @Template("ncuoeif/form_view_act.html.twig")
     * 
     * Страница просмотра формы
     */
    
    public function form_view_act(Request $request) {
        // Формируем контент страницы
        $context = [];
        $context['msg_service_error'] = $this->container->getParameter('ncuoeif.msg.service_error');
        
        $id_form = $this->checkUuid($request->query->get('id_form'));
        
        // Получаем входные параметры
        try {
            $em     = $this->getDoctrine()->getManager();
            $user   = $this->getUser();
            
            if ($this->isGranted('ROLE_FOIV')) {
                // Ищем форму по идентификатору и пользователю ФОИВ/РОИВ
                if ($user->getFoiv())
                    $form = $em->getRepository('NCUOEifBundle:Form')->findByUser($id_form, 'FOIV', $user->getFoiv());
                else
                    $form = $em->getRepository('NCUOEifBundle:Form')->findByUser($id_form, 'ROIV', $user->getRoiv());                  
            } else {
                $form = $em->getRepository('NCUOEifBundle:Form')->find($id_form);
            }
            
            if (!$form) {
                $context['err_msg'] = 'Форма не найдена!';
                return $context;
            }             
            
            // Добавляем информацию в контекст страницы
            $context['form']        = $form;                        
            $context['protocol']    = ($protocol = $form->getProtocol());
            $context['source']      = $protocol->getSource();            

            // Получаем заголовок таблицы формы данных
            $context['form_fields'] = $em->getRepository('NCUOEifBundle:FormField')->getAllFields($form);              
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $context['err_msg'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        // Результирующий массив
        return $context;
    }      
    
    /**
     * @Route("/form_view_act__get_data", name = "eif_form_view_act__get_data")
     * 
     * Сервис получения данных формы
     */
    
    public function form_view_act__get_data(Request $request) {
        $resp = ['draw' => $request->request->get('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []];
        
        // Получаем входные данные
        $req_flag = $request->request->get('req_flag');
        if ($req_flag == 0)        
            return $this->createResponse($resp);
        
        $id_form = $this->checkUuid($request->request->get('id_form'));
        
        try {
            $em     = $this->getDoctrine()->getManager();
            $user   = $this->getUser();
            
            // Получаем форму
            if ($this->isGranted('ROLE_FOIV')) {
                // Ищем форму по идентификатору и пользователю ФОИВ/РОИВ
                if ($user->getFoiv())
                    $form = $em->getRepository('NCUOEifBundle:Form')->findByUser($id_form, 'FOIV', $user->getFoiv());
                else
                    $form = $em->getRepository('NCUOEifBundle:Form')->findByUser($id_form, 'ROIV', $user->getRoiv());                  
            } else {
                $form = $em->getRepository('NCUOEifBundle:Form')->find($id_form);
            }
            
            if (!$form) {
                $resp['error'] = 'Форма не найдена!';
                return $this->createResponse($resp);
            }
            
            // Получаем диапазон дат актуальности
            $dt_from = $request->request->get('dt_from');
            if (!($df = \DateTime::createFromFormat('d.m.Y', $dt_from))) {
                $resp['error'] = 'Неверно задана дата начала периода!';
                return $this->createResponse($resp);                
            }            
            
            $dt_to = $request->request->get('dt_to');
            if (!($dt = \DateTime::createFromFormat('d.m.Y', $dt_to))) {
                $resp['error'] = 'Неверно задана дата конца периода!';
                return $this->createResponse($resp);                
            }
            
            if ($dt->diff($df)->days > 31) {
                $resp['error'] = 'Максимальный размер периода - 31 день!';
                return $this->createResponse($resp);                  
            }
            
            // Получаем список ключевых полей
            $key_fields = $em->getRepository('NCUOEifBundle:FormField')->getKeyFields($form);
            if (0 == count($key_fields)) {
                $resp['error'] = 'У формы не заданы ключевые поля!';
                return $this->createResponse($resp);             
            }
            
            // Выбираем данные в зависимости от пришедших параметров            
            $res = $em->getRepository('NCUOEifBundle:Form')->getDBTableDataAct(
                $form,
                $key_fields,
                $dt_from,
                $dt_to,
                $request->request->get('columns'),
                $request->request->get('order'),
                $request->request->get('start'),
                $request->request->get('length')
            );            
            
            $resp['data']               = $res['data'];
            $resp['recordsTotal']       = $res['total_cnt'];
            $resp['recordsFiltered']    = $res['total_cnt'];
        } catch (\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['error'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);
        }
        
        return $this->createResponse($resp);
    }
}