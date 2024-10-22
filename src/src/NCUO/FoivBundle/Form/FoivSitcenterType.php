<?php

namespace App\NCUO\FoivBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FoivSitcenterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('address')
            ->add('phone')
            ->add('email')
            ->add('functions')
            ->add('webSite')
            ->add('director')
            ->add('foiv')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NCUO\FoivBundle\Entity\FoivSitcenter'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ncuo_foivbundle_foivsitcenter';
    }
}
