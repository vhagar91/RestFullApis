<?php

namespace FrontendBundle\Form;

use Doctrine\ORM\EntityRepository;
use FrontendBundle\Entity\NomApiTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatProjectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('description', 'textarea', array('required' => false, 'attr' => array('class' => 'form-control desc-cbs')))
            ->add('applicationtype', 'entity', array(
                'class' => 'FrontendBundle\Entity\NomApplicationType',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
                'property' => 'name',
                'required' => true,
                'multiple' => false
            ))
            ->add('urls', 'collection', array(
                'type' => new DatUrlType(),
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => 'frontendbundle.tab.urls'
            ))
            ->add('apisprojects', 'collection', array(
                'type' => new DatApiProjectType(),
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => 'frontendbundle.name.apis'
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FrontendBundle\Entity\DatProject'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'frontendbundle_datproject';
    }
}
