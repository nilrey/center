<?php

namespace App\NCUO\PortalBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('label' => 'Логин'))
            ->add('password', 'repeated', array(
                            'first_options'  => array('label' => 'Пароль', 'attr' => array('autocomplete' => 'off')),
                            'second_options' => array('label' => 'Повтор пароля', 'attr' => array('autocomplete' => 'off')),
                            'type' => 'password',
                            'required' => false))
            ->add('email')
            ->add('lastname', null, array('label' => 'Фамилия'))
            ->add('firstname', null, array('label' => 'Имя'))
            ->add('middlename', null, array('label' => 'Отчество'))
            ->add('position', null, array('label' => 'Должность'))
            ->add('contactphone', null, array('label' => 'Контактный телефон'))
         //   ->add('salt')
            ->add('foiv')
            ->add('roiv')
            ->add('role')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NCUO\PortalBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ncuo_portalbundle_user';
    }
}
