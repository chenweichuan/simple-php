<?php
define( 'IS_DEBUG', 1 );
define( 'PUBLIC_PATH', dirname( __FILE__ ) );
define( 'APPLICATION_PATH', dirname( PUBLIC_PATH ) );

require APPLICATION_PATH . '/Core/runtime.php';

Core_Application::run();

