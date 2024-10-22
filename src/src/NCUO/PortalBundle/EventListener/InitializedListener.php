<?php
namespace App\NCUO\PortalBundle\EventListener;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext; 
use Doctrine\ORM\EntityManager;
use NCUO\PortalBundle\Entity\User;
use NCUO\PortalBundle\Entity\UserLoginLog;


class InitializedListener
{

    private $security;
    private $twig;
    private $em;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(\Twig_Environment $twig, SecurityContext $security, EntityManager $em) {
        $this->twig      = $twig;
        $this->security  = $security;
        $this->em        = $em;
    }

    /**
    * Invoked after a successful login.
    *
    * @param InteractiveLoginEvent $event The event
    */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
		// Генерируем меню
        $this->generateMenu();
		
		// Сохраняем информацию в таблицу аудита
		$user_login_log = new UserLoginLog();
		$user_login_log->setUser($this->security->getToken()->getUser());
		$user_login_log->setLoginTimestamp(new \DateTime('now'));
		
		$this->em->persist($user_login_log);
		$this->em->flush();		
    }
    
    
    /**
    * On each request we want to generate menu for the current user
    *
    * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
    * @return void
    */ 
    public function onCoreController(FilterControllerEvent $event) {
        // if user logged in    
        if ($this->security->getToken() && $this->security->getToken()->getUser() instanceof User) {
			// Генерируем меню
            $this->generateMenu();			
        }
        
    }
    
    private function generateMenu() {
        $user   = $this->security->getToken()->getUser();
        $role   = $user->getRole();
         
        //get menu items
        $menuRepository = $this->em->getRepository('NCUOPortalBundle:MenuItems');
                            
    //    return  $menuRepository->findBy(
    //        array('parent' => null)
    //    );
        
        $rootItems = $menuRepository->getRootAllowedItems($role);    
        
        $menu = array();
        foreach ($rootItems as $item) {
            $subMenu = $menuRepository->getChildAllowedItems($role, $item->getId());
            
            $item->setSubmenu($subMenu);
            
            array_push($menu, $item);
        }
        
        $this->twig->addGlobal('menu', $menu);
    }
}