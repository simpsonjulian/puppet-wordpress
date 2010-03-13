<?php
$serpInfo = $info['GetAnalysisResult']['Analysis']['Serp'];
?>
<form method="post">
	<p>
		<?php echo $serpInfo['Description']; ?>
	</p>
	<div id="ecordia-google-item">
        <span style="<?php echo esc_attr($serpInfo['Headline']['Style'] ); ?>" id="ecordia-google-headline"><?php echo esc_html($serpInfo['Headline']['Value'] ); ?></span><br/>
        <span style="<?php echo esc_attr($serpInfo['Snippit']['Style'] ); ?>" id="ecordia-google-snippet"><?php echo htmlentities($serpInfo['Snippit']['Value'] ); ?></span>
        <span style="<?php echo esc_attr($serpInfo['Url']['Style'] ); ?>" id="ecordia-google-url"><?php echo esc_html($serpInfo['Url']['Value'] ); ?></span>
    </div>
</form>
