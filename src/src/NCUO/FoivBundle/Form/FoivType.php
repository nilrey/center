<?php

namespace App\NCUO\FoivBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FoivType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('shortname')
            ->add('siteurl')
            ->add('sitename')
            ->add('version')
            ->add('iconstyle')
            ->add('sortOrder')
            ->add('type')
            ->add('basictasks')
            ->add('subsystems')
            ->add('conventions')
            ->add('mapurl')
            ->add('localSite')
            ->add('engaged')
            ->add('descriptionText')
            ->add('stateLink')
            ->add('director')
            ->add('address')
            ->add('reglament')
            ->add('superfoiv')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NCUO\FoivBundle\Entity\Foiv'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ncuo_foivbundle_foiv';
    }
}
