<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Entity\Role;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Persistence\ObjectManager;

class SecurityController extends AbstractController
{
    private $passwordEncoder;
    
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        
        // $manager = $this->getDoctrine()->getManager();

        // $user = new User();
        // $user->setEmail('user@server.ru');
        // //$user->setRoles( ['ROLE_USER'] );
        // $user->setRole( 1 );
        // $user->setUsername( 'test11' );
        // $user->setPassword($this->passwordEncoder->encodePassword(
        //         $user,
        //         '2222'
        //     ));
        //     $manager->persist($user);
        //     $manager->flush($user);

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = $this->getUser();
        if ( $user === NULL )
        {
            $user = new User();
        }
        elseif( intval($user->getId()) > 0){
            $em = $this->getDoctrine()->getManager();
            $role = $em->getRepository('App:Role')->findOneBy(array('id' => intval($user->getRole()) ) );
            $user->setRoles($role->getName() ) ;
        }

        
        // var_dump($user->getRoles());

        return $this->render('ncuoportal/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
