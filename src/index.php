<?php

use Wpify\Tracy\Tracy;

add_action( "init", "wpify_tracy_init", 2 );
function wpify_tracy_init() {
	new Tracy();
}
