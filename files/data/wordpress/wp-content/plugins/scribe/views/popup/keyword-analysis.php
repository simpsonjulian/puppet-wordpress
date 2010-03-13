<?php
$keywordInfo = $info['GetAnalysisResult']['Analysis']['PrimaryKeywords'];
$analysisInfo = $info['GetAnalysisResult']['Analysis']['KeywordAnalysis'];
?>
<form method="post">
	<h3><?php _e( 'Contextual Analysis' ); ?></h3>
	<p>
		<?php echo $keywordInfo['Description']; ?>
	</p>
	<table class="form-table">
		<tbody>
			<?php foreach($keywordInfo['KeywordDescriptions']['KeywordDescription'] as $keywordDescription) { ?>
				<?php if( $keywordDescription['Type'] == 'Primary Keywords') { ?>
					<th scope="row"><strong><?php echo $keywordDescription['Type']; ?></strong></th>
					<td>
						<?php
						$primaryKeywords = $this->getSeoPrimaryKeywordsForPost($_GET['post']);
						if( count( $primaryKeywords ) == 0 ) {
							echo $keywordDescription['Value'];
						} elseif( count( $primaryKeywords ) == 1 ) {
							printf(__('The term %s is emphasized within your content and is considered a Primary Keyword.' ), "<strong>{$primaryKeywords[0]}</strong>");
						} elseif(count($primaryKeywords)==2) {
							printf(__('The terms %s and %s are emphasized within your content and are considered Primary Keywords.'),"<strong>{$primaryKeywords[0]}</strong>","<strong>{$primaryKeywords[1]}</strong>");
						} else {
							$last = array_pop($primaryKeywords);
							printf(__('The terms %s, and %s are emphasized within your content and are considered Primary Keywords.'),'<strong>' . implode('</strong>, <strong>', $primaryKeywords) . '</strong>',"<strong>{$last}</strong>");
						}
						?>
					</td>
				<?php } else { ?>
				<tr>
					<th scope="row"><strong><?php echo $keywordDescription['Type']; ?></strong></th>
					<td>
						<?php echo $keywordDescription['Value']; ?>
					</td>
				</tr>
				<?php } ?>
			<?php } ?>
		</tbody>
	</table>
	<h3><?php _e( 'Keyword Analysis' ); ?></h3>
	<p>
		<?php echo $analysisInfo['Description']; ?>
	</p>
	<table class="widefat" style="width: 99%;">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Keywords' ); ?></th>
				<th scope="col"><?php _e( 'Rank' ); ?></th>
				<th scope="col"><?php _e( 'Prominence' ); ?></th>
				<th scope="col"><?php _e( 'Frequency' ); ?></th>
				<th scope="col"><?php _e( 'Density' ); ?></th>
				<!--Remove Annual Search Volume
					<th scope="col"><?php _e( 'Annual Search Volume' ); ?></th>
				-->
			</tr>
		</thead>
		<tbody>
			<?php if( !is_array($keywordInfo['Keywords']['Keyword'] ) ) { ?>
				<tr class="normal">
					<td colspan="6" style="text-align: center;"><?php _e( 'No keywords found.' ); ?></td>
				</tr>
			<?php } else { 
				foreach( $keywordInfo['Keywords']['Keyword'] as $keyword) { ?>
			<tr class="normal">
				<th scope="row"><?php echo $keyword['Term']; ?></td>
				<td><?php echo $keyword['Rank']; ?></td>
				<td><?php echo $keyword['Prominence']; ?></td>
				<td><?php echo $keyword['Frequency']; ?></td>
				<td><?php printf( '%.2f%%', $keyword['Density'] ); ?></td>
				<!-- Remove Annual Search Volume
				<td><?php echo number_format_i18n(intval($keyword['AnnualSearchVolume'])); ?></td>
				-->
			</tr>
			<?php } } ?>
		</tbody>
	</table>
</form>

