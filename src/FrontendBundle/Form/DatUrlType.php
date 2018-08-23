<?php

namespace FrontendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatUrlType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dir', 'text', array('required' => true, 'attr' => array('placeholder' => 'Url', 'class' => 'form-control')))
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
            'data_class' => 'FrontendBundle\Entity\DatUrl'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'frontendbundle_daturl';
    }
}
