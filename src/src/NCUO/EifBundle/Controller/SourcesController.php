<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;
use App\NCUO\EifBundle\Entity\Source;
use App\NCUO\EifBundle\Entity\Protocol;
use App\NCUO\EifBundle\Entity\Form;
use App\NCUO\EifBundle\Entity\FormField;

/**
 * Класс контроллера обработки источников
 * ######################################
 *  
 */

class SourcesController extends BaseController {
 
    /**
     * @Route("/sources", name = "eif_sources")
     * @Template("ncuoeif/sources.html.twig")
     * 
     * Страница списка источников
     */
    
    public function sources(Request $request) {
        // Формируем контент страницы
        $context = array();
        $context['msg_service_error'] = 'test'; //$this->container->getParameter('ncuoeif.msg.service_error');
                
        // Получаем список ФОИВ/РОИВ
        try {
            $em = $this->getDoctrine()->getManager();
            
            $context['foiv_lst'] = null; //$em->createQuery('SELECT t FROM NCUOFoivBundle:Foiv t ORDER BY t.name')->getResult();
            $context['roiv_lst'] = null; //$em->createQuery('SELECT t FROM NCUOFoivBundle:Roiv t ORDER BY t.name')->getResult();
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = 'Doc err';// $this->log_e($ex);            
            
            $context['err_msg'] = $ex; //sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }                
        
        // Результирующий массив
        return $context;
    }
    
    /**
     * @Route("/sources__get_list", name = "eif_sources__get_list")
     * 
     * Сервис получения списка источников
     */
    
    public function sources__get_list(Request $request) {               
        // Получаем входные параметры
        
        $row_start      = intval($request->request->get('start'));
        $res_length     = intval($request->request->get('length'));
        $search_str     = $request->request->get('search')['value'];
        
        $resp = array('draw' => $request->request->get('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array());
        
        /*   
        // Получаем данные в БД
        try {
            $em     = $this->getDoctrine();  
            $user   = $this->getUser();
            
            if ($this->isGranted('ROLE_FOIV')) {
                if ($user->getFoiv())
                    $res = $em->getRepository('NCUOEifBundle:Source')->getDataByBlocksByUser($row_start, $res_length, 'FOIV', $user->getFoiv(), $search_str);
                else
                    $res = $em->getRepository('NCUOEifBundle:Source')->getDataByBlocksByUser($row_start, $res_length, 'ROIV', $user->getRoiv(), $search_str);                
            } else {
                $res = $em->getRepository('NCUOEifBundle:Source')->getDataByBlocks($row_start, $res_length, $search_str);
            }            
            
            // Формируем результат
            $resp['recordsTotal'] = $res['total_cnt'];
            $resp['recordsFiltered'] = $res['total_cnt'];            

            foreach($res['data'] as $d) {
                if ($this->isGranted('ROLE_ADMIN')) {
                    array_push(
                        $resp['data'],
                        array(
                            $d->getIdSource(),
                            $d->getSourceName(),
                            $d->getSourceCreateDate()->format('d.m.Y H:i:s'),
                            '<i class="fa fa-pencil icon-link source_edit" title="Редактирование источника"></i> <i class="fa fa-exchange to_protocols icon-link" title="Протоколы информационно-технического сопряжения"></i> <a href="' . $this->generateUrl('eif_sources__export') . '?id_source=' . $d->getIdSource() .'"><i class="fa fa-download source_export icon-link" title="Экспортирование источника"></i></a> <i class="fa fa-trash-o source_del icon-link" title="Удаление источника"></i>'
                        )
                    );
                } else {
                    array_push(
                        $resp['data'],
                        array(
                            $d->getIdSource(),
                            $d->getSourceName(),
                            $d->getSourceCreateDate()->format('d.m.Y H:i:s'),
                            '<i class="fa fa-info-circle icon-link source_info" title="Информация"></i> <i class="fa fa-exchange to_protocols icon-link" title="Протоколы информационно-технического сопряжения"></i>'
                        )
                    );                    
                }
            }
        } catch (\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['error'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);
        }
        */
        $where = "";
        if(!empty($search_str)){
            $where .= " WHERE source_name LIKE '%".$search_str."%' ";
        }
        
        $conn = $this->getDoctrine()->getManager()->getConnection();

        $stmt = $conn->prepare("SELECT count(*) as cnt FROM eif.sources {$where}");
        $stmt->execute();        
        $cnt = $stmt->fetchAll();

        $stmt = $conn->prepare("SELECT * FROM eif.sources {$where} ORDER BY source_name ASC LIMIT {$res_length} OFFSET {$row_start} ");
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
                array_push(
                    $resp['data'],
                    array(
                        $arItem['id_source'],
                        $arItem['source_name'],
                        $arItem['source_create_date'],
                        //'<i class="fa fa-pencil icon-link source_edit" title="Редактирование источника"></i> <i class="fa fa-exchange to_protocols icon-link" title="Протоколы информационно-технического сопряжения"></i>'
                        ''
                    )
                );
            }
        }

        // $respp = '{"draw":"1","recordsTotal":13,"recordsFiltered":13,"data":[["929f2818-83e2-4f06-9883-4a49422b1728","Ростехнадзор","15.04.2015 09:24:33","<i class=\"fa fa-pencil icon-link source_edit\" title=\"Редактирование источника\"><\/i> <i class=\"fa fa-exchange to_protocols icon-link\" title=\"Протоколы информационно-технического сопряжения\"><\/i> <a href=\"\/eif\/sources__export?id_source=929f2818-83e2-4f06-9883-4a49422b1728\"><i class=\"fa fa-download source_export icon-link\" title=\"Экспортирование источника\"><\/i><\/a> <i class=\"fa fa-trash-o source_del icon-link\" title=\"Удаление источника\"><\/i>"],["77f7d8e4-8146-4eb6-b078-34a16f24599f","ВЦМК \"Защита\"","15.04.2015 10:21:57","<i class=\"fa fa-pencil icon-link source_edit\" title=\"Редактирование источника\"><\/i> <i class=\"fa fa-exchange to_protocols icon-link\" title=\"Протоколы информационно-технического сопряжения\"><\/i> <a href=\"\/eif\/sources__export?id_source=77f7d8e4-8146-4eb6-b078-34a16f24599f\"><i class=\"fa fa-download source_export icon-link\" title=\"Экспортирование источника\"><\/i><\/a> <i class=\"fa fa-trash-o source_del icon-link\" title=\"Удаление источника\"><\/i>"],["5b9382d6-73dd-45cb-bf37-e6d23abe90a2","Росатом","24.04.2015 15:59:09","<i class=\"fa fa-pencil icon-link source_edit\" title=\"Редактирование источника\"><\/i> <i class=\"fa fa-exchange to_protocols icon-link\" title=\"Протоколы информационно-технического сопряжения\"><\/i> <a href=\"\/eif\/sources__export?id_source=5b9382d6-73dd-45cb-bf37-e6d23abe90a2\"><i class=\"fa fa-download source_export icon-link\" title=\"Экспортирование источника\"><\/i><\/a> <i class=\"fa fa-trash-o source_del icon-link\" title=\"Удаление источника\"><\/i>"],["8dfeabb9-aa47-4cab-8c94-e6f94133b3b2","Росводресурсы","30.04.2015 15:52:29","<i class=\"fa fa-pencil icon-link source_edit\" title=\"Редактирование источника\"><\/i> <i class=\"fa fa-exchange to_protocols icon-link\" title=\"Протоколы информационно-технического сопряжения\"><\/i> <a href=\"\/eif\/sources__export?id_source=8dfeabb9-aa47-4cab-8c94-e6f94133b3b2\"><i class=\"fa fa-download source_export icon-link\" title=\"Экспортирование источника\"><\/i><\/a> <i class=\"fa fa-trash-o source_del icon-link\" title=\"Удаление источника\"><\/i>"],["de6c9a68-393e-4ead-a11b-339af4ec43a2","Авиалесохрана","18.05.2015 18:53:40","<i class=\"fa fa-pencil icon-link source_edit\" title=\"Редактирование источника\"><\/i> <i class=\"fa fa-exchange to_protocols icon-link\" title=\"Протоколы информационно-технического сопряжения\"><\/i> <a href=\"\/eif\/sources__export?id_source=de6c9a68-393e-4ead-a11b-339af4ec43a2\"><i class=\"fa fa-download source_export icon-link\" title=\"Экспортирование источника\"><\/i><\/a> <i class=\"fa fa-trash-o source_del icon-link\" title=\"Удаление источника\"><\/i>"],["2299142d-d692-4e35-9eac-1ffb2376bb64","ФМБА","26.05.2015 12:24:23","<i class=\"fa fa-pencil icon-link source_edit\" title=\"Редактирование источника\"><\/i> <i class=\"fa fa-exchange to_protocols icon-link\" title=\"Протоколы информационно-технического сопряжения\"><\/i> <a href=\"\/eif\/sources__export?id_source=2299142d-d692-4e35-9eac-1ffb2376bb64\"><i class=\"fa fa-download source_export icon-link\" title=\"Экспортирование источника\"><\/i><\/a> <i class=\"fa fa-trash-o source_del icon-link\" title=\"Удаление источника\"><\/i>"],["e5d33e12-c0ea-4791-84d2-44a6d1ef058b","Ростуризм","18.06.2015 14:48:31","<i class=\"fa fa-pencil icon-link source_edit\" title=\"Редактирование источника\"><\/i> <i class=\"fa fa-exchange to_protocols icon-link\" title=\"Протоколы информационно-технического сопряжения\"><\/i> <a href=\"\/eif\/sources__export?id_source=e5d33e12-c0ea-4791-84d2-44a6d1ef058b\"><i class=\"fa fa-download source_export icon-link\" title=\"Экспортирование источника\"><\/i><\/a> <i class=\"fa fa-trash-o source_del icon-link\" title=\"Удаление источника\"><\/i>"],["64df6ecc-94c8-4fc5-87a1-0612016e45c3","Росморречфлот","25.06.2015 17:57:12","<i class=\"fa fa-pencil icon-link source_edit\" title=\"Редактирование источника\"><\/i> <i class=\"fa fa-exchange to_protocols icon-link\" title=\"Протоколы информационно-технического сопряжения\"><\/i> <a href=\"\/eif\/sources__export?id_source=64df6ecc-94c8-4fc5-87a1-0612016e45c3\"><i class=\"fa fa-download source_export icon-link\" title=\"Экспортирование источника\"><\/i><\/a> <i class=\"fa fa-trash-o source_del icon-link\" title=\"Удаление источника\"><\/i>"],["1156ec72-4a98-4cd2-a9a2-03c01f08f467","РЖД","22.09.2015 16:48:05","<i class=\"fa fa-pencil icon-link source_edit\" title=\"Редактирование источника\"><\/i> <i class=\"fa fa-exchange to_protocols icon-link\" title=\"Протоколы информационно-технического сопряжения\"><\/i> <a href=\"\/eif\/sources__export?id_source=1156ec72-4a98-4cd2-a9a2-03c01f08f467\"><i class=\"fa fa-download source_export icon-link\" title=\"Экспортирование источника\"><\/i><\/a> <i class=\"fa fa-trash-o source_del icon-link\" title=\"Удаление источника\"><\/i>"],["880476ae-4530-46a4-b192-78a573bf33d6","Ространснадзор","12.10.2015 14:01:30","<i class=\"fa fa-pencil icon-link source_edit\" title=\"Редактирование источника\"><\/i> <i class=\"fa fa-exchange to_protocols icon-link\" title=\"Протоколы информационно-технического сопряжения\"><\/i> <a href=\"\/eif\/sources__export?id_source=880476ae-4530-46a4-b192-78a573bf33d6\"><i class=\"fa fa-download source_export icon-link\" title=\"Экспортирование источника\"><\/i><\/a> <i class=\"fa fa-trash-o source_del icon-link\" title=\"Удаление источника\"><\/i>"]]}';
        
        // $r = new Response($respp);
        // $r->headers->set('Content-Type', 'application/json; charset=UTF-8');
        // return $r;
        return $this->createResponse($resp);
    }
    
    /**
     * @Route("/sources__get", name = "eif_sources__get")
     * 
     * Сервис получения источника
     */
    
    public function sources__get(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Получаем входные параметры
        $id_source = $this->checkUuid($request->query->get('id_source'));
        
        // Получаем запись из БД
        try {
            $em     = $this->getDoctrine()->getManager();
            $user   = $this->getUser();
            
            if ($this->isGranted('ROLE_FOIV')) {
                if ($user->getFoiv())
                    $source = $em->getRepository('NCUOEifBundle:Source')->findOneBy(array('id_source' => $id_source, 'foiv' => ($fr_obj = $user->getFoiv())));
                else             
                    $source = $em->getRepository('NCUOEifBundle:Source')->findOneBy(array('id_source' => $id_source, 'roiv' => ($fr_obj = $user->getRoiv())));                
            }
            else {
                $source = $em->getRepository('NCUOEifBundle:Source')->find($id_source);
            }
            
            if (!$source) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Источник не найден!';
                
                return $this->createResponse($resp);                 
            }            
            
            $foiv_roiv_flag = $source->getFoivRoivFlag();                
            if ($foiv_roiv_flag == 'FOIV') {
                if (!($obj = $source->getFoiv())) {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'У источника не задан ФОИВ!';

                    return $this->createResponse($resp);                    
                }
                
                $id_fr      = $obj->getId();
                $fr_name    = $obj->getName();
            }
            else if ($foiv_roiv_flag == 'ROIV') {
                if (!($obj = $source->getRoiv())) {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'У источника не задан РОИВ!';

                    return $this->createResponse($resp);                       
                }
                
                $id_fr      = $obj->getId();
                $fr_name    = $obj->getName();
            }

            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = array(
                'source_name' => $source->getSourceName(),
                'source_descr' => $source->getSourceDescr(),
                'foiv_roiv_flag' => $foiv_roiv_flag,
                'id_fr' => $id_fr,
                'fr_name' => $fr_name
            );
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
     * @Route("/sources__save", name = "eif_sources__save") 
     * 
     * Сервис сохранения источника
     */
    
    public function sources__save(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Проверка входных параметров
            $id_source = $request->request->get('id_source');            
            
            $source_name = trim($request->request->get('source_name'));
            if ($source_name == '') {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Не задано наименование источника!';
                
                return $this->createResponse($resp);
            }
            
            $source_descr = $request->request->get('source_descr');
            
            $foiv_roiv_flag = $request->request->get('foiv_roiv_flag');
            if (!($foiv_roiv_flag == 'NONE' || $foiv_roiv_flag == 'FOIV' || $foiv_roiv_flag == 'ROIV')) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Неверно задана привязка к ФОИВ/РОИВ!';
                
                return $this->createResponse($resp);                
            }
            if ($foiv_roiv_flag == 'FOIV') {
                if (!($objFR = $em->getRepository('NCUOFoivBundle:Foiv')->find($request->request->get('id_fr')))) {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'Неверно задан ФОИВ!';

                    return $this->createResponse($resp);                     
                }
            } else if ($foiv_roiv_flag == 'ROIV') {
                if (!($objFR = $em->getRepository('NCUOFoivBundle:Roiv')->find($request->request->get('id_fr')))) {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'Неверно задан РОИВ!';

                    return $this->createResponse($resp);                     
                }                
            }            
            
            // Сохранение
            if ($id_source == -1) {
                // Создание источника
                $source = new Source();
                $source->setSourceName($source_name);
                $source->setSourceDescr($source_descr);
                $source->setSourceCreateDate(new \DateTime('now'));
                
                // Связка ФОИВ/РОИВ                                
                $source->setFoivRoivFlag($foiv_roiv_flag);
                if ($foiv_roiv_flag == 'FOIV')
                    $source->setFoiv($objFR);
                else if ($foiv_roiv_flag == 'ROIV')
                    $source->setRoiv($objFR);
                    
                $em->persist($source);
                $em->flush();
                
                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = 'Источник успешно создан!';
            } else {
                // Редактирование источника
                if (!($source = $em->getRepository('NCUOEifBundle:Source')->find($this->checkUuid($id_source)))) {
                    $resp['err_id'] = 1;
                    $resp['err_msg'] = 'Service error';
                    $resp['content'] = 'Источник не найден!';                    
                } else {
                    $source->setSourceName($source_name);
                    $source->setSourceDescr($source_descr);  
                
                    // Связка ФОИВ/РОИВ
                    $source->setFoivRoivFlag($foiv_roiv_flag);
                    if ($foiv_roiv_flag == 'FOIV') {
                        $source->setFoiv($objFR);
                        $source->setRoiv(null);
                    }
                    else if ($foiv_roiv_flag == 'ROIV') {
                        $source->setRoiv($objFR);
                        $source->setFoiv(null);
                    }
                    else if ($foiv_roiv_flag == 'NONE') {
                        $source->setFoiv(null);
                        $source->setRoiv(null);
                    }
                    
                    $em->flush();
                    
                    $resp['err_id'] = 0;
                    $resp['err_msg'] = 'Ok';
                    $resp['content'] = 'Источник успешно обновлен!';
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
     * @Route("/sources__delete", name = "eif_sources__delete") 
     * 
     * Сервис удаления источника
     */    
    
    public function sources__delete(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);
        
        // Удаляем запись из БД
        try {
            $em = $this->getDoctrine()->getManager();
            
            if (!($source = $em->getRepository('NCUOEifBundle:Source')->find($this->checkUuid($request->request->get('id_source'))))) {
                $resp['err_id'] = 1;
                $resp['err_msg'] = 'Service error';
                $resp['content'] = 'Источник не найден!';
            } else {            
                $em->remove($source);
                $em->flush();
                
                $resp['err_id'] = 0;
                $resp['err_msg'] = 'Ok';
                $resp['content'] = 'Источник успешно удален!';                
            }
        } catch(\Exception $ex) {           
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            

            if (strpos($ex->getMessage(), 'fk_protocols_id_source') !== false)
                $resp['content'] = 'Невозможно удалить источник, т.к. по нему есть протоколы информационно-технического сопряжения!';
            else    
                $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
            
        return $this->createResponse($resp);        
    } 

    /**
     * @Route("/sources__export", name = "eif_sources__export") 
     * 
     * Сервис экспорта структуры источника в файл
     */
    
    public function sources__export(Request $request) {
		// Ответ на случай ошибки
        $resp = new Response('Ошибка загрузки файла!');
		$resp->headers->set('Content-Type', 'text/plain');
		$resp->headers->set('Content-Transfer-Encoding', 'binary');
		$resp->headers->set('Pragma', 'no-cache');	
		$resp->headers->set('Content-Disposition', 'attachment; filename="Ошибка.txt"');
        
        try {
            $em = $this->getDoctrine()->getManager();                    
            
            if ($source = $em->getRepository('NCUOEifBundle:Source')->find($this->checkUuid($request->query->get('id_source')))) {
				// Выгружаем информацию по источнику
				$src_struct = [
					'id_source' => $source->getIdSource(),
					'source_name' => $source->getSourceName(),
					'source_descr' => $source->getSourceDescr(),
					'source_create_date' => $source->getSourceCreateDate()->format('d.m.Y H:i:s'),
					'foiv_roiv_flag' => $source->getFoivRoivFlag()
				];
				
				if ($source->getFoivRoivFlag() == 'FOIV')
					$src_struct['foiv'] = $source->getFoiv()->getId();
				else if ($source->getFoivRoivFlag() == 'ROIV')
					$src_struct['roiv'] = $source->getRoiv()->getId();
				
				// Выгружаем информацию по протоколам источника
				$src_struct['protocols'] = [];
				foreach($source->getProtocols() as $protocol) {
					$prot_struct = [
						'id_protocol' => $protocol->getIdProtocol(),
						'protocol_name' => $protocol->getProtocolName(),
						'protocol_descr' => $protocol->getProtocolDescr(),
						'protocol_file_mime_type' => $protocol->getProtocolFileMimeType(),
						'protocol_sign_date' => $protocol->getProtocolSignDate()->format('d.m.Y H:i:s'),
						'protocol_doc' => $protocol->getProtocolDoc(),
						'protocol_xml_xsd' => $protocol->getProtocolXmlXsd(),
						'enable_migration' => $protocol->getEnableMigration()
					];
					
					// Выгружаем информацию по формам протокола
					$prot_struct['forms'] = [];
					foreach($protocol->getForms() as $form) {
						$form_struct = [
							'id_form' => $form->getIdForm(),
							'form_name' => $form->getFormName(),
							'form_descr' => $form->getFormDescr(),
							'form_create_date' => $form->getFormCreateDate()->format('d.m.Y H:i:s'),
							'xml_xslt_protocol_file_import' => $form->getXmlXsltProtocolFileImport(),
							'data_act_control_interval' => $form->getDataActControlInterval()
						];						
						
						// Выгружаем информацию по полям формы
						$form_struct['fields'] = [];
						foreach($form->getFormFields() as $field) {
							$field_struct = [
								'id_field' => $field->getIdField(),
								'field_name' => $field->getFieldName(),
								'field_pos' => $field->getFieldPos(),
								'id_datatype' => $field->getDatatype()->getIdDatatype(),
								'key_flag' => $field->getKeyFlag()
							];							

							//
							$form_struct['fields'][] = $field_struct;
						}
						
						//
						$prot_struct['forms'][] = $form_struct;
					}
					
					//
					$src_struct['protocols'][] = $prot_struct;
				}
			
				// Формируем ответ
                $resp = new Response(json_encode($src_struct, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                $resp->headers->set('Content-Type', 'application/json');
                $resp->headers->set('Content-Transfer-Encoding', 'binary');
                $resp->headers->set('Pragma', 'no-cache');
                $resp->headers->set('Content-Disposition', sprintf('attachment; filename="%s.json"', addslashes($source->getSourceName())));
            }                            
        } catch(\Exception $ex) {
            // Логируем ошибку
            $this->log_e($ex);                        
        }
        
        return $resp;
    }  	
	
    /**
     * @Route("/sources__import", name = "eif_sources__import") 
     * 
     * Сервис импорта структуры источника из файла
     */	
	 
    public function sources__import(Request $request) {
        $resp = array('err_id' => NULL, 'err_msg' => NULL, 'content' => NULL);

        try {  
            $em = $this->getDoctrine()->getManager();
            
            // Получаем файл структуры источника
            $upl_file = $request->files->get('source_file');        
            if (is_null($upl_file)) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Не найден загруженный файл!';
                
                return $this->createResponse($resp);                
            }    
            
			// Получаем сохраненную структуру
			$src_struct = json_decode(file_get_contents($upl_file->getRealPath()), true);
			if (json_last_error() != JSON_ERROR_NONE) {
                $resp['err_id']  = 1;
                $resp['err_msg'] = 'Service error';            
                $resp['content'] = 'Ошибка загрузки файла для импорта источника!';
                
                return $this->createResponse($resp); 				
			}
			
			// Репозитории
			$rep_source 	= $em->getRepository('NCUOEifBundle:Source');
			$rep_protocol 	= $em->getRepository('NCUOEifBundle:Protocol');
			$rep_form 		= $em->getRepository('NCUOEifBundle:Form');
			$rep_form_field = $em->getRepository('NCUOEifBundle:FormField');
			$rep_dt 		= $em->getRepository('NCUOEifBundle:DataType');			
			
			// Создаем или находим источник
			if (!($source = $rep_source->find($this->checkUuid($src_struct['id_source'])))) {
				$source = new Source();
				// !!! Меняем тип генератора ключа - на назначаемый вручную
				$em->getClassMetaData(get_class($source))->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
				//
				
				$source->setIdSource($src_struct['id_source']);
				$source->setSourceName($src_struct['source_name']);
				$source->setSourceDescr($src_struct['source_descr']);
				$source->setSourceCreateDate(\DateTime::createFromFormat('d.m.Y H:i:s', $src_struct['source_create_date']));
				$source->setFoivRoivFlag($src_struct['foiv_roiv_flag']);
				
				if ($src_struct['foiv_roiv_flag'] == 'FOIV') {
					if ($foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($src_struct['foiv']))
						$source->setFoiv($foiv);
					else
						$source->setFoivRoivFlag('NONE');
				} else if ($src_struct['foiv_roiv_flag'] == 'ROIV') {
					if ($roiv = $em->getRepository('NCUOFoivBundle:Roiv')->find($src_struct['roiv']))
						$source->setRoiv($roiv);
					else
						$source->setFoivRoivFlag('NONE');			
				}
				$em->persist($source);
				$em->flush();
			}
			
			// Создаем или находим протоколы источника
			foreach($src_struct['protocols'] as $p) {
				if (!($protocol = $rep_protocol->find($this->checkUuid($p['id_protocol'])))) {
					$protocol = new Protocol();				
					$em->getClassMetaData(get_class($protocol))->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
					
					$protocol->setIdProtocol($p['id_protocol']);
					$protocol->setSource($source);
					$protocol->setProtocolName($p['protocol_name']);
					$protocol->setProtocolDescr($p['protocol_descr']);
					$protocol->setProtocolFileMimeType($p['protocol_file_mime_type']);
					$protocol->setProtocolSignDate(\DateTime::createFromFormat('d.m.Y H:i:s', $p['protocol_sign_date']));
					$protocol->setProtocolDoc($p['protocol_doc']);
					$protocol->setProtocolXmlXsd($p['protocol_xml_xsd']);
					$protocol->setEnableMigration($p['enable_migration']);
					
					$em->persist($protocol);
					$em->flush();	
				}

				// Создаем или находим формы протокола								
				foreach($p['forms'] as $f) {
					$created = false;
					
					if (!($form = $rep_form->find($this->checkUuid($f['id_form'])))) {
						$created = true;
						
						$form = new Form();
						$em->getClassMetaData(get_class($form))->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
						
						$form->setIdForm($f['id_form']);
						$form->setProtocol($protocol);
						$form->setFormName($f['form_name']);
						$form->setFormDescr($f['form_descr']);
						$form->setFormCreateDate(\DateTime::createFromFormat('d.m.Y H:i:s', $f['form_create_date']));
						$form->setXmlXsltProtocolFileImport($f['xml_xslt_protocol_file_import']);
						$form->setDataActControlInterval($f['data_act_control_interval']);
						
						$em->persist($form);
						$em->flush();	
					}
					
					// Создаем или находим поля формы										
					foreach($f['fields'] as $ff) {
						if (!($field = $rep_form_field->find($this->checkUuid($ff['id_field'])))) {
							$created = true;
							
							$field = new FormField();
							$em->getClassMetaData(get_class($field))->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
							
							$field->setIdField($ff['id_field']);
							$field->setForm($form);
							$field->setFieldName($ff['field_name']);
							$field->setFieldPos($ff['field_pos']);
							$field->setDatatype($rep_dt->find($ff['id_datatype']));
							$field->setKeyFlag($ff['key_flag']);
							
							$em->persist($field);
							$em->flush();	
						}							
					}
					
					// Пересоздаем физическую таблицу
					if ($created) {
						$rep_form->dropDBTable($form);
						$rep_form->createDBTable($form);
					}
				}
			}
						
            // Удаляем загруженный файл
            unlink($upl_file->getRealPath());

            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = 'Источник успешно импортированs!';
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