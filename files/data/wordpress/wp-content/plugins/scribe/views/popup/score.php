<?php
$scoreInfo = $info['GetAnalysisResult']['Analysis']['SeoScore'];
$score = $scoreInfo['Score']['Value'];
$description = $scoreInfo['Score']['Description'];
$sections = $scoreInfo[ 'Sections' ][ 'SeoScoreSection' ];
$sectionCount = count( $sections );
$firstSection = array_shift($sections);
?>
<form method="post">
	<p>
		
		<?php 
		echo $scoreInfo['Description']; 
		?>
	</p>
	<table class="widefat" style="width:99%" id="ecordia-analysis-score-table">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Overall' ); ?></th>
				<th scope="col"><?php _e( 'Content' ); ?></th>
				<th scope="col"><?php _e( 'Analysis &amp; Recommendations' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td id="ecordia-score-analysis-overview" class="ecordia-middle-cell <?php echo $this->getSeoScoreClassForPost( $scoreInfo['Score']['Value'] ); ?>-background" rowspan="<?php echo $sectionCount; ?>">
					<div id="overall-score-analysis"><?php printf( __( '%d%%' ), $scoreInfo['Score']['Value']); ?></div>
					<p><?php echo $scoreInfo['Score']['Description']; ?></p>
				</td>
				<?php $this->displaySection($firstSection); ?>
			</tr>
			<?php foreach( $sections as $section ) { ?>
			<tr>
				<?php $this->displaySection( $section ); ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</form>

