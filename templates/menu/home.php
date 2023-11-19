<?php

$core = \Storipress::instance()->core;

try {
	$url = $core->is_connected()
		? $core->get_app_url()
		: $core->get_install_url();

    require __DIR__ . '/../dist/home.html';
} catch ( Exception $e ) {
    echo sprintf('Something went wrong, error code: %d', $e->getCode());
}

