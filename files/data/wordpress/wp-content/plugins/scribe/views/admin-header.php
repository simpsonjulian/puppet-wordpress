<?php
$dependency = $this->getEcordiaDependency();
?>
<!-- Start Ecordia Output -->
<script type="text/javascript">
var ecordia_dependency = '<?php echo $dependency; ?>';
var ecordia = new ecordia(ecordia_dependency);
function ecordia_addTinyMCEEvent(ed) {
	ed.onChange.add(function(ed, e) { ecordia.blurEvent(); } );
}
</script>
<!-- End Ecordia Output -->