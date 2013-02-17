<?php

namespace Msi\Bundle\CmfBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageBlockTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('published')
            ->add('settings', 'msi_block_settings', array(
                'fields' => array(array('body', 'textarea', array('attr' => array('class' => 'tinymce')))),
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Msi\Bundle\CmfBundle\Entity\PageBlockTranslation',
        ));
    }

    public function getName()
    {
        return 'page_block_translation';
    }
}
