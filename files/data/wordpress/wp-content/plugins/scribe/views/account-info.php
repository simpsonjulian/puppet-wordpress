<?php
if( is_wp_error( $userInfo ) ) {
	$code = $userInfo->get_error_code();
	if( -100 == $code ) {
	?>
<table class="form-table" id="ecordia-user-account-info">
	<tbody>
		<tr>
			<th scope="row"><?php _e( 'Retrieving' ); ?></th>
			<td>
				<?php
				printf( __( 'Retrieving your current info... Please wait.' ) ); ?><?php
				?>
			</td>
		</tr>
	</tbody>
</table>
	<?php
	} else {
	?>
<table class="form-table" id="ecordia-user-account-info">
	<tbody>
		<tr>
			<th scope="row"><?php _e( 'Error' ); ?></th>
			<td>
				<?php
				printf( __( 'There was a problem retrieving your account details: <strong class="ecordia-error">%s</strong>' ), $userInfo->get_error_code() ); 
				?>
			</td>
		</tr>
	</tbody>
</table>
	<?php
	}
} else {
	?>
<table class="form-table" id="ecordia-user-account-info">
	<tbody>
		<!--
		<tr>
			<th scope="row"><?php _e( 'Last Billed' ); ?></th>
			<td><strong>
				<?php printf( __( '%1$s for $%2$.2f' ), $userInfo->getLastBilledDate(), $userInfo->getLastBilledAmount() ); ?>
			</strong></td>
		</tr>
		-->
		<tr>
			<th scope="row"><?php _e( 'Account Status' ); ?></th>
			<td><strong>
				<?php echo $userInfo->getAccountStatus(); ?>
			</strong></td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Account Type' ); ?></th>
			<td><strong>
				<?php echo $userInfo->getAccountType(); ?>
			</strong></td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Evaluations' ); ?></th>
			<td><strong>
				<?php printf( __( '%d Evaluations Per Month (1 Evaluation = 1 SEO Analysis)' ), $userInfo->getCreditsTotal() ); ?>
			</strong></td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Evaluations Left' ); ?></th>
			<td><strong>
				<?php 
				$creditsRemaining = $userInfo->getCreditsRemaining();
				printf( __( '%1$d Evaluations as of %2$s' ), $creditsRemaining, date('F j, Y') );
				if( 0 == $creditsRemaining ) {
					_e( '<div class="ecordia-error"><strong>Update your account today!</strong><br />Since you have no evaluations left in your account, you should upgrade now.</div>' );
				}
				?> 
			</strong></td>
		</tr>
	</tbody>
</table>	
	<?php
}
?>
<p class="submit">
	<a class="button" href="https://my.scribeseo.com/change-plan.aspx"><?php _e( 'Upgrade Account' ); ?></a>
</p>