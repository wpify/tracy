<?php

use Wpify\Tracy\Tracy;

if (function_exists('add_action')) {
	// Load only once
	if (class_exists('\Wpify\Tracy\Tracy')) {
		return;
	}
	if (defined('WPIFY_TRACY_INIT_ACTION')) {
		add_action( "init", WPIFY_TRACY_INIT_ACTION, 2 );
		function wpify_tracy_init() {
			new Tracy();
		}
	} else {
		new Tracy();
	}
}
