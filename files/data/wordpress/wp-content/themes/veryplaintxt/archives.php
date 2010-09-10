<?php
/*
Template Name: Archives Page
*/
?>
<?php get_header() ?>
	
	<div id="container">
		<div id="content" class="hfeed">

<?php the_post() ?>

			<div id="post-<?php the_ID() ?>" class="<?php veryplaintxt_post_class() ?>">
				<h2 class="entry-title"><?php the_title() ?></h2>
				<div class="entry-content">
<?php the_content(); ?>

					<div class="alignleft content-column">
						<h3><?php _e('Archives by Category', 'veryplaintxt') ?></h3>
						<ul>
						<?php wp_list_categories('title_li=&orderby=name&show_count=1&use_desc_for_title=1&feed=RSS') ?>
						</ul>
					</div>
					<div class="alignright content-column">
						<h3><?php _e('Archives by Month', 'veryplaintxt') ?></h3>
						<ul>
							<?php wp_get_archives('type=monthly&show_post_count=1') ?>
						</ul>
					</div>
					<div class="full-column">
						<h3><?php _e('Archives by Tag', 'veryplaintxt') ?></h3>
						<p><?php wp_tag_cloud() ?></p>
					</div>
<?php edit_post_link(__('Edit this entry.', 'veryplaintxt'),'<p class="entry-edit">','</p>') ?>

				</div>
			</div><!-- .post -->

<?php if ( get_post_custom_values('comments') ) comments_template() // Add a key/value of "comments" to load comments on a page ?>

		</div><!-- #content .hfeed -->
	</div><!-- #container -->

<?php get_sidebar() ?>
<?php get_footer() ?>