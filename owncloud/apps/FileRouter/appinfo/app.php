<?php

namespace OCA\FileRouter;

/**

 */
\OC::$CLASSPATH['OCA\FileRouter\Controller\PageController'] = 'FileRouter/controller/PageController.php';
\OC::$CLASSPATH['OCA\FileRouter\Db\TransmissionMapper']='FileRouter/db/TransmissionMapper.php';
\OC::$CLASSPATH['OCA\FileRouter\Db\Transmission']='FileRouter/db/Transmission.php';
\OC::$CLASSPATH['OCA\FileRouter\DependencyInjection\DIContainer'] = 'FileRouter/dependencyinjection/DIContainer.php';
\OC::$CLASSPATH['OCA\FileRouter\applogic\SharingManager'] = 'FileRouter/applogic/SharingManager.php';
\OC::$CLASSPATH['OCA\FileRouter\applogic\Sharer'] = 'FileRouter/applogic/Sharer.php';
\OC::$CLASSPATH['OCA\FileRouter\applogic\RulesManager'] = 'FileRouter/applogic/RulesManager.php';
\OC::$CLASSPATH['OCA\FileRouter\IGATypes'] = 'FileRouter/IGATypes.php';
\OC::$CLASSPATH['OCA\FileRouter\TransmissionStates'] = 'FileRouter/TransmissionStates.php';
\OC::$CLASSPATH['OCA\FileRetentionApp\applogic\FileQueue'] = 'FileRetentionApp/applogic/FileQueue.php';

//namespace OCA\FileRouter;
use OCA\FileRouter\Db\Transmission;
use OCA\FileRouter\Db\TransmissionMapper;
use OCP\App;
use OCP\MC_User;
use OCP\Util;

if (App::isEnabled('appframework')) {
    $api = new \OCA\AppFramework\Core\API('FileRouter');

    $api->addNavigationEntry(array(
        // the string under which your app will be referenced in owncloud
        'id' => $api->getAppName(),
        // sorting weight for the navigation. The higher the number, the higher
        // will it be listed in the navigation
        'order' => 10,
        // the route that will be shown on startup
        'href' => $api->linkToRoute('tx_index'),
        // the icon that will be shown in the navigation
        // this file needs to exist in img/example.png
        'icon' => $api->imagePath('env.png'),
        // the title of your application. This will be used in the
        // navigation or on the settings page of your app
        'name' => $api->getTrans()->t('Transmissions')
    ));

    //    $api->addScript('public');
    $api->addStyle('public');

    //Here, signal_put_contents is emitted by files\view::putcontents
    \OCP\Util::connectHook(\OC\Files\Filesystem::CLASSNAME, \OC\Files\Filesystem::signal_post_write, 'OCA\FileRouter\applogic\Sharer', 'handlePostWrite');


    //OCP\Util::connectHook(OC\Files\Filesystem::CLASSNAME, OC\Files\Filesystem::signal_post_write, 'OCA\FileRouter\applogic\SharingManager', 'write'); //Here, signal_put_contents is emitted by files\view::putcontents
    //\OCP\Util::connectHook('OC\Files\Cache\Updater', 'write_update', 'OCA\FileRouter\applogic\SharingManager', 'write');
    //\OC_Hook::emit('cache_updater','write_update',array('path' => $path));
    //OCP\Util::connectHook(OC\Files\Filesystem::CLASSNAME, OC\Files\Filesystem::signal_write, 'OCA\FileRouter\applogic\SharingManager', 'write');
    //OCP\Util::connectHook(OC\Files\Filesystem::CLASSNAME, OC\Files\Filesystem::signal_post_create, 'OCA\FileRouter\applogic\SharingManager', 'write');

    /**
     * Creates a new "transmission" object, which gets saved in the db
     */
//    if (\OC_User::isLoggedIn()) {
//        $userInfo = MC_User::getUserInfo();
//        if (in_array('TA', $userInfo['types'])) {
//
//        } else {
//            $tx = new Transmission();
//            $tx->setFileid(20);
//            $tx->setIgatype(IGATypes::ModelOneOptionTwo);
//            $tx->setIntermediate('CRA');
//            $tx->setRecipient('IRS');
//            $tx->setSender($userInfo['userId']);
//            $tx->setState(TransmissionStates::PendingReview);
//
//            $db = new TransmissionMapper($api);
//            $db->insert($tx);
//        }
//    }

} else {
    $msg = 'Can not enable the File Router app because the App Framework App is disabled';
    Util::writeLog('FileRouter', $msg, Util::ERROR);
}
