<?php

namespace Aqpglug\CodemedoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Aqpglug\CodemedoBundle\Form\MetaType;
use Aqpglug\CodemedoBundle\Extension\Config;

class BlockType extends AbstractType
{

    private $meta;
    function __construct($meta)
    {
        $this->meta = $meta;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('content','textarea', array(
                    'required' => false,
                    'attr' => array('class' => 'content')
                ))
                ->add('title','text', array(
                    'attr' => array('autofocus' => 'autofocus'),
                ))
                ->add('slug', 'text', array(
                    'required' => false,
                ))
                ->add('published', 'checkbox', array(
                    'required' => false,
                ))
                ->add('featured', 'checkbox', array(
                    'required' => false,
                ))
                ->add('image', 'file', array(
                    'required' => false,
                ));
        if ($this->meta !== array()) $builder->add('metadata', new MetaType($this->meta));
    }
}
