<?php

namespace Wpify\Tracy;
add_action( "init", "Wpify\Tracy\init", 2 );
function init() {
	new Tracy();
}
