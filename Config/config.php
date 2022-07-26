<?php

declare(strict_types=1);

return [
    'name'        => 'Surge SMS',
    'description' => 'Surge plugin for SMS Integration',
    'version'     => '1.0.1',
    'author'      => 'Surge.Media',
    'routes'      => [
        'main'   => [
            'mautic_surge_sms_action' => [
                'path'       => '/suregsms/{objectAction}/{objectId}',
                'controller' => 'SurgeBundle:Sms:execute',
            ],
        ],
        'public' => [],
        'api'    => [],
    ],
    'menu'        => [
        'main' => [
            'items' => [
                'mautic.sms.smses' => [
                    'route'  => 'mautic_sms_index',
                    'access' => ['sms:smses:viewown', 'sms:smses:viewother'],
                    'parent' => 'mautic.core.channels',
                    'checks' => [
                        'integration' => [
                            'Messagewhiz' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                    'priority' => 70,
                ],
            ],
        ],
    ],
    'services'    => [
        'events' => [
            'mautic.surgebundle.button.subscriber' => [
                'class'     => \MauticPlugin\SurgeBundle\EventListener\ButtonSubscriber::class,
                'arguments' => [
                    'router',
                    'translator',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.messagewhiz.smsconfig' => [
                'class' => \Mautic\SmsBundle\Form\Type\ConfigType::class,
            ],
            'mautic.form.type.messagewhiz.sms.config.form' => [
                'class'     => \Mautic\SmsBundle\Form\Type\ConfigType::class,
                'arguments' => ['mautic.sms.transport_chain', 'translator'],
            ],
        ],
        'other'        => [
            'mautic.sms.messagewhiz.configuration' => [
                'class'        => \MauticPlugin\SurgeBundle\Integration\Messagewhiz\Configuration::class,
                'arguments'    => [
                    'mautic.helper.integration',
                ],
            ],
            'mautic.sms.messagewhiz.transport' => [
                'class'        => \MauticPlugin\SurgeBundle\Integration\Messagewhiz\MessagewhizTransport::class,
                'arguments'    => [
                    'mautic.sms.messagewhiz.configuration',
                    'monolog.logger.mautic',
                ],
                'tag'          => 'mautic.sms_transport',
                'tagArguments' => [
                    'integrationAlias' => 'Messagewhiz',
                ],
                'serviceAliases' => [
                    'sms_api',
                    'mautic.sms.api',
                ],
            ],
            'mautic.sms.messagewhiz.callback' => [
                'class'     => \MauticPlugin\SurgeBundle\Integration\Messagewhiz\MessagewhizCallback::class,
                'arguments' => [
                    'mautic.sms.helper.contact',
                    'mautic.sms.messagewhiz.configuration',
                ],
                'tag'   => 'mautic.sms_callback_handler',
            ],
        ],
        'integrations' => [
            'mautic.integration.messagewhiz' => [
                'class'     => \MauticPlugin\SurgeBundle\Integration\MessagewhizIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                ],
            ],
        ],
    ],
];
