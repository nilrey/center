<?php

namespace App\NCUO\WebmailBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Doctrine\ORM\EntityRepository;
// use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
// use Symfony\Component\Routing\Generator\UrlGenerator;

use App\NCUO\PortalBundle\Entity\User;


/**
 * Webmail controller.
 */
class WebmailController extends WebmailBaseController
{
    
    public $logger;
    public $userRoleId = null;
    public $userRoleName = null;
    public $idOiv = null;
    public $parentUrl = 'webmail';
    public $security = null;
    public $session = null;
    public $request = null;
    public $isAccessGranted = null;
    public $isEditGranted = false;
    // public $rolePerms = array();
    public $extraPerms = array();
    public $arPriorityRoles = array('ROLE_ROIV', 'ROLE_FOIV', 'ROLE_VDL', 'ROLE_NCUO');
    public $upload_path = '/public/webmail/upload/';
    public $attach_path = '/public/webmail/attachments/';
    public $attach_path_tmp = '/public/webmail/tmpsend/';
    public $loggedInUser = null;
    public $dbconn = null;


    /** 
     * Lists all Webmail entities.
     *
     * @Route("/", name="webmail")
     * @Method("GET")
     * @Template("ncuowebmail/webmail/index.html.twig")
     */
    public function indexAction(Request $request)
    {

        $emails_list = array();
        if ($request->request->get("FIELDS")){
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = $value;
            }
            if(isset($arData['deleteEmail']) && is_array($arData['deleteEmail'])){
                $this->setMessageError("Начало удаления списка сообщений");
                // идем по списку хешей писем
                foreach ($arData['deleteEmail'] as $key => $hash) {
                    $status = 'trash';
                    // в разделе Полученные сначала обращаемся к таблице webmail_msg_recieved, если такая запись существует в привязке к текущему пользователю
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
                    if( !empty($db_params[0]['rec_id'])){
                        $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => $status ), 'upd_msg_recieved_status');
                        // в разделе Отправленные сначала обращаемся к таблице webmail_msg_sent проверим если такая запись существует в привязке к текущему пользователю (случай когда письмо послано самому себе, т.е пользователь является отправителем и одним из получателей), т.о. статус удаления нужно синхронизировать в записях обеих таблиц
                        $db_params = $this->constructSelect(array('hash'=>$hash, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
                        if(count($db_params)> 0 ){
                            // если запись существует в таблице webmail_msg_sent, то синхронизируем статус удаления
                            $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => $status ), 'upd_msg_sent_status');
                        }
                        $this->setMessageSuccess("Выбранные письма успешно удалены");
                    }else{
                        $this->setMessageError("Ошибка удаления сообщения с кодом '{$message_id}'");
                    }
                    /*
                    // Данный блок отвечает за полное удаление письма с почтового сервера, функционал решено не задействовать. Возможно будет применен для удаления письма из Корзины
                    $res = $this->deleteEmail($message_id);
                    if($res[0]['status'] == 'Error'){
                        $this->setMessageError("Ошибка удаления сообщения с кодом {$message_id}");
                        break;
                    }else{
                        $this->setMessageSuccess("Выбранные письма успешно удалены");
                    }*/
                }
            }
            // обновление о важности писем
            if(isset($arData['important']) && is_array($arData['important']) && count($arData['important']) > 0 ){
                if($arData['isImportant'] == 'imp') $isImportant = 'True';
                else $isImportant = 'False';

                // идем по списку хешей писем, пока в списке всегда 1 элемент
                foreach ($arData['important'] as $hash) {
                    // в разделе Полученные сначала обращаемся к таблице webmail_msg_recieved если такая запись существует в привязке к текущему пользователю
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
                    // вносим обновление о важности
                    $this->constructUpdate( array( 'important' => $isImportant, 'id' => $db_params[0]['rec_id'] ), 'upd_msg_recieved_important');
                    
                    // в разделе Отправленные сначала обращаемся к таблице webmail_msg_sent проверим если такая запись существует в привязке к текущему пользователю (случай когда письмо послано самому себе, т.е пользователь является отправителем и одним из получателей), т.о. статус важности нужно синхронизировать в записях обеих таблиц
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
                    if(count($db_params)> 0 ){
                        // если запись существует в таблице webmail_msg_sent, то синхронизируем статус важности
                        $this->constructUpdate( array( 'important' => $isImportant, 'id' => $db_params[0]['rec_id'] ), 'upd_msg_sent_important');
                    }
                }
            }
        }
        $dirFilesCount = array();
        $fileUsersCount = array();
        $allFilesSize = array( 0 => array("all_files_size" => 0) );
        $listType = 'user_all_files';
        if($this->getListType() == 'user_shared_files') {
            $listType = 'user_shared_files';
        }
        // $arFiles = $this->constructSelect(array('user_id'=> $this->loggedInUser->getId()), $listType );
        // $allFilesSize = $this->constructSelect(array('user_id'=> $this->loggedInUser->getId()), 'all_files_size' );
        // // list($arAllowedUsersId, $arAllowedUsers) = $this->getFileAllowedUsers(array('file_id'=> intval($arFiles[0]['id']) ));
        // $userDirs = $this->getSortedDirs();

        // $dirFilesTmp = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'user_dir_files_count');
        // foreach ($dirFilesTmp as $arVal) {
        //     $dirFilesCount[$arVal['dir_id']] = intval($arVal['cnt']);
        // }

        // $fileUsersTmp = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'files_shared_users_count');
        // foreach ($fileUsersTmp as $arVal) {
        //     $fileUsersCount[$arVal['file_id']] = intval($arVal['cnt']);
        // }

        // $emails_list = $this->getEmailsListFromServer();

        $new = $this->constructSelect(array('email_to' => $this->loggedInUser->getUserEmailLogin() ), 'all_recieved_messages');
        foreach ($new as $key => $tmp) {
            $tmp['date'] = $tmp['msg_date'];
            $time = strtotime($tmp['msg_date']);
            $tmp['sortByDate'] = date('YmdHis',$time);
            $tmp['fdate'] = date('H:i:s d.m.Y',$time);
            $tmp['from'] = $tmp['msg_from'];
            $tmp['messageId'] = $tmp['messageid'];
            $emails_list[] = $tmp;
        }

        $arUsersList = $this->constructSelect(array(), 'users_list_all');
        foreach ($arUsersList as $key => $arValue) {
            if(!empty($arValue['user_email_login'])){
                $arUsersEmail[ $arValue['user_email_login'] ] = $arValue;
            }elseif(!empty($arValue['email'])){
                $arUsersEmail[ trim($arValue['email']) ] = $arValue;
            }
        }

        return array(
            'emailsList' => $emails_list,
            'rolePerms' => $this->rolePerms,
            'list_type' => $this->getListType(),
            'Stat' => $this->getInboxStat(),
            'message' => $this->getMessage(),
            'urlShowMessage' => 'show',
            'usersEmail' => $arUsersEmail,
        );
    }


    /**
     * Upload new file
     *
     * @Route("/new_create", name="webmail_new")
     * @Template("ncuowebmail/webmail/new.html.twig")
     */
    public function actionCreateNew(Request $request)
    {
        // $this->deleteCurrentEmailFromServer();

        $arData = $this->getEmptyEmail();
        $arResult = array();
        $arMailFiles = array();
        $date_upd = date('Y-m-d H:i:s');
        $post_max_size = intval(ini_get('post_max_size'));
        $arUsersList = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'users_list_but_me');

        if( !empty($request->request->get("FIELDS")) ){ // получаем поля формы
            // var_dump($request->request->get("FIELDS"));
            $arData = array();
            foreach ($request->request->get("FIELDS") as $key => $value) { // проверка данных
                if(is_string($value)) $value = trim($value);
                $arData[$key] = $value;
            }
            $arEmailsToSend = explode(',', urldecode($arData['emails_to_send']) );
            if(!empty($_FILES['file_upload'])){
                if(count($_FILES['file_upload']['name']) > 0):
                    foreach ($_FILES['file_upload']['name'] as $i => $fn):
                    $uploaddir = '/var/www/html/public/webmail/upload/';
                    // $uploadfile = $uploaddir . $_FILES['file_upload']['name'][$i];
                    $fileNameHash = md5($_FILES['file_upload']['name'][$i] . date('U'));
                    $pathHashFile = $uploaddir . $fileNameHash;

                    $upl_res = move_uploaded_file($_FILES['file_upload']['tmp_name'][$i], $pathHashFile);

                    if($upl_res){
                        $arMailFiles[] = array(
                            "msg_id" => "",
                            "name_orig" => $_FILES['file_upload']['name'][$i],
                            "name_hash" => $fileNameHash,
                            "title" => $_FILES['file_upload']['name'][$i],
                            "content_type" => $_FILES['file_upload']['type'][$i],
                            "ext" => pathinfo($_FILES['file_upload']['name'][$i], PATHINFO_EXTENSION),
                            "size" => $_FILES['file_upload']['size'][$i],
                            "description" => "",
                        );
                    }
                    endforeach;
                    // $arData['uploadfile_name'] = $_FILES['file_upload']['name'][0];
                endif;
                // OLD UPLOAD ATTACH TO MAIL BODY
                // if (!move_uploaded_file($_FILES['file_upload']['tmp_name'][0], $uploadfile)) {
                //     $this->setMessageError("Ошибка отправки файла ");
                // }else{
                //     //ins_file
                //     $arData['uploadfile_name'] = $_FILES['file_upload']['name'][0];
                // }
            }

            if(count($arEmailsToSend) > 0 ):
                $result = true;
		$emails_success = array();
		$emails_error = array();

                $arData["hash"] = md5(date('U'));
                $arData["msg_important"] = empty($arData['isImportant']) ? "False" : "True";

                $arData["msg_id"] = $this->constructInsert( array('hash' => $arData["hash"], 'msg_from' => $this->loggedInUser->getUserEmailLogin(), 'msg_to' => $arData['emails_to_send'], 'msg_subj' => $arData["subj"], 'msg_text' => $arData["content"], 'cnt_files' => count($arMailFiles), 'msg_status' => 'prepared', "msg_date" => date("d-m-Y H:i:s") ), 'ins_message', true );
                
                if( intval($arData["msg_id"]) > 0 ){
                    if(count($arMailFiles) > 0 ){
                        foreach ($arMailFiles as $key => $arFile) {
                            $arFile['msg_id'] = intval($arData["msg_id"]);
                            $msg_file_id = $this->constructInsert( $arFile, 'ins_file', true, '.seq_webmail_file_id' );
                        }
                    }
                    $this->constructInsert( 
                    array(
                        'msg_id' => intval($arData["msg_id"]), 
                        'email_from' => $this->loggedInUser->getUserEmailLogin(), 
                        // 'user_id' => $this->loggedInUser->getId(), 
                        'important' => $arData["msg_important"],
                        'status' => 'sent'
                    ), 
                    'ins_msg_sent' );
                }

                foreach ($arEmailsToSend as $key => $email):
                    if(!empty($email)):
                        $arData["to_addr"] = trim($email);
                        $res = $this->sendEmail($arData);
                        if($res) $emails_success[] =  $email;
                        else $emails_error[] =  $email;

                    endif;
                endforeach;
                if ($result){
                    $this->setMessageSuccess("Сообщение успешно отправлено по адресам: '".(implode(', ', $emails_success))."' ");
                    $this->constructUpdate( array( 'id' => $arData["msg_id"], 'msg_status' => 'sent' ), 'upd_message_status');
                }
                else{
                    $this->setMessageError("Ошибка отправки сообщения по адресам: '".(implode(', ', $emails_error))."' .");
                }
            endif;
        }
        
        $arUsersList = $this->constructSelect(array(), 'emails_list_but_me');

        return array(
            'urlAction' => $this->generateUrl('webmail_new', array() ),
            'urlList' => $this->generateUrl('webmail', array()),
            'rolePerms' => $this->rolePerms,
            'FIELDS' => $arData,
            'message' => $this->getMessage(),
            'post_max_size' => $post_max_size,
            'Stat' => $this->getInboxStat(),
            'usersList' => $arUsersList,
        );
    }


    /**
     *
     * @Route("/{email_id}/show", name="webmail_show")
     * @Method("GET")
     * @Template("ncuowebmail/webmail/show.html.twig")
     */
    public function showAction($email_id)
    {
        // $this->getMail2( substr($email_id, 0, strpos($email_id, '@')) );

        $email_params = $this->constructSelect(array('hash'=>$email_id, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');

        // old variant - get message from mail server
        // $email_content = $this->getEmailContent($this->getEmailIdShort($email_id));

        $arResult = $this->getOneMessageContent($email_params);
        // $arResult['urlShowMessage'] = $this->generateUrl('webmail_show', array('email_id'=>$email_id));

        $arUsersList = $this->constructSelect(array(), 'users_list_all');
        foreach ($arUsersList as $key => $arValue) {
            if(!empty($arValue['user_email_login'])){
                $arUsersEmail[ $arValue['user_email_login'] ] = $arValue;
            }elseif(!empty($arValue['email'])){
                $arUsersEmail[ trim($arValue['email']) ] = $arValue;
            }
        }
        $arResult['usersEmail'] = $arUsersEmail;

        return $arResult;
    }

    /**
     *
     * @Route("/{email_id}/one_sent", name="webmail_one_sent")
     * @Method("GET")
     * @Template("ncuowebmail/webmail/show.html.twig")
     */
    public function sentMessageAction($email_id)
    {
        $email_params = $this->constructSelect(array('hash'=>$email_id, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
        $arResult = $this->getOneMessageContent($email_params);
        $arResult['urlList'] = $this->generateUrl('webmail_sent', array());
        $arResult['active_left_tab'] = 'sent';
        return $arResult;
    }

    /**
     *
     * @Route("/{email_id}/one_imp", name="webmail_one_imp")
     * @Method("GET")
     * @Template("ncuowebmail/webmail/show.html.twig")
     */
    public function importantMessageAction($email_id)
    {
        $email_params = $this->constructSelect(array('hash'=>$email_id, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
        if(count($email_params) > 0 ){
            $arResult = $this->getOneMessageContent($email_params);
        }else{
            $email_params = $this->constructSelect(array('hash'=>$email_id, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
            if(count($email_params) > 0 ){
                $arResult = $this->getOneMessageContent($email_params);
            }
        }
        
        $arResult['urlList'] = $this->generateUrl('webmail_imp', array());
        $arResult['active_left_tab'] = 'important';
        return $arResult;
    }

    /**
     *
     * @Route("/{email_id}/one_attach", name="webmail_one_attach")
     * @Method("GET")
     * @Template("ncuowebmail/webmail/show.html.twig")
     */
    public function attachMessageAction($email_id)
    {
        $email_params = $this->constructSelect(array('hash'=>$email_id, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
        if(count($email_params) > 0 ){
            $arResult = $this->getOneMessageContent($email_params);
        }else{
            $email_params = $this->constructSelect(array('hash'=>$email_id, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
            if(count($email_params) > 0 ){
                $arResult = $this->getOneMessageContent($email_params);
            }
        }
        
        $arResult['urlList'] = $this->generateUrl('webmail_attach', array());
        $arResult['active_left_tab'] = 'attach';
        return $arResult;
    }


    /**
     *
     * @Route("/{email_id}/one_trash", name="webmail_one_trash")
     * @Method("GET")
     * @Template("ncuowebmail/webmail/show.html.twig")
     */
    public function trashMessageAction($email_id)
    {
        $email_params = $this->constructSelect(array('hash'=>$email_id, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_trash');
        if(count($email_params) > 0 ){
            $arResult = $this->getOneMessageContent($email_params);
        }else{
            $email_params = $this->constructSelect(array('hash'=>$email_id, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_recieved_trash');
            if(count($email_params) > 0 ){
                $arResult = $this->getOneMessageContent($email_params);
            }
        }
        
        $arResult['urlList'] = $this->generateUrl('webmail_trash', array());
        $arResult['active_left_tab'] = 'trash';
        $arResult['entity']['status_title'] = 'Помещен в корзину';
        $arResult['entity']['status_color'] = '#000000';
        return $arResult;
    }

    public function getOneMessageContent($email_params)
    {
        foreach ($email_params as $key => $tmp) {
            $tmp['date'] = $tmp['msg_date'];
            $time = strtotime($tmp['msg_date']);
            $tmp['sortByDate'] = date('YmdHis',$time);
            $tmp['fdate'] = date('H:i:s d.m.Y',$time);
            $tmp['from'] = $tmp['msg_from'];
            $tmp['messageId'] = $tmp['hash'];
            $tmp['subj'] = $tmp['msg_subj'];
            $tmp['content'] = $tmp['msg_text'];
            $email_content[] = $tmp;
        }

        $entity = array_shift($email_content);
        if($entity['cnt_files'] > 0 ){
            $entity['filesList'] = $this->constructSelect(array('msg_id'=>$entity['id']), 'message_all_files');   
           
        }
        if(!empty($entity['content'])) $entity['content'] = nl2br($entity['content']);

        if( null !== $this->getLocalRequest()->get("download") ){
            // формируем путь к файлу
            $fileId = $this->getLocalRequest()->get("download");
            $tmpInfo = $this->constructSelect(array( 'id'=>$fileId ), 'file_info');
            $fileInfo = array_shift($tmpInfo);
            $filepath = $_SERVER["DOCUMENT_ROOT"] . $this->upload_path . $fileInfo['name_hash'];
            if(file_exists($filepath)){
                $content_type = mime_content_type($filepath);
                $content = file_get_contents($filepath);
                // Generate output file
                header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                header("Cache-Control: public"); // needed for internet explorer
                header("Content-Type: {$content_type}");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($filepath));
                header("Content-Disposition: attachment; filename={$fileInfo['name_orig']}");
                print $content;
                die();
            }
/*          
            // Old version uses data from messages on mail server
            $fileNmb = $this->getLocalRequest()->get("download");
            if(!empty($entity['fileRoot'][$fileNmb])){
                $filepath = $_SERVER["DOCUMENT_ROOT"] . $this->attach_path.$entity['fileRoot'][$fileNmb];
                $content_type = mime_content_type($filepath);
                $fName = urldecode(substr($entity['fileRoot'][$fileNmb], strrpos($entity['fileRoot'][$fileNmb], '/')+1 ) );
                $data = file_get_contents($filepath);
                $content= base64_decode($data);

                if(file_exists($filepath)){
                    // Generate output file
                    header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                    header("Cache-Control: public"); // needed for internet explorer
                    header("Content-Type: {$content_type}");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length:".filesize($filepath));
                    header("Content-Disposition: attachment; filename={$fName}");
                    print $content;
                    die();
                }
            }*/
        }
        return array(
            'entity' => $entity,
            'urlList' => $this->generateUrl('webmail', array()),
            'rolePerms' => $this->rolePerms,
            'message' => $this->getMessage(),
            'Stat' => $this->getInboxStat(),
        );
    }


    /** 
     * Lists User Sent messages.
     *
     * @Route("/sent", name="webmail_sent")
     * @Template("ncuowebmail/webmail/index.html.twig")
     */
    public function sentListAction(Request $request)
    {

        $emails_list = array();
        if ($request->request->get("FIELDS")){
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = $value;
            }
            if(isset($arData['deleteEmail']) && is_array($arData['deleteEmail'])){
                $this->setMessageError("Начало перенесения в корзину списка сообщений");
                // идем по списку хешей писем
                foreach ($arData['deleteEmail'] as $key => $hash) {
                    $status = 'trash';
                    // в разделе Отправленные сначала обращаемся к таблице webmail_msg_sent, если такая запись существует в привязке к текущему пользователю
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
                    if( !empty($db_params[0]['rec_id'])){
                        $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => $status ), 'upd_msg_sent_status');
                        // в разделе Полученные сначала обращаемся к таблице webmail_msg_recieved, проверим если такая запись существует в привязке к текущему пользователю (случай когда письмо послано самому себе, т.е пользователь является отправителем и одним из получателей), т.о. статус удаления нужно синхронизировать в записях обеих таблиц
                        $db_params = $this->constructSelect(array('hash'=>$hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
                        if(count($db_params)> 0 ){
                            // если запись существует в таблице webmail_msg_recieved, то синхронизируем статус удаления
                            $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => $status ), 'upd_msg_recieved_status');
                        }
                        $this->setMessageSuccess("Выбранные письма помещены в корзину");
                    }else{
                        $this->setMessageError("Ошибка перенесения в корзину сообщения с кодом '{$message_id}'");
                    }
                    /*
                    // Данный блок отвечает за полное удаление письма с почтового сервера, функционал пока не задействован. В будущем будет применен для удаления письма из Корзины
                    $res = $this->deleteEmail($message_id);
                    if($res[0]['status'] == 'Error'){
                        $this->setMessageError("Ошибка удаления сообщения с кодом {$message_id}");
                        break;
                    }else{
                        $this->setMessageSuccess("Выбранные письма успешно удалены");
                    }*/
                }
            }
            // Обработка сообщений со статусом Важные
            if(isset($arData['important']) && is_array($arData['important']) && count($arData['important']) > 0 ){
                // Если пришел блок hash сообщений, 
                foreach ($arData['important'] as $hash) {
                    // define email type - sent or recieved
                    if($arData['isImportant'] == 'imp') $isImportant = 'True';
                    else $isImportant = 'False';
                    // ищем в списке отправленных
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
                    if(count($db_params)> 0 ) $this->constructUpdate( array( 'important' => $isImportant, 'id' => $db_params[0]['rec_id'] ), 'upd_msg_sent_important');
                    // ищем в списке полученных
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
                    if(count($db_params)> 0 ) $this->constructUpdate( array( 'important' => $isImportant, 'id' => $db_params[0]['rec_id'] ), 'upd_msg_recieved_important');
                }
            }
        }
        $dirFilesCount = array();
        $fileUsersCount = array();
        $allFilesSize = array( 0 => array("all_files_size" => 0) );
        $listType = 'user_all_files';
        if($this->getListType() == 'user_shared_files') {
            $listType = 'user_shared_files';
        }

        $new = $this->constructSelect(array('email_from' => $this->loggedInUser->getUserEmailLogin() ), 'all_sent_messages');

        foreach ($new as $key => $tmp) {
            $tmp['date'] = $tmp['msg_date'];
            $time = strtotime($tmp['msg_date']);
            $tmp['sortByDate'] = date('YmdHis',$time);
            $tmp['fdate'] = date('H:i:s d.m.Y',$time);
            $tmp['from'] = $tmp['msg_from'];
            $tmp['messageId'] = $tmp['messageid'];
            $emails_list[] = $tmp;
        }

        return array(
            'emailsList' => $emails_list,
            'rolePerms' => $this->rolePerms,
            'list_type' => $this->getListType(),
            'Stat' => $this->getInboxStat(),
            'message' => $this->getMessage(),
            'active_left_tab' => 'sent',
            'urlShowMessage' => 'one_sent',
        );
    }



    /** 
     * Lists User Sent messages.
     *
     * @Route("/imp", name="webmail_imp")
     * @Template("ncuowebmail/webmail/index.html.twig")
     */
    public function importantListAction(Request $request)
    {

        $emails_list = array();
        if ($request->request->get("FIELDS")){
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = $value;
            }
            if(isset($arData['deleteEmail']) && is_array($arData['deleteEmail'])){
                $this->setMessageError("Начало удаления списка сообщений");
                // идем по списку хешей писем
                foreach ($arData['deleteEmail'] as $key => $hash) {
                    $status = 'trash';
                    // в разделе Отправленные сначала обращаемся к таблице webmail_msg_sent, если такая запись существует в привязке к текущему пользователю
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
                    if( !empty($db_params[0]['rec_id'])){
                        $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => $status ), 'upd_msg_sent_status');
                        // в разделе Полученные сначала обращаемся к таблице webmail_msg_recieved, проверим если такая запись существует в привязке к текущему пользователю (случай когда письмо послано самому себе, т.е пользователь является отправителем и одним из получателей), т.о. статус удаления нужно синхронизировать в записях обеих таблиц
                    }
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
                    if(count($db_params)> 0 ){
                        // если запись существует в таблице webmail_msg_recieved, то синхронизируем статус удаления
                        $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => $status ), 'upd_msg_recieved_status');
                    }
                    $this->setMessageSuccess("Выбранные письма успешно удалены");
                    // }else{
                    //     $this->setMessageError("Ошибка удаления сообщения с кодом '{$hash}'");
                    // }
                    /*
                    // Данный блок отвечает за полное удаление письма с почтового сервера, функционал решено не задействовать. Возможно будет применен для удаления письма из Корзины
                    $res = $this->deleteEmail($message_id);
                    if($res[0]['status'] == 'Error'){
                        $this->setMessageError("Ошибка удаления сообщения с кодом {$message_id}");
                        break;
                    }else{
                        $this->setMessageSuccess("Выбранные письма успешно удалены");
                    }*/
                }
            }
            if(isset($arData['important']) && is_array($arData['important']) && count($arData['important']) > 0 ){
                foreach ($arData['important'] as $hash) {
                    // define email type - sent or recieved
                    if($arData['isImportant'] == 'imp') $isImportant = 'True';
                    else $isImportant = 'False';
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
                    $this->constructUpdate( array( 'important' => $isImportant, 'id' => $db_params[0]['rec_id'] ), 'upd_msg_sent_important');
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
                    if(count($db_params)> 0 ){
                        $this->constructUpdate( array( 'important' => $isImportant, 'id' => $db_params[0]['rec_id'] ), 'upd_msg_recieved_important');
                    }
                }
            }
        }
        $dirFilesCount = array();
        $fileUsersCount = array();
        $allFilesSize = array( 0 => array("all_files_size" => 0) );
        $listType = 'user_all_files';
        if($this->getListType() == 'user_shared_files') {
            $listType = 'user_shared_files';
        }

        $arImportant = $this->constructSelect(array('email' => $this->loggedInUser->getUserEmailLogin() ), 'all_important_messages');

        foreach ($arImportant as $key => $tmp) {
            $tmp['date'] = $tmp['msg_date'];
            $time = strtotime($tmp['msg_date']);
            $tmp['sortByDate'] = date('YmdHis',$time);
            $tmp['fdate'] = date('H:i:s d.m.Y',$time);
            $tmp['from'] = $tmp['msg_from'];
            $tmp['messageId'] = $tmp['hash'];
            $tmp['subj'] = $tmp['msg_subj'];
            $tmp['important'] = True;
            $emails_list[] = $tmp;
        }

        return array(
            'emailsList' => $emails_list,
            'rolePerms' => $this->rolePerms,
            'list_type' => $this->getListType(),
            'Stat' => $this->getInboxStat(),
            'message' => $this->getMessage(),
            'active_left_tab' => 'important',
            'urlShowMessage' => 'one_imp',
        );
    }



    /** 
     * Lists messages with attach.
     *
     * @Route("/attach", name="webmail_attach")
     * @Template("ncuowebmail/webmail/index.html.twig")
     */
    public function attachListAction(Request $request)
    {

        $emails_list = array();
        if ($request->request->get("FIELDS")){
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = $value;
            }
            if(isset($arData['deleteEmail']) && is_array($arData['deleteEmail'])){
                $this->setMessageError("Начало удаления списка сообщений");
                // идем по списку хешей писем
                foreach ($arData['deleteEmail'] as $key => $hash) {
                    $status = 'trash';
                    // в разделе Отправленные сначала обращаемся к таблице webmail_msg_sent, если такая запись существует в привязке к текущему пользователю
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
                    if( !empty($db_params[0]['rec_id'])){
                        $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => $status ), 'upd_msg_sent_status');
                        // в разделе Полученные сначала обращаемся к таблице webmail_msg_recieved, проверим если такая запись существует в привязке к текущему пользователю (случай когда письмо послано самому себе, т.е пользователь является отправителем и одним из получателей), т.о. статус удаления нужно синхронизировать в записях обеих таблиц
                    }
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
                    if(count($db_params)> 0 ){
                        // если запись существует в таблице webmail_msg_recieved, то синхронизируем статус удаления
                        $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => $status ), 'upd_msg_recieved_status');
                    }
                    $this->setMessageSuccess("Выбранные письма успешно удалены");
                    // }else{
                    //     $this->setMessageError("Ошибка удаления сообщения с кодом '{$hash}'");
                    // }
                    /*
                    // Данный блок отвечает за полное удаление письма с почтового сервера, функционал решено не задействовать. Возможно будет применен для удаления письма из Корзины
                    $res = $this->deleteEmailFromServer($message_id);
                    if($res[0]['status'] == 'Error'){
                        $this->setMessageError("Ошибка удаления сообщения с кодом {$message_id}");
                        break;
                    }else{
                        $this->setMessageSuccess("Выбранные письма успешно удалены");
                    }*/
                }
            }
            if(isset($arData['important']) && is_array($arData['important']) && count($arData['important']) > 0 ){
                foreach ($arData['important'] as $hash) {
                    // define email type - sent or recieved
                    if($arData['isImportant'] == 'imp') $isImportant = 'True';
                    else $isImportant = 'False';
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
                    $this->constructUpdate( array( 'important' => $isImportant, 'id' => $db_params[0]['rec_id'] ), 'upd_msg_sent_important');
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
                    if(count($db_params)> 0 ){
                        $this->constructUpdate( array( 'important' => $isImportant, 'id' => $db_params[0]['rec_id'] ), 'upd_msg_recieved_important');
                    }
                }
            }
        }
        $dirFilesCount = array();
        $fileUsersCount = array();
        $allFilesSize = array( 0 => array("all_files_size" => 0) );
        $listType = 'user_all_files';
        if($this->getListType() == 'user_shared_files') {
            $listType = 'user_shared_files';
        }

        $arImportant = $this->constructSelect(array('email' => $this->loggedInUser->getUserEmailLogin() ), 'all_attach_messages');

        foreach ($arImportant as $key => $tmp) {
            $tmp['date'] = $tmp['msg_date'];
            $time = strtotime($tmp['msg_date']);
            $tmp['sortByDate'] = date('YmdHis',$time);
            $tmp['fdate'] = date('H:i:s d.m.Y',$time);
            $tmp['from'] = $tmp['msg_from'];
            $tmp['messageId'] = $tmp['hash'];
            $tmp['subj'] = $tmp['msg_subj'];
            // $tmp['important'] = True;
            $emails_list[] = $tmp;
        }

        return array(
            'emailsList' => $emails_list,
            'rolePerms' => $this->rolePerms,
            'list_type' => $this->getListType(),
            'Stat' => $this->getInboxStat(),
            'message' => $this->getMessage(),
            'active_left_tab' => 'attach',
            'urlShowMessage' => 'one_attach',
        );
    }


    /** 
     * Lists User Sent messages.
     *
     * @Route("/trash", name="webmail_trash")
     * @Template("ncuowebmail/webmail/trash_index.html.twig")
     */
    public function trashListAction(Request $request)
    {

        $emails_list = array();
        if ($request->request->get("FIELDS")){
            foreach ($request->request->get("FIELDS") as $key => $value) {
                $arData[$key] = $value;
            }
            if(isset($arData['deleteEmail']) && is_array($arData['deleteEmail'])){
                $this->setMessageError("Начало удаления списка сообщений");
                // идем по списку хешей писем
                foreach ($arData['deleteEmail'] as $key => $hash) {
                    $status = 'trash';
                    // в разделе Отправленные сначала обращаемся к таблице webmail_msg_sent, если такая запись существует в привязке к текущему пользователю
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_message');
                    if( !empty($db_params[0]['rec_id'])){
                        $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => $status ), 'upd_msg_sent_status');
                        // в разделе Полученные сначала обращаемся к таблице webmail_msg_recieved, проверим если такая запись существует в привязке к текущему пользователю (случай когда письмо послано самому себе, т.е пользователь является отправителем и одним из получателей), т.о. статус удаления нужно синхронизировать в записях обеих таблиц
                    }
                    $db_params = $this->constructSelect(array('hash'=>$hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
                    if(count($db_params)> 0 ){
                        // если запись существует в таблице webmail_msg_recieved, то синхронизируем статус удаления
                        $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => $status ), 'upd_msg_recieved_status');
                    }
                    $this->setMessageSuccess("Выбранные письма успешно удалены");
                    // }else{
                    //     $this->setMessageError("Ошибка удаления сообщения с кодом '{$hash}'");
                    // }
                    /*
                    // Данный блок отвечает за полное удаление письма с почтового сервера, функционал решено не задействовать. Возможно будет применен для удаления письма из Корзины
                    $res = $this->deleteEmail($message_id);
                    if($res[0]['status'] == 'Error'){
                        $this->setMessageError("Ошибка удаления сообщения с кодом {$message_id}");
                        break;
                    }else{
                        $this->setMessageSuccess("Выбранные письма успешно удалены");
                    }*/
                }
            }
            if(isset($arData['important']) && is_array($arData['important']) && count($arData['important']) > 0 ){
                foreach ($arData['important'] as $hash) {
                    // $db_params = $this->constructSelect(array( 'hash' => $hash, 'email' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_trash_message');
                    // var_dump($db_params);
                    // восстановим сообщение в разделе Отправленые, для данного пользователя
                    $db_params = $this->constructSelect(array( 'hash' => $hash, 'email_from' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_sent_trash');
                    if(count($db_params)> 0 ){
                        $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => 'sent' ), 'upd_msg_sent_status');
                    }
                    // восстановим сообщение в разделе Полученные, для данного пользователя
                    $db_params = $this->constructSelect(array( 'hash' => $hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_recieved_trash');
                    if(count($db_params)> 0 ){
                        $this->constructUpdate( array( 'id' => $db_params[0]['rec_id'], 'status' => 'recieved' ), 'upd_msg_recieved_status');
                    }
                }
            }
        }
        $dirFilesCount = array();
        $fileUsersCount = array();
        $allFilesSize = array( 0 => array("all_files_size" => 0) );
        $listType = 'user_all_files';
        if($this->getListType() == 'user_shared_files') {
            $listType = 'user_shared_files';
        }

        $arDelete = $this->constructSelect(array('email' => $this->loggedInUser->getUserEmailLogin() ), 'all_trash_messages');

        foreach ($arDelete as $key => $tmp) {
            $tmp['date'] = $tmp['msg_date'];
            $time = strtotime($tmp['msg_date']);
            $tmp['sortByDate'] = date('YmdHis',$time);
            $tmp['fdate'] = date('H:i:s d.m.Y',$time);
            $tmp['from'] = $tmp['msg_from'];
            $tmp['messageId'] = $tmp['hash'];
            $tmp['subj'] = $tmp['msg_subj'];
            $tmp['important'] = False;
            $emails_list[] = $tmp;
        }

        return array(
            'emailsList' => $emails_list,
            'rolePerms' => $this->rolePerms,
            'list_type' => $this->getListType(),
            'Stat' => $this->getInboxStat(),
            'message' => $this->getMessage(),
            'active_left_tab' => 'trash',
            'urlShowMessage' => 'one_trash',
        );
    }


    /**
     * Форма доступа к почте
     *
     * @Route("/email_auth_form", name="email_auth_form")
     * @Template("ncuowebmail/webmail/email_auth_form.html.twig")
     */
    public function actionEmailAuthForm()
    {

        $Stat['inbox_total'] = 0;
        return array(
            'urlAction' => $this->generateUrl('email_auth_form', array() ),
            'urlList' => '',
            'rolePerms' => $this->rolePerms,
            'message' => $this->getMessage(),
            'entity' => array('id'=>1, 'name'=>''),
            'Stat' => $Stat,
        );
    }

    /**
     * Форма доступа к почте
     *
     * @Route("/email_import_form", name="email_import_form")
     * @Template("ncuowebmail/webmail/email_import_form.html.twig")
     */
    public function actionEmailImportForm(Request $request)
    {
        $resImport = array();
        $do = isset($_GET['do']) ? $_GET['do'] : 'connect';
        $btnImportTitle = isset($_GET['do']) ?  'Импортировать' : 'Подключиться';
        $connectData = array('dn'=>'centr26.local.citis', 'login'=>'', 'pwd'=>'');
        if($do == 'import'){
            if ($request->request->get("FIELDS")){
                foreach ($request->request->get("FIELDS") as $key => $value) {
                    $arData[$key] = $value;
                }
                // выгрузка всей почты пользователя
                if( !empty($arData['user_email_login']) && !empty($arData['user_email_pass']) && !empty($arData['user_email_ip']) ){
                    $resImport = $this->reserveExternalMail($arData['user_email_login'], $arData['user_email_pass'], $arData['user_email_ip']);
                    $this->setMessageSuccess("Почтовые сообщения успешно получены");
                    // echo '<pre>', print_r($resImport), '</pre>';
                }else{
                    $this->setMessageError("Все обязательные поля должны быть заполнены");
                }
            }
        }elseif($do == 'connect'){

            if ($request->request->get("FIELDS")){
                foreach ($request->request->get("FIELDS") as $key => $value) {
                    $arData[$key] = $value;
                }
                // выгрузка всей почты пользователя
                if( !empty($arData['user_email_login']) && !empty($arData['user_email_pass']) && !empty($arData['user_email_ip']) ){
                    $res = $this->connectExternalMail($arData['user_email_login'], $arData['user_email_pass'], $arData['user_email_ip']);
                    $this->setMessageSuccess("Подключение успешно установлено, количество писем {$res}, нажмите кнопку 'Импортировать' чтобы продолжить");
                    $do = 'import';
                    $connectData = array('dn'=>$arData['user_email_ip'], 'login'=>$arData['user_email_login'], 'pwd'=>$arData['user_email_pass']);
                }else{
                    $this->setMessageError("Все обязательные поля должны быть заполнены");
                }
            }
        }

/*
// СОХРАНЕНИЕ АРХИВА ПОЧТЫ ПОЛЬЗОВАТЕЛЯ

// ПРИМЕР СКАЧИВАНИЯ ФАЙЛА В BASE64 С КОНВЕРТАЦИЕЙ НА ЛЕТУ. АТТАЧ ФАЙЛЫ ПИТОН (email_get_all.py) СОХРАНЯЕТ В BASE64 В РАЗДЕЛЕ ATTACH ПОЛЬЗОВАТЕЛЯ, А КОНВЕРТАЦИЮ И СОХРАНЕНЕИЕ В ЧИТАЕМОМ ФОРМАТЕ ОСУЩ. PHP
                $filepath = $_SERVER["DOCUMENT_ROOT"] . $this->attach_path.'Тестовый файл № 1.docx';
                // var_dump($filepath);
                $content_type = mime_content_type($filepath);
                // var_dump($content_type);
                $fName = 'Тестовый файл № 1.docx';
                $data = file_get_contents($filepath);
                $content= base64_decode($data);

                if(file_exists($filepath)){
                    // Generate output file
                    // Здесь конвертация контента и пересохраненеие 
                    header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                    header("Cache-Control: public"); // needed for internet explorer
                    header("Content-Type: {$content_type}");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length:".filesize($filepath));
                    header("Content-Disposition: attachment; filename={$fName}");
                    print $content;
                    die();
                }*/

        $arStat = $this->getInboxStat();
        $arStat["cnt_import"] = 0;
        return array(
            'urlAction' => $this->generateUrl('email_import_form', array('do'=>$do) ),
            'urlNewAction' => $this->generateUrl('email_import_form', array() ),
            'urlList' => '',
            'rolePerms' => $this->rolePerms,
            'message' => $this->getMessage(),
            'emailsList' => $resImport,
            'Stat' => $arStat,
            'btnImportTitle' => $btnImportTitle,
            'connectData' => $connectData,
        );
    }



    /**
     * Ошибка в форме доступа к почте
     *
     * @Route("/email_auth_error", name="email_auth_error")
     * @Template("ncuowebmail/webmail/email_auth_form.html.twig")
     */
    public function actionEmailAuthError()
    {
        $this->setMessageError("Неверный логин или пароль.");
        return $this->actionEmailAuthForm();
    }


    public function getInboxStat()
    {
        $arStat = array();
        $arStat['inbox_total'] = $this->countRecievedMessages(); //intval($this->getEmailCount());
        $arStat['inbox_sent'] = $this->countSentMessages();
        $arStat['inbox_important'] = $this->countImportantMessages();
        $arStat['inbox_trash'] = $this->countTrashMessages();
        $statFiles = $this->statFiles();
        // $arStat['inbox_files_cnt'] = $statFiles[0]['files_cnt'];
        // $arStat['inbox_files_size'] = $statFiles[0]['files_size'];
        list($arStat['inbox_files_size'], $arStat['inbox_files_cnt']) = $this->statFiles();
        return $arStat;
    }

    public function countRecievedMessages()
    {
        $cnt = $this->constructSelect(array('email_to' => $this->loggedInUser->getUserEmailLogin() ), 'count_recieved_messages');
        return intval($cnt[0]['cnt']);
    }

    public function countSentMessages()
    {
        $cnt = $this->constructSelect(array('email_from' => $this->loggedInUser->getUserEmailLogin() ), 'count_sent_messages');
        return intval($cnt[0]['cnt']);
    }

    public function countImportantMessages()
    {
        $cnt = $this->constructSelect(array('email' => $this->loggedInUser->getUserEmailLogin() ), 'count_important_messages');
        return intval($cnt[0]['cnt']);
    }

    public function countTrashMessages()
    {
        $cnt = $this->constructSelect(array('email' => $this->loggedInUser->getUserEmailLogin() ), 'count_trash_messages');
        return intval($cnt[0]['cnt']);
    }

    public function statFiles()
    {

        $arImportant = $this->constructSelect(array('email' => $this->loggedInUser->getUserEmailLogin() ), 'all_attach_messages');
	$emails_list = array();
        foreach ($arImportant as $key => $tmp) {
            $tmp['date'] = $tmp['msg_date'];
            $time = strtotime($tmp['msg_date']);
            $tmp['sortByDate'] = date('YmdHis',$time);
            $tmp['fdate'] = date('H:i:s d.m.Y',$time);
            $tmp['from'] = $tmp['msg_from'];
            $tmp['messageId'] = $tmp['hash'];
            $tmp['subj'] = $tmp['msg_subj'];
            // $tmp['important'] = True;
            $emails_list[] = $tmp;
        }        
        $cnt = $this->constructSelect(array('email' => $this->loggedInUser->getUserEmailLogin() ), 'get_files_size');
        return array(intval($cnt[0]['files_size']), intval(count($emails_list)) );
    }

}
