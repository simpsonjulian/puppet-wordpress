<?php header("HTTP/1.1 404 Not Found"); ?>
<?php get_header() ?>

	<div id="container">
		<div id="content">
			<div id="post-0" class="post">
				<h2 class="entry-title"><?php _e('Not Found', 'veryplaintxt') ?></h2>
				<div class="entry-content">
					<p><?php _e('Apologies, but we were unable to find what you were looking for. Perhaps the search box will help.', 'veryplaintxt') ?></p>
				</div>
			</div><!-- #post-0 .post -->
			<form id="error404-searchform" method="get" action="<?php bloginfo('home') ?>">
				<div>
					<input id="error404-s" name="s" type="text" value="<?php echo wp_specialchars(stripslashes($_GET['s']), true) ?>" size="40" />
					<input id="error404-searchsubmit" name="searchsubmit" type="submit" value="<?php _e('Search', 'veryplaintxt') ?>" />
				</div>
			</form>
		</div><!-- #content .hfeed -->
	</div><!-- #container -->

<?php get_sidebar() ?>
<?php get_footer() ?>