<?php

namespace MauticPlugin\SurgeBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Controller\AjaxLookupControllerTrait;
use Mautic\CoreBundle\Controller\VariantAjaxControllerTrait;
use Symfony\Component\HttpFoundation\Request;
use Mautic\CampaignBundle\Executioner\ContactFinder\Limiter\ContactLimiter;

class AjaxController extends CommonAjaxController
{
    use VariantAjaxControllerTrait;
    use AjaxLookupControllerTrait;

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sendBatchAction(Request $request)
    {
        $dataArray = ['success' => 0];

        /** @var \Mautic\EmailBundle\Model\SmsModel $model */
        $model    = $this->getModel('sms');
        $objectId = $request->request->get('id', 0);
        $pending  = $request->request->get('pending', 0);
        $limit    = $request->request->get('batchlimit', 100);

        if ($objectId && $entity = $model->getEntity($objectId)) {
            $dataArray['success'] = 1;
            $session              = $this->container->get('session');
            $progress             = $session->get('mautic.sms.send.progress', [0, (int) $pending]);
            $stats                = $session->get('mautic.sms.send.stats', ['sent' => 0, 'failed' => 0, 'failedRecipients' => []]);
            $inProgress           = $session->get('mautic.sms.send.active', false);
            $minContactId = $session->get('mautic.sms.send.min_contact_id', null);

            if ($pending && !$inProgress && $entity->isPublished()) {
                $session->set('mautic.sms.send.active', true);

                $batchFailedRecipients = [];
                $batchSentCount         = 0;
                $batchFailedCount       = 0;

                $contactLimiter = new ContactLimiter($limit, null, $minContactId);
                $broadcastQuery     = $this->get('mautic.sms.broadcast.query');
                $contacts = $broadcastQuery->getPendingContacts($entity, $contactLimiter);
                foreach ($contacts as $contact) {
                    $contactId  = $contact['id'];
                    $results    = $model->sendSms($entity, $contactId);
                    if (count($results) && (!isset($results[$contactId]['sent']) || $results[$contactId]['sent'] !== true)) {
                        $batchFailedCount++;
                        $contactModel    = $this->getModel('lead');
                        $contactEntity = $contactModel->getEntity($contactId);
                        $batchFailedRecipients[$contactId] = $contactEntity->getName();
                    }else{
                        $batchSentCount++;
                    }
                }

                $progress[0] += ($batchSentCount + $batchFailedCount);
                $stats['sent'] += $batchSentCount;
                $stats['failed'] += $batchFailedCount;
                $stats['failedRecipients'] = $stats['failedRecipients'] + $batchFailedRecipients;

                $session->set('mautic.sms.send.min_contact_id', $contactId + 1);
                $session->set('mautic.sms.send.progress', $progress);
                $session->set('mautic.sms.send.stats', $stats);
                $session->set('mautic.sms.send.active', false);
            }

            $dataArray['percent']  = ($progress[1]) ? ceil(($progress[0] / $progress[1]) * 100) : 100;
            $dataArray['progress'] = $progress;
            $dataArray['stats']    = $stats;
        }

        return $this->sendJsonResponse($dataArray);
    }
}
