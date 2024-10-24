<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

use Psr\Log\LoggerInterface;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $isAldActive = false;
    private $isSvipActive = false;

    private $logger;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;

        try{
            if ( 'on' === strtolower($_ENV['ALD_AUTH_ON'] ) ){
                $this->isAldActive = true;
            }
        }
        catch(\Exception $e){
            $this->isAldActive = false;
        }

        try{
            if ( 'on' === strtolower($_ENV['SVIP_AUTH_ON'] ) ){
                $this->isSvipActive = true;
            }
        }
        catch(\Exception $e){
            $this->isSvipActive = false;
        }
        $this->logger = $logger;

        
        // if( $this->isAldActive === "on" ):
        //     $remote_user = "a.suprun@DEV.AORTI.TECH"; //$_SERVER['REMOTE_USER'];
        //     $username = substr($remote_user, 0, strpos( $remote_user, '@') );
        //     var_dump($username);
        //     // $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        //     return new RedirectResponse($this->urlGenerator->generate('region'));
        // endif;
        // var_dump($_SERVER);
        // die("__construct");
        
    }

    public function supports(Request $request)
    {
/**/
        // if ALD is on there is no local authorization 

        if(self::LOGIN_ROUTE === $request->attributes->get('_route') 
            
        ) {
            if($this->isAldActive === true ){
                return true;
            }
            
        }

/**/

         return self::LOGIN_ROUTE === $request->attributes->get('_route') && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        // if ALD_AUTH_ON is ON than pass user without password
        $ald_user = null;
        if( $this->isAldActive === true && !empty($_SERVER['REMOTE_USER']) ):   
            $remote_user = $_SERVER['REMOTE_USER']; //"citis@DEV.AORTI.TECH"
            $username = substr($remote_user, 0, strpos( $remote_user, '@') );
/*
            // GET Ald User group from SWIP
            $handle = @fopen(__DIR__."/ald_users.txt", "r");
            if ($handle) {
                
                while (($buffer = fgets($handle, 4096)) !== false) {
                    if( $username == trim($buffer) ) {
                       $ald_user = $username;
                    }
                }
                if (!feof($handle)) {
                    die("Ошибка: некорректное окончание файла");
                }
                fclose($handle);
            }else{
                die("Ошибка: список аккредитованных ALD аккаунтов не найден");
            }
            $ald_groups = array();
            $ald_user_info = 'none';
            $USER_ROLE_UID = '';
            if(!empty($ald_user)){
                // GET USER_ROLE FROM SWIP (ROLE_ADMIN, ROLE_NCUO, ROLE_VDL, ROLE_FOIV)

                $arSvipGroups = $this->getSvipGroups();
                $arSvipAccounts = $this->getSvipAccounts();
                foreach ($arSvipAccounts as $obUser) {
                    if( strtolower($remote_user) === strtolower($obUser->account_id) ){
                        $USER_ROLE_UID = $obUser->group_id;
                        break;
                    }
                }
                foreach ($arSvipGroups as $obGroup) {
                    if( $USER_ROLE_UID === $obGroup->uid ){
                        $USER_ROLE_NAME = $obGroup->name;
                        break;
                    }
                }
                
            }
*/
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            // $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $request->request->get('username') ]);
            if($user === null){
                $username = 'ALD_AUTH_USER';
            }
            $request->request->set('username', $username);
        endif;

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $request->request->get('username') ]);
        
            // if( $user === null ){
            //     $this->isAldActive = false;
            //     var_dump($this->isAldActive);
            //     throw new CustomUserMessageAuthenticationException('Ald Username could not be found.');
            //     die();
            // }
        $credentials = [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        

        return $credentials;
    }

    public function getSvipGroups()
    {
        $server_name = 'http://1adr-book02.dev.aorti.tech:8840';
        $ald_login = $_ENV['ALD_SWIP_ACCOUNT']; //'citis@DEV.AORTI.TECH';
        $set_id = $_ENV['ALD_SWIP_SET_ID']; //'f1569754-d8a8-4c1e-96ce-1b3ba5d9132d';

        $path = "/public/api/idm/{$set_id}/group/";

        $url = $_ENV['ALD_AUTH_SERVER_PROTOCOL'].'://'.$_ENV['ALD_AUTH_SERVER_NAME'];
        if($_ENV['ALD_AUTH_SERVER_PORT'] != 80 ){
            $url .= ':'.$_ENV['ALD_AUTH_SERVER_PORT'];
        }
        $url .= $path;
        $error = array();
        $http_method = 'GET';
        // echo "<p>url={$url}</p>";

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Authorization:Explicit {$ald_login}\r\nContent-type: application/json\r\n",
                'method'  => $http_method,
            )
        );
        $context  = stream_context_create($options);

        // echo "<p>Begin of script</p>";
        try {
            $result = file_get_contents($url, false, $context);

            if ($result === false) { 
                echo "<p>Error: Account was not found</p>";
                $content = error_get_last();
                // var_dump($error);
            }else{
                // echo "<p>Account Info:</p>";
                $content = json_decode($result)->data;
            }
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }

        return $content;
    }

    public function getSvipAccounts()
    {
        /*
        GET /public/api/idm/31fdfd9e-2a54-4b90-9a5b-3986b17af2e5/account_group/ HTTP/1.1
        Authorization: Explicit kraken@domain
        Content-Type: application/json
        Host: http://1adr-book02.dev.aorti.tech:8840
        */


        $content = '';
        $server_name = 'http://1adr-book02.dev.aorti.tech:8840';
        $ald_login = $_ENV['ALD_SWIP_ACCOUNT']; //'citis@DEV.AORTI.TECH';
        $set_id = $_ENV['ALD_SWIP_SET_ID']; //'f1569754-d8a8-4c1e-96ce-1b3ba5d9132d';

        $path = "/public/api/idm/{$set_id}/account_group/";

        $url = $_ENV['ALD_AUTH_SERVER_PROTOCOL'].'://'.$_ENV['ALD_AUTH_SERVER_NAME'];
        if($_ENV['ALD_AUTH_SERVER_PORT'] != 80 ){
            $url .= ':'.$_ENV['ALD_AUTH_SERVER_PORT'];
        }
        $url .= $path;
        $error = array();
        $http_method = 'GET';

            // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Authorization:Explicit {$ald_login}\r\nContent-type: application/json\r\n",
                'method'  => $http_method,
            )
        );
        $context  = stream_context_create($options);

        try {
            $result = file_get_contents($url, false, $context);
            // var_dump(json_decode($result)->data);

            if ($result === false) { 
                // echo "<p>Error: Account was not found</p>";
                $error = error_get_last();
                var_dump($error);
                die();
            }else{
                // echo "<p>Account Info:</p>";
                $content = json_decode($result)->data;
            }
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }

        return $content;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
/**/
        // if ALD is ON there is no local authorization 
        if( $this->isAldActive !== true ){
            $token = new CsrfToken('authenticate', $credentials['csrf_token']);
            if (!$this->csrfTokenManager->isTokenValid($token)) {
                throw new InvalidCsrfTokenException();
            }
        }
        else{
            $credentials['username'] = mb_substr($_SERVER['REMOTE_USER'], 0, mb_strpos($_SERVER['REMOTE_USER'], '@'));
        }
/**/        
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username']]);

        if (!$user) {
            // fail authentication with a custom error
            if( $this->isAldActive === true ):
            	$error_message = 'ALD Пользователь не найден';
            else:
                $error_message = 'Пользователь не найден';
            endif;
            $this->logger->error($error_message);
            // throw new CustomUserMessageAuthenticationException($error_message);
            echo $error_message;
            die();
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
/**/
        // if ALD is on there is no local authorization 
        if( $this->isAldActive === true ):   
            return true;
        endif;    
/**/        
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
        
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $redirectUrl = 'url_manager';
        // if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
        //     return new RedirectResponse($targetPath);
        // }

        // // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);

        // check ALD USERNAME in svip 

        $this->logger->info('User auth success');
        $request->getSession()->set("role_id", null);


        if( $this->isAldActive === true ): 
            if( $this->isSvipActive === true ): 
                $arSvipAccounts = $this->getSvipAccounts();
                $arSvipGroups = $this->getSvipGroups();
                $isSvipUser = false;
            endif;

            $user = $_SERVER['REMOTE_USER']; //"citis@citis.local"
            if(empty($user)){
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $request->request->get('username') ])->getUsername();
            }

            if( !empty($user) ):
                if( $this->isSvipActive === true ){
                    foreach ($arSvipAccounts as $obUser) {
                        if( strtolower($user) === strtolower($obUser->account_id) ){
                            $isSvipUser = true;
                            $USER_ROLE_UID = $obUser->group_id;
                            break;
                        }
                    }
                    foreach ($arSvipGroups as $obGroup) {
                        if( $USER_ROLE_UID === $obGroup->uid ){
                            $USER_ROLE_NAME = $obGroup->name;
                            $request->getSession()->set("role", $USER_ROLE_NAME);
                            $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $USER_ROLE_NAME]);
                            $request->getSession()->set("role_id", $role->getId());
                            break;
                        }
                    }

                    if( !$isSvipUser )
                    {
                        // return new RedirectResponse($this->urlGenerator->generate('svip_user_error'));
                        $redirectUrl = 'svip_user_error';
                    }

                }else{
                    $role_id = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $request->request->get('username') ])->getRole();
                    $request->getSession()->set("role_id", $role_id );
                    $role = $this->entityManager->getRepository(Role::class)->findOneBy(['id' => $role_id]);
                    $request->getSession()->set("role", $role->getName());
                    // die("finished");
                }

            endif;

        endif;

        

        return new RedirectResponse($this->urlGenerator->generate($redirectUrl));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
