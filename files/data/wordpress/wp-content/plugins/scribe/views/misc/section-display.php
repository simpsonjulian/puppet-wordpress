	<td class="ecordia-middle-cell" >
		<?php echo $section['Name']; ?>
	</td>
	<td class="ecordia-no-pad">
		<ul>
			<?php foreach( $section['Items']['SeoScoreSectionItem'] as $sectionItem ) { ?>
			<li id="<?php echo $sectionItem['Id']; ?>" class="<?php echo ($sectionItem['IsPassing'] == 'true') ? 'complete' : 'warn'; ?>">
				<?php echo $sectionItem[ 'Text' ]; ?>
			</li>
			<?php } ?>
		</ul>
	</td>
