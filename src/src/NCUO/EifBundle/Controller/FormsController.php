<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;
use App\NCUO\EifBundle\Entity\Form;

/**
 * Класс контроллера обработки форм
 * ################################
 * 
 */

class FormsController extends BaseController {
    
    /**
     * @Route("/forms", name = "eif_forms")
     * @Template("ncuoeif/forms.html.twig")
     * 
     * Страница списка форм
     */
    
    public function forms(Request $request) {
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
                $protocol = $this->getDoctrine()->getRepository('NCUOEifBundle:Protocol')->find($id_protocol);
            }
            
            if (!$protocol) {
                $context['err_msg'] = 'Протокол не найден!';
                return $context;
            }

            $context['source']   = $protocol->getSource();
            $context['protocol'] = $protocol;                    
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $context['err_msg'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        // Результирующий массив
        return $context;
    }
    
    /**
     * @Route("/forms__get_list", name = "eif_forms__get_list")
     * 
     * Сервис получения списка форм протокола
     */
    
    public function forms__get_list(Request $request) {
        // Получаем входные параметры
        $row_start      = $request->request->get('start');
        $res_length     = $request->request->get('length');   
        $search_str     = $request->request->get('search')['value'];   
        
        $resp = array('draw' => $request->request->get('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array());
        
        $id_protocol = $this->checkUuid($request->request->get('id_protocol'));
        
        // Запрашиваем список в БД
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
                $resp['error'] = 'Протокол не найден!';
                return $this->createResponse($resp);
            }
            
            $res = $em->getRepository('NCUOEifBundle:Form')->getDataByBlocks($protocol, $row_start, $res_length, $search_str);

            // Формируем результат
            $resp['recordsTotal'] = $res['total_cnt'];
            $resp['recordsFiltered'] = $res['total_cnt'];

            foreach($res['data'] as $d) {
                
                if ($this->isGranted('ROLE_ADMIN'))
                    $opts = '<i class="fa fa-pencil icon-link form_edit" title="Редактирование формы"></i> <i class="fa fa-th-list icon-link to_form_fields" title="Настройка полей формы"></i> <i class="fa fa-eye icon-link to_form_view" title="Просмотр данных по импортированным файлам"></i> <i class="fa fa-binoculars icon-link to_form_view_act" title="Просмотр всех данных за период актуальности"></i> <i class="fa fa-trash-o icon-link form_delete" title="Удаление формы"></i>';
                else
                    $opts = '<i class="fa fa-info-circle icon-link form_info" title="Информация"></i> <i class="fa fa-th-list icon-link to_form_fields" title="Поля формы"></i> <i class="fa fa-eye icon-link to_form_view" title="Просмотр данных по импортированным файлам"></i> <i class="fa fa-binoculars icon-link to_form_view_act" title="Просмотр всех данных за период актуальности"></i>';
                
                array_push(
                    $resp['data'],
                    array(
                        $d->getIdForm(),
                        $d->getFormName(),
                        $d->getFormCreateDate()->format('d.m.Y H:i:s'),
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
     * @Route("/forms__get", name = "eif_forms__get")
     * 
     * Сервис получения формы
     */
    
    public function forms__get(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Получаем входные параметры
        $id_form = $this->checkUuid($request->query->get('id_form'));
        
        // Получаем запись из БД
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
                $form = $em->getRepository('NCUOEifBundle:Form')->find($id_form);
            }
            
            if (!$form) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Форма не найдена!';
            } else {
                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = [
                    'form_name' => $form->getFormName(),
                    'form_descr' => $form->getFormDescr(),
                    'form_data_act_control_interval' => $form->getDataActControlInterval(),
                    'xml_xslt_protocol_file_import' => is_null($form->getXmlXsltProtocolFileImport()) ? 0 : 1
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
     * @Route("/forms__save", name = "eif_forms__save") 
     * 
     * Сервис сохранения формы
     */
    
    public function forms__save(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            $em = $this->getDoctrine()->getManager();

            // Проверка входных параметров
            $id_form = $request->request->get('id_form');
            
            if (!($protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($this->checkUuid($request->request->get('id_protocol'))))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Протокол не найден!';
                
                return $this->createResponse($resp);
            }
            
            $form_name = trim($request->request->get('form_name'));
            if ($form_name == '') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Не задано наименование формы!';
                
                return $this->createResponse($resp);                
            }
            
            $form_descr = $request->request->get('form_descr');
            
            $form_data_act_control_interval = trim($request->request->get('form_data_act_control_interval'));
            if (!is_numeric($form_data_act_control_interval) || (intval($form_data_act_control_interval) < 0)) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Интервал контроля актуальности данных должен быть целым >= 0!';
                
                return $this->createResponse($resp);                 
            }
            
            if ($f = $request->files->get('xml_xslt_protocol_file_import')) {
                if ($f->getMimeType() != 'application/xml') {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'Неверный формат XSLT преобразования!';
                
                    return $this->createResponse($resp);                      
                }
                
                $objFile = $f->getMimeType() . ';' . $f->getClientOriginalName() . ';' . base64_encode(file_get_contents($f->getRealPath()));
            } else
                $objFile = null;
            
            // Сохранение
            if ($id_form == -1) {
                // Создание формы
                $form = new Form();
                $form->setFormName($form_name);
                $form->setFormDescr($form_descr);
                $form->setDataActControlInterval(intval($form_data_act_control_interval));
                $form->setFormCreateDate(new \DateTime('now'));
                $form->setXmlXsltProtocolFileImport($objFile);

                $form->setProtocol($protocol);

                $em->persist($form);
                $em->flush();

                // Создаем таблицу в БД для формы
                $em->getRepository('NCUOEifBundle:Form')->createDBTable($form);

                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = 'Форма успешно создана!';
            } else {
                if (!($form = $em->getRepository('NCUOEifBundle:Form')->find($this->checkUuid($id_form)))) {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'Форма не найдена!';
                } else {
                    $form->setFormName($form_name);
                    $form->setFormDescr($form_descr);
                    $form->setDataActControlInterval(intval($form_data_act_control_interval));

                    if ($request->request->get('xml_xslt_protocol_file_import_save') == 1)
                        $form->setXmlXsltProtocolFileImport($objFile);

                    $em->flush();

                    $resp['err_id'] = 0;
                    $resp['err_msg'] = 'Ok';
                    $resp['content'] = 'Форма успешно обновлена!';
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
     * @Route("/forms__delete", name = "eif_forms__delete") 
     * 
     * Сервис удаления формы
     */
    
    public function forms__delete(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Удаляем запись из БД
        try {
            $em = $this->getDoctrine()->getManager();            
            $rep = $em->getRepository('NCUOEifBundle:Form');
            
            // Ищем форму
            if (!($form = $rep->find($this->checkUuid($request->request->get('id_form'))))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Форма не найдена!';
            }
            
            // Проверяем форму на наличие данных
            if ($rep->hasRowsDBTable($form) != 0) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Форма содержит данные! Удаление не возможно!';
                
                return $this->createResponse($resp);
            }            
            
            // Удаляем таблицу в БД для формы
            $rep->dropDBTable($form);
            
            // Удаляем поля формы
            foreach($form->getFormFields() as $field)
                $em->remove($field);
            
            // Удаляем форму
            $em->remove($form);
            $em->flush();
            
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Форма успешно удалена!';                
        } catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            

            if (strpos($ex->getMessage(), 'ДОБАВИТЬ ПРОВЕРКУ НА КЛЮЧИ') !== false)
                $resp['content'] = 'ОШИБКА ПО КЛЮЧАМ';
            else    
                $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
            
        return $this->createResponse($resp);
    }
    
    /**
     * @Route("/forms__xslt_download", name = "eif_forms__xslt_download") 
     * 
     * Сервис скачивания XSLT формы
     */
    
    public function forms__xslt_download(Request $request) {
		// Ответ на случай ошибки
        $resp = new Response('Ошибка загрузки файла!');
		$resp->headers->set('Content-Type', 'text/plain');
		$resp->headers->set('Content-Transfer-Encoding', 'binary');
		$resp->headers->set('Pragma', 'no-cache');	
		$resp->headers->set('Content-Disposition', 'attachment; filename="Ошибка.txt"');
        
        try {
            // Получаем объект формы
            if ($form = $this->getDoctrine()->getRepository('NCUOEifBundle:Form')->find($this->checkUuid($request->query->get('id_form')))) {           
                $data = explode(';', $form->getXmlXsltProtocolFileImport());
                
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
     * @Route("/forms_clear", name = "eif_forms_clear")
     * @Template("ncuoeif/forms_clear.html.twig") 
     * 
     * Страница очистки форм
     */
    
    public function forms_clear(Request $request) {
        // Формируем контент страницы
        $context = array();
        $context['msg_service_error'] = $this->container->getParameter('ncuoeif.msg.service_error');
        
        // Получаем входные параметры
        try {
            if (!($protocol = $this->getDoctrine()->getRepository('NCUOEifBundle:Protocol')->find($this->checkUuid($request->query->get('id_protocol'))))) {
                $context['err_msg'] = 'Протокол не найден!';
                return $context;
            }            

            $context['source']   = $protocol->getSource();
            $context['protocol'] = $protocol;                    
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $context['err_msg'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        // Результирующий массив
        return $context;
    }  
    
    /**
     * @Route("/forms_clear__get_file_date_list", name = "eif_forms_clear__get_file_date_list") 
     * 
     * Сервис получения списка дат актуальности
     */
    
    public function forms_clear__get_file_date_list(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);

        try {
            $em = $this->getDoctrine()->getManager();
                                  
            if (!($protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($this->checkUuid($request->request->get('id_protocol'))))) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Протокол не найден!';
                
                return $this->createResponse($resp);
            }
            
            $files = $em->getRepository('NCUOEifBundle:File')
                        ->getFileListBy_Protocol_Status_Date(
                            $protocol,
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
     * @Route("/forms_clear__clear_all", name = "eif_forms_clear__clear_all") 
     * 
     * Сервис удаления всех данных форм протокола
     */
    
    public function forms_clear__clear_all(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Получаем протокол
            if (!($protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($this->checkUuid($request->request->get('id_protocol'))))) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Протокол не найден!';
                
                return $this->createResponse($resp);
            }
            
            // Получаем список форм протокола
            $forms      = $protocol->getForms();
            $form_rep   = $em->getRepository('NCUOEifBundle:Form');
                        
            // Очищаем полностью таблицы форм
            foreach($forms as $f)
                $form_rep->truncDBTable($f);
            
            // У всех файлов протокола со статусом "Импортирован в формы данных" устанавливаем статус "Загружен"
            $status_loaded = $em->getRepository('NCUOEifBundle:FileStatus')->findOneBy(array('status_name' => 'Загружен'));
            
            $file_rep   = $em->getRepository('NCUOEifBundle:File');
            $files      = $file_rep->getFileListBy_Protocol_Status($protocol, $em->getRepository('NCUOEifBundle:FileStatus')->findOneBy(array('status_name' => 'Импортирован в формы данных')));
            foreach($files as $f) {
                $f->setFileStatus($status_loaded);
                // Очищаем лог импорта для данного файла
                $file_rep->deleteFromImportLog($f);
            }
            
            $em->flush();
            
            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Формы протокола очищены, статус соответствующих файлов установлен в "Загружен"';
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
     * @Route("/forms_clear__clear_sel", name = "eif_forms_clear__clear_sel") 
     * 
     * Сервис удаления данных форм протокола по выбранному файлу
     */
    
    public function forms_clear__clear_sel(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Получаем протокол
            if (!($protocol = $em->getRepository('NCUOEifBundle:Protocol')->find($this->checkUuid($request->request->get('id_protocol'))))) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Протокол не найден!';
                
                return $this->createResponse($resp);
            }
            
            // Получаем срез (один файл или несколько)
            $file_lst = $request->request->get('id_file');
            
            // Статус загружен
            $file_status_uploaded = $em->getRepository('NCUOEifBundle:FileStatus')->findOneBy(array('status_name' => 'Загружен'));
            
            // Получаем список форм протокола
            $forms      = $protocol->getForms();
            $form_rep   = $em->getRepository('NCUOEifBundle:Form');            
            
            foreach($file_lst as $file_lst_item) {
                if (!($file = $em->getRepository('NCUOEifBundle:File')->find($file_lst_item))) {
                    $resp['err_id']  = 1;
                    $resp['err_msg'] = 'Service error';            
                    $resp['content'] = 'Файл не найден!';
                    
                    return $this->createResponse($resp);
                }            
                
                // Очищаем полностью таблицы форм
                foreach($forms as $f)
                    $form_rep->delFromDBTable($f, $file);
                
                // Устанавливаем у файла статус "Загружен"
                $file->setFileStatus($file_status_uploaded);
                // Очищаем лог импорта для данного файла
                $em->getRepository('NCUOEifBundle:File')->deleteFromImportLog($file);            
                
                $em->flush();
            }
            
            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Формы протокола очищены по выбранному срезу, статус соответствующих файлов установлен в "Загружен"';
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