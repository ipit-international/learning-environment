<?php
/**
 * Copyright (c) 2011, Robin Appelman <icewind1991@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

$allGroups=array();
foreach($_["groups"] as $group) {
	$allGroups[] = $group['groupId'];
}
$_['subadmingroups'] = $allGroups;
//$items = array_flip($_['subadmingroups']);
//unset($items['admin']);
//$_['subadmingroups'] = array_flip($items);
?>

<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkToRoute('isadmin'));?>"></script>

<div id="controls">
	<form id="newGroupTop" action="group">
	
		<?php if((bool) $_['isadmin']): ?>
		<input type="submit" value="Make New Jurisdiction">
		<?php endif; ?>

	</form>

<table class="hascontrols grid" data-groups="<?php p(json_encode($allGroups));?>">
	<thead>
	<tr>
		<?php if ($_['enableAvatars']): ?>
		<th id='headerAvatar'></th>
		<?php endif; ?>
		<th id='headerName'><?php p($l->t('Jurisdiction ID'))?></th>
		<th id="headerDisplayName"><?php p($l->t( 'Display Name' )); ?></th>
		<th id="headerCountry"><?php p($l->t( 'Country Code' )); ?></th>
		<th id="headerGIIN"><?php p($l->t( 'GIIN' )); ?></th>
		<th id="headerType"><?php p($l->t( 'Type' )); ?></th>
		<th id="publicKey"><?php p($l->t('Public Key')); ?></th>
		<th id="headerSUEmail"><?php p($l->t('Super User Email')); ?></th>
		<th id="headerEncryption"><?php p($l->t('Encryption Method')); ?></th>
                <th id="headerIssuer"><?php p($l->t('Certificate Issuer')); ?></th>
                <th id="headerSubject"><?php p($l->t('Certificate Subject')); ?></th>
                <th id="headerVersion"><?php p($l->t('Certificate Version')); ?></th>
		<th id="headerCompression"><?php p($l->t('Compression Method')); ?></th>
		<th id="headerAntivirus"><?php p($l->t('Antivirus Software')); ?></th>
		<th id="headerRemove">Delete</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($_["groups"] as $item): ?>
	  <!--?php foreach($item as $i): OCP\Util::writeLog('groups template',"item value $i",4); endforeach; ?-->
	  
	<tr data-uid="<?php p($item["groupId"]) ?>"
	    data-displayName="<?php p($item["displayName"]) ?>">
		<?php $groupname = $item["groupId"]; ?>
		<td class="name">
		  <!--?php if((bool) $_['isadmin']): ?-->
		  <?php if(((bool) $_['isadmin'] || OC_SubAdmin::isSubAdminofGroup(OC_User::getUser(), $groupname)) && ($groupname != 'admin')): ?>
		    <a href="group?groupname=<?php echo $groupname ?>"><?php echo $groupname ?></a>
		  <?php else : ?>
		    <?php echo $groupname; ?>
		  <?php endif; ?>
		</td>
		<!--td class="displayName"><?php p($item["displayName"]); ?></td-->
		<td ><?php p($item["displayName"]); ?></td>
		<td class="country"><?php p($item["country"]); ?></td>
		<td class="giin"><?php p($item["giin"]); ?></td>
		<td class="type"><?php p($item["type"]); ?></td>
		<td class="publicKey">
			<?php if( $item["publicKey"] ) : ?>
				<a href="#">●●●●●●●</a>
			<?php else : ?>
				-
			<?php endif ?>
		</td>
		<td class="suEmail"><?php p($item["suEmail"]); ?></td>
		<td class="encryption"><?php p($item["encryption"]); ?></td>
                <td class="issuer"><?php p($item["issuer"]); ?></td>
                <td class="subject"><?php p($item["subject"]); ?></td>
                <td class="certVersion"><?php p($item["certVersion"]); ?></td>
		<td class="compression"><?php p($item["compression"]); ?></td>
		<td class="antivirus"><?php p($item["antivirus"]); ?></td>
		<td class="remove">
			<!--?php if (OC_User::isAdminUser(OC_User::getUser())) : ?-->
		  	<?php if((bool) $_['isadmin']): ?>
			<?php if($item['groupId']!='admin'):?>
			  <a href="groups?groupname=<?php echo $groupname ?>&deleteGroup=delete" original-title="<?php p($l->t('Delete'))?>">
				<img src="<?php print_unescaped(image_path('core', 'actions/delete.svg')) ?>" class="svg" />
			  </a>
			<?php endif; ?>
			<?php endif;?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
	<br />
	<br />
	<br />
	<form id="newGroupBottom" action="group">
		<?php if((bool) $_['isadmin']): ?>
		<input type="submit" value="Make New Jurisdiction">
		<?php endif; ?>
	</form>
