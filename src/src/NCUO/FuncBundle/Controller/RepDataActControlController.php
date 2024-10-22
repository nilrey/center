<?php

namespace App\NCUO\FuncBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\EifBundle\Controller\BaseController;

/**
 * Класс контроллера отчета "Контроль актуальности данных"
 * #######################################################
 * 
 */

class RepDataActControlController extends BaseController {
 
    /**
     * @Route("/rep_data_act_control", name = "func_rep_data_act_control")
     * @Template("ncuofunc/new_rep_data_act_control.html.twig")
     * 
     * Страница отчета
     */
    
    public function rep_data_act_control(Request $request) {
        // Формируем контент страницы
        $context = [];
        $context['msg_service_error'] = 'Ошибка: некорректные данные'; //$this->container->getParameter('ncuofunc.msg.service_error');                    
        
        $context['report_name'] = 'Контроль актуальности данных';
        
        // Результирующий массив
        return $context;
    }
    
    /**
     * @Route("/rep_data_act_control__get_data", name = "func_rep_data_act_control__get_data")
     *
     * Сервис получения данных для отчета
     */
    
    public function rep_data_act_control__get_data(Request $request) {
        $resp = ['err_id' => NULL, 'err_msg' => NULL, 'content' => NULL];
    
        /*
        // Запрашиваем параметры в БД
        try {
            $em = $this->getDoctrine()->getManager();
            
            // Получаем информацию по актуальности данных
            $act_info = $em->getRepository('NCUOEifBundle:Form')->getDataActControlInfo();
            
            $tbl_info = '';
            foreach($act_info as $info) {
                if ($info['form_act_flag'] == 'Нет')
                    $clr = '#d9534f';
                else
                    $clr = '#5cb85c';
                
		$prot_url = $this->generateUrl('eif_protocols');
		$form_url = $this->generateUrl('eif_forms');
                
                $tbl_info .=
                "                
                <tr>
                    <td><a href=\"" . $prot_url . "?id_source={$info['id_source']}\" title=\"Перейти к протоколам источника\">{$info['source_name']}</a></td>
                    <td><a href=\"" . $form_url . "?id_protocol={$info['id_protocol']}\" title=\"Перейти к формам протокола\">{$info['protocol_name']}</a></td>
                    <td>{$info['form_name']}</td>
                    <td class=\"text-center\" style=\"background: {$clr};\">{$info['form_act_flag']}</td>
                </tr>
                "
                ;
            }
            
            $resp['err_id'] = 0;
            $resp['err_msg'] = 'Ok';
            $resp['content'] = $tbl_info;            
        } catch(\Exception $ex) {
            // Логируем ошибку
            $err_log_id = $this->log_e($ex);            
            
            $resp['err_id']  = 1;
            $resp['err_msg'] = 'Service error';            
            $resp['content'] = sprintf($this->container->getParameter('ncuofunc.msg.exception'), $err_log_id);                        
        }
        */

        $conn = $this->getDoctrine()->getManager()->getConnection();
        //getDataActControlInfo
/*        
        $stmt = $conn->prepare(
            "
            select
                  s.id_source
                , s.source_name
                , p.id_protocol
                , p.protocol_name
                , f.id_form
                , f.form_name
                /*
                , f.data_act_control_interval
                , f_dt.last_form_date
                , f_dt.last_form_date + interval '1 minute' * f.data_act_control_interval
                * /
                , case
                    when f.data_act_control_interval is null then 'Нет'
                    else
                        case
                            when f.data_act_control_interval = 0 then 'Да'
                            else
                                case
                                    when coalesce(f_dt.last_form_date, to_timestamp('01.01.1970 00:00:00', 'DD.MM.YYYY HH24:MI:SS')) + interval '1 minute' * f.data_act_control_interval < now()::timestamp(0) then 'Нет'
                                    else 'Да'
                                end
                        end
                  end as form_act_flag
            from
                  eif.sources   s
                  left join eif.protocols p on p.id_source = s.id_source
                  left join eif.forms f on f.id_protocol = p.id_protocol
                  left join
                  (
                    -- Получаем идентификатор формы с последней датой загрузки в нее
                    select
                          id_form
                        , file_upload_date as last_form_date
                    from
                        (
                            select
                                  l.id_form
                                , f.file_upload_date
                                , row_number() over (partition by l.id_form order by f.file_upload_date desc) as rn
                            from
                                  eif.files_forms_import_log l
                                , eif.files f
                            where
                                l.id_file = f.id_file
                        ) t1
                    where
                        t1.rn = 1	  
                  ) f_dt on f.id_form = f_dt.id_form
            order by
                  s.source_create_date
                , p.protocol_sign_date
                , p.protocol_name
                , f.form_create_date
            "
            );
            $stmt->execute();

        $arItems = $stmt->fetchAll();
*/        
        
        $conn_string = "host=".$_ENV['EIF_HOST']." port=".$_ENV['EIF_PORT']." dbname=".$_ENV['EIF_DBNAME']." user=".$_ENV['EIF_USER']." password=".$_ENV['EIF_PASSWORD']."";
        $conn = pg_connect($conn_string);
        if( !$conn ) die("Connection fail \n");

        $sql = 
            "
            select
                  s.id_source
                , s.source_name
                , p.id_protocol
                , p.protocol_name
                , f.id_form
                , f.form_name
                /*
                , f.data_act_control_interval
                , f_dt.last_form_date
                , f_dt.last_form_date + interval '1 minute' * f.data_act_control_interval
                */
                , case
                    when f.data_act_control_interval is null then 'Нет'
                    else
                        case
                            when f.data_act_control_interval = 0 then 'Да'
                            else
                                case
                                    when coalesce(f_dt.last_form_date, to_timestamp('01.01.1970 00:00:00', 'DD.MM.YYYY HH24:MI:SS')) + interval '1 minute' * f.data_act_control_interval < now()::timestamp(0) then 'Нет'
                                    else 'Да'
                                end
                        end
                  end as form_act_flag
            from
                  eif.sources   s
                  left join eif.protocols p on p.id_source = s.id_source
                  left join eif.forms f on f.id_protocol = p.id_protocol
                  left join
                  (
                    -- Получаем идентификатор формы с последней датой загрузки в нее
                    select
                          id_form
                        , file_upload_date as last_form_date
                    from
                        (
                            select
                                  l.id_form
                                , f.file_upload_date
                                , row_number() over (partition by l.id_form order by f.file_upload_date desc) as rn
                            from
                                  eif.files_forms_import_log l
                                , eif.files f
                            where
                                l.id_file = f.id_file
                        ) t1
                    where
                        t1.rn = 1     
                  ) f_dt on f.id_form = f_dt.id_form
            order by
                  s.source_create_date
                , p.protocol_sign_date
                , p.protocol_name
                , f.form_create_date
            ";

        $res = pg_query($conn, $sql );
        $arItems = array();

        while ($row = pg_fetch_row($res)) {
            $arTmp = array(
                'id_source' => $row[0],
                'source_name' => $row[1],
                'id_protocol' => $row[2],
                'protocol_name' => $row[3],
                'id_form' => $row[4],
                'form_name' => $row[5],
                'form_act_flag' => $row[6],
            );
            $arItems[] = $arTmp;
        }
        
        $tbl_info = '';
        foreach($arItems as $info) {
            if ($info['form_act_flag'] == 'Нет')
                $clr = '#d9534f';
            else
                $clr = '#5cb85c';
            
            $prot_url = $this->generateUrl('eif_protocols');
            $form_url = $this->generateUrl('eif_forms');
            
            $tbl_info .=
            "                
            <tr>
                <td>{$info['source_name']}</td>
                <td>{$info['protocol_name']}</td>
                <td>{$info['form_name']}</td>
                <td class=\"text-center\" style=\"background: {$clr};\">{$info['form_act_flag']}</td>
            </tr>
            "
            ;
        }
        
        $resp['err_id'] = 0;
        $resp['err_msg'] = 'Ok';
        $resp['content'] = $tbl_info;     


        return $this->createResponse($resp);
    }
    
}
