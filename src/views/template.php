<!DOCTYPE html>
<?php if(defined('HTML_LANG')): ?>
<html lang="<?php echo HTML_LANG; ?>">
<?php else: ?>
<html>
<?php endif; ?>
<head>
<?php echo Accela\get_header_html($page); ?>
<style class="accela-css">
<?php echo (Accela\PageCommon::instance())->get_css(); ?>
<?php echo implode("\n", array_filter(array_map(function($p){return $p->get_css();}, Accela\Page::all()))); ?>
</style>
</head>
<body>
<div id="accela"></div>
<script>const ACCELA = <?php echo json_encode(Accela\get_initial_data($page)); ?>; ACCELA.modules = {};</script>
<script src="/assets/js/accela.js?__t=<?php echo Accela\get_utime(); ?>" defer></script>
</body>
</html>
