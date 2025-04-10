<!DOCTYPE html>
<?php if(defined('HTML_LANG')): ?>
<html lang="<?php echo HTML_LANG; ?>">
<?php else: ?>
<html>
<?php endif; ?>
<head>
<?php echo Accela\getHeaderHtml($page); ?>
</head>
<body>
<div id="accela"></div>
<script>const ACCELA = <?php echo json_encode(Accela\getInitialData($page)); ?>; ACCELA.modules = {};</script>
<script src="/assets/js/accela.js?__t=<?php echo Accela\getUtime(); ?>" type="module"></script>
</body>
</html>
