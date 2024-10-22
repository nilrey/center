<?php

namespace App\NCUO\WebftpBundle\Controller;


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
 * Webftp controller.
 */
class WebftpController extends WebftpBaseController
{
    
    public $logger;
    public $userRoleId = null;
    public $userRoleName = null;
    public $idOiv = null;
    public $parentUrl = 'webftp';
    public $security = null;
    public $session = null;
    public $request = null;
    public $isAccessGranted = null;
    public $isEditGranted = false;
    // public $rolePerms = array();
    public $extraPerms = array();
    public $arPriorityRoles = array('ROLE_ROIV', 'ROLE_FOIV', 'ROLE_VDL', 'ROLE_NCUO');
    public $ftp_path = '/public/webftp/';
    public $loggedInUser = null;
    public $dbconn = null;


    /** 
     * Lists all Webftp entities.
     *
     * @Route("/", name="webftp")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $dirFilesCount = array();
        $fileUsersCount = array();
        $listType = 'user_all_files';
        if($this->getListType() == 'user_shared_files') {
            $listType = 'user_shared_files';
        }
        $arFiles = $this->constructSelect(array('user_id'=> $this->loggedInUser->getId()), $listType );
        $allFilesSize = $this->constructSelect(array('user_id'=> $this->loggedInUser->getId()), 'all_files_size' );
        // list($arAllowedUsersId, $arAllowedUsers) = $this->getFileAllowedUsers(array('file_id'=> intval($arFiles[0]['id']) ));
        $userDirs = $this->getSortedDirs();

        $dirFilesTmp = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'user_dir_files_count');
        foreach ($dirFilesTmp as $arVal) {
            $dirFilesCount[$arVal['dir_id']] = intval($arVal['cnt']);
        }

        $fileUsersTmp = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'files_shared_users_count');
        foreach ($fileUsersTmp as $arVal) {
            $fileUsersCount[$arVal['file_id']] = intval($arVal['cnt']);
        }

        return array(
            'listFiles' => $arFiles,
            'rolePerms' => $this->rolePerms,
            'list_type' => $this->getListType(),
            'all_files_size' => intval($allFilesSize[0]["all_files_size"]),
            'urls' => array(
                'urlDirNew' => $this->generateUrl('webftp_dirnew', array() ),
            ),
            'userDirs' => $this->getSortedDirs(),
            'dirFilesCount' => $dirFilesCount,
            'fileUsersCount' => $fileUsersCount,
        );
    }

    /**
     * Добавить новый файл
     *
     * @Route("/new", name="webftp_new")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/new.html.twig")
     */
    public function actionNew()
    {
        $arUsersList = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'users_list_but_me');
        $post_max_size = intval(ini_get('post_max_size'));
        $userDirs = $this->getSortedDirs();

        return array(
            'urlAction' => $this->generateUrl('webftp_create', array() ),
            'urlList' => $this->generateUrl('webftp', array()),
            'rolePerms' => $this->rolePerms,
            'FIELDS' => $this->getEmptySet('emptyset_file'),
            'usersList' => $arUsersList,
            'post_max_size' => $post_max_size,
            'userDirs' => $userDirs,
        );
    
    }

    /**
     * Upload new file
     *
     * @Route("/new_create", name="webftp_create")
     * @Method("POST")
     * @Template("ncuowebftp/webftp/new.html.twig")
     */
    public function actionCreateNew(Request $request)
    {
        $arData = $this->getEmptySet('emptyset_file');
        $arResult = array();
        $date_upd = date('Y-m-d H:i:s');
        $post_max_size = intval(ini_get('post_max_size'));
        $arUsersList = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'users_list_but_me');

        if ( isset($_FILES['file_upload']) && is_array($request->request->get("FIELDS") ) )
        {
            $this->setMessageError("Файл получен но не обработан.");
            $user_folder = md5($this->loggedInUser->getId());
            $filepath = $_SERVER['DOCUMENT_ROOT'].$this->ftp_path.$user_folder.'/';
            if( !is_dir($filepath) ):
                try{
                    mkdir($filepath, 0777, true);                    
                }catch(Exception $e){
                    $this->setMessageError("Раздел пользователя на диске не может быть создан. Уточните наличие достаточных прав.");
                }
            endif;
            
            if( is_dir($filepath) ):
                $users_allowed = array();
                $params = array();

                $file_upload = $_FILES['file_upload'];
                foreach ($request->request->get("FIELDS") as $key => $value) {
                    $arData[$key] = trim($value);
                }

                $params['user_id'] = $this->loggedInUser->getId();
                $params['dir_id'] = intval($arData['dir_id']);
                $params['name_orig'] = $file_upload['name'];
                $params['name_hash'] = md5( $file_upload['name'].$date_upd);
                $params['title'] = empty($arData['title']) ? $file_upload['name'] : $arData['title'];
                $params['content_type'] = $file_upload['type'];
                $params['ext'] = mb_substr($file_upload['name'],  mb_strpos($file_upload['name'], '.')+1 ) ;
                $params['size'] = $file_upload['size'];
                $params['description'] = $arData['description'];

                $save_res = move_uploaded_file( $file_upload['tmp_name'], $filepath.$params['name_hash'] );

                if($save_res):

                    $file_id = $this->constructInsert( $params, 'ins_file', true, '.seq_webftp_files_id');

                    if( !empty($arData['users_allowed_str']) )
                    {
                        $users_allowed = explode('&', str_replace('u=', '', $arData['users_allowed_str']) );
                    }

                    foreach($users_allowed as $key => $value)
                    {
                        $this->constructInsert( array('file_id' => $file_id, 'user_id' => $value), 'ins_access' );
                    }

                    $this->setMessageSuccess("Файл успешно сохранен.");

                    $arData = $this->getEmptySet('emptyset_file');
                else:
                    $this->setMessageError("Раздел пользователя на диске не может быть создан. Сохранение отклонено сервером.");
                endif; // if($save_res)
            endif;

        }

        return array(
            'urlAction' => $this->generateUrl('webftp_create', array() ),
            'urlList' => $this->generateUrl('webftp', array()),
            'rolePerms' => $this->rolePerms,
            'FIELDS' => $arData,
            'usersList' => $arUsersList,
            'message' => $this->getMessage(),
            'post_max_size' => $post_max_size,
        );
    }

    /**
     *
     * @Route("/{file_hash}/show", name="webftp_show")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/show.html.twig")
     */
    public function showAction($file_hash)
    {
        $userOwner = array();
        $arUsersList = array();
        $arAllowedUsers = array();
        $arAllowedUsersId = array();
        // поиск файла в базе по хеш
        $arFile = $this->getFileInfo($file_hash);
        if ( empty($arFile) || intval($arFile['id']) < 1 ) {
            return $this->redirect($this->generateUrl('ftp_deny_access_show', array() ) );
        }
        // список расшаренных пользователей файла
        list($arAllowedUsersId, $arAllowedUsers) = $this->getFileAllowedUsers(array('file_id'=> intval($arFile['id']) ));
        // проверка доступа для текущего пользователя
        if(
            !in_array($this->loggedInUser->getId(), $arAllowedUsersId )
            && intval($arFile['user_id']) != $this->loggedInUser->getId()
        ){
            return $this->redirect($this->generateUrl('ftp_deny_access_show', array() ) );
        }
        if( in_array($this->loggedInUser->getId(), $arAllowedUsersId ) ) // current user is shared
        {
            // get file owner info
            $arOwnerInfoTmp = $this->constructSelect(array('user_id' => $arFile['user_id'] ), 'user_info');
            if( !empty($arOwnerInfoTmp)) $userOwner = $arOwnerInfoTmp[0];
        }

        $arUsersList = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'users_list_but_me');

        // quick hack - change in future
        if(intval($arFile['user_id']) != $this->loggedInUser->getId()){
            $arUsersList = array();
            $arAllowedUsers = array();
            $arAllowedUsersId = array();
        }

        $userDirs = $this->getSortedDirs();

        $dirFilesTmp = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'user_dir_files_count');
        foreach ($dirFilesTmp as $arVal) {
            $dirFilesCount[$arVal['dir_id']] = $arVal['cnt'];
        }

        return array(
            'FIELDS' => $arFile,
            'urlEdit' => $this->generateUrl('webftp_edit', array('file_hash'=>$file_hash )),
            'urlList' => $this->generateUrl('webftp', array()),
            'rolePerms' => $this->rolePerms,
            'allowedUsers' => $arAllowedUsers,
            'allowedUsersId' => $arAllowedUsersId,
            'message' => $this->getMessage(),
            'userOwner' => $userOwner,
            'userDirs' => $userDirs,
            'dirFilesCount' => $dirFilesCount,
        );
    
    }

    /**
     *
     * @Route("/{file_hash}/edit", name="webftp_edit")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/edit.html.twig")
     */
    public function editAction($file_hash)
    {
        return $this->prepareEditFormData($file_hash);
    }

    /**
     *
     * @Route("/{file_hash}/update", name="webftp_update")
     * @Method("POST")
     * @Template("ncuowebftp/webftp/edit.html.twig")
     */
    public function updateAction(Request $request, $file_hash)
    {
        $arData = array();
        if ( is_array($request->request->get("FIELDS") ) ) $arData = $request->request->get("FIELDS");
        $arResult = $this->prepareEditFormData($file_hash, $arData);
        
        return $arResult;
    }

    
    private function prepareEditFormData($file_hash, $arData = array() )
    {   
        $arAllowedUsers = array();
        $arAllowedUsersId = array();

        // поиск файла в базе с привязкой к пользователю
        $arFiles = $this->constructSelect(array('username'=>$this->loggedInUser->getId(), 'name_hash'=> $file_hash) );
        if ( empty($arFiles) ) {
            return $this->redirect($this->generateUrl('ftp_deny_access_edit', array() ) );
        }

        if( $this->userRoleName != 'ROLE_ADMIN' && $arFiles[0]['user_id'] != $this->loggedInUser->getId() ):
            $this->setMessageError("Файл отсутствует, либо у вас нет прав на его изменение.");
        elseif(count($arFiles) > 2 ):
            $this->setMessageError("Ошибка записей в базе данных, множественное значение UUID");
            $arFiles[0] = $this->getEmptySet('emptyset_file');
        else:
            list($arAllowedUsersId, $arAllowedUsers) = $this->getFileAllowedUsers(array('file_id'=> $arFiles[0]['id']) );
            if( count($arData) > 0 ):
                $file_name = '';
                // check file owner
                $params = array( 
                    'username' => $this->loggedInUser->getId(), 
                    'name_hash' => $file_hash, 
                    'title' => trim($arData['title']),
                    'dir_id' => intval($arData['dir_id']),
                    'description' => trim($arData['description']), 
                    'modified' => date("Y-m-d H:i:s") 
                );
                // update table files
                $this->constructUpdate($params, 'upd_file' );

                // проверка на полученых shared users
                if( !empty($arData['users_allowed_str']) ){
                    $arData['users_allowed_str'] = str_replace('u=', '', $arData['users_allowed_str']);
                    $arData['users_allowed'] = explode('&', $arData['users_allowed_str']);

                    // !!! Добавить проверку на существование пользователей
                    if($arAllowedUsers != $arData['users_allowed'] )
                    {
                        // Delete all users_id for this file
                        $this->constructDelete( array('file_id' => $arFiles[0]['id']), 'delete_shared_users' );

                        // Insert new users_id
                        foreach ($arData['users_allowed'] as $u_id) 
                        {
                            $this->constructInsert( array('file_id' => $arFiles[0]['id'], 'user_id' => $u_id), 'ins_access' );
                        }
                    }
                }else{
                    // Delete all users_id for this file
                    $this->constructDelete( array('file_id' => $arFiles[0]['id']), 'delete_shared_users' );
                }
                $this->setMessageSuccess("Данные успешно сохранены.");
                // запрос на измененые данные
                $arFiles = $this->constructSelect(array('username'=>$this->loggedInUser->getId(), 'name_hash'=> $file_hash) );
                
                list($arAllowedUsersId, $arAllowedUsers) = $this->getFileAllowedUsers(array('file_id'=> $arFiles[0]['id']) );
            endif;

            $arUsersList = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'users_list_but_me');

        endif;

        $userDirs = $this->getSortedDirs();

        $dirFilesTmp = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'user_dir_files_count');
        foreach ($dirFilesTmp as $arVal) {
            $dirFilesCount[$arVal['dir_id']] = $arVal['cnt'];
        }

        return array(
            'FIELDS'   => $arFiles[0],
            "urlAction"=> $this->generateUrl('webftp_update', array('file_hash'=>$file_hash )),
            "urlView"  => $this->generateUrl('webftp_show', array('file_hash'=>$file_hash )),
            'urlList' => $this->generateUrl('webftp', array()),
            'rolePerms' => $this->rolePerms,
            'message' => $this->getMessage(),
            'usersList' => $arUsersList,
            'allowedUsers' => $arAllowedUsers,
            'allowedUsersId' => $arAllowedUsersId,
            'userDirs' => $userDirs,
            'dirFilesCount' => $dirFilesCount,
            );
    }

    
    /**
     * Delete user file
     *
     * @Route("/{file_hash}/delete", name="webftp_delete")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/delete.html.twig")
     */
    public function deleteAction($file_hash)
    {
        // проверка доступа для текущего пользователя
        $arFile = $this->getFileInfo($file_hash);
        if ( empty($arFile) 
            || intval($arFile['id']) < 1 
            || intval($arFile['user_id']) != $this->loggedInUser->getId()
        ){
            return $this->redirect($this->generateUrl('ftp_deny_access_delete', array() ) );
        }
        // формируем путь к файлу
        $user_folder = md5($this->loggedInUser->getId());
        $filepath = $_SERVER["DOCUMENT_ROOT"] . $this->ftp_path."{$user_folder}/{$arFile['name_hash']}";

        if( file_exists($filepath) ):
            $this->deleteFileRecord($arFile['id'], $filepath);
            $this->setMessageSuccess("Файл успешно удален.");
        else:
            return $this->redirect($this->generateUrl('ftp_deny_access_delete', array() ) );
        endif;

        return array(
                'file_name' => $arFile['title'],
                'message' => $this->getMessage(),
                'rolePerms' => $this->rolePerms,
               );
    }

    public function deleteFileRecord($file_id, $filepath){
        $this->constructDelete( array('file_id' => $file_id), 'delete_shared_users' );
        $this->constructDelete( array('user_id' => $this->loggedInUser->getId(), 'file_id' => $file_id), 'delete_user_file' );
        unlink($filepath);

    }

    /**
     * Скачать файл
     *
     * @Route("/{file_hash}/download", name="webftp_download")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/download.html.twig")
     */
    public function actionDownload($file_hash)
    {
        $userOwner = array();
        $arUsersList = array();
        $arAllowedUsers = array();
        $arAllowedUsersId = array();
        // поиск файла в базе по хеш
        $arFile = $this->getFileInfo($file_hash);
        if ( empty($arFile) || intval($arFile['id']) < 1 ) {
            return $this->redirect($this->generateUrl('ftp_deny_access_show', array() ) );
        }
        // список расшаренных пользователей файла
        list($arAllowedUsersId, $arAllowedUsers) = $this->getFileAllowedUsers(array('file_id'=> intval($arFile['id']) ));
        // проверка доступа для текущего пользователя
        if(
            !in_array($this->loggedInUser->getId(), $arAllowedUsersId )
            && intval($arFile['user_id']) != $this->loggedInUser->getId()
        ){
            return $this->redirect($this->generateUrl('ftp_deny_access_show', array() ) );
        }
        // формируем путь к файлу
        $filepath = $_SERVER["DOCUMENT_ROOT"] . $this->ftp_path.(md5($arFile['user_id']))."/{$arFile['name_hash']}";

        $arFile['ext'] = trim($arFile['ext']);
        // проверка на наличие расширения в конце названия файла 
        if( !empty($arFile['ext']) && $arFile['ext'] !== mb_substr($arFile['title'], -1*mb_strlen($arFile['ext'])) ){
            $arFile['title'] .= ".{$arFile['ext']}";
        }

        if (file_exists($filepath)) {
            // Generate output file
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for internet explorer
            header("Content-Type: {$arFile['content_type']}");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:".filesize($filepath));
            header("Content-Disposition: attachment; filename={$arFile['title']}");
            readfile($filepath);
            die();        
        } else {
            // $this->setMessageError("Файл не найден.");
            return $this->redirect($this->generateUrl('ftp_deny_access_delete', array() ) );
        } 
            
        return array(
                'file_hash' => $file_hash,
                'message' => $this->getMessage(),
                'rolePerms' => $this->rolePerms,
               );
    }
    
    
    /**
     * Скачать файл
     *
     * @Route("/{file_hash}/duplicate", name="webftp_duplicate")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/duplicate.html.twig")
     */
    public function actionDuplicate($file_hash)
    {
        $date_upd = date('Y-m-d H:i:s');
        // поиск файла в базе по хеш
        $arFile = $this->getFileInfo($file_hash);
        if ( empty($arFile) || intval($arFile['id']) < 1 ) {
            return $this->redirect($this->generateUrl('ftp_deny_access_show', array() ) );
        }
        // список расшаренных пользователей файла
        list($arAllowedUsersId, $arAllowedUsers) = $this->getFileAllowedUsers(array('file_id'=> intval($arFile['id']) ));
        // проверка доступа для текущего пользователя
        if(
            !in_array($this->loggedInUser->getId(), $arAllowedUsersId )
            && intval($arFile['user_id']) != $this->loggedInUser->getId()
        ){
            return $this->redirect($this->generateUrl('ftp_deny_access_show', array() ) );
        }

        // Duplicate file proccess
        // path vars
        $from_user_folder = md5($arFile['user_id']);
        $from_filepath = $_SERVER['DOCUMENT_ROOT'].$this->ftp_path.$from_user_folder.'/';
        $to_user_folder = md5($this->loggedInUser->getId());
        $to_filepath = $_SERVER['DOCUMENT_ROOT'].$this->ftp_path.$to_user_folder.'/';

        if ( !is_dir($from_filepath) ):
            $this->setMessageError("Раздел пользователя на диске не существует.");
        elseif( !is_dir($to_filepath) ):
            try{
                mkdir($to_filepath, 0777, true);                    
            }catch(Exception $e){
                $this->setMessageError("Раздел копирования на диске не определен. Уточните наличие достаточных прав.");
            }
        endif;

        $from_filename = $arFile['name_hash'];
        $to_filename = md5( $arFile['name_orig'].$date_upd);
        if( !file_exists($from_filepath.$from_filename) ):
            $this->setMessageError("Копируемый файл не существует.");
        else:
            $params['user_id'] = $this->loggedInUser->getId();
            $params['name_hash'] = md5( $arFile['name_orig'].$date_upd);
            $params['dir_id'] = 0;
            $params['name_orig'] = $arFile['name_orig'];
            $params['title'] = $arFile['title']." [Копия]";
            $params['content_type'] = $arFile['content_type'];
            $params['ext'] = $arFile['ext'];
            $params['size'] = $arFile['size'];
            $params['description'] = $arFile['description'];                    

            $save_res = copy( $from_filepath.$from_filename, $to_filepath.$params['name_hash'] );
            if($save_res):

                $res = $this->constructInsert( $params, 'ins_file');
                if($res):
                    $this->setMessageSuccess("Файл \"{$params['title']}\" успешно сохранен.");
                    $file_hash = $params['name_hash'];
                endif;

            else:
                $this->setMessageError("Файл не может быть создан. Сохранение отклонено сервером.");
            endif; // if($save_res)
        endif;
        
        return array(
            'urlAction' => $this->generateUrl('webftp_create', array() ),
            'urlList' => $this->generateUrl('webftp', array()),
            "urlView"  => $this->generateUrl('webftp_show', array('file_hash'=>$file_hash )),
            'rolePerms' => $this->rolePerms,
            'message' => $this->getMessage(),
        );
    }    


    /**
     * Добавить новый раздел
     *
     * @Route("/dirnew", name="webftp_dirnew")
     * @Template("ncuowebftp/webftp/dirnew.html.twig")
     */
    public function actionNewDir(Request $request)
    {
        $arUsersList = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'users_list_but_me');
        $post_max_size = intval(ini_get('post_max_size'));

        $userDirs = $this->getSortedDirs();

        if( is_array($request->request->get("FIELDS") ) ):
            $arData = $request->request->get("FIELDS");
            $params['user_id'] = $this->loggedInUser->getId();
            $params['parent_id'] = empty($arData['parent_id']) ? 0 : intval($arData['parent_id']);
            $params['title'] = $arData['title'];
            $params['description'] = $arData['description'];
            $params['sort'] = 0;

            $dir_id = $this->constructInsert( $params, 'ins_directory', true, '.seq_webftp_dir_id');
            if($dir_id > 0)
                return $this->redirect($this->generateUrl('webftp_diredit', array('dir_id' => $dir_id, 'message' => 'success') ) );
            else
                $this->setMessageSuccess('Ошибка создания директории. Запись не сохранена.');

        endif;

        return array(
            'message' => $this->getMessage(),
            'urls' => array(
                'urlAction' => $this->generateUrl('webftp_dirnew', array() ),
                'urlList' => $this->generateUrl('webftp', array()),
            ),
            'rolePerms' => $this->rolePerms,
            'FIELDS' => $this->getEmptySet('emptyset_dir'),
            'userDirs' => $userDirs,
        );
    
    }

    /**
     *
     * @Route("/{dir_id}/diredit", name="webftp_diredit")
     * @Template("ncuowebftp/webftp/diredit.html.twig")
     */
    public function direditAction(Request $request, $dir_id, $message = '')
    {
        $dir_id = intval($dir_id);

        if( $dir_id > 0 ){
            $arTmp = $arData = $this->constructSelect(array('user_id' => $this->loggedInUser->getId(), 'dir_id' => $dir_id ), 'user_dir');
            if( empty($arTmp) )
            {
                return $this->redirect($this->generateUrl('ftp_deny_access_dir', array() ) );
            }
            $arData = $arTmp[0];
        }

        if( is_array($request->request->get("FIELDS") ) ):
            $arData = $request->request->get("FIELDS");
            $params['user_id'] = $this->loggedInUser->getId();
            $params['parent_id'] = empty($arData['parent_id']) ? 0 : intval($arData['parent_id']);
            $params['title'] = $arData['title'];
            $params['description'] = $arData['description'];
            $params['sort'] = 0;

            if( $dir_id > 0 ){
                $params['id'] = $dir_id;
                $params['modified'] = date("Y-m-d H:i:s");
                $this->constructUpdate($params, 'upd_directory' );
                $this->setMessageSuccess('Запись успешно обновлена.');
            }else{
                $dir_id = $this->constructInsert( $params, 'ins_directory', true, '.seq_webftp_dir_id');
                if($dir_id > 0)
                    $this->setMessageSuccess('Папка успешно создана.');
                else
                    $this->setMessageSuccess('Ошибка сохранения папки. Запись не сохранена.');
            }

        endif;

        $arTmp = $arData = $this->constructSelect(array('user_id' => $this->loggedInUser->getId(), 'dir_id' => $dir_id ), 'user_dir');
        if( empty($arTmp) )
        {
            return $this->redirect($this->generateUrl('ftp_deny_access_dir', array() ) );
        }
        $arData = $arTmp[0];

        if( !empty($request->query->get("message")) && $request->query->get("message") == 'success') $this->setMessageSuccess('Папка успешно создана.');

        // $userDirs = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'user_dirs');
        $userDirs = $this->getSortedDirs();
        // echo "<pre>", print_r($userDirs), "</pre>";
        return array(
            'FIELDS'   => $arData,
            'urls' => array(
                'action' => $this->generateUrl('webftp_diredit', array('dir_id'=> $dir_id) ),
                'list' => $this->generateUrl('webftp', array()),
                "view"  => $this->generateUrl('webftp_dirshow', array('dir_id'=> $dir_id) ),
            ),
            'rolePerms' => $this->rolePerms,
            'message' => $this->getMessage(),
            'userDirs' => $userDirs,
            );
    }

    /** 
     * List dir files.
     *
     * @Route("/{dir_id}/dirshow", name="webftp_dirshow")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/dirshow.html.twig")
     */
    public function dirshowAction(Request $request, $dir_id)
    {
        $dirFilesCount = array();
        // check access
        if($dir_id > 0){
            $arTmp = $this->constructSelect(array('user_id' => $this->loggedInUser->getId(), 'dir_id' => $dir_id ), 'user_dir');
            if( empty($arTmp) )
            {
                return $this->redirect($this->generateUrl('ftp_deny_access_dir', array() ) );
            }
            $dirInfo = $arTmp[0];
            $this->permitUpdateGrant();
        }else{
            $dirInfo = $this->getRootDirectory();
        }

        $arFiles = $this->constructSelect(array('user_id'=> $this->loggedInUser->getId() , 'dir_id' => $dir_id), 'user_dir_files' );
        $allFilesSize = $this->constructSelect(array('user_id'=> $this->loggedInUser->getId()), 'all_files_size' );

        $userDirs = $this->getSortedDirs();

        $dirFilesTmp = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'user_dir_files_count');
        foreach ($dirFilesTmp as $arVal) {
            $dirFilesCount[$arVal['dir_id']] = $arVal['cnt'];
        }

        $fileUsersTmp = $this->constructSelect(array('user_id' => $this->loggedInUser->getId() ), 'files_shared_users_count');
        foreach ($fileUsersTmp as $arVal) {
            $fileUsersCount[$arVal['file_id']] = intval($arVal['cnt']);
        }

        return array(
            'entities' => $arFiles,
            'rolePerms' => $this->rolePerms,
            'list_type' => $this->getListType(),
            'all_files_size' => intval($allFilesSize[0]["all_files_size"]),
            'urls' => array(
                'list' => $this->generateUrl('webftp', array()),
                'edit' => $this->generateUrl('webftp_diredit', array('dir_id' => $dir_id)),
                'urlDirNew' => $this->generateUrl('webftp_dirnew', array() ),
            ),
            'userDirs' => $this->getSortedDirs(),
            'dirFilesCount' => $dirFilesCount,
            'dirTitle' => $dirInfo['title'],
            'fileUsersCount' => $fileUsersCount,
        );
    }

    
    /**
     * Delete user dir
     *
     * @Route("/{dir_id}/dirdelete", name="webftp_dirdelete")
     * @Method("GET")
     * @Template("ncuowebftp/webftp/dirdelete.html.twig")
     */
    public function deleteDirAction(Request $request, $dir_id)
    {
        $dir_id = intval($dir_id);
        // проверка доступа для текущего пользователя
        if( $dir_id > 0 ){
            $arTmp = $arData = $this->constructSelect(array('user_id' => $this->loggedInUser->getId(), 'dir_id' => $dir_id ), 'user_dir');
        }
        if( empty($arTmp) )
        {
            return $this->redirect($this->generateUrl('ftp_deny_access_dir', array() ) );
        }
        $arData = $arTmp[0];

        if( is_array($request->request->get("FIELDS") ) 
            && $request->request->get("FIELDS")['delete_complete'] == "yes"):
            // Deleted all child direcories and files
            $subDirsDelete = $this->collectSubDirs($dir_id);
            $subFilesDelete = $this->collectSubFiles(array_merge(array(array("id" =>$dir_id )), $subDirsDelete) );

            // Delete files 
            if(count($subFilesDelete) > 0 ){
                foreach ($subFilesDelete as $arFile) {
                    // формируем путь к файлу
                    $filepath = $_SERVER["DOCUMENT_ROOT"] . $this->ftp_path.(md5($arFile['user_id']))."/{$arFile['name_hash']}";
                    $this->deleteFileRecord($arFile['id'], $filepath);
                }
            }
            // Delete sub directories 
            if(count($subDirsDelete) > 0 ){
                foreach ($subDirsDelete as $arDir) {
                    $this->constructDelete( array('user_id' => $this->loggedInUser->getId(), 'dir_id' => $arDir['id']), 'delete_user_dir' );
                }
            }

        endif;


        $subDirsDelete = $this->collectSubDirs($dir_id);
        $subFilesDelete = $this->collectSubFiles(array_merge(array(array("id" =>$dir_id )), $subDirsDelete) );

        if( count($subDirsDelete) > 0 || count($subFilesDelete) > 0 ){
            $txt_dirs = count($subDirsDelete) > 0 ? "папки":"";
            $txt_files = count($subFilesDelete) > 0 ? "файлы":"";
            $txt_and = count($subDirsDelete) && count($subFilesDelete) ? " и " :"";
            $this->setMessageError("Папка \"{$arData['title']}\" содержит вложеные {$txt_dirs}{$txt_and}{$txt_files}. Вы действительно хотите продолжить удаление? ");
        }else{
            // Here delete directory record
            $this->constructDelete( array('user_id' => $this->loggedInUser->getId(), 'dir_id' => $dir_id), 'delete_user_dir' );

            $this->setMessageSuccess("Папка \"{$arData['title']}\" успешно удалена.");
        }
        // echo "<pre>", print_r($userDirs), "</pre>";

        // $this->setMessageSuccess("Папка \"{$arData['title']}\" успешно удалена.");

        // Получим все дочерние папки и их вложенные файлы
/*
            // формируем путь к файлу
            $user_folder = md5($this->loggedInUser->getId());
            $filepath = $_SERVER["DOCUMENT_ROOT"] . $this->ftp_path."{$user_folder}/{$arFile['name_hash']}";

            if( file_exists($filepath) ):
                $this->constructDelete( array('file_id' => $arFile['id']), 'delete_shared_users' );
                $this->constructDelete( array('user_id' => $this->loggedInUser->getId(), 'file_id' => $arFile['id']), 'delete_user_file' );
                unlink($filepath);
                $this->setMessageSuccess("Папка "{$arData['title']}" успешно удалена.");
            else:
                return $this->redirect($this->generateUrl('ftp_deny_access_delete', array() ) );
            endif;*/

        return array(
                'file_name' => $arData['title'],
                'message' => $this->getMessage(),
                'rolePerms' => $this->rolePerms,
                'subDirsDelete' => $subDirsDelete,
                'subFilesDelete' => $subFilesDelete,
                'urls' => array(
                    "action" => $this->generateUrl('webftp_dirdelete', array("dir_id"=>$dir_id) ),
                    "list" => $this->generateUrl('webftp', array() ),
                ),
               );
    }


    /**
     * Форма доступа к ftp
     *
     * @Route("/ftp_auth_form", name="ftp_auth_form")
     * @Template("ncuowebftp/webftp/ftp_auth_form.html.twig")
     */
    public function actionFtpAuthForm()
    {

        $Stat['inbox_total'] = 0;
        return array(
            'urlAction' => $this->generateUrl('ftp_auth_form', array() ),
            'urlList' => '',
            'rolePerms' => $this->rolePerms,
            'message' => $this->getMessage(),
            'entity' => array('id'=>1, 'name'=>''),
            'Stat' => $Stat,
        );
    }


    /**
     * Ошибка в форме доступа к ftp
     *
     * @Route("/ftp_auth_error", name="ftp_auth_error")
     * @Template("ncuowebftp/webftp/ftp_auth_form.html.twig")
     */
    public function actionFtpAuthError()
    {
        $this->setMessageError("Неверный логин или пароль.");
        return $this->actionFtpAuthForm();
    }


}
