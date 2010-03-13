<form>
	<?php
	$response = wp_remote_get('http://vesta.ecordia.com/optimizer/docs/seo-best-practice.html', array('sslverify'=>true));
	if( !is_wp_error( $response ) ) {
		$body = $response[ 'body' ];
		$bodyStart = strpos($body,'<body>') + 6;
		$bodyEnd = strpos($body,'</body>');
		$body = substr($body,$bodyStart,$bodyEnd-$bodyStart);
		echo $body;
		error_log($body);
	} else {
		echo '<p><a target="_blank" href="http://vesta.ecordia.com/optimizer/docs/seo-best-practice.html">See the Ecordia SEO Best Practices</a></p>';
	}
	?>
</form>
