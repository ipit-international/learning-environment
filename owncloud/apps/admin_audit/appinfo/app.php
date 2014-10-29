<?php

use OCA\admin_audit\hooks\Handlers;

OCP\Util::connectHook('OC_User', 'pre_login', Handlers::CLASSNAME, 'pre_login');
OCP\Util::connectHook('OC_User', 'post_login', Handlers::CLASSNAME, 'post_login');
OCP\Util::connectHook('OC_User', 'logout', Handlers::CLASSNAME, 'logout');

OCP\Util::connectHook(OC\Files\Filesystem::CLASSNAME, OC\Files\Filesystem::signal_rename, Handlers::CLASSNAME, 'rename');
OCP\Util::connectHook(OC\Files\Filesystem::CLASSNAME, OC\Files\Filesystem::signal_create, Handlers::CLASSNAME, 'create');
OCP\Util::connectHook(OC\Files\Filesystem::CLASSNAME, OC\Files\Filesystem::signal_copy, Handlers::CLASSNAME, 'copy');
OCP\Util::connectHook(OC\Files\Filesystem::CLASSNAME, OC\Files\Filesystem::signal_write, Handlers::CLASSNAME, 'write');
OCP\Util::connectHook(OC\Files\Filesystem::CLASSNAME, OC\Files\Filesystem::signal_read, Handlers::CLASSNAME, 'read');
OCP\Util::connectHook(OC\Files\Filesystem::CLASSNAME, OC\Files\Filesystem::signal_delete, Handlers::CLASSNAME, 'delete');

/// torch added
OCP\Util::connectHook(OC\Files\Filesystem::CLASSNAME, OC\Files\Filesystem::signal_post_write, Handlers::CLASSNAME, 'post_write');
/// end torch added

OCP\Util::connectHook('OC_User', 'pre_createUser', Handlers::CLASSNAME, 'pre_createUser');
OCP\Util::connectHook('OC_User', 'post_createUser', Handlers::CLASSNAME, 'post_createUser');
OCP\Util::connectHook('OC_User', 'pre_deleteUser', Handlers::CLASSNAME, 'pre_deleteUser');
OCP\Util::connectHook('OC_User', 'post_deleteUser', Handlers::CLASSNAME, 'post_deleteUser');
OCP\Util::connectHook('OC_User', 'post_setPassword', Handlers::CLASSNAME, 'post_setPassword');

OCP\Util::connectHook('OC_Group', 'pre_createGroup', Handlers::CLASSNAME, 'pre_createGroup');
OCP\Util::connectHook('OC_Group', 'post_createGroup', Handlers::CLASSNAME, 'post_createGroup');
OCP\Util::connectHook('OC_Group', 'pre_addToGroup', Handlers::CLASSNAME, 'pre_addToGroup');
OCP\Util::connectHook('OC_Group', 'post_addToGroup', Handlers::CLASSNAME, 'post_addToGroup');
OCP\Util::connectHook('OC_Group', 'pre_removeFromGroup', Handlers::CLASSNAME, 'pre_removeFromGroup');
OCP\Util::connectHook('OC_Group', 'post_removeFromGroup', Handlers::CLASSNAME, 'post_removeFromGroup');
OCP\Util::connectHook('OCA\FileRetentionApp\Controller', 'shouldDelete', 'OCA\FileRetentionApp\Controller', 'fileMovedToTrash');


//FIXME OC_Share does no longer exist, probably should add funstionality for File Router
/*
OCP\Util::connectHook('OC_Share', 'public', 'OC_admin_audit_Hooks_Handlers', 'share_public');
OCP\Util::connectHook('OC_Share', 'public-download', 'OC_admin_audit_Hooks_Handlers', 'share_public_download');
OCP\Util::connectHook('OC_Share', 'user', 'OC_admin_audit_Hooks_Handlers', 'share_user');
*/
