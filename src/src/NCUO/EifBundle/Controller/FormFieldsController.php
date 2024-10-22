<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;
use App\NCUO\EifBundle\Entity\FormField;

/**
 * Класс контроллера обработки полей формы
 * #######################################
 * 
 */

class FormFieldsController extends BaseController {
    
    /**
     * @Route("/form_fields", name = "eif_form_fields")
     * @Template("ncuoeif/form_fields.html.twig")
     * 
     * Страница полей формы
     */
    
    public function form_fields(Request $request) {
        // Формируем контент страницы
        $context = array();
        $context['msg_service_error'] = $this->container->getParameter('ncuoeif.msg.service_error');
        
        $id_form = $this->checkUuid($request->query->get('id_form'));
        
        // Получаем входные параметры
        try {
            $em   = $this->getDoctrine()->getManager();
            $user = $this->getUser();            
            
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

            $context['form']        = $form;                        
            $context['protocol']    = ($protocol = $form->getProtocol());
            $context['source']      = $protocol->getSource();                        
            
            // Добавляем список типов данных
            $arr_dt = $this->getDoctrine()->getRepository('NCUOEifBundle:Datatype')->getList();
            $res = array();
            foreach($arr_dt as $i)
                array_push($res, array('id' => $i->getIdDataType(), 'name' => $i->getDataTypeName()));
            
            $context['arr_dt'] = $res;            
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $context['err_msg'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        // Результирующий массив
        return $context;
    }
    
    /**
     * @Route("/form_fields__get_list", name = "eif_form_fields__get_list")
     * 
     * Сервис получения списка полей
     */
    
    public function form_fields__get_list(Request $request) {
        // Получаем входные параметры
        $row_start      = $request->request->get('start');
        $res_length     = $request->request->get('length');
        $search_str     = $request->request->get('search')['value'];
        
        $resp = array('draw' => $request->request->get('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array());
        
        $id_form = $this->checkUuid($request->request->get('id_form'));
        
        // Запрашиваем список в БД
        try {    
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            
            if ($this->isGranted('ROLE_FOIV')) {
                // Ищем форму по идентификатору и пользователю ФОИВ/РОИВ
                if ($user->getFoiv())
                    $form = $em->getRepository('NCUOEifBundle:Form')->findByUser($id_form, 'FOIV', $user->getFoiv());
                else
                    $form = $em->getRepository('NCUOEifBundle:Form')->findByUser($id_form, 'ROIV', $user->getRoiv());                   
            } else {
                $form = $this->getDoctrine()->getRepository('NCUOEifBundle:Form')->find($id_form);
            }            
            
            if (!$form) {
                $resp['error'] = 'Форма не найдена!';
                return $this->createResponse($resp);
            }
            
            $form_rep   = $em->getRepository('NCUOEifBundle:Form');
            $res        = $em->getRepository('NCUOEifBundle:FormField')->getDataByBlocks($form, $row_start, $res_length, $search_str);

            // Формируем результат
            $resp['recordsTotal'] = $res['total_cnt'];
            $resp['recordsFiltered'] = $res['total_cnt'];

            // Определяем есть ли в форме данные
            if ($this->isGranted('ROLE_ADMIN')) {
                $opts = '';
                if ($form_rep->hasRowsDBTable($form) == 0)
                    $opts = '<i class="fa fa-pencil icon-link field_edit" title="Редактирование поля"></i> <i class="fa fa-trash-o icon-link field_delete" title="Удаление поля"></i>';
            }
                
            foreach($res['data'] as $d) {
                if ($this->isGranted('ROLE_ADMIN')) {
                    array_push(
                        $resp['data'],
                        array(
                            $d->getFieldPos(),                        
                            $d->getIdField(),
                            $d->getFieldName(),
                            $d->getDatatype()->getDatatypeName(),
                            $d->getKeyFlag() == 0 ? 'Нет' : 'Да',
                            $opts
                        )
                    );
                } else {
                    array_push(
                        $resp['data'],
                        array(
                            $d->getIdField(),
                            $d->getFieldPos(),
                            $d->getFieldName(),
                            $d->getDatatype()->getDatatypeName(),
                            $d->getKeyFlag() == 0 ? 'Нет' : 'Да'
                        )
                    );                
                }
            }
        } catch (\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['error'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);
        }
        
        return $this->createResponse($resp);
    }
    
    /**
     * @Route("/form_fields__get", name = "eif_form_fields__get") 
     * 
     * Сервис получения информации о поле формы
     */
    
    public function form_fields__get(Request $request) {
        $resp = ['err_id' => NULL, 'err_msg' => NULL, 'content' => NULL];
        
        // Получаем запись из БД
        try {
            $em = $this->getDoctrine()->getManager();
            
            if (!($field = $em->getRepository('NCUOEifBundle:FormField')->find($this->checkUuid($request->query->get('id_field'))))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Поле не найдено!';
            } else {
                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = [
                    'field_name' => $field->getFieldName(),
                    'field_id_datatype' => $field->getDatatype()->getIdDatatype(),
                    'field_key_flag' => $field->getKeyFlag()
                ];
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
     * @Route("/form_fields__save", name = "eif_form_fields__save") 
     * 
     * Сервис сохранения поля формы
     */
    
    public function form_fields__save(Request $request) {
        $resp = ['err_id' => NULL, 'err_msg' => NULL, 'content' => NULL];

        // Сохраняем в БД        
        try {
            $em = $this->getDoctrine()->getManager();

            // Проверка входных параметров
            $id_field = $request->request->get('id_field');
            
            $rep_form = $em->getRepository('NCUOEifBundle:Form');
            
            if (!($form = $rep_form->find($this->checkUuid($request->request->get('id_form'))))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Форма не найдена!';

                return $this->createResponse($resp);
            }             
            
            if ($rep_form->hasRowsDBTable($form) != 0) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Форма содержит данные! Изменение полей не возможно!';
                
                return $this->createResponse($resp);
            }
            
            $field_name = trim($request->request->get('field_name'));
            if ($field_name == '') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Не задано наименование поля!';
                
                return $this->createResponse($resp);                
            }
            
            if (!($obj_dt = $em->getRepository('NCUOEifBundle:Datatype')->find($this->checkUuid($request->request->get('field_id_datatype'))))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Не верно задан тип данных!';
                
                return $this->createResponse($resp);                 
            }
            
            $field_key_flag = $request->request->get('field_key_flag');
            if ($field_key_flag != 0 && $field_key_flag != 1)
                $field_key_flag = 1;

            // Сохранение
            if ($id_field == -1) {                             
                // Создание поля
                $field = new FormField();
                $field->setFieldName($field_name);
                $field->setFieldPos($em->getRepository('NCUOEifBundle:FormField')->getFieldsCnt($form) + 1);
                $field->setDatatype($obj_dt);
                $field->setKeyFlag($field_key_flag);
                $field->setForm($form);

                $em->persist($field);
                $em->flush();

                // Пересоздаем таблицу в БД для формы с новыми полями
                $rep_form->createDBTable($form);

                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = 'Поле успешно добавлено!';
            } else {
                if (!($field = $em->getRepository('NCUOEifBundle:FormField')->find($this->checkUuid($id_field)))) {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'Поле не найдено!';
                } else {
                    $field->setFieldName($field_name);
                    $field->setDatatype($obj_dt);
                    $field->setKeyFlag($field_key_flag);

                    $em->flush();

                    $resp['err_id'] = 0;
                    $resp['err_msg'] = 'Ok';
                    $resp['content'] = 'Поле успешно обновлено!';
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
     * @Route("/form_fields__delete", name = "eif_form_fields__delete") 
     * 
     * Сервис удаления поля формы
     */
    
    public function form_fields__delete(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            $field_rep  = $em->getRepository('NCUOEifBundle:FormField');
            
            if (!($field = $field_rep->find($this->checkUuid($request->request->get('id_field'))))) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Поле не найдено!';
                
                return $this->createResponse($resp);
            }
            
            if (!($form = $field->getForm())) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Форма не найдена!';
                
                return $this->createResponse($resp);                
            }            
            
            if ($em->getRepository('NCUOEifBundle:Form')->hasRowsDBTable($form) != 0) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Форма содержит данные! Изменение полей не возможно!';
                
                return $this->createResponse($resp);                
            }            
                        
            // Удаляем поле
            $em->remove($field);
            $em->flush();

            // Перерассчитываем порядковый номер оставшихся полей
            $field_rep->updateFieldPos($form, $request->request->get('field_pos'));

            // Пересоздаем таблицу в БД для формы
            $em->getRepository('NCUOEifBundle:Form')->createDBTable($form);

            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Поле успешно удалено!';
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