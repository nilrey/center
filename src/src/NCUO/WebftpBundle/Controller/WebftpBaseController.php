<?php

namespace App\NCUO\WebftpBundle\Controller;

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
 * WebftpBase controller.
 */
class WebftpBaseController extends Controller
{   
    private $SCHEMA_FILES = 'webftp';
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

            // if( !empty($this->userRoleName) && in_array($this->userRoleName, $this->arPriorityRoles) ){
                // $stmt = $this->dbconn->prepare("SELECT * FROM cms.users as u where u.username =:username  LIMIT 1");
                // $this->bindParams($stmt, array('username' => $this->loggedInUser->getUsername()) );
                // $stmt->execute();        
                // $arUser = $stmt->fetchAll()[0];
                // if (isset($arUser['oiv_id']) ) $this->idOiv = $arUser['oiv_id'];
                if( !empty($this->getLocalRequest()->attributes->get('file_hash') ) ){
                    if( $this->checkFilePermits($this->getLocalRequest()->attributes->get('file_hash')) ){
                        $this->extraPerms[$this->userRoleName][] = 'C';
                        $this->extraPerms[$this->userRoleName][] = 'U';
                        $this->extraPerms[$this->userRoleName][] = 'D';
                    }
                }
            // }

            $this->getRolePerms();
        elseif(!$this->isAuthActive()):
            $this->redirectToUrl('/');
        endif;
        if(!empty($this->session->get("role_id"))) {
            $this->loggedInUser->setRoles($this->session->get("role"));
            $this->loggedInUser->setRole(intval($this->session->get("role_id")));
        }
        
        if(!empty($_GET['list_type']) ) $this->setListType($_GET['list_type']);
        if ( empty($this->loggedInUser->getUserFtpAutologin()) // если доступ запрещен администратором
        ) {
            if( strpos($this->request->getPathInfo(), 'ftp_admin_deny_access') == false // доступна только страница ftp_admin_deny_access
            ){
                $this->session->set("ftpAutologin", "deny");
                $this->redirectToUrl('/public/index.php/webftp/ftp_admin_deny_access');
            }
        // если сквозная авторизация запрещена
        }elseif($this->loggedInUser->getUserFtpAutologin() == 1 // сквозная авторизация для пользователя запрещена администратором
        ) {
                
            if(empty($this->session->get("ftpAutologin")) || $this->session->get("ftpAutologin") != 'allow') // авторизация не пройдена
            {
                if(strpos($this->request->getPathInfo(), 'ftp_auth_form') == false
                    && strpos($this->request->getPathInfo(), 'ftp_auth_error') == false
                ): // вызываемая страница - не страница формы авторизации
                    // первый проброс на авторизацию
                    $this->redirectToUrl('/public/index.php/webftp/ftp_auth_form');
                else:
                    // проверка на содержимое POST 
                    // если пусто - пропускаем на форму авторизации в 1 раз
                    $arData = array();
                    if ( is_array($this->request->get("FIELDS") ) ): 
                        // получим данные от формы авторизации
                        $arData = $this->request->get("FIELDS");
                        if( !empty($arData['user_ftp_login'])
                            && !empty($arData['user_ftp_pass'])
                            && $arData['user_ftp_login'] == $this->loggedInUser->getUserFtpLogin() 
                            && $arData['user_ftp_pass'] == $this->loggedInUser->getUserFtpPass() 
                        ){
                        // если авторизация прошла корректно, отмечаем в сессии и отправляем на главную страницу почты 
                            $this->session->set("ftpAutologin", "allow");
                            $this->redirectToUrl('/public/index.php/webftp');
                        }else{
                        // если пустые поля или если авторизация не пройдена, то генерируем ошибку и обратно на форму авторизации
                            $this->redirectToUrl('/public/index.php/webftp/ftp_auth_error');
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

    private function getRolePerms(){
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


    private function getSqlTemplate($type)
    {
        if($type == 'user_one_file')
            // получить данные по 1 файлу для владелеца файла
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webftp_files WHERE user_id = :username AND name_hash=:name_hash';
        elseif($type == 'user_all_files')
            // получить список всех файлов для владелеца файлов
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webftp_files WHERE user_id = :user_id';
        elseif($type == 'user_dir_files')
            // получить список всех файлов для владелеца файлов из директории
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webftp_files WHERE user_id = :user_id AND dir_id=:dir_id';
        elseif($type == 'file_info')
            // получить запись файла 
            return 'SELECT * FROM '.$this->SCHEMA_FILES.'.webftp_files WHERE name_hash=:name_hash';
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

//--------------------------------------- UPDATES ---------------------------------------
        elseif($type == 'upd_file')
            return 'UPDATE '.$this->SCHEMA_FILES.'.webftp_files SET dir_id=:dir_id, title=:title, description=:description, modified=:modified WHERE user_id = :username AND name_hash=:name_hash';
        elseif($type == 'upd_directory')
            return 'UPDATE '.$this->SCHEMA_FILES.'.webftp_dir SET parent_id=:parent_id, title=:title, description=:description, sort=:sort, modified=:modified WHERE user_id = :user_id AND id=:id';

//--------------------------------------- INSERTS ---------------------------------------
        elseif($type == 'ins_file')
            return 'INSERT INTO '.$this->SCHEMA_FILES.'.webftp_files ( user_id, dir_id, name_orig, name_hash, title, content_type, ext, size, description) VALUES (:user_id, :dir_id, :name_orig, :name_hash, :title, :content_type, :ext, :size, :description)';
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

    public function constructInsert($params, $insType = '', $getId = false, $seqName = '.seq_webftp_files_id' )
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
            'urlList' => $this->generateUrl('webftp', array()),
            'rolePerms' => $this->rolePerms,
            'message' => $this->getMessage(),
            'pageTitle'=> $page_title,
            );        
    }

    /**
     * @Route("/ftp_deny_access_edit", name="ftp_deny_access_edit")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/ftp_deny_access.html.twig")
     */
    public function denyEditPage(){
        $page_title='Редактирование данных о файле';
        $this->setMessageError('Файл отсутствует, либо у вас нет прав на его редактирование.');
        $output = $this->generateDenyPage($page_title);
        return $output;
    }

    /**
     * @Route("/ftp_deny_access_show", name="ftp_deny_access_show")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/deny_access.html.twig")
     */
    public function denyShowPage(){
        $page_title='Просмотр данных о файле';
        $this->setMessageError('Файл отсутствует, либо у вас нет прав на его просмотр.');
        $output = $this->generateDenyPage($page_title);
        return $output;
    }

    /**
     * @Route("/ftp_admin_deny_access", name="ftp_admin_deny_access")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/deny_access.html.twig")
     */
    public function adminDenyAccessPage(){
        $page_title='Служба передачи файлов';
        $this->setMessageError('Доступ к разделу запрещен Администратором');
        $output = $this->generateDenyPage($page_title);
        return $output;
    }

    /**
     * @Route("/ftp_deny_access_delete", name="ftp_deny_access_delete")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/ftp_deny_access.html.twig")
     */
    public function denyDeletePage(){
        $page_title='Уадаление файла';
        $this->setMessageError('Файл отсутствует, либо у вас нет прав на его удаление.');
        $output = $this->generateDenyPage($page_title);
        return $output;
    }
    
    /**
     * @Route("/ftp_deny_access_dir", name="ftp_deny_access_dir")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/ftp_deny_access.html.twig")
     */
    public function denyDirAccess(){
        $page_title='Доступ к папке';
        $this->setMessageError('Папка отсутствует либо у вас нет доступа.');
        $output = $this->generateDenyPage($page_title);
        return $output;
    }


}
