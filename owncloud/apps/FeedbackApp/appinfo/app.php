<?php

namespace OCA\FeedbackApp;

use OCA\AppFramework\Core\API;
use OCP\Util;
use OCP\App;

if (App::isEnabled('appframework')) {
    $api = new API('FeedbackApp');
    $api->addNavigationEntry(array(
        'id' => $api->getAppName(),
        'order' => 2,
        'href' => 'mailto:LEARNING-ENVIRONMENT-FEEDBACK-LIST@LISTS.MITRE.ORG?Subject=IPIT%20Feedback',
        'icon' => $api->imagePath('utilities-log-viewer.png'),
        'name' => $api->getTrans()->t('Feedback')
    ));
    $api->addStyle('public');
} else {
    $msg = 'Cannot enable Feedback App because App Framework is disabled';
    Util::writeLog('FeedbackApp', $msg, Util::ERROR);
}
