<?php get_header(); ?>

	<div id="container">
		<div id="content" class="hfeed">

<?php the_post() ?>

			<h2 class="page-title"><a href="<?php echo get_permalink($post->post_parent) ?>" rev="attachment"><?php echo get_the_title($post->post_parent) ?></a></h2>
			<div id="post-<?php the_ID(); ?>" class="<?php veryplaintxt_post_class() ?>">
				<h3 class="entry-title"><?php the_title() ?></h3>
				<div class="entry-content">
					<div class="entry-attachment"><a href="<?php echo wp_get_attachment_url($post->ID); ?>" title="<?php echo wp_specialchars( get_the_title($post->ID), 1 ) ?>" rel="attachment"><?php echo wp_get_attachment_image( $post->ID, 'large' ); ?></a></div>
					<div class="entry-caption"><?php if ( !empty($post->post_excerpt) ) the_excerpt(); ?></div>
<?php the_content('<span class="more-link">'.__('Read More', 'veryplaintxt').'</span>') ?>

				</div>
				<div id="nav-images" class="navigation">
					<div class="nav-previous"><?php previous_image_link() ?></div>
					<div class="nav-next"><?php next_image_link() ?></div>
				</div>
				<div class="entry-meta">
					<?php printf(__('Image posted by %1$s. Posted on <abbr class="published" title="%2$s">%3$s at %4$s</abbr>. Filed under %5$s. %6$sBookmark the <a href="%7$s" title="Permalink to %8$s" rel="bookmark">permalink</a>. Follow comments here with the <a href="%9$s" title="Comments RSS to %8$s" rel="alternate" type="application/rss+xml">RSS feed</a>.', 'sandbox'),
						'<span class="vcard"><span class="fn n">' . $authordata->display_name . '</span></span>',
						get_the_time('Y-m-d\TH:i:sO'),
						the_date('l, F j, Y,', '', '', false),
						get_the_time(),
						get_the_category_list(', '),
						get_the_tag_list('Tagged ', ', ', '. '),
						get_permalink(),
						wp_specialchars(get_the_title(), 'double'),
						comments_rss() ) ?>
<?php if (('open' == $post-> comment_status) && ('open' == $post->ping_status)) : ?>
					<?php printf(__('<a href="#respond" title="Post a comment">Post a comment</a>, <a href="%s" rel="trackback" title="Trackback URL for your post">trackback URL</a>.', 'veryplaintxt'), get_trackback_url()) ?>
<?php elseif (!('open' == $post-> comment_status) && ('open' == $post->ping_status)) : ?>
					<?php printf(__('Comments are closed, <a href="%s" rel="trackback" title="Trackback URL for your post">trackback URL</a>.', 'veryplaintxt'), get_trackback_url()) ?>
<?php elseif (('open' == $post-> comment_status) && !('open' == $post->ping_status)) : ?>
					<?php printf(__('Trackbacks are closed, but you can <a href="#respond" title="Post a comment">post a comment</a>.', 'veryplaintxt')) ?>
<?php elseif (!('open' == $post-> comment_status) && !('open' == $post->ping_status)) : ?>
					<?php _e('Both comments and trackbacks are currently closed.', 'veryplaintxt') ?>
<?php endif; ?>

<?php edit_post_link(__('Edit this entry.', 'veryplaintxt'),'',''); ?>
				</div>
			</div><!-- .post -->

<?php comments_template() ?>

		</div><!-- #content .hfeed -->
	</div><!-- #container -->

<?php get_sidebar() ?>
<?php get_footer() ?>