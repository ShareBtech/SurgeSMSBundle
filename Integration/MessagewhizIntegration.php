<?php

namespace MauticPlugin\SurgeBundle\Integration;

use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MessagewhizIntegration extends AbstractIntegration
{

    public const NAME         = 'Messagewhiz';
    public const DISPLAY_NAME = 'Surge.SMS Gateway';

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return [
            'apiKey' => 'surge.api_key',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return self::DISPLAY_NAME;
    }

    public function getIcon(): string
    {
        return 'plugins/SurgeBundle/Assets/img/icon.png';
    }

    /**
     * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ('features' == $formArea) {
            $builder->add(
                'sender_id',
                TextType::class,
                [
                    'label'      => 'mautic.sms.message_whiz.sender_id',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => true,
                    'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'mautic.sms.message_whiz.sender_id.tooltip',
                    ],
                ]
            );
            $builder->add(
                'default_campaign_id',
                TextType::class,
                [
                    'label'      => 'mautic.sms.message_whiz.default_campaign_id',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => true,
                    'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'mautic.sms.message_whiz.default_campaign_id.tooltip',
                    ],
                ]
            );
            $builder->add(
                'disable_trackable_urls',
                YesNoButtonGroupType::class,
                [
                    'label' => 'mautic.sms.config.form.sms.disable_trackable_urls',
                    'attr'  => [
                        'tooltip' => 'mautic.sms.config.form.sms.disable_trackable_urls.tooltip',
                    ],
                    'data' => !empty($data['disable_trackable_urls']) ? true : false,
                ]
            );
            $builder->add(
                'frequency_number',
                NumberType::class,
                [
                    'scale'      => 0,
                    'label'      => 'mautic.sms.list.frequency.number',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'attr'       => [
                        'class' => 'form-control frequency',
                    ],
                ]
            );
            $builder->add(
                'frequency_time',
                ChoiceType::class,
                [
                    'choices' => [
                        'day'   => 'DAY',
                        'week'  => 'WEEK',
                        'month' => 'MONTH',
                    ],
                    'label'             => 'mautic.lead.list.frequency.times',
                    'label_attr'        => ['class' => 'control-label'],
                    'required'          => false,
                    'multiple'          => false,
                    'attr'              => [
                        'class' => 'form-control frequency',
                    ],
                ]
            );
        }
    }
}
