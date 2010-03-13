<?php
$settings = $this->getSettings();
/**
 * @var EcordiaUserAccount
 */
$userInfo = $this->getUserInfo(true);
?>
<div class="wrap">
	<?php screen_icon(); ?><h2><?php _e( 'Scribe Settings' ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=scribe' ) ); ?>">
		<p><?php _e( 'Use these settings to activate, upgrade, and configure your Scribe Content Optimizer plugin.' ); ?></p>
		<table class="form-table">
			<tbody>
				<?php if( is_wp_error( $userInfo ) && $userInfo->get_error_code() == 'ApiKeyInvalid' ) { ?>
				<tr>
					<td colspan="2">
						<strong class="ecordia-error"><?php _e( 'Your API Key is invalid.' ); ?></strong>
						<?php printf( __( 'Re-enter your API Key or <a target="_blank" href="%s">log into your account to retrieve your API Key</a>.' ), 'https://my.scribeseo.com' ); ?>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th scope="row"><label for="current-seo-tool"><?php _e( 'Current SEO Tool' ); ?></label></th>
					<td>
						<?php
						$ecordiaDependency = $this->getEcordiaDependency();
						if( !empty( $ecordiaDependency ) ) {
							_e( 'Based on your configuration, we have determined you are using the following supported SEO tool: ' );
						}
						echo '<strong>';
						switch( $ecordiaDependency ) {
							case 'aioseo':
								_e( 'All in One SEO Pack Plugin' );							
								break;
							case 'headwa':
								_e( 'Headway Theme' );
								break;	
							case 'hybrid':
								_e( 'Hybrid Theme' );
								break;
							case 'thesis':
								_e( 'Thesis Theme' );
								break;
							default:
								printf( __( '<span class="ecordia-error">No solution active</span>.  Please install and activate either the <a href="%1$s" target="_blank">Thesis Theme</a>, <a href="%2$s" target="_blank">Hybrid Theme</a>, <a href="%3$s" target="_blank">Headway Theme</a> or the <a href="%4$s" target="_blank">All in One SEO Pack Plugin</a>.' ), 'http://diythemes.com', 'http://themehybrid.com', 'http://headwaythemes.com/' , 'http://wordpress.org/extend/plugins/all-in-one-seo-pack/'  );
								break;
						}
						echo '</strong>';
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ecordia-api-key"><?php _e( 'Scribe API Key' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" name="ecordia-api-key" id="ecordia-api-key" value="<?php echo esc_attr( $settings[ 'api-key' ] ); ?>" />
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="ecordia-connection-method"><?php _e( 'Security Method' ); ?></label></th>
					
					
					<td>
						<select name="ecordia-connection-method" id="ecordia-connection-method">
							<option <?php selected( false, $settings[ 'use-ssl' ] ); ?> value="http">Basic Non-SSL</option>
							<option <?php selected( true, $settings[ 'use-ssl' ] ); ?> value="https">Enhanced SSL</option>
						</select>
						<?php
						$faqUrl = 'https://my.scribeseo.com';
						?>
						<div id="ecordia-https-warning" <?php if( !$settings['use-ssl'] ) { echo 'style="display:none;"'; } ?>><p><?php printf( __( 'Use of <strong>Enhanced Security (with SSL)</strong> can cause problems on some shared hosts.  Only select this option if you are certain you need it.  See <a href="%s">the Suppport Site</a> for more information.' ), $faqUrl ); ?></p></div>
					</td>
					
				</tr>
				
			</tbody>
		</table>
		<p class="submit">
			<?php wp_nonce_field( 'save-ecordia-api-key-information' ); ?>
			<input type="submit" class="button-primary" name="save-ecordia-api-key-information" id="save-ecordia-api-key-information" value="<?php _e( 'Save' ); ?>" />
		</p>
	</form>
		
	<h3><?php _e( 'Account Information' ); ?></h3>
	<div id="ecordia-account-information">
		<?php include( dirname( __FILE__ ) . '/account-info.php' ); ?>
	</div>
	
	<form action="https://my.scribeseo.com/login.aspx" method="post">
		<h3><?php _e( 'Account Login &amp; Support' ); ?></h3>
		<p><?php _e( 'Our online account center will help you manage your account and billing information.' ); ?></p>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for=""><?php _e( 'Email' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" name="txtEmail" id="txtEmail" /> 
					</td>
				</tr>
				<tr>
					<th scope="row"><label for=""><?php _e( 'Password' ); ?></label></th>
					<td>
						<input type="password" class="regular-text" name="txtPassword" id="txtPassword" /> <a href="https://my.scribeseo.com/forgot-password.aspx/"><?php _e( 'Forgot Password?' ); ?></a>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" id="__EVENTTARGET" name="__EVENTTARGET" value="btnLogin" />
		<input type="hidden" id="__EVENTARGUMENT" name="__EVENTARGUMENT" value="" />
		<p class="submit">
			<input type="submit" name="account-history-login" id="account-history-login" value="<?php _e( 'Login to Account' ); ?>" />
		</p>
	</form>
</div>
