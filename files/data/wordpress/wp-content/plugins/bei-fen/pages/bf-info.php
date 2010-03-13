<div class="beifen_widget">
	<h3><?php _e('News about Bei Fen', WP_BEIFEN_DOMAIN); ?></h3>
	<div class="beifen_widget_content">
		<?php
			include_once(ABSPATH . WPINC . '/feed.php');
			$rss = fetch_feed('http://www.beifen.info/feed/');
			$maxitems = $rss->get_item_quantity(5);
			$rss_items = $rss->get_items(0, $maxitems);
		?>
		<ul>
			<?php
				if ($maxitems == 0)
				{
					echo '<li>' . __('No items', WP_BEIFEN_DOMAIN) . '</li>';
				}
				else
				{
					foreach ( $rss_items as $item ) : ?>
						<li><a href='<?php echo $item->get_permalink(); ?>' title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'><?php echo $item->get_title(); ?></a></li>
					<?php endforeach;
				}
			?>
		</ul>
	</div>
</div>
<div class="beifen_widget">
	<h3><?php _e('Documentation and Support', WP_BEIFEN_DOMAIN); ?></h3>
	<div class="beifen_widget_content">
		<ul style="text-align:center;">
			<li style="display:inline;white-space:nowrap;padding:3px;margin:0;"><strong><a href="http://www.beifen.info/documentation/"><?php _e('Read the manual', WP_BEIFEN_DOMAIN); ?></a></strong></li>
			<li style="display:inline;white-space:nowrap;padding:3px;margin:0;"><strong><a href="http://www.beifen.info/request-support/"><?php _e('Get support', WP_BEIFEN_DOMAIN); ?></a></strong></li>
		</ul>
	</div>
</div>
<div class="beifen_widget">
	<h3><?php _e('Donate', WP_BEIFEN_DOMAIN); ?></h3>
	<div class="beifen_widget_content">
		<p><?php _e('Please consider donating to improve the development of Bei Fen and benefit from this in return. You can even donate without paying any money!', WP_BEIFEN_DOMAIN); ?><br/><a href="http://www.beifen.info/donate/"><?php _e('Read about how you can donate!', WP_BEIFEN_DOMAIN); ?></a></p>
	</div>
</div>