<?php

namespace Nercury\ObjectRouterBundle\Form\Type;

/**
 * Form type for editing a router entity
 *
 * @author nerijus
 */
class ObjectRouteType extends \Symfony\Component\Form\AbstractType {
    
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        
        $builder->add('slug', 'text', array(
            'label' => 'object_router.edit.slug',
            'required' => false,
        ));
        
    }
    
    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => '\Nercury\ObjectRouterBundle\Entity\ObjectRoute',
        ));
    }
    
    public function getName() {
        return 'object_route';
    } 
    
}