	<div id="footer">
		<span id="copyright">&copy; <?php echo( date('Y') ); ?> <?php veryplaintxt_admin_hCard(); ?></span>
		<span class="meta-sep">&para;</span>
		<span id="generator-link"><?php _e('Thanks, <a href="http://wordpress.org/" title="WordPress">WordPress</a>.', 'veryplaintxt') ?></span>
		<span class="meta-sep">&para;</span>
		<span id="theme-link"><a href="http://www.plaintxt.org/themes/veryplaintxt/" title="veryplaintxt theme for WordPress" rel="follow designer">veryplaintxt</a> <?php _e('theme by', 'veryplaintxt') ?> <span class="vcard"><a class="url fn n" href="http://scottwallick.com/" title="scottwallick.com" rel="follow designer"><span class="given-name">Scott</span><span class="additional-name"> Allan</span><span class="family-name"> Wallick</span></a></span>.</span>
		<span class="meta-sep">&para;</span>
		<span id="web-standards"><?php _e('It\'s nice', 'veryplaintxt') ?> <a href="http://validator.w3.org/check/referer" title="Valid XHTML">XHTML</a> &amp; <a href="http://jigsaw.w3.org/css-validator/validator?profile=css2&amp;warning=2&amp;uri=<?php bloginfo('stylesheet_url'); ?>" title="Valid CSS">CSS</a>.</span>
	</div><!-- #footer -->

<?php wp_footer() // Do not remove; helps plugins work ?>

</div><!-- #wrapper -->

</body><!-- end trasmission -->
</html>