<?php

namespace MauticPlugin\SurgeBundle\EventListener;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomButtonEvent;
use Mautic\CoreBundle\Templating\Helper\ButtonHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Mautic\SmsBundle\Entity\Sms;

class ButtonSubscriber implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router     = $router;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_BUTTONS => ['injectSendMessageButtons', 0],
        ];
    }

    public function injectSendMessageButtons(CustomButtonEvent $event)
    {
        $sms = $event->getItem();

        if ($sms = $event->getItem()) {
            if ($sms instanceof Sms && "list" == $sms->getSmsType()) {

                $sendExampleSmsButton = [
                    'attr' => [
                        'class'       => 'btn btn-default btn-nospin',
                        'data-toggle' => 'ajaxmodal',
                        'data-target' => '#MauticSharedModal',
                        'href'        => $this->router->generate('mautic_surge_sms_action', ['objectAction' => 'sendExample', 'objectId' => $sms->getId()]),
                        'data-header' => $this->translator->trans('mautic.sms.surge.send.example'),
                    ],
                    'iconClass' => 'fa fa-send',
                    'btnText'   => $this->translator->trans('mautic.sms.surge.send.example'),
                    'primary'   => true,
                ];
                $sendSmsButton = [
                    'attr' => [
                        'class'       => 'btn btn-default btn-nospin',
                        'data-toggle' => 'ajax',
                        'href'        => $this->router->generate('mautic_surge_sms_action', ['objectAction' => 'send', 'objectId' => $sms->getId()]),
                        'data-header' => $this->translator->trans('mautic.sms.surge.send.sms'),
                    ],
                    'iconClass' => 'fa fa-send-o',
                    'btnText'   => $this->translator->trans('mautic.sms.surge.send.sms'),
                    'primary'   => true,
                ];
                
                $event->addButton(
                    $sendExampleSmsButton,
                    ButtonHelper::LOCATION_PAGE_ACTIONS,
                )->addButton(
                    $sendSmsButton,
                    ButtonHelper::LOCATION_PAGE_ACTIONS,
                )->addButton(
                    $sendSmsButton,
                    ButtonHelper::LOCATION_LIST_ACTIONS,
                );
                
            }
        }
    }
}
