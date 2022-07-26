
Mautic.smsSendOnLoad = function (container, response) {
    if (mQuery('.sms-send-progress').length) {
        if (!mQuery('#smsSendProgress').length) {
            Mautic.clearModeratedInterval('smsSendProgress');
        } else {
            Mautic.setModeratedInterval('smsSendProgress', 'sendSmsBatch', 2000);
        }
    }
};

Mautic.smsSendOnUnload = function () {
    if (mQuery('.sms-send-progress').length) {
        Mautic.clearModeratedInterval('smsSendProgress');
        if (typeof Mautic.sendSmsBatchXhr != 'undefined') {
            Mautic.sendSmsBatchXhr.abort();
            delete Mautic.sendSmsBatchXhr;
        }
    }
};

Mautic.sendSmsBatch = function () {
    var data = 'id=' + mQuery('.progress-bar-send').data('sms') + '&pending=' + mQuery('.progress-bar-send').attr('aria-valuemax') + '&batchlimit=' + mQuery('.progress-bar-send').data('batchlimit');
    Mautic.sendSmsBatchXhr = Mautic.ajaxActionRequest('plugin:surge:sendBatch', data, function (response) {
        if (response.progress) {
            if (response.progress[0] > 0) {
                mQuery('.imported-count').html(response.progress[0]);
                mQuery('.progress-bar-send').attr('aria-valuenow', response.progress[0]).css('width', response.percent + '%');
                mQuery('.progress-bar-send span.sr-only').html(response.percent + '%');
            }

            if (response.progress[0] >= response.progress[1]) {
                Mautic.clearModeratedInterval('smsSendProgress');

                setTimeout(function () {
                    mQuery.ajax({
                        type: 'POST',
                        showLoadingBar: false,
                        url: window.location,
                        data: 'complete=1',
                        success: function (response) {

                            if (response.newContent) {
                                // It's done so pass to process page
                                Mautic.processPageContent(response);
                            }
                        }
                    });
                }, 1000);
            }
        }

        Mautic.moderatedIntervalCallbackIsComplete('smsSendProgress');
    });
};