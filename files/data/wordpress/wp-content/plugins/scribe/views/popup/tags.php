<?php
$tagInfo = (array)$info['GetAnalysisResult']['Analysis']['TagAnalysis'];
$tags = array();
foreach($tagInfo['Tags']['Tag'] as $tag) {
	$tags[] = $tag['Value'];
}
?>
<form method="post">
	<p>
	<?php echo $tagInfo['Description']; ?>
	</p>
	<blockquote id="ecordia-tags">
	<?php echo '<strong>' . implode('</strong>, <strong>',$tags) . '</strong>'; ?>
	</blockquote>
</form>
