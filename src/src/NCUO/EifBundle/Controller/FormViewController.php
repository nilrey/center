<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;

/**
 * Класс контроллера просмотра формы по импортированным файлам
 * ###########################################################
 * 
 */

class FormViewController extends BaseController {
    
    /**
     * @Route("/form_view", name = "eif_form_view")
     * @Template("ncuoeif/form_view.html.twig")
     * 
     * Страница просмотра формы
     */
    
    public function form_view(Request $request) {
        // Формируем контент страницы
        $context = array();
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
     * @Route("/form_view__get_file_date_list", name = "eif_form_view__get_file_date_list")
     * 
     * Сервис получения списка дат загрузки файлов
     */
    
    public function form_view__get_file_date_list(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);

        $id_form = $this->checkUuid($request->request->get('id_form'));
        
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
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Форма не найдена!';                  
                
                return $this->createResponse($resp);
            }
            
            $files = $em->getRepository('NCUOEifBundle:File')
                        ->getFileListBy_Protocol_Status_Date(
                            $form->getProtocol(),
                            $em->getRepository('NCUOEifBundle:FileStatus')->findOneBy(array('status_name' => 'Импортирован в формы данных')),
                            'DESC',
                            \DateTime::createFromFormat('d.m.Y H:i:s', $request->request->get('dt_start')),
                            \DateTime::createFromFormat('d.m.Y H:i:s', $request->request->get('dt_end'))
            );
            
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = array();
            foreach($files as $f)
                array_push($resp['content'], array('id_file' => $f->getIdFile(), 'date' => $f->getFileUploadDate()->format('d.m.Y H:i:s') . " [{$f->getFileName()}]"));
                
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
     * @Route("/form_view__get_data", name = "eif_form_view__get_data")
     * 
     * Сервис получения данных формы
     */
    
    public function form_view__get_data(Request $request) {
        $resp = array('draw' => $request->request->get('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array());
        
        // Получаем входные данные
        $id_file = $request->request->get('id_file');
        if ($id_file == -1)        
            return $this->createResponse($resp);
        
        $id_file = $this->checkUuid($id_file);
        $id_form = $this->checkUuid($request->request->get('id_form'));
        
        try {
            $em     = $this->getDoctrine()->getManager();
            $user   = $this->getUser();
            
            // Получаем форму и файл
            if ($this->isGranted('ROLE_FOIV')) {
                // Ищем файл по идентификатору и пользователю ФОИВ/РОИВ
                if ($user->getFoiv())
                    $file = $em->getRepository('NCUOEifBundle:File')->findByUser($id_file, 'FOIV', $user->getFoiv());
                else
                    $file = $em->getRepository('NCUOEifBundle:File')->findByUser($id_file, 'ROIV', $user->getRoiv());                
                
                // Ищем форму по идентификатору и пользователю ФОИВ/РОИВ
                if ($user->getFoiv())
                    $form = $em->getRepository('NCUOEifBundle:Form')->findByUser($id_form, 'FOIV', $user->getFoiv());
                else
                    $form = $em->getRepository('NCUOEifBundle:Form')->findByUser($id_form, 'ROIV', $user->getRoiv());                 
            } else {
                $file = $em->getRepository('NCUOEifBundle:File')->find($id_file);
                $form = $em->getRepository('NCUOEifBundle:Form')->find($id_form);
            }
            
            if (!$file) {
                $resp['error'] = 'Файл не найден';
                return $this->createResponse($resp);
            }
            
            if (!$form) {
                $resp['error'] = 'Форма не найдена';
                return $this->createResponse($resp);
            }            
            
            // Выбираем данные в зависимости от пришедших параметров            
            $res = $em->getRepository('NCUOEifBundle:Form')->getDBTableData(
                $form,
                $file,
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