<?php

namespace MauticPlugin\SurgeBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\CoreBundle\Form\Type\SortableListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ExampleSendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'recipients',
            SortableListType::class,
            [
                'entry_type'       => TextType::class,
                'label'            => 'mautic.sms.surge.example_recipients',
                'add_value_button' => 'mautic.sms.surge.add_recipient',
                'option_notblank'  => false,
            ]
        );

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text' => false,
                'save_text'  => 'mautic.sms.surge.send.sms',
                'save_icon'  => 'fa fa-send',
            ]
        );
    }
}
