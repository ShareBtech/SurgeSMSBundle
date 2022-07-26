<?php

namespace MauticPlugin\SurgeBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use MauticPlugin\SurgeBundle\Form\Type\ExampleSendType;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\EmailBundle\Form\Type\BatchSendType;
use Mautic\LeadBundle\Model\LeadModel;

class SmsController extends FormController
{
    /**
     * Generating the modal box content for
     * the send multiple example sms option.
     */
    public function sendExampleAction($objectId)
    {
        $model  = $this->getModel('sms');
        /** @var Sms $entity */
        $entity = $model->getEntity($objectId);

        //not found or not allowed
        if (
            null === $entity
            || (!$this->get('mautic.security')->hasEntityAccess(
                'sms:smses:viewown',
                'sms:smses:viewother',
                $entity->getCreatedBy()
            ))
        ) {
            return $this->postActionRedirect(
                [
                    'passthroughVars' => [
                        'closeModal' => 1,
                        'route'      => false,
                    ],
                ]
            );
        }

        // Get the quick add form
        $action = $this->generateUrl('mautic_surge_sms_action', ['objectAction' => 'sendExample', 'objectId' => $objectId]);

        $form = $this->createForm(ExampleSendType::class, ['recipients' => ['list' => []]], ['action' => $action]);
        /* @var \Mautic\EmailBundle\Model\EmailModel $model */

        if ('POST' == $this->request->getMethod()) {
            $isCancelled = $this->isFormCancelled($form);
            $isValid     = $this->isFormValid($form);
            if (!$isCancelled && $isValid) {
                $recipients = $form['recipients']->getData()['list'];

                $errors = [];
                foreach ($recipients as $recipient) {
                    if (!empty($recipient)) {
                        // Prepare a fake lead
                        $leadModel = $this->getModel('lead');
                        $lead = new Lead();
                        $lead->setNewlyCreated(true);

                        // Updated/new fields
                        $leadFields = array(
                            'firstname' => 'Bob',
                            'mobile' => $recipient
                        );
                        $leadModel->setFieldValues($lead, $leadFields);

                        // Save the entity
                        $leadModel->saveEntity($lead);

                        // Send to current user
                        $results = $model->sendSms($entity, $lead);
                        $leadId = $lead->getId();
                        if (count($results) && (!isset($results[$leadId]['sent']) || $results[$leadId]['sent'] !== true)) {
                            array_push($errors, $results[$leadId]['status']);
                        }
                        $leadModel->deleteEntity($lead);
                    }
                }

                if (0 != count($errors)) {
                    $this->addFlash(implode('; ', $errors));
                } else {
                    $this->addFlash('mautic.sms.surge.test_sent_multiple.success');
                }
            }

            if ($isValid || $isCancelled) {
                return $this->postActionRedirect(
                    [
                        'passthroughVars' => [
                            'closeModal' => 1,
                            'route'      => false,
                        ],
                    ]
                );
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $form->createView(),
                ],
                'contentTemplate' => 'SurgeBundle:Sms:recipients.html.php',
            ]
        );
    }

    /**
     * Manually sends Text Messages.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendAction($objectId)
    {
        /** @var \Mautic\EmailBundle\Model\SmsModel $model */
        $model   = $this->getModel('sms');
        $entity  = $model->getEntity($objectId);
        $session = $this->container->get('session');
        $page    = $session->get('mautic.sms.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('mautic_sms_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticSmsBundle:Sms:index',
            'passthroughVars' => [
                'activeLink'    => '#mautic_sms_index',
                'mauticContent' => 'sms',
            ],
        ];

        //not found
        if (null === $entity) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'mautic.sms.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        }

        if (!$entity->isPublished()) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'mautic.sms.error.send.unpublished',
                                'msgVars' => [
                                    '%id%'   => $objectId,
                                    '%name%' => $entity->getName(),
                                ],
                            ],
                        ],
                    ]
                )
            );
        }

        if (
            'template' == $entity->getSmsType()
            || !$this->get('mautic.security')->hasEntityAccess(
                'sms:smses:viewown',
                'sms:smses:viewother',
                $entity->getCreatedBy()
            )
        ) {
            return $this->accessDenied();
        }

        $action   = $this->generateUrl('mautic_surge_sms_action', ['objectAction' => 'send', 'objectId' => $objectId]);
        $pending = $this->getPendingMessagesCount($entity);
        $form     = $this->get('form.factory')->create(BatchSendType::class, [], ['action' => $action]);
        $complete = $this->request->request->get('complete', false);

        if ('POST' == $this->request->getMethod() && ($complete || $this->isFormValid($form))) {
            if (!$complete) {
                $progress = [0, (int) $pending];
                $session->set('mautic.sms.send.progress', $progress);
                $session->set('mautic.sms.send.min_contact_id', null);

                $stats = ['sent' => 0, 'failed' => 0, 'failedRecipients' => []];
                $session->set('mautic.sms.send.stats', $stats);

                $status     = 'inprogress';
                $batchlimit = $form['batchlimit']->getData();

                $session->set('mautic.sms.send.active', false);
            } else {
                $stats      = $session->get('mautic.sms.send.stats');
                $progress   = $session->get('mautic.sms.send.progress');
                $batchlimit = 100;
                $status     = (!empty($stats['failed'])) ? 'with_errors' : 'success';
            }

            $contentTemplate = 'SurgeBundle:Send:progress.html.php';
            $viewParameters  = [
                'progress'   => $progress,
                'stats'      => $stats,
                'status'     => $status,
                'sms'      => $entity,
                'batchlimit' => $batchlimit,
            ];
        } else {
            //process and send
            $contentTemplate = 'SurgeBundle:Send:form.html.php';
            $viewParameters  = [
                'form'    => $form->createView(),
                'sms'   => $entity,
                'pending' => $pending,
            ];
        }

        return $this->delegateView(
            [
                'viewParameters'  => $viewParameters,
                'contentTemplate' => $contentTemplate,
                'passthroughVars' => [
                    'mauticContent' => 'smsSend',
                    'route'         => $action,
                ],
            ]
        );
    }

    private function getPendingMessagesCount($entity){
        $broadcastQuery     = $this->get('mautic.sms.broadcast.query');
        $pending = $broadcastQuery->getPendingCount($entity);
        if (false !== $pending) {
            $entity->setPendingCount($pending);
        }
        
        return $entity->getPendingCount();
    }
}
