<div id="thirstrunwizard">
	<h1>
		<?php p($l->t('Welcome to'));?> <?php p($theme->getTitle()); ?>
	</h1>
	<form id="t_pwf">
       		<fieldset>
                	<h2><?php p($l->t('Password'));?></h2>
                	
			<div id="t_pwc"><?php echo $l->t('Your password was changed');?></div>
                	<div id="t_pwe"><?php echo $l->t('Unable to change your password');?></div>
                	
			<input type="password" id="t_pw1" name="oldpassword"
                	        placeholder="<?php echo $l->t('Current password');?>" autocomplete="off" />
                	
			<input type="password" id="t_pw2" name="personal-password"
                       		placeholder="<?php echo $l->t('New password');?>"
                        	data-typetoggle="#personal-show" autocomplete="off" />
                	
			<input type="password" id="t_pw3" name="personal-password"
                       		placeholder="<?php echo $l->t('New password');?>"
                        	data-typetoggle="#personal-show" autocomplete="off" />
                	
			<input id="t_pwb" type="submit" value="<?php echo $l->t('Change password');?>" />
                	
			<input type="checkbox" id="t_s_pw" name="show" /><label for="t_s_pw"></label>
        	</fieldset>
	</form>

</div>

