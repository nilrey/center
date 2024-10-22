<?php

namespace App\NCUO\EifBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use App\NCUO\EifBundle\Controller\DefaultController;
use App\NCUO\EifBundle\Entity\File;

/**
 * Класс контроллера импорта файлов в формы данных
 * ###############################################
 */

class FilesImportController extends BaseController {
    
    /**
     * @Route("/files__import", name = "eif_files__import")
     * 
     * Сервис импорт файлов в формы данных
     */
    
    public function files__import(Request $request) {
        $resp = ['err_id' => NULL, 'err_msg' => NULL, 'content' => NULL];
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            $rep_form           = $em->getRepository('NCUOEifBundle:Form');
            $rep_form_fields    = $em->getRepository('NCUOEifBundle:FormField');
            $rep_file_status    = $em->getRepository('NCUOEifBundle:FileStatus');
            
            $file_status_err    = $rep_file_status->findOneBy(['status_name' => 'Ошибка']);
            $file_status_imp    = $rep_file_status->findOneBy(['status_name' => 'Импортирование']);
            $file_status_imp_s  = $rep_file_status->findOneBy(['status_name' => 'Импортирован в формы данных']);
            $file_status_imp_q  = $rep_file_status->findOneBy(['status_name' => 'В очереди на импорт']);
            
            // Массив XSLT процессоров по форме
            $arr_xslt_proc = [];
            
            // Получаем список файлов в статусе "В очереди на импорт"
            $file_list = $em->getRepository('NCUOEifBundle:File')->getFileListBy_Status($file_status_imp_q);
            
            $cnt_imported = 0;
            $cnt_error = 0;
            
            foreach($file_list as $file) {                
                // Если у файла статус "Импортирование" или "Импортирован в формы данных", то файл не обрабатываем
                // (если другой поток успел изменить статус файла)
                if (in_array($file->getFileStatus()->getStatusName(), ['Импортирование', 'Импортирован в формы данных']))
                    continue;
                
                // Устанавливаем для файла статус "Импортирование"
                $file->setFileStatus($file_status_imp);
                $file->setStatusMsg(null);
                $em->flush();                

                // Получаем протокол
                if (!$protocol = $file->getProtocol()) {
                    $file->setFileStatus($file_status_err);
                    $file->setStatusMsg('Не найден протокол информационно-технического сопряжения для файла. Импортирование в БД ЕИФ невозможно');
                    $em->flush();
                    ++$cnt_error;
                    
                    continue;   // Переходим к следующему файлу                    
                }

                // Получаем XSD, заданную в протоколе
                $protocol_xml_xsd = $protocol->getProtocolXmlXsd();
                
                // Идентификатор файла
                $id_file = $file->getIdFile();                
                                
                // Загружаем файл
                libxml_use_internal_errors(true);
                
                $xml = new \DOMDocument();
				$file_content = implode(gzfile($this->container->getParameter('ncuoeif.file_upload_dir') . '/' . $id_file . '.gz'));
                if (!$xml->loadXML($file_content)) {
					file_put_contents($this->container->getParameter('ncuoeif.file_upload_dir') . '/' . $id_file . '_err1', $file_content);
					$xml->save($this->container->getParameter('ncuoeif.file_upload_dir') . '/' . $id_file . '_err2');
				
					$str_msg = "Ошибка загрузки исходного XML документа.\n";
                    foreach(libxml_get_errors() as $err) {
                        $str_msg .= sprintf(" [%d:%d] => %s\n", $err->line, $err->column, $err->message);
                    }				
                    $str_msg .= 'Импортирование в БД ЕИФ невозможно';
                    libxml_clear_errors();				
				
                    $file->setFileStatus($file_status_err);
                    $file->setStatusMsg($str_msg);
                    $em->flush();
                    ++$cnt_error;

                    continue; // Переходим к следующему файлу
                }        
                
                // Если XSD задан, то валидируем файл. Иначе нет.
                if (($protocol_xml_xsd != NULL) && (!$xml->schemaValidateSource(base64_decode(explode(';', $protocol_xml_xsd)[2])))) {
                    $str_msg = "Ошибка валидации исходного XML документа по файлу XSD протокола информационно-технического сопряжения.\n";                    
                    foreach(libxml_get_errors() as $err) {
                        $str_msg .= sprintf(" [%d:%d] => %s\n", $err->line, $err->column, $err->message);
                    }
                    $str_msg .= 'Импортирование в БД ЕИФ невозможно';
                    libxml_clear_errors();
                    
                    $file->setFileStatus($file_status_err);
                    $file->setStatusMsg($str_msg);
                    $em->flush();
                    ++$cnt_error;
                    
                    continue; // Переходим к следующему файлу                    
                }
                        
                // Получаем список форм протокола
                $forms = $protocol->getForms();            
                if (count($forms) == 0) {
                    $file->setFileStatus($file_status_err);
                    $file->setStatusMsg('У протокола информационно-технического сопряжения не задана ни одна форма. Импортирование в БД ЕИФ невозможно');
                    $em->flush();
                    ++$cnt_error;
                    
                    continue;   // Переходим к следующему файлу
                }
                                
                
                // Проверяем наличие у форм протокола XSLT преобразования
                // А также по форме сохраняем XSLT процессор по преобразованию (чтобы для нескольких файлов не создавать одно и тоже)
                foreach($forms as $f) {
                    if (array_key_exists($f->getIdForm(), $arr_xslt_proc))
                        continue;   // Пропускаем, т.к. данная форма уже проверялась в одном из предыдущих файлов

                    // Загружаем XSLT документ
                    $xslt = new \DOMDocument();
                    if (!$xslt->loadXML(base64_decode(explode(';', $f->getXmlXsltProtocolFileImport())[2]))) {
                        $str_msg = sprintf("Ошибка загрузки XSLT преобразования формы \"%s\".\n", $f->getFormName());
                        foreach(libxml_get_errors() as $err) {
                            $str_msg .= " [{$err->line}:{$err->column}] => {$err->message}\n";
                        }
                        $str_msg .= 'Импортирование в БД ЕИФ невозможно';
                        libxml_clear_errors();                         
                        
                        $file->setFileStatus($file_status_err);
                        $file->setStatusMsg($str_msg);
                        $em->flush();
                        ++$cnt_error;
                        
                        continue 2; // Переходим к следующему файлу
                    }
                    
                    // Создаем и сохраняем XSLT процессор
                    $arr_xslt_proc[$f->getIdForm()] = new \XSLTProcessor();
                    if (!$arr_xslt_proc[$f->getIdForm()]->importStylesheet($xslt)) {
                        $str_msg = "Ошибка инициализации XSLT преобразования формы \"{$f->getFormName()}\".\n";
                        foreach(libxml_get_errors() as $err) {
                            $str_msg .= " [{$err->line}:{$err->column}] => {$err->message}\n";
                        }
                        $str_msg .= 'Импортирование в БД ЕИФ невозможно';
                        libxml_clear_errors();                                                 
                        
                        $file->setFileStatus($file_status_err);
                        $file->setStatusMsg($str_msg);
                        $em->flush();
                        ++$cnt_error;
                        
                        continue 2; // Переходим к следующему файлу
                    }   
                }                                  
                
                try {                                                           // Заключаем в дополн. try-catch, чтобы иметь возможность при ошибке, установить корректный статус файла
                    $arr_cmds = [];    // Массив команд вставки по всем формам

                    /**
                     * Для каждой формы протокола
                     */
                    foreach($forms as $form) {
                        $id_form = $form->getIdForm();
                        
                        // Выполняем XSLT преобразование
                        $xslt_content = '';
                        if (false === ($xslt_content = $arr_xslt_proc[$id_form]->transformToXml($xml))) {
                            $str_msg = "Ошибка выполнения XSLT преобразования формы \"{$f->getFormName()}\".\n";
                            foreach(libxml_get_errors() as $err) {
                                $str_msg .= " [{$err->line}:{$err->column}] => {$err->message}\n";
                            }
                            $str_msg .= 'Импортирование в БД ЕИФ невозможно';
                            libxml_clear_errors();                             
                            
                            $file->setFileStatus($file_status_err);
                            $file->setStatusMsg($str_msg);
                            $em->flush();
                            ++$cnt_error;

                            continue 2; // Переходим к следующему файлу
                        }
                        
                        // Анализируем полученнуб строку данных и формируем команды вставки данных
                       
                        /**
                         * Структура даных после XSLT преобразования
                         * <...>$#$-PART-$#$<...>$#$-PART-$#$<...>$#$-PART-$#$<...>$#$-BLOCK-$#$
                         */

                        // Формируем команду вставки
                        $fields_cnt = $rep_form_fields->getFieldsCnt($form);

                        $cmd_fields = "INSERT INTO eif_forms_data.\"_{$id_form}\"(id_row, id_file, del_flag";
                        for($i = 1; $i <= $fields_cnt; $i++)
                            $cmd_fields .= ", f{$i}";
                        $cmd_fields .= ', num_row, migration_flag) VALUES ';                                

                        $arr_cmd_vals = [];  
                        $num_row = 0;

                        // Считываем из XSLT строки по-блочно $#$-BLOCK-$#$
                        
                        /* 
                        $pos_e = -1;
                        $pos_s = 0;
                        while(($pos_e = strpos($xslt_content, '$#$-BLOCK-$#$', $pos_s)) !== false) {
                            $block = substr($xslt_content, $pos_s, $pos_e - $pos_s);
                            $pos_s = $pos_e + 13;
                          
                            $parts = explode('$#$-PART-$#$', $block);
                            ...
                        }
                        $xslt_content = null;
                        */
                        
                        $blocks = explode('$#$-BLOCK-$#$', $xslt_content);      // Оставим пока вариант с explode, вариант с обработкой substr выше в комментариях
                        $xslt_content = null;
                        foreach($blocks as $block) {
                            if ($block == '')
                                continue;
                            
                            // Получаем части блока
                            $parts = explode('$#$-PART-$#$', $block);
                            if (count($parts) != $fields_cnt + 1) {     // К кол-ву полей добавляем поле флага удаления (который также приходит из XSLT)
                                $file->setFileStatus($file_status_err);
                                $file->setStatusMsg("Количество полей XSLT преобразования формы \"{$form->getFormName()}\" не совпадает с количеством полей самой формы. Импортирование в БД ЕИФ невозможно");
                                $em->flush();
                                ++$cnt_error;

                                continue 3; // Переходим к следующему файлу                                
                            }   
                            
                            $cmd_val = "(public.uuid_generate_v4(), '{$id_file}'";
                            foreach($parts as $part)
                                $cmd_val .= sprintf(', \'%s\'', str_replace('\'', '\'\'', $part));                            
                            $cmd_val .= sprintf(', %d, 0)', ++$num_row);
                            
                            $arr_cmd_vals[] = $cmd_val;
                        }
                        $block = null;                        
                        
                        // Если по результатам обработки XSLT есть команды, то сохраняем
                        if (count($arr_cmd_vals) > 0) {
                            $arr_cmds[] = ['ins_cmd' => $cmd_fields, 'arr_vals' => $arr_cmd_vals];
                            // Добавляем команду вставки в лог
                            $arr_cmds[] = [
                                'ins_cmd' => 'INSERT INTO eif.files_forms_import_log(id_file, id_form, import_date) VALUES ',
                                'arr_vals' => ["('{$id_file}', '{$id_form}', now()::timestamp(0))"]
                            ];
                        }

                    } // forms                       

                    // Если по всем формам преобразование ничего не вернуло, то ставим ошибочный статус
                    if (count($arr_cmds) == 0) {
                        // Иначе выдаем ошибку
                        $file->setFileStatus($file_status_err);
                        $file->setStatusMsg('Пустой результат XSLT преобразования по всем формам. Импортирование в БД ЕИФ невозможно');
                        $em->flush();
                        ++$cnt_error;

                        continue; // Переходим к следующему файлу                                             
                    }

                    // Выполняем итоговую вставку во все формы 
                    $rep_form->insertToDBTable($arr_cmds);
                    $arr_cmds = null;

                    // Устанавливаем для файла статус "Импортирован"
                    $file->setFileStatus($file_status_imp_s);
                    $file->setStatusMsg(null);
                    $em->flush();
                    ++$cnt_imported;
                } catch(\Exception $e) {
                    $this->log_e($e);
                    
                    // Устанавливаем ошибочный статус файла
                    $file->setFileStatus($file_status_err);
                    $file->setStatusMsg('Внутренняя ошибка сервиса');
                    $em->flush();
                    ++$cnt_error;
                }
            } // files
            
            libxml_use_internal_errors(false);
            
            //
            $resp['err_id']  = 0;
            $resp['err_msg'] = 'Ok';
            
            $s_res = "Сервис импортирования файлов завершил работу. Обработано файлов успешно - {$cnt_imported}, с ошибкой - ${cnt_error}";
            $this->get('logger')->debug($s_res);
            
            $resp['content'] = $s_res;
        } catch (\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = sprintf($this->container->getParameter('ncuoeif.msg.exception'), $err_log_id);            
        }
        
        // Очищаем массив XSLT процессоров
        $arr_xslt_proc = null;
        
        return $this->createResponse($resp);        
    }
}