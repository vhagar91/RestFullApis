<?php

namespace FrontendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatApiProjectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('apikey', 'text', array(
                'required' => true,
                'attr' => array('placeholder' => 'Key', 'class' => 'form-control')
            ))
            ->add('api', 'entity', array(
                'class' => 'FrontendBundle\Entity\DatApi',
                'property' => 'name',
                'required' => true,
                'attr' => ['data-placeholder' => 'Api', 'class' => 'chosen-select'],
                'multiple' => false
            ))
            /*->add('project', 'entity', array(
                'class' => 'FrontendBundle\Entity\DatProject',
                'property' => 'name',
                'required' => true,
                'multiple' => false
            ))*/
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FrontendBundle\Entity\DatApiProject'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'frontendbundle_datapiproject';
    }
}
