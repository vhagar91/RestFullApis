<?php

namespace FrontendBundle\Form;

use FrontendBundle\Entity\NomApiTypeRepository;
use FrontendBundle\Entity\NomDriver;
use FrontendBundle\Entity\NomDriverRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatApiType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('attr' => array('class' => 'form-control')))
            ->add('description', 'textarea', array('required' => false, 'attr' => array('class' => 'form-control desc-cbs')))
            ->add('code', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('status', 'checkbox', array('label' => 'frontendbundle.field_label.state', 'required' => false, 'attr' => array()))
            ->add('class', 'text', array('attr' => array('class' => 'form-control')))
            ->add('db_host', 'text', array('attr' => array('class' => 'form-control')))
            ->add('db_port', 'integer', array('attr' => array('class' => 'form-control')))
            ->add('db_name', 'text', array('attr' => array('class' => 'form-control')))
            ->add('db_user', 'text', array('attr' => array('class' => 'form-control')))
            ->add('db_password', 'password', array('attr' => array('class' => 'form-control')))
            ->add('apitype', 'entity', array(
                'class' => 'FrontendBundle\Entity\NomApiType',
                'query_builder' => function (NomApiTypeRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
                'property' => 'name',
                'required' => true,
                'multiple' => false
            ))
            ->add('driver', 'entity', array(
                'class' => 'FrontendBundle\Entity\NomDriver',
                'query_builder' => function (NomDriverRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
                'property' => 'name',
                'required' => true,
                'multiple' => false
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FrontendBundle\Entity\DatApi'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'frontendbundle_datapi';
    }
}
