<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    public function load(ObjectManager $manager)
    {
        $user = new User();
        // $product = new Product();
        // $manager->persist($product);
        $user->setEmail('user@server.ru');
        $user->setRoles( ['ROLE_ADMIN'] );
        $user->setRole( 1 );
        $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                '1111'
            ));
            $manager->persist($user);
            $manager->flush($user);
            
        $user = new User();
        $user->setEmail('sup@server.ru');
        $user->setRoles( ['ROLE_ADMIN'] );
        $user->setRole( 1 );
        $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'Q1w2e3r4'
            ));
            $manager->persist($user);
            $manager->flush($user);
    }
}
