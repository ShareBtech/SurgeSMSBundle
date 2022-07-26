<?php


$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('mauticContent', 'smsSend');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.sms.send.list', ['%name%' => $sms->getName()]));

$percent = ($progress[1]) ? ceil(($progress[0] / $progress[1]) * 100) : 100;
$id      = ('inprogress' != $status) ? 'smsSendProgressComplete' : 'smsSendProgress';
?>

<div class="row ma-lg sms-send-progress" id="<?php echo $id; ?>">
    <div class="col-sm-offset-3 col-sm-6 text-center">
        <div class="panel panel-<?php echo ('inprogress' != $status) ? 'success' : 'danger'; ?>">
            <div class="panel-heading">
                <h4 class="panel-title"><?php echo $view['translator']->trans('mautic.sms.send.'.$status, ['%name%' => $sms->getName()]); ?></h4>
            </div>
            <div class="panel-body">
                <?php if ('inprogress' != $status): ?>
                <h4><?php echo $view['translator']->trans('mautic.sms.send.stats', ['%sent%' => $stats['sent'], '%failed%' => $stats['failed']]); ?></h4>
                <?php endif; ?>
                <div class="progress mt-md" style="height:50px;">
                    <div class="progress-bar-send progress-bar progress-bar-striped<?php if ('inprogress' == $status) {
    echo ' active';
} ?>" role="progressbar" aria-valuenow="<?php echo $progress[0]; ?>" aria-valuemin="0" aria-valuemax="<?php echo $progress[1]; ?>" style="width: <?php echo $percent; ?>%; height: 50px;" data-batchlimit="<?php echo $batchlimit; ?>" data-sms="<?php echo $sms->getId(); ?>">
                        <span class="sr-only"><?php echo $percent; ?>%</span>
                    </div>
                </div>
            </div>
            <?php if (!empty($stats['failedRecipients'])): ?>
            <ul class="list-group">
                <?php foreach ($stats['failedRecipients'] as $leadId => $failedEmail): ?>
                <li class="list-group-item">
                    <a target="_new" class="text-danger" href="<?php echo $view['router']->path('mautic_contact_action', ['objectAction' => 'view', 'objectId' => $leadId]); ?>">
                        <?php echo $failedEmail; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <div class="panel-footer">
                <span class="small"><span class="imported-count"><?php echo $progress[0]; ?></span> / <span class="total-count"><?php echo $progress[1]; ?></span></span>

                <?php if ('inprogress' == $status): ?>
                <div>
                    <a class="text-danger mt-md" href="<?php echo $view['router']->path('mautic_surge_sms_action', ['objectAction' => 'send', 'objectId' => $sms->getId()]); ?>" data-toggle="ajax"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                </div>
                <?php else: ?>
                <div>
                    <a class="text-success mt-md" href="<?php echo $view['router']->path('mautic_sms_action', ['objectAction' => 'view', 'objectId' => $sms->getId()]); ?>" data-toggle="ajax"><?php echo $view['translator']->trans('mautic.core.form.done'); ?></a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
