<?php get_header() ?>

	<div id="container">
		<div id="content" class="hfeed">

<?php the_post() ?>

<?php if ( is_day() ) : ?>
			<h2 class="page-title"><?php printf(__('Daily Archives: <span>%s</span>', 'veryplaintxt'), get_the_time(__('F jS, Y', 'veryplaintxt'))) ?></h2>
<?php elseif ( is_month() ) : ?>
			<h2 class="page-title"><?php printf(__('Monthly Archives: <span>%s</span>', 'veryplaintxt'), get_the_time(__('F Y', 'veryplaintxt'))) ?></h2>
<?php elseif ( is_year() ) : ?>
			<h2 class="page-title"><?php printf(__('Yearly Archives: <span>%s</span>', 'veryplaintxt'), get_the_time(__('Y', 'veryplaintxt'))) ?></h2>
<?php elseif ( is_author() ) : ?>
			<h2 class="page-title"><?php printf(__('Author Archives: <span class="vcard"><span class="fn n">%s</span></span>', 'veryplaintxt'), get_the_author() ) ?></h2>
			<div class="archive-meta"><?php if ( !(''== $authordata->user_description) ) : echo apply_filters('archive_meta', $authordata->user_description); endif; ?></div>
<?php elseif ( is_category() ) : ?>
			<h2 class="page-title"><?php _e('Category Archives:', 'veryplaintxt') ?> <span class="page-cat"><?php echo single_cat_title(); ?></span></h2>
			<div class="archive-meta"><?php if ( !(''== category_description()) ) : echo apply_filters('archive_meta', category_description()); endif; ?></div>
<?php elseif ( is_tag() ) : ?>
			<h2 class="page-title"><?php _e('Tag Archives:', 'veryplaintxt') ?> <span class="tag-cat"><?php single_tag_title(); ?></span></h2>
<?php elseif ( isset($_GET['paged']) && !empty($_GET['paged']) ) : ?>
			<h2 class="page-title"><?php _e('Blog Archives', 'veryplaintxt') ?></h2>
<?php endif; ?>

<?php rewind_posts() ?>

<?php while ( have_posts() ) : the_post(); ?>

			<div id="post-<?php the_ID() ?>" class="<?php veryplaintxt_post_class() ?>">
				<h3 class="entry-title"><a href="<?php the_permalink() ?>" title="<?php printf(__('Permalink to %s', 'veryplaintxt'), wp_specialchars(get_the_title(), 1)) ?>" rel="bookmark"><?php the_title() ?></a></h3>
				<div class="entry-date"><abbr class="published" title="<?php the_time('Y-m-d\TH:i:sO'); ?>"><?php unset($previousday); printf(__('%1$s', 'veryplaintxt'), the_date('l, F j, Y', false)) ?></abbr></div>
				<div class="entry-content">
<?php the_excerpt('<span class="more-link">'.__('Continue reading &rsaquo;', 'veryplaintxt').'</span>') ?>

				</div>
				<div class="entry-meta">
					<span class="entry-category"><?php if ( !is_category() ) { printf(__('Filed in %s', 'veryplaintxt'), get_the_category_list(', ') ); } else { $other_cats = veryplaintxt_other_cats(', '); printf(__('Also filed in %s', 'veryplaintxt'), $other_cats ); } ?></span>
					<span class="meta-sep">|</span>
					<span class="entry-tags"><?php if ( !is_tag() ) { echo the_tags(__('Tagged ', 'veryplaintxt'), ", "); } else { $other_tags = veryplaintxt_other_tags(', '); printf(__('Also tagged %s', 'veryplaintxt'), $other_tags); } ?></span>
					<span class="meta-sep">|</span>
<?php edit_post_link(__('Edit', 'veryplaintxt'), "\t\t\t\t\t<span class='entry-edit'>", "</span>\n\t\t\t\t\t<span class='meta-sep'>|</span>\n"); ?>
					<span class="entry-comments"><?php comments_popup_link(__('Comments (0)', 'veryplaintxt'), __('Comments (1)', 'veryplaintxt'), __('Comments (%)', 'veryplaintxt')) ?></span>
				</div>
			</div><!-- .post -->

<?php endwhile ?>

			<div id="nav-below" class="navigation">
				<div class="nav-previous"><?php next_posts_link(__('&lsaquo; Older posts', 'veryplaintxt')) ?></div>
				<div class="nav-next"><?php previous_posts_link(__('Newer posts &rsaquo;', 'veryplaintxt')) ?></div>
			</div>

		</div><!-- #content .hfeed -->
	</div><!-- #container -->

<?php get_sidebar() ?>
<?php get_footer() ?>