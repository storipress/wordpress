<?php

$url = '';

$error = 0;

$core = \Storipress::instance()->core;

try {
	$url = $core->is_connected()
		? $core->get_app_url()
		: $core->get_install_url();
} catch ( Exception $e ) {
	$error = $e->getCode();
}

require __DIR__ . '/../dist/home.html';
