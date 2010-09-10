<?php get_header() ?>

	<div id="container">
		<div id="content" class="hfeed">

<?php if (have_posts()) : ?>

		<h2 class="page-title"><?php _e('Search Results for:', 'veryplaintxt') ?> <?php echo wp_specialchars(stripslashes($_GET['s']), true); ?></h2>

<?php while (have_posts()) : the_post(); ?>

			<div id="post-<?php the_ID() ?>" class="<?php veryplaintxt_post_class() ?>">
				<h3 class="entry-title"><a href="<?php the_permalink() ?>" title="<?php printf(__('Permalink to %s', 'veryplaintxt'), wp_specialchars(get_the_title(), 1)) ?>" rel="bookmark"><?php the_title() ?></a></h3>
				<div class="entry-date"><abbr class="published" title="<?php the_time('Y-m-d\TH:i:sO'); ?>"><?php unset($previousday); printf(__('%1$s', 'veryplaintxt'), the_date('l, F j, Y', false)) ?></abbr></div>
				<div class="entry-content">
<?php the_excerpt('<span class="more-link">'.__('(Continued)', 'veryplaintxt').'</span>') ?>

				</div>
				<div class="entry-meta">
					<span class="entry-category"><?php printf(__('Filed in %s', 'veryplaintxt'), get_the_category_list(', ') ) ?></span>
					<span class="meta-sep">|</span>
					<span class="entry-tags"><?php the_tags(__('Tagged ', 'veryplaintxt'), ", ", "") ?></span>
					<span class="meta-sep">|</span>
<?php edit_post_link(__('Edit', 'veryplaintxt'), "\t\t\t\t\t<span class='entry-edit'>", "</span>\n\t\t\t\t\t<span class='meta-sep'>|</span>\n"); ?>
					<span class="entry-comments"><?php comments_popup_link(__('Comments (0)', 'veryplaintxt'), __('Comments (1)', 'veryplaintxt'), __('Comments (%)', 'veryplaintxt')) ?></span>
				</div>
			</div><!-- .post -->

<?php endwhile; ?>

			<div id="nav-below" class="navigation">
				<div class="nav-previous"><?php next_posts_link(__('&lsaquo; Older posts', 'veryplaintxt')) ?></div>
				<div class="nav-next"><?php previous_posts_link(__('Newer posts &rsaquo;', 'veryplaintxt')) ?></div>
			</div>

<?php else : ?>

			<div id="post-0" class="post">
				<h2 class="entry-title"><?php _e('Nothing Found', 'veryplaintxt') ?></h2>
				<div class="entry-content">
					<p><?php _e('Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'veryplaintxt') ?></p>
				</div>
			</div><!-- #post-0 .post -->
			<form id="noresults-searchform" method="get" action="<?php bloginfo('home') ?>">
				<div>
					<input id="noresults-s" name="s" type="text" value="<?php echo wp_specialchars(stripslashes($_GET['s']), true) ?>" size="40" />
					<input id="noresults-searchsubmit" name="searchsubmit" type="submit" value="<?php _e('Search', 'veryplaintxt') ?>" />
				</div>
			</form>

<?php endif; ?>

		</div><!-- #content .hfeed -->
	</div><!-- #container -->

<?php get_sidebar() ?>
<?php get_footer() ?>