<?php
$group=$_['group'];
$rb_checked = 'checked="checked" ';
$amTA = $group['type']=='TA';
//$amModel2 = $group['model']=='2';
//$amOption2 = $group['option']=='2';

//OCP\Util::writeLog("kev",($_['groups']),4);
//OCP\Util::writeLog("kev",($_['group']),4);

//$_['subadmingroups'] = $allGroups;
//$items = array_flip($_['subadmingroups']);
//unset($items['admin']);
//$_['subadmingroups'] = array_flip($items);
?>

<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkToRoute('isadmin'));?>"></script>

	
	<br /><br /><br />
	<form id="new_group" action="groups" method="post">
		<input name="groupname" type="text" value="<?php echo $group['groupId']?>" placeholder="<?php p($l->t('Group Name'))?>" />
		<input name="displayname" type="text" value="<?php echo $group['displayName']?>" placeholder="<?php p($l->t('Display Name'))?>" /><br />
		<input name="country" type="text" value="<?php echo $group['country']?>" placeholder="<?php p($l->t('Country'))?>" />
		<input name="giin" type="text" value="<?php echo $group['giin']?>" placeholder="<?php p($l->t('GIID'))?>" /><br />
<!--		<select name="type">-->
<!--			<option value="FFI">Foreign Financial Institution (FFI)</option>-->
<!--			<option value="TA">Tax Authority (TA)</option>-->
<!--		</select>-->
		<fieldset>
			<legend>Type</legend>
			<input name="type" type="radio" value="FFI" <?php if ( !$amTA ): ?>checked="checked"<?php endif;?> />Foreign Financial Institution (FFI)<br />
			<input name="type" type="radio" value="TA" <?php if ( $amTA ): ?>checked="checked"<?php endif;?> />Tax Authority (TA)
		</fieldset>
		<br />

		<br />
		<label>Public Key</label><br />
		<textarea name="publicKey" rows="5" cols="100"><?php echo $group['publicKey']?></textarea>
		<br />

		<label> Jurisdiction Super User Email Address </label><br />
<!--		<textarea name="suEmail" rows ="1" cols="100"></textarea>-->
		<input name ="suEmail" type="text" value="<?php echo $group['suEmail']?>" placeholder="<?php p($l->t('Super User Email'))?>" />
		</br>

        	<fieldset class="personalblock">
               		<h2><?php p($l->t('Encryption'));?></h2>
	                <input type="text" name="encryption" id="encryption" style="width: 300px" value="<?php echo $group['encryption']?>" 
				placeholder="<?php p($l->t('Encryption Tool/Algorithm'))?>" />
			<span class="msg"></span><br />
	               <em><?php p($l->t('Enter the name of the tool or algorithm you will use to encrypt transmissions'));?></em>
        	</fieldset>


	        <fieldset class="personalblock">
        	        <h2><?php p($l->t('Compression'));?></h2>
                	<input type="text" name="compression" id="compression" style="width: 300px" value="<?php echo $group['compression']?>" cols="100"
	                        placeholder="<?php p($l->t('Compression Tool/Algorithm'));?>" /><span class="msg"></span><br />
        	        <em><?php p($l->t('Enter the name of the tool or algorithm you will use to compress transmissions'));?></em>
	        </fieldset>


                <fieldset class="personalblock">
                        <h2><?php p($l->t('Antivirus'));?></h2>
                        <input type="text" name="antivirus" id="antivirus" style="width: 300px" value="<?php echo $group['antivirus']?>" cols="100"
                                 placeholder="<?php p($l->t('Antivirus Software'));?>" /><span class="msg"></span><br />
                        <em><?php p($l->t('Enter the name of your antivirus software'));?></em>
                </fieldset>

                <fieldset class="personalblock">
                        <h2><?php p($l->t('Certificate Issuer'));?></h2>
                        <input type="text" name="issuer" id="issuer" style="width: 300px" value="<?php echo $group['issuer']?>"
                                placeholder="<?php p($l->t('X.509 Certificate Issuer'))?>" />
                        <span class="msg"></span><br />
                       <em><?php p($l->t('Enter the X.500 name of the entity that signed your certificate'));?></em>
                </fieldset>


                <fieldset class="personalblock">
                        <h2><?php p($l->t('Certificate Subject'));?></h2>
                        <input type="text" name="subject" id="subject" style="width: 300px" value="<?php echo $group['subject']?>"
                                placeholder="<?php p($l->t('X.509 Certificate Subject'))?>" />
                        <span class="msg"></span><br />
                       <em><?php p($l->t('Enter the X.500 Distinguished name of the entity your certificate was issued to'));?></em>
                </fieldset>


                <fieldset class="personalblock">
                        <h2><?php p($l->t('Certificate Version'));?></h2>
                        <input type="text" name="certVersion" id="certVersion" style="width: 300px" value="<?php echo $group['certVersion']?>"
                                placeholder="<?php p($l->t('X.509 Certificate Version'))?>" />
                        <span class="msg"></span><br />
                       <em><?php p($l->t('Enter the version of your X.509 Certificate'));?></em>
                </fieldset>


	       

		<input type="submit" value="<?php p($l->t('Create'))?>" />
	</form>
<div/>
