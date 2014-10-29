<?php

namespace OCA\FileRetentionApp;



\OC::$CLASSPATH['OCA\FileRetentionApp\applogic\AutoDeleter'] = 'FileRetentionApp/applogic/AutoDeleter.php';

\OC::$CLASSPATH['OCA\FileRetentionApp\applogic\FileQueue.php'] = 'FileRetentionApp/applogic/FileQueue.php';

\OC::$CLASSPATH['OCP\Util'] = 'lib/public/util.php';

\OC::$CLASSPATH['OC\Files\Filesystem'] = 'files/filesystem.php';

\OC::$CLASSPATH['OC\Files\View'] = 'files/view.php';


\OCP\Util::connectHook(\OC\Files\Filesystem::CLASSNAME,
    \OC\Files\Filesystem::signal_post_write,
    'OCA\FileRetentionApp\applogic\FileQueue',
    'write');

\OCP\Util::connectHook(\OC\Files\Filesystem::CLASSNAME,
    \OC\Files\Filesystem::signal_post_rename,
    'OCA\FileRetentionApp\applogic\FileQueue',
    'rename');

\OCP\Util::connectHook(\OC\Files\Filesystem::CLASSNAME,
    \OC\Files\Filesystem::signal_delete,
    'OCA\FileRetentionApp\applogic\FileQueue',
    'delete');



//all these are redundant but may be needed later


//Changing the final arg from 'read' to 'write' will satisfy BR IRSIDES-28
/*\OCP\Util::connectHook(\OC\Files\Filesystem::CLASSNAME,
   \OC\Files\Filesystem::signal_read,
    '\OCA\FileRetentionApp\applogic\FileQueue',
    'read');*/

/*\OCP\Util::connectHook('\OCA\FileRouter\applogic\SharingManager',
    'auto_upload',
    '\OCA\FileRetentionApp\applogic\FileQueue',
    'auto_write');*/


//\OC_Hook::connect('\OCA\FileRouter\applogic\SharingManager','signal_auto_upload','\OCA\FileRetentionApp\applogic\FileQueue', 'write');

//this is nonsensical, the filesystem class would never emit a auto upload signal as an arguement to emit()

//\OCP\Util::connectHook(\OC\Files\Filesystem::CLASSNAME,'signal_auto_upload','OCA\FileRetentionApp\applogic\FileQueue', 'write');

/*\OCP\Util::connectHook('\OCA\FileRetentionApp\applogic\DownloadHooks',
    "Signal_Upload_Failed",
    '\OCA\FileRetentionApp\applogic\FileQueue',
    'downloadFailed');

//used to have Signal_Upload_Failed
\OCP\Util::connectHook('\OCA\FileRetentionApp\applogic\DownloadHooks',
    "Signal_Download_Failed",
    '\OCA\FileRetentionApp\applogic\FileQueue',
    'downloadSucceeded');  */

\OCP\BackgroundJob::registerJob('\OCA\FileRetentionApp\applogic\AutoDeleter', NULL);
