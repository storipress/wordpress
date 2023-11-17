<?php

$pluginName = 'Storipress';

ob_start();
	do_action( 'storiress/admin/menu/content', 'home' );
$content = ob_get_clean();

echo $content;
