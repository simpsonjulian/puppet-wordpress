<?php
global $post;
if( isset( $post->ID ) ) {
	$score = $this->getSeoScoreForPost( $post->ID );
	$primaryKeywords = $this->getSeoPrimaryKeywordsForPost($post->ID);
} else {
	$score = 0;
	$keywords = array();
}
?>
<input type="hidden" name="serialized-ecordia-results" value="<?php echo get_post_meta($pid, $this->_meta_seoInfo, true); ?>" />
<div>
	<div id="ecordia-review-score">
		<p><strong><?php _e( 'Content Score' ); ?></strong></p>
		<p><strong id="ecordia-review-score-number" class="<?php echo $this->getSeoScoreClassForPost( $score ); ?>"><?php printf( __( '%1$d%%' ), $score ); ?></strong></p>
	</div>
	<div id="ecordia-review-keywords">
		<p><strong><?php _e( 'Primary Keywords' ); ?></strong></p>
		<?php if( empty( $primaryKeywords ) ) { ?>
		<p class="ecordia-error"><?php _e( 'No Primary Keywords Found.'); ?></p>
		<?php } else { ?>
		<ul style="margin-left: 6px;">
			<?php foreach( $primaryKeywords as $primary ) { ?>
			<li class="<?php echo $this->getSeoScoreClassForPost(100); ?>"><strong><?php echo $primary; ?></strong></li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
	<br class="clear" />
</div>
<?php include ( dirname( __FILE__ ) . '/validation-list.php' ); ?>
<br class="clear" />
<div id="ecordia-analyze-action">
	<p class="ajax-feedback" id="ecordia-ajax-feedback">
		<img alt="" title="" src="images/wpspin_light.gif" /> <?php _e( 'Analyzing...' ); ?>
	</p>
	<div class="alignleft">
		<p>
			<a href="<?php echo esc_url(admin_url('media-upload.php?tab=ecordia-score&post=' . $post->ID . '&TB_iframe=true')); ?>" id="ecordia-seo-analysis-review-button" class="button"><?php _e( 'Review' ); ?></a>
		</p>
	</div>
	<div class="alignright">
		<p>
			<a href="#" id="ecordia-seo-analysis-analyze-button" class="button-primary"><?php _e( 'Analyze' ); ?></a>
		</p>
	</div>
	<br class="clear" />
</div>
