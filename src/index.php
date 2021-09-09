<?php

use Wpify\Tracy\Tracy;

if (function_exists('add_action')) {
	add_action( "init", "wpify_tracy_init", 2 );
	function wpify_tracy_init() {
		new Tracy();
	}
}
