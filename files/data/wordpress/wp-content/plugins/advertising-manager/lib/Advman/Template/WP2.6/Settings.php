<?php
require_once(OX_LIB . '/Tools.php');
class Advman_Template_Settings
{
	function display($target = null)
	{
		global $advman_engine;

		$action = isset($_POST['advman-action']) ? OX_Tools::sanitize($_POST['advman-action'], 'key') : '';
		
		$oxMarket = $advman_engine->getSetting('openx-market');
		if (is_null($oxMarket)) {
			$oxMarket = false;
		}
		$oxUpdates = $advman_engine->getSetting('openx-sync');
		if (is_null($oxUpdates)) {
			$oxUpdates = false;
		}
		$oxCpm = $advman_engine->getSetting('openx-market-cpm');
		if (is_null($oxCpm)) {
			$oxCpm = '0.20';
		}
		
?><?php if ($action == 'save') : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Settings saved.') ?></strong></p></div>
<?php endif; ?>

<div class="wrap">
<h2><?php _e('Ad Settings', 'advman'); ?></h2>

<form action="" method="post" id="advman-form" enctype="multipart/form-data">
<input type="hidden" name="advman-mode" id="advman-mode" value="settings" />
<input type="hidden" name="advman-action" id="advman-action" value="save" />
<input type="hidden" name="advman-target" id="advman-target" />

<table class="form-table">
<tr valign="top">
	<th scope="row"><?php _e('Optimization', 'advman'); ?></th>
	<td>
		<fieldset>
			<legend class="hidden"><?php _e('Optimization', 'advman'); ?></legend>
			<label for="users_can_register"><input name="advman-openx-market" type="checkbox" id="advman-openx-market" value="1"<?php echo $oxMarket ? ' checked="checked"' : ''; ?> /> <?php _e('Optimize ads on OpenX Market by default', 'advman'); ?></label>
		</fieldset>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Default floor price:', 'advman'); ?> <input type="text" name="advman-openx-market-cpm" value="<?php echo $openxCpm; ?>" class="small-text" /><br />
		<span class="setting-description"><?php _e('By enabling the OpenX Market, an alternative ad may show if it will make you more money than the existing ad.  The floor price is the eCPM (revenue per 1000 ads) that your ad network pays.', 'advman'); ?></span>
	</td>
</tr>
<tr valign="top">
	<th scope="row"><?php _e('Updates', 'advman'); ?></th>
	<td>
		<fieldset>
			<legend class="hidden"><?php _e('Updates', 'advman'); ?></legend>
			<label for="users_can_register"><input name="advman-openx-sync" type="checkbox" id="advman-openx-sync" value="1"<?php echo $oxUpdates ? ' checked="checked"' : ''; ?> /> <?php _e('Check for updates', 'advman'); ?></label>
		</fieldset>
		<span class="setting-description"><?php _e('Checking for updates will keep you informed of not only updates, but of any offers from advertisers who want to buy your ad space.', 'advman'); ?></span>
	</td>
</tr>
</table>


<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', 'advman'); ?>" />
</p>
</form>

</div>

<?php
	}
}
?>