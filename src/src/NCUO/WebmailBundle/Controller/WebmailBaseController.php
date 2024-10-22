<?php

namespace App\NCUO\WebmailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Psr\Log\LoggerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * WebmailBase controller.
 */
class WebmailBaseController extends Controller
{   
    private $DIR_PY_FILES = 'py';
    private $SCHEMA_FILES = 'webmail';
    private $SCHEMA_CMS = 'cms';

    private $logger;
    private $security = null;
    private $session = null;
    private $request = null;
    private $message = array('message_error'=>false, 'message_text'=>'', 'error_explain'=>'');
    private $filesAccessType = array('user_all_dirs', 'user_all_files', 'user_shared_files');

    public $userRoleId = null;
    public $userRoleName = null;
    public $extraPerms = array();
    public $loggedInUser = null;
    public $dbconn = null;
    public $rolePerms = array();


    public function __construct(Security $security, SessionInterface $session, ContainerInterface $container, RequestStack $requestStack, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->security = $security;
        $this->session = $session;
        $this->setLocalRequest($requestStack->getMasterRequest());

        $this->loggedInUser = $security->getToken()->getUser();
        $this->dbconn = $container->get('doctrine')->getManager()->getConnection();
        
        if($this->loggedInUser !== 'anon.'):
            $this->userRoleName = $this->loggedInUser->getRoles()[0];
            $this->userRoleId = $this->loggedInUser->getRole()[0];

            $this->setRolePerms();

        elseif(!$this->isAuthActive()):
            $this->redirectToUrl('/');
        endif;
        if(!empty($this->session->get("role_id"))) {
            $this->loggedInUser->setRoles($this->session->get("role"));
            $this->loggedInUser->setRole(intval($this->session->get("role_id")));
        }

        if ( empty($this->loggedInUser->getUserEmailAutologin()) // если доступ запрещен администратором
        ) {
            if( strpos($this->request->getPathInfo(), 'deny_access_show') == false // доступна только страница deny_access_show
            ){
                $this->session->set("emailAutologin", "deny");
                $this->redirectToUrl('/public/index.php/webmail/deny_access_show');
            }
        // если сквозная авторизация запрещена
        }elseif($this->loggedInUser->getUserEmailAutologin() == 1 // сквозная авторизация для пользователя запрещена администратором
        ) {
            if(empty($this->session->get("emailAutologin")) || $this->session->get("emailAutologin") != 'allow') // авторизация не пройдена
            {
                if(strpos($this->request->getPathInfo(), 'email_auth_form') == false
                    && strpos($this->request->getPathInfo(), 'email_auth_error') == false
                ): // вызываемая страница - не страница формы авторизации
                    // первый проброс на авторизацию
                    $this->redirectToUrl('/public/index.php/webmail/email_auth_form');
                else:
                    // проверка на содержимое POST 
                    // если пусто - пропускаем на форму авторизации в 1 раз
                    $arData = array();
                    if ( is_array($this->request->get("FIELDS") ) ): 
                        // получим данные от формы авторизации
                        $arData = $this->request->get("FIELDS");
                        if( !empty($arData['user_email_login'])
                            && !empty($arData['user_email_pass'])
                            && $arData['user_email_login'] == $this->loggedInUser->getUserEmailLogin() 
                            && $arData['user_email_pass'] == $this->loggedInUser->getUserEmailPass() 
                        ){
                        // если авторизация прошла корректно, отмечаем в сессии и отправляем на главную страницу почты 
                            $this->session->set("emailAutologin", "allow");
                            $this->redirectToUrl('/public/index.php/webmail');
                        }else{
                        // если пустые поля или если авторизация не пройдена, то генерируем ошибку и обратно на форму авторизации
                            $this->redirectToUrl('/public/index.php/webmail/email_auth_error');
                        }
                    endif;

                endif;
            }
        }
    }

    private function setMessage($text, $type = false){
        $this->message = array('message_error'=>$type, 'message_text'=>$text, 'error_explain'=>'');
    }

    public function setMessageError($text){
        $this->setMessage($text, true);
    }

    public function setMessageSuccess($text){
        $this->setMessage($text);
    }

    public function getMessage(){
        return $this->message;
    }

    public function getLocalRequest(){
        return $this->request;
    }

    public function setLocalRequest($req){
        $this->request = $req;
    }
    
    public function redirectToUrl($url){
        header('Location:'.$url);
        die();
    }

    private function setRolePerms(){
        $arPerms = array('R', 'C');
        // Ограничим роль Администратора общим уровнем, для работы с файлами пользователей создадим отдельный функционал 
        // if($this->userRoleName == 'ROLE_ADMIN'){
            // $arPerms = array('C','R','U','D'); // Create(New) Read Update(Edit) Delete
        // }else{
            if(count($this->extraPerms ) > 0 ){
                foreach ($this->extraPerms as $role => $arRolePerms) {
                    if($this->userRoleName == $role ){
                        foreach ($arRolePerms as $value) {
                            $arPerms[] = $value;
                        }
                        array_unique($arPerms);
                        break;
                    }
                }
            }
        // }

        $this->rolePerms = array(
            'create' => in_array('C', $arPerms) ? true : false,
            'read' =>   in_array('R', $arPerms) ? true : false,
            'update' => in_array('U', $arPerms) ? true : false,
            'delete' => in_array('D', $arPerms) ? true : false,
        );
    }

    public function permitUpdateGrant(){
        $this->rolePerms['update'] = true;
    }

    public function permitUpdateDeny(){
        $this->rolePerms['update'] = false;
    }

    public function getRootDirectory(){
        return array(
                "id" => 0,
                "user_id" => $this->loggedInUser->getId(),
                "parent_id" => 0,
                "title" => "Корневая папка",
                "description" => "",
                "sort" => "0",
                "created" => "",
                "modified" => "",
            );
    }


    private function checkPageAccess(){
        if( $this->isAccessGranted === null ){
            $symfRoute = $this->getLocalRequest()->getPathInfo();
            $symfRoute = str_replace('/', '', $symfRoute);

            $conn = $this->getDoctrine()->getManager()->getConnection();
            $sql = '
                SELECT mi.*, mip.parent_id as isParent
                FROM cms.menu_items as mi
                LEFT JOIN cms.menu_roles as mr ON mi.id=mr.menu_id
                LEFT JOIN cms.roles as r ON r.id=mr.role_id
                LEFT JOIN ( SELECT parent_id FROM cms.menu_items WHERE parent_id IS NOT NULL GROUP BY parent_id ) AS mip ON mip.parent_id=mi.id
                WHERE 
                r.id = :id_role
                ORDER BY mi.item_position ASC
            ';
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id_role'=>$this->userRoleId]);
            $listPages = $stmt->fetchAll();

            $arMMenu = array();
            $arSubMenu = array();
            $isAccessGranted = false;
            foreach ($listPages as $arPage) {
                $arPage['url_alias'] = str_replace(array('/public/index.php', '/public', '/'), '', $arPage['url']);
                if ( !empty($arPage['url_alias'])) {
                    if( !$isAccessGranted && strpos( $symfRoute, $arPage['url_alias']) !== false) {
                        $isAccessGranted = true;
                        break;
                    }
                }
            }
            $this->isAccessGranted = $isAccessGranted;
        }
        return $this->isAccessGranted;
    }

    private function isAuthActive()
    {
        if($this->loggedInUser !== 'anon.') return true;
        return false;
    }

    private function checkFilePermits($file_hash){
        $sql_tmpl = $this->getSqlTemplate('user_one_file');
        $stmt = $this->dbconn->prepare($sql_tmpl);
        $stmt->bindValue(':username',  $this->loggedInUser->getId());
        $stmt->bindValue(':name_hash',  $file_hash);
        // $this->bindParams($stmt, array(''=>, 'name_hash'=> $file_hash) );
        $stmt->execute();
        $arRes = $stmt->fetchAll();
        if(isset($arRes) && count($arRes) == 1){
            if($arRes[0]['user_id'] == $this->loggedInUser->getId() )
                return true;
        }
        return false;
    }

    public function getEmailIdShort($email_id){
        if(strpos($email_id, '@') !== false ) $email_id = substr($email_id, 0, strpos($email_id, '@')+1);
        return $email_id;
    }

    public function execMailScript($file_name, $params = array()){
        $userLogin = $this->loggedInUser->getUserEmailLoginShort();
        $userPass = $this->loggedInUser->getUserEmailPass();
        $input_command = "python3 {$file_name} \"{$userLogin}\" \"{$userPass}\"";
        if(count($params) > 0){
            foreach ($params as $key => $param) {
                $input_command .= " \"{$param}\"";
            }
        }
        // var_dump($input_command);
        exec($input_command, $output, $res);
        // var_dump($output);
        return $output;
    }

    public function getMail2($id){ // TESTING 
        $execFile = dirname(__FILE__).'/'.$this->DIR_PY_FILES.'/email_delete.py';
        
        // $userLogin = $this->loggedInUser->getUserEmailLoginShort();
        // $userPass = $this->loggedInUser->getUserEmailPass();
        // $input_command = "python3 {$execFile} \"{$userLogin}\" \"{$userPass}\"";
        // exec($input_command, $output, $res);
        // var_dump($output);
        // var_dump(json_decode($output[0], true));

        $output = $this->execMailScript($execFile, array($id) ) ;
        // var_dump($output);
        // $data = !empty($output[0]) ? json_decode($output[0], true) : false;
        // var_dump($data);
        /*if(count($data[0]) > 0){
            foreach ($data[0] as $key => $value) {
                // var_dump($key);
                $email = json_decode($value, true);
                $email['num'] = $key;
                $time = strtotime($email['date']);
                $email['sortByDate'] = date('YmdHis',$time);
                $email['fdate'] = date('H:i:s d.m.Y',$time);
                $emails_list[intval($email['sortByDate'])] = $email;
            }
            var_dump($email);
        }   */     

    }
    public function getMail(){
        $execFile = dirname(__FILE__).'/'.$this->DIR_PY_FILES.'/client_imap.py';
        return $this->execMailScript($execFile);
    }

    public function getMailById($id){
        // $userLogin = $this->loggedInUser->getUserEmailLoginShort();
        // $userPass = $this->loggedInUser->getUserEmailPass();
        $execFile = dirname(__FILE__).'/'.$this->DIR_PY_FILES.'/email_get_by_id.py';
        // $input_command = "python3 {$file_name} \"{$userLogin}\" \"{$userPass}\" \"{$id}\"";
        // exec($input_command, $output, $res);
        $output = $this->execMailScript($execFile, array($id));
        // var_dump($output);
        return $output;
    }

    public function reserveExternalMail($userLogin, $userPass, $server_ip){

        $emails_list = array();
        // $execFile = dirname(__FILE__).'/'.$this->DIR_PY_FILES.'/email_get_all2.py';
        // $output = $this->execMailScript($execFile, array('centr26.local.citis'));

        $execFile = dirname(__FILE__).'/'.$this->DIR_PY_FILES.'/email_import_all.py';
        $input_command = "python3 {$execFile} \"{$userLogin}\" \"{$userPass}\" \"{$server_ip}\"";
        exec($input_command, $output, $res);
        $data = !empty($output[0]) ? json_decode($output[0], true) : false;
        if(count($data[0]) > 0){
            foreach ($data[0] as $key => $value) {
                // var_dump($key);
                $email = json_decode($value, true);
                $email['num'] = $key;
                $time = strtotime($email['date']);
                $email['sortByDate'] = date('YmdHis',$time);
                $email['fdate'] = date('H:i:s d.m.Y',$time);
                $emails_list[intval($email['sortByDate'])] = $email;
            }
        }else{
            return array();
        }
        return $emails_list;
    }

    public function connectExternalMail($userLogin, $userPass, $server_ip){

        $emails_list = array();
        // $execFile = dirname(__FILE__).'/'.$this->DIR_PY_FILES.'/email_get_all2.py';
        // $output = $this->execMailScript($execFile, array('centr26.local.citis'));

        $execFile = dirname(__FILE__).'/'.$this->DIR_PY_FILES.'/email_import_count.py';
        $input_command = "python3 {$execFile} \"{$userLogin}\" \"{$userPass}\" \"{$server_ip}\"";
        exec($input_command, $output, $res);
        // var_dump("Найдено писем ",$output[0]);
        // $data = !empty($output[0]) ? json_decode($output[0], true) : false;
        return $output[0];
    }

    public function getEmailCount(){
        $output = $this->getMail();
        $data = !empty($output[0]) ? json_decode($output[0], true) : false;
        if(!is_array($data)) return false; // json_decode return not_array format 
        
        return count($data[0]);
    }

    public function getEmailsList(){

        $data = $this->constructSelect(array('hash'=>$hash, 'email_to' => $this->loggedInUser->getUserEmailLogin() ), 'get_one_message');
        $emails_list = array();
        // $output = $this->getMail();
        // $data = !empty($output[0]) ? json_decode($output[0], true) : false;
        // if(!is_array($data)) return false; // json_decode return not_array format 
        if(count($data[0]) > 0){
            foreach ($data[0] as $key => $value) {
                // var_dump($key);
                $email = json_decode($value, true);
                $email['num'] = $key;
                $time = strtotime($email['date']);
                $email['sortByDate'] = date('YmdHis',$time);
                $email['fdate'] = date('H:i:s d.m.Y',$time);
                $emails_list[intval($email['sortByDate'])] = $email;
            }
        }else{
            return array();
        }
        // var_dump($emails_list);
        return $emails_list;
    }

    public function getEmailsListFromServer(){
        $emails_list = array();
        $output = $this->getMail();
        $data = !empty($output[0]) ? json_decode($output[0], true) : false;
        if(!is_array($data)) return false; // json_decode return not_array format 
        if(count($data[0]) > 0){
            foreach ($data[0] as $key => $value) {
                // var_dump($key);
                $email = json_decode($value, true);
                $email['num'] = $key;
                $time = strtotime($email['date']);
                $email['sortByDate'] = date('YmdHis',$time);
                $email['fdate'] = date('H:i:s d.m.Y',$time);
                $emails_list[intval($email['sortByDate'])] = $email;
            }
        }else{
            return array();
        }
        // var_dump($emails_list);
        return $emails_list;
    }

    public function getEmailContent($email_id){
        $output = $this->getMailById($email_id);
        $data = !empty($output[0]) ? json_decode($output[0], true) : false;
        if(!is_array($data)) return false; // json_decode return not_array format 
        if(count($data[0]) == 1){
            foreach ($data[0] as $key => $value) {
                // var_dump($key);
                $email = json_decode(($value), true);
                $email['num'] = $key;
                $time = strtotime($email['date']);
                $email['sortByDate'] = date('YmdHis',$time);
                $email['fdate'] = date('H:i:s d.m.Y',$time);
                $emails_list[intval($email['sortByDate'])] = $email;
            }
        }else{
            $this->setMessageError('Ошибка: множественный Id');
            return array();
        }
        return $emails_list;
    }



    public function sendEmail($params){

        $locale='ru_RU.UTF-8';
        setlocale(LC_ALL,$locale);
        putenv('LC_ALL='.$locale);

        $py_file_smtp = dirname(__FILE__).'/'.$this->DIR_PY_FILES.'/client_smtp2.py';
        $fname1 = !empty($params['uploadfile_name']) ? rawurlencode($params['uploadfile_name']) : "";

        $userLogin = $this->loggedInUser->getUserEmailLoginShort();
        $userPass = $this->loggedInUser->getUserEmailPass();
        $from = $this->loggedInUser->getUserEmailLogin();
        $to_addr = $params['to_addr'];
        $hash = $params['hash'];

        $msg_subj = $this->prepareDataToSend($params['subj']);
        $msg_text = $this->prepareDataToSend($params['content']);

        if( intval($params['msg_id']) > 0 ){
            $msg_recieved_id = $this->constructInsert( 
                array(
                    'msg_id' => intval($params['msg_id']), 
                    'email_to' => $to_addr, 
                    'important' => $params["msg_important"],
                    'status' => 'undelivered'
                ), 
                'ins_msg_recieved', true, '.seq_webmail_msg_recieved_id' );
        }

        $input_command = "python3 {$py_file_smtp} \"{$userLogin}\" \"{$userPass}\" \"{$from}\" \"{$to_addr}\" \"{$msg_subj}\" \"{$msg_text}\" \"{$hash}\" \"{$fname1}\" ";
//var_dump($input_command);
//die();
        exec($input_command, $output, $res);
        // var_dump($output);
        if( !empty($output[0]) && $output[0] == 'ok') {
            $this->constructUpdate( array( 'id' => $msg_recieved_id, 'status' => 'recieved' ), 'upd_msg_recieved_status');
            return true;
        }else{

        }
        return false; 
    }

    public function deleteEmailFromServer($id){
        $execFile = dirname(__FILE__).'/'.$this->DIR_PY_FILES.'/email_delete.py';
        $output = $this->execMailScript($execFile, array($this->getEmailIdShort($id)) ) ;
        return json_decode($output[0], true);
    }

    public function deleteUserEmailFromServer($message_id, $login, $pass){
        if(!empty($login) && strlen($login) > 1 && !empty($message_id) && strlen($message_id) > 6):
            $execFile = dirname(__FILE__).'/'.$this->DIR_PY_FILES.'/email_delete.py';
            // $output = $this->execMailScript($execFile, array($this->getEmailIdShort($id)) ) ;
            $input_command = "python3 {$execFile} \"{$login}\" \"{$pass}\" \"{$message_id}\"";
            // var_dump($input_command);
            exec($input_command, $output, $res);        
        else:
            return array('res'=>'Ошибка проверки: неверный логин');
        endif;
        return json_decode($output[0], true);
    }

    private function getSqlTemplate($type)
    {
        if($type == 'user_one_file')
            // получить данные по 1 файлу для владелеца файла
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webmail_file WHERE user_id = :username AND name_hash=:name_hash';
        elseif($type == 'user_all_files')
            // получить список всех файлов для владелеца файлов
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webmail_file WHERE user_id = :user_id';
        elseif($type == 'message_all_files')
            // получить список всех файлов для 1 сообщения
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webmail_file WHERE msg_id = :msg_id';
        elseif($type == 'file_info')
            // получить запись файла 
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webmail_file WHERE id=:id';
        elseif($type == 'get_files_size')
            return 'SELECT SUM(size) AS files_size, COUNT(size) AS files_cnt FROM 
                    (
                    SELECT m.*, r.id AS rec_id, r.email_to FROM webmail.webmail_message AS m
                    LEFT JOIN webmail.webmail_msg_sent AS s ON s.msg_id=m.id
                    LEFT JOIN webmail.webmail_msg_recieved as r ON r.msg_id = m.id
                    WHERE m.cnt_files > 0 AND m.msg_status = \'sent\' AND 
                        (
                            (s.email_from = :email AND s.status=\'sent\') 
                            OR (r.email_to = :email AND r.status=\'recieved\') 
                        )
                    ) AS m1 
                    LEFT JOIN webmail.webmail_file AS f ON m1.id=f.msg_id';
        elseif($type == 'user_shared_files')
            // получить список всех РАСШАРЕНЫХ файлов для пользователя
            return 'SELECT acc.user_id as user_id, files.*, us.firstname, us.middlename, us.lastname, us.username, oiv.name as oiv_name
                    FROM '.$this->SCHEMA_FILES.'.webftp_access AS acc
                    LEFT JOIN '.$this->SCHEMA_FILES.'.webftp_files AS files on acc.file_id=files.id
                    LEFT JOIN cms.users AS us on files.user_id=us.id
                    LEFT JOIN cms.roles AS roles on roles.id=us.role
                    LEFT JOIN oivs_passports.oivs AS oiv on oiv.id_oiv=us.oiv_id
                    WHERE acc.user_id = :user_id
                    ORDER BY files.modified DESC';
        elseif($type == 'user_shared_file')
            // получить данные по РАСШАРЕНОМУ 1 файлу для пользователя
            return 'SELECT acc.user_id as user_id, files.*, us.firstname, us.middlename, us.lastname, us.username, oiv.name as oiv_name
                    FROM '.$this->SCHEMA_FILES.'.webftp_access AS acc
                    LEFT JOIN '.$this->SCHEMA_FILES.'.webftp_files AS files on acc.file_id=files.id
                    LEFT JOIN cms.users AS us on files.user_id=us.id
                    LEFT JOIN cms.roles AS roles on roles.id=us.role
                    LEFT JOIN oivs_passports.oivs AS oiv on oiv.id_oiv=us.oiv_id
                    WHERE acc.user_id = :user_id 
                        AND acc.file_id = :file_id 
                    ORDER BY files.modified DESC';
        elseif($type == 'users_list_all')
            // получить список всех пользователей сорт. по фамилии
            return 'SELECT * FROM '.$this->SCHEMA_CMS.'.users ORDER BY lastname';
        elseif($type == 'users_list_but_me')
            // получить список всех пользователей сорт. по фамилии, кроме текущего
            return 'SELECT * FROM '.$this->SCHEMA_CMS.'.users WHERE id <> :user_id ORDER BY lastname';
        elseif($type == 'user_info')
            return 'SELECT u.id, u.username, u.firstname, u.middlename, u.lastname, roles.description as role_name, op.name as oiv_name 
                    FROM cms.users u
                    LEFT JOIN cms.roles ON u.role=roles.id
                    LEFT JOIN oivs_passports.oivs op ON u.oiv_id=op.id_oiv
                    WHERE 
                    u.id = :user_id';
        elseif($type == 'users_allowed')
            // получить список пользователей имеющих доступ к РАСШАРЕНОМУ 1 файлу
            return 'SELECT roles.id as role_id, roles.name as role_name, roles.description as role_title, acc.user_id as user_id, us.oiv_id, us.firstname, us.middlename, us.lastname, us.username, oiv.name as oiv_name
                    FROM '.$this->SCHEMA_FILES.'.webftp_access AS acc
                    LEFT JOIN cms.users AS us on acc.user_id=us.id
                    LEFT JOIN cms.roles AS roles on roles.id=us.role
                    LEFT JOIN oivs_passports.oivs AS oiv on oiv.id_oiv=us.oiv_id
                    WHERE acc.file_id = :file_id
                    ORDER BY us.lastname ASC
                    ';
        elseif($type == 'emails_list_but_me')
            // получить список всех email пользователей сорт. по фамилии, кроме текущего
            return 'SELECT * FROM '.$this->SCHEMA_CMS.'.users WHERE email <> \'\' ORDER BY lastname';
        elseif($type == 'files_shared_users_count')
            // получить кол-во расшареных пользователей для файлов пользователя
            return 'SELECT * FROM webftp.webftp_files AS f
                    LEFT JOIN 
                        (SELECT COUNT (file_id) AS cnt, file_id FROM webftp.webftp_access GROUP BY file_id) AS t
                    ON f.id = t.file_id
                    WHERE user_id=:user_id';
        elseif($type == 'emptyset_file')
            return 'SELECT column_name FROM information_schema.columns WHERE table_name = \'webftp_files\'';
        elseif($type == 'all_files_size')
            return 'SELECT SUM(size) AS all_files_size FROM '.$this->SCHEMA_FILES.'.webftp_files WHERE user_id=:user_id';
        elseif($type == 'user_dirs')
            // получить данные по всем разделам владелеца 
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webftp_dir WHERE user_id = :user_id ORDER BY parent_id, title';
        elseif($type == 'user_dir')
            // получить данные по 1 директории для владелеца директории
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webftp_dir WHERE user_id = :user_id AND id=:dir_id';
        elseif($type == 'user_dir_files_list')
            // получить спиоск файлов по dir_id
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webftp_files WHERE user_id=:user_id AND dir_id=:dir_id';
        elseif($type == 'user_dir_files_count')
            // получить кол-во файлов в директории
            return 'SELECT dir_id,  COUNT (dir_id) as cnt, user_id FROM '.$this->SCHEMA_FILES.'.webftp_files WHERE user_id=:user_id GROUP BY user_id, dir_id';
        elseif($type == 'emptyset_dir')
            return 'SELECT column_name FROM information_schema.columns WHERE table_name = \'webftp_dir\'';
        elseif($type == 'mail_all_emails')
            // получить список всех полученых писем 
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webmail_msg_recieved WHERE email_to = :email_to';
        elseif($type == 'get_one_message')
            // получить одно сообщение, но только где пользователь адресат
            return 'SELECT m.*, r.id as rec_id, r.important, r.status FROM '.$this->SCHEMA_FILES.'.webmail_message as m
                    LEFT JOIN webmail.webmail_msg_recieved as r
                    ON m.id=r.msg_id
                    WHERE m.hash = :hash AND r.email_to = :email_to AND r.status <> \'trash\'';
        elseif($type == 'get_one_sent_message')
            // получить одно сообщение, но только где пользователь отправитель
            return 'SELECT m.*, r.id as rec_id, r.important, r.status FROM '.$this->SCHEMA_FILES.'.webmail_message as m
                    LEFT JOIN webmail.webmail_msg_sent as r
                    ON m.id=r.msg_id
                    WHERE m.hash = :hash AND r.email_from = :email_from AND r.status <> \'trash\'';
        elseif($type == 'get_one_recieved_trash')
            // получить одно сообщение, но только где пользователь адресат
            return 'SELECT m.*, r.id as rec_id, r.important, r.status FROM '.$this->SCHEMA_FILES.'.webmail_message as m
                    LEFT JOIN webmail.webmail_msg_recieved as r
                    ON m.id=r.msg_id
                    WHERE m.hash = :hash AND r.email_to = :email_to AND r.status = \'trash\'';
        elseif($type == 'get_one_sent_trash')
            // получить одно сообщение, но только где пользователь отправитель
            return 'SELECT m.*, r.id as rec_id, r.important, r.status FROM '.$this->SCHEMA_FILES.'.webmail_message as m
                    LEFT JOIN webmail.webmail_msg_sent as r
                    ON m.id=r.msg_id
                    WHERE m.hash = :hash AND r.email_from = :email_from AND r.status = \'trash\'';
        elseif($type == 'all_recieved_messages')
            // получить одно сообщение, но только где пользователь адресат
            return 'SELECT m.id, m.hash as messageId, m.msg_subj as subj, m.msg_from, m.msg_date, m.cnt_files, r.id as rec_id, r.email_to, r.important, r.status 
                FROM webmail.webmail_message as m
                LEFT JOIN webmail.webmail_msg_recieved AS r
                ON m.id=r.msg_id
                WHERE r.email_to = :email_to AND r.status = \'recieved\' ORDER BY m.msg_date DESC';
        elseif($type == 'all_sent_messages')
            // получить все сообщения, где пользователь адресат
            return 'SELECT m.id, m.hash as messageId, m.msg_subj as subj, msg_from, msg_date, m.cnt_files, r.id as rec_id, r.email_from, r.important, r.status 
                FROM webmail.webmail_message as m
                LEFT JOIN webmail.webmail_msg_sent AS r
                ON m.id=r.msg_id
                WHERE r.email_from = :email_from AND r.status = \'sent\' ORDER BY m.msg_date DESC';
        elseif($type == 'all_important_messages')
            // получить список важных сообщений
            return 'SELECT DISTINCT m.* AS cnt FROM webmail.webmail_message AS m
                    LEFT JOIN webmail.webmail_msg_sent AS s ON s.msg_id=m.id
                    LEFT JOIN webmail.webmail_msg_recieved AS r ON r.msg_id=m.id
                    WHERE 
                        (s.email_from = :email AND s.important=True AND s.status=\'sent\') 
                        OR (r.email_to = :email AND r.important=True AND r.status=\'recieved\') ';
        elseif($type == 'all_attach_messages')
            // получить список важных сообщений
            return 'SELECT DISTINCT m.id, m.hash, m.hash as messageId, m.msg_subj, m.msg_subj as subj, msg_from, msg_date, s.email_from , r.email_to, r.important, m.cnt_files 
                    FROM webmail.webmail_message AS m
                    LEFT JOIN webmail.webmail_msg_sent AS s ON s.msg_id=m.id
                    LEFT JOIN webmail.webmail_msg_recieved AS r ON r.msg_id=m.id
                    WHERE  m.cnt_files > 0 AND m.msg_status = \'sent\' AND 
                    (
                        (s.email_from = :email AND s.status=\'sent\') 
                        OR (r.email_to = :email AND r.status=\'recieved\')
                    )';
        elseif($type == 'all_trash_messages')
            // получить список удаленных сообщений
            return 'SELECT DISTINCT m.* AS cnt FROM webmail.webmail_message AS m
                    LEFT JOIN webmail.webmail_msg_sent AS s ON s.msg_id=m.id
                    LEFT JOIN webmail.webmail_msg_recieved AS r ON r.msg_id=m.id
                    WHERE 
                        (s.email_from = :email AND s.status=\'trash\') 
                        OR (r.email_to = :email AND r.status=\'trash\') ';
        elseif($type == 'get_one_trash_message')
            // получить одно удаленное сообщение
            return 'SELECT DISTINCT m.* AS cnt FROM webmail.webmail_message AS m
                    LEFT JOIN webmail.webmail_msg_sent AS s ON s.msg_id=m.id
                    LEFT JOIN webmail.webmail_msg_recieved AS r ON r.msg_id=m.id
                    WHERE 
                        m.hash = :hash
                        AND (
                            (s.email_from = :email AND s.important=True AND s.status=\'trash\') 
                            OR (r.email_to = :email AND r.important=True AND r.status=\'trash\') 
                        ) ';
        elseif($type == 'count_recieved_messages')
            // получить количество полученных сообщений
            return 'SELECT COUNT(id) AS cnt FROM webmail.webmail_msg_recieved WHERE email_to = :email_to AND status = \'recieved\' ';
        elseif($type == 'count_sent_messages')
            // получить количество отправленных сообщений
            return 'SELECT COUNT(id) AS cnt FROM webmail.webmail_msg_sent WHERE email_from = :email_from AND status = \'sent\' ';
        elseif($type == 'count_important_messages')
            // получить количество важных сообщений
            return 'SELECT COUNT(DISTINCT m.*) AS cnt FROM webmail.webmail_message AS m
                    LEFT JOIN webmail.webmail_msg_sent AS s ON s.msg_id=m.id
                    LEFT JOIN webmail.webmail_msg_recieved AS r ON r.msg_id=m.id
                    WHERE 
                        (s.email_from = :email AND s.important=True AND s.status=\'sent\') 
                        OR (r.email_to = :email AND r.important=True AND r.status=\'recieved\') ';
        elseif($type == 'count_trash_messages')
            // получить количество важных сообщений
            return 'SELECT COUNT(DISTINCT m.*) AS cnt FROM webmail.webmail_message AS m
                    LEFT JOIN webmail.webmail_msg_sent AS s ON s.msg_id=m.id
                    LEFT JOIN webmail.webmail_msg_recieved AS r ON r.msg_id=m.id
                    WHERE 
                        (s.email_from = :email AND s.status=\'trash\') 
                        OR (r.email_to = :email AND r.status=\'trash\') ';


//--------------------------------------- UPDATES ---------------------------------------
        elseif($type == 'upd_msg_sent_important')
            return 'UPDATE '.$this->SCHEMA_FILES.'.webmail_msg_sent SET important=:important WHERE id=:id';
        elseif($type == 'upd_msg_recieved_important')
            return 'UPDATE '.$this->SCHEMA_FILES.'.webmail_msg_recieved SET important=:important WHERE id=:id';
        elseif($type == 'upd_msg_sent_status')
            return 'UPDATE '.$this->SCHEMA_FILES.'.webmail_msg_sent SET status=:status, modified = \''.date('Y-m-d H:i:s').'\' WHERE id=:id';
        elseif($type == 'upd_msg_recieved_status')
            return 'UPDATE '.$this->SCHEMA_FILES.'.webmail_msg_recieved SET status=:status, modified = \''.date('Y-m-d H:i:s').'\' WHERE id=:id';
        elseif($type == 'upd_message_status')
            return 'UPDATE '.$this->SCHEMA_FILES.'.webmail_message SET msg_status=:msg_status, modified = \''.date('Y-m-d H:i:s').'\' WHERE id=:id';
        elseif($type == 'upd_file')
            return 'UPDATE '.$this->SCHEMA_FILES.'.webftp_files SET dir_id=:dir_id, title=:title, description=:description, modified=:modified WHERE user_id = :username AND name_hash=:name_hash';
        elseif($type == 'upd_directory')
            return 'UPDATE '.$this->SCHEMA_FILES.'.webftp_dir SET parent_id=:parent_id, title=:title, description=:description, sort=:sort, modified=:modified WHERE user_id = :user_id AND id=:id';

//--------------------------------------- INSERTS ---------------------------------------
        elseif($type == 'ins_message')
            return 'INSERT INTO '.$this->SCHEMA_FILES.'.webmail_message ( hash, msg_from, msg_to, msg_subj, msg_text, cnt_files, msg_date, msg_status) VALUES (:hash, :msg_from, :msg_to, :msg_subj, :msg_text, :cnt_files, :msg_date, :msg_status)';
        elseif($type == 'ins_msg_recieved')
            return 'INSERT INTO '.$this->SCHEMA_FILES.'.webmail_msg_recieved ( msg_id, email_to, important, status ) VALUES (:msg_id, :email_to, :important, :status)';
        elseif($type == 'ins_msg_sent')
            return 'INSERT INTO '.$this->SCHEMA_FILES.'.webmail_msg_sent ( msg_id, email_from, important, status ) VALUES (:msg_id, :email_from, :important, :status)';
        elseif($type == 'ins_file')
            return 'INSERT INTO '.$this->SCHEMA_FILES.'.webmail_file ( msg_id, name_orig, name_hash, title, content_type, ext, size, description) VALUES (:msg_id, :name_orig, :name_hash, :title, :content_type, :ext, :size, :description)';
        elseif($type == 'ins_access')
            return 'INSERT INTO '.$this->SCHEMA_FILES.'.webftp_access ( file_id, user_id) VALUES (:file_id, :user_id)';
        elseif($type == 'ins_directory')
            return 'INSERT INTO '.$this->SCHEMA_FILES.'.webftp_dir ( user_id, parent_id, title, description, sort) VALUES ( :user_id, :parent_id, :title, :description, :sort)';

//--------------------------------------- DELETE ---------------------------------------
        elseif($type == 'delete_user_file')
            return 'DELETE FROM '.$this->SCHEMA_FILES.'.webftp_files WHERE user_id = :user_id AND id=:file_id';
        elseif($type == 'delete_user_dir')
            return 'DELETE FROM '.$this->SCHEMA_FILES.'.webftp_dir WHERE user_id = :user_id AND id = :dir_id';
        elseif($type == 'delete_shared_users')
            return 'DELETE FROM '.$this->SCHEMA_FILES.'.webftp_access WHERE file_id = :file_id';

        else
            return '';
    }

    private function bindParams($st, $params)
    {
        if(is_array($params) && count($params) > 0 ){
            foreach($params as $key => $value)
            {
                $st->bindValue(':'.$key,  $value);
            }
        }
        return $st;
    }


    public function constructSelect($params, $selectType = 'user_one_file')
    {
        $sql_tmpl = $this->getSqlTemplate($selectType);
        $stmt = $this->dbconn->prepare($sql_tmpl);
        $this->bindParams($stmt, $params);
        $stmt->execute();
        $res = $stmt->fetchAll();
        return $res;
    }

    public function constructUpdate($params, $updType = '')
    {
        $res = false;
        $sql_tmpl = $this->getSqlTemplate($updType);
        if(!empty($sql_tmpl)):
            $stmt = $this->dbconn->prepare($sql_tmpl);
            $this->bindParams($stmt, $params);
            $res = $stmt->execute();
        endif;
        
        return $res;
    }

    public function constructInsert($params, $insType = '', $getId = false, $seqName = '.seq_webmail_message_id' )
    {
        $res = false;
        $ins_id = 0;
        $sql_tmpl = $this->getSqlTemplate($insType);
        if(!empty($sql_tmpl)):
            $stmt = $this->dbconn->prepare($sql_tmpl);
            $this->bindParams($stmt, $params);
            $res = $stmt->execute();
            if ( $getId ) {
                $ins_id = $this->dbconn->lastInsertId($this->SCHEMA_FILES.$seqName );
                return $ins_id;
            }
        endif;

        return $res;
    }

    public function constructDelete($params, $delType = '')
    {
        $res = false;
        $sql_tmpl = $this->getSqlTemplate($delType);
        if(!empty($sql_tmpl)):
            $stmt = $this->dbconn->prepare($sql_tmpl);
            $this->bindParams($stmt, $params);
            $res = $stmt->execute();
        endif;
        
        return $res;
    }

    public function getFileInfo($file_hash){
        $arFile = $this->constructSelect(array('name_hash'=> $file_hash), 'file_info' );
        if(empty($arFile)) return false;
        else return $arFile[0];
    }

    public function getEmptySet($setName){
        $arReturn = array();
        if(!empty($setName)):
            $arRes = $this->constructSelect( array(), $setName);
            foreach ($arRes as $value) {
                $arReturn[$value['column_name']] = '';
            }
        endif;
        return $arReturn;
    }

    public function getEmptyEmail(){
        $arReturn = array(
            'date' => '',
            'subj' => '',
            'from' => '',
            'content' => '',
        );
        return $arReturn;
    }

    public function getFileAllowedUsers($params){
        $arUsersId = array();
        $arUsers = array();
        $dbUsers = $this->constructSelect( $params, 'users_allowed');
        foreach ($dbUsers as $arUser) {
            $arUsersId[] = (string)$arUser['user_id']; // collect array of ids only to select checked from list of all users
            $arUsers[$arUser['user_id']] = $arUser;
        }
        return array($arUsersId, $arUsers);
    }


    public function getListType(){
        if(empty($_SESSION['ftp']['list_type'])) $_SESSION['ftp']['list_type'] = 'user_all_dirs';
        return $_SESSION['ftp']['list_type'];
    }
    
    public function setListType($type){
        if( in_array($type, $this->filesAccessType )) {
            $_SESSION['ftp']['list_type'] = $type;
            return true;
        }
        return false;
    }

    public function getSortedDirs(){
        $userDirsNew = array();
        $arTmp = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'user_dirs');
        if( count($arTmp ) > 0 ):
            foreach ($arTmp as $key => $arr) {
                $userDirs[$arr['id']] = $arr;
            }

            $i=0;
            $db = array();
            foreach ($userDirs as $value) {
                $db[] = array(
                        0 => $value['id'],
                        1 => $value['parent_id'],
                        2 => $value['title'],
                        3 => array(), // unused
                        4 => 0, // level
                        5 => $value['sort'],
                        );
            }

            $res = array();

            // echo "<pre>", print_r($db), "</pre>";

            /* ----------------- SORTING ALGORITHM ------------------ */
            if( count($db) >0 ):
                foreach ($db as $a):
                    if($a[1] > 0){
                        // collect non root elements
                        $res_new = array();
                        // echo "<br>New RES_NEW ".(count($res))."<br>";
                        // var_dump($res);
                        $is_parent_found = false;
                        $is_place_found = false;
                        if(count($res) > 0){
                            // collect $res_new from $res 
                            foreach ($res as $rec) { // search parent element to insert child after it
                                if($a[1] == $rec[0]){ // if parent element found
                                    // echo "<p>{$a[2]} = Parent {$rec[2]} found</p>";
                                    $a[4] = $rec[4]+1; // set level
                                    $res_new[] = $rec;
                                    // printSet($rec);
                                    // $res_new[] = $a;
                                    // printSet($a);
                                    $is_parent_found = true;
                                }elseif($is_parent_found){
                                    if($a[1] == $rec[1]){
                                        // echo "<p>{$a[2]} = Parent {$rec[2]}</p>";
                                        if($a[5] < $rec[5]){
                                        // echo "<p>Sort {$a[2]}={$a[5]} < {$rec[2]}={$rec[5]}</p>";
                                            // $is_place_found = true;
                                            $res_new[] = $a;
                                        }else{
                                        // echo "<p>Sort {$a[2]}={$a[5]} >= {$rec[2]}={$rec[5]}</p>";
                                            $res_new[] = $rec;
                                        }
                                    }else{
                                        $is_parent_found = false;
                                        $res_new[] = $a;
                                        $res_new[] = $rec;
                                    }
                                }else{
                                    $res_new[] = $rec;
                                }
                            }
                            // if loop finished but element must be the last and wasn't assigned 
                            if($is_parent_found){
                                $res_new[] = $a;
                                $is_parent_found = false;
                            }
                        }
                        $res = $res_new;
                    }else{
                        // collect root elements because they go first
                        $res[$i] = $a;
                        $a[4] = 0; //level
                    }
                    $i++;
                endforeach;
            endif;
            // echo "<pre>", print_r($res), "</pre>";

            /* ----------------- \ SORTING ALGORITHM ------------------ */

            if(is_array($res) && count($res) > 0){
                foreach ($res as $p) {
                    $userDirsNew[] = array(
                        "id" => $p[0],
                        "user_id" => $userDirs[$p[0]]['user_id'],
                        "parent_id" => $p[1],
                        "title" => $p[2],
                        "description" => $userDirs[$p[0]]['description'],
                        "sort" => $p[5],
                        "level" => $p[4],
                    );
                    // echo "<span style='padding-left: ".(20*$p[4])."px'>$p[2]</span><br>";
                }
            }else{
                echo "userDirs Array is empty";
            }

        endif;

        return $userDirsNew;   
    }

    public function collectSubDirs($dir_id){
        $del_start = 0;
        $delete_level = -1;
        $subDirs = array();
        $output = array();
        $userDirs = $this->getSortedDirs();
        if(count($userDirs) > 0):
            foreach ($userDirs as $key => $arDir) {
                if($arDir['parent_id'] == $dir_id ){
                    $del_start = 1;
                    $delete_level = $arDir['level'];
                }
                if($del_start==1 && $arDir['level'] >= $delete_level ){
                    $output[] = $arDir;
                }else{
                    $del_start = 0;
                }
            }
        endif;

        return $output;
    }

    public function collectSubFiles($inputDirs){
        $subFilesDelete = array();
        if( count($inputDirs) > 0){
            foreach ($inputDirs as $subDir) {
                $arTmp = $this->constructSelect(array('user_id'=> $this->loggedInUser->getId(), 'dir_id' => $subDir['id'] ), 'user_dir_files_list' );
                if (!empty($arTmp) ) {
                    foreach ($arTmp as $arFile) {
                        $subFilesDelete[] = $arFile;
                    }
                }
            }
        }
        return $subFilesDelete;
    }

    /**
     * @Route("/url_error", name="url_error")
     * @Method("GET")
     * @Template("ncuoportal/url_error.html.twig")
     */
    public function aldUserError(){
        
        return array();
        
    }
    
    private function generateDenyPage($page_title=''){
        return array(
            // 'urlList' => $this->generateUrl('webmail', array()),
            'rolePerms' => $this->rolePerms,
            'message' => $this->getMessage(),
            'pageTitle'=> $page_title,
            );        
    }

    /**
     * @Route("/deny_access_show", name="deny_access_show")
     * @Method("GET")
     * @Template("ncuowebmail/webmail/deny_access.html.twig")
     */
    public function denyShowPage(){
        $page_title='Электронная почта';
        $this->setMessageError('Доступ к разделу запрещен Администратором');
        $output = $this->generateDenyPage($page_title);
        return $output;
    }

    /**
     * @Route("/deny_access_delete", name="deny_access_delete")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/deny_access.html.twig")
     */
    public function denyDeletePage(){
        $page_title='Уадаление файла';
        $this->setMessageError('Файл отсутствует, либо у вас нет прав на его удаление.');
        $output = $this->generateDenyPage($page_title);
        return $output;
    }
    
    /**
     * @Route("/deny_access_dir", name="deny_access_dir")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/deny_access.html.twig")
     */
    public function denyDirAccess(){
        $page_title='Доступ к папке';
        $this->setMessageError('Папка отсутствует либо у вас нет доступа.');
        $output = $this->generateDenyPage($page_title);
        return $output;
    }

    public function replaceSingleQuote($txt){
        return str_replace("'", '&#039;', $txt);
    }

    public function prepareDataToSend($txt='')
    {
        return addslashes(json_encode($this->replaceSingleQuote($txt))) ; 
    }

    public function getMessageStatus($txt='')
    {
        $arStatus = array(
            "prepared" => "prepared",
            "send" => "send",
            "delete" => "deleted",
        );
        if ( in_array($txt, $arStatus) ) return $arStatus[$txt];
        return false;
    }

    public function deleteCurrentEmailFromServer(){
        $arMessagesDelete = array( "E1q6p6y-0008I5-3v@", "01545d55e040bd466c91a84baa847a4a", "8ebb18ced4268cb746c69f50830a31d7" );
        $arMessagesDelete = array(); // заглушка
        if(count($arMessagesDelete) > 0 ){
            foreach ($arMessagesDelete as $message_id) {
                $res = $this->deleteUserEmailFromServer(
                    $message_id,
                    'lu1',
                    '12345678'
                );
                echo "<p>{$res[0]['status']} = {$message_id}</p>";
            }
        }
    }

}
