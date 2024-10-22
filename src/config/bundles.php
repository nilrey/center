<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    
    App\NCUO\PortalBundle\NCUOPortalBundle::class => ['all' => true],
    App\NCUO\OivBundle\NCUOOivBundle::class => ['all' => true],
    App\NCUO\RegionBundle\NCUORegionBundle::class => ['all' => true],
    App\NCUO\EifBundle\NCUOEifBundle::class => ['all' => true],
    App\NCUO\FoivBundle\NCUOFoivBundle::class => ['all' => true],
    App\NCUO\FuncBundle\NCUOFuncBundle::class => ['all' => true],
    App\NCUO\MapBundle\NCUOMapBundle::class => ['all' => true],
    App\NCUO\ServiceBundle\NCUOServiceBundle::class => ['all' => true],
    App\NCUO\WebftpBundle\NCUOWebftpBundle::class => ['all' => true],
    App\NCUO\WebmailBundle\NCUOWebmailBundle::class => ['all' => true],
    App\NCUO\ConstructorBundle\NCUOConstructorBundle::class => ['all' => true],

];
