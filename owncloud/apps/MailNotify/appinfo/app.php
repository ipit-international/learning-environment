<?php

namespace OCA\MailNotify;

\OC::$CLASSPATH['OCA\MailNotify\Hooks\HookHandler'] = 'MailNotify/Hooks/HookHandler.php';
\OC::$CLASSPATH['OCA\MailNotify\Hooks\MailJob'] = 'MailNotify/Hooks/MailJob.php';
\OC::$CLASSPATH['OCA\MailNotify\Hooks\RulesManager'] = 'MailNotify/Hooks/RulesManager.php';
\OC::$CLASSPATH['OCA\MailNotify\DependencyInjection\DIContainer'] = 'MailNotify/dependencyinjection/dicontainer.php';
\OC::$CLASSPATH['OCA\MailNotify\Db\ItemMapper'] = 'MailNotify/db/ItemMapper.php';
\OC::$CLASSPATH['OCA\MailNotify\Db\Item'] = 'MailNotify/db/Item.php';
\OC::$CLASSPATH['OCA\MailNotify\Controller\PageController'] = 'MailNotify/controller/pagecontroller.php';

\OC::$CLASSPATH['OCA\FileRouter\Db\TransmissionMapper'] = 'FileRouter/db/TransmissionMapper.php';
\OC::$CLASSPATH['OCA\MailNotify\NotificationTypes'] = 'MailNotify/NotificationTypes.php';
\OC::$CLASSPATH['OCA\MailNotify\MailTemplates'] = 'MailNotify/MailTemplates.php';
\OC::$CLASSPATH['OCA\MailNotify\Controller\PageController'] = 'MailNotify/controller/pagecontroller.php';
\OC::$CLASSPATH['OC\Files\Filesystem'] = 'files/filesystem.php';


use OCA\AppFramework\Core\API;
use OCA\MailNotify\Hooks\HookHandler;
use OCA\MailNotify\Hooks\MailJob;
use OCP\BackgroundJob;
use OCP\Util;
use OCP\App;
use OC\Files\Filesystem;

// don't break owncloud when the appframework is not enabled
if (App::isEnabled('appframework')) {
    $api = new API('MailNotify');

    $api->addNavigationEntry(array(
        // the string under which your app will be referenced in owncloud
        'id' => $api->getAppName(),
        // sorting weight for the navigation. The higher the number, the higher
        // will it be listed in the navigation
        'order' => 10,
        // the route that will be shown on startup
        'href' => $api->linkToRoute('mn_index'),
        // the icon that will be shown in the navigation
        // this file needs to exist in img/example.png
        'icon' => $api->imagePath('utilities-log-viewer.png'),
        // the title of your application. This will be used in the
        // navigation or on the settings page of your app
        'name' => $api->getTrans()->t('Alert Log')
    ));

    $api->addScript('public');
    $api->addStyle('public');

    HookHandler::setApi($api);
    // filesystem events
    Util::connectHook(Filesystem::CLASSNAME, Filesystem::signal_post_write, HookHandler::CLASSNAME, HookHandler::handler_file_write);
    Util::connectHook(Filesystem::CLASSNAME, Filesystem::signal_post_create, HookHandler::CLASSNAME, HookHandler::handler_file_create);
    Util::connectHook(Filesystem::CLASSNAME, Filesystem::signal_post_rename, HookHandler::CLASSNAME, HookHandler::handler_file_rename);
    Util::connectHook(Filesystem::CLASSNAME, Filesystem::signal_delete, HookHandler::CLASSNAME, HookHandler::handler_file_delete);
    //owncloud has no copy functionality on the web front end, also it is kinda pointless with how owncloud does storage...
    Util::connectHook(Filesystem::CLASSNAME, Filesystem::signal_post_copy, HookHandler::CLASSNAME, HookHandler::handler_file_copy);
    //read should be some other hook to signify a download
    Util::connectHook(Filesystem::CLASSNAME, Filesystem::signal_read, HookHandler::CLASSNAME, HookHandler::handler_file_read);

    // share events
    Util::connectHook('OCP\Share', 'post_shared', HookHandler::CLASSNAME, HookHandler::handler_shared);
    Util::connectHook('OCP\Share', 'post_unshare', HookHandler::CLASSNAME, HookHandler::handler_unshare);

    // upload events
    Util::connectHook(Filesystem::CLASSNAME, 'upload_success', HookHandler::CLASSNAME, HookHandler::handler_file_upload_success);
    Util::connectHook(Filesystem::CLASSNAME, 'upload_failure', HookHandler::CLASSNAME, HookHandler::handler_file_upload_failure);
    Util::connectHook('UploadFile', 'upload_success', HookHandler::CLASSNAME, HookHandler::handler_file_upload_success);
    Util::connectHook('UploadFile', 'upload_failure', HookHandler::CLASSNAME, HookHandler::handler_file_upload_failure);

    // message events
    $senderClass = '\MessageSender';
    $sendMessage_hook = 'send_message';
    Util::connectHook($senderClass, $sendMessage_hook, HookHandler::CLASSNAME, HookHandler::handler_external_message);

    // file unavailable event
    Util::connectHook("FileQueue","Signal_Download_Failed", HookHandler::CLASSNAME, HookHandler::handler_file_unavailable);
    Util::connectHook('MappedLocal','file_unavailable', HookHandler::CLASSNAME, HookHandler::handler_file_unavailable_two);

    // approve/reject for transmissions
    Util::connectHook('SharingManager', 'transmission_approved', HookHandler::CLASSNAME, HookHandler::handler_transmission_approve);
    Util::connectHook('SharingManager', 'transmission_rejected', HookHandler::CLASSNAME, HookHandler::handler_transmission_reject);

//    Util::connectHook('FileDownload', 'unavailable',HookHandler::CLASSNAME, HookHandler::handler_file_unavailable);
    // setup recurring task.
    $mailSenderJob = new MailJob();
    $mailSenderJob->setInterval(5*60);// set time period for 5 minutes?
    BackgroundJob::registerJob($mailSenderJob, null);
} else {
    $msg = 'Can not enable the Mail Notification app because the App Framework App is disabled';
    Util::writeLog('MailNotify', $msg, Util::ERROR);
}
