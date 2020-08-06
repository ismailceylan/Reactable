<?php

use Reactable\Core\Reactable;

ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );

// dizinleri tanımlayalım
define( 'REACTABLEPATH', __DIR__ . DIRECTORY_SEPARATOR );
define( 'REACTABLECORE', REACTABLEPATH . 'Core/' );
define( 'REACTABLEVIEW', REACTABLEPATH . 'View/' );
define( 'REACTABLECONFIG', REACTABLEPATH . 'Config/' );
define( 'REACTABLEEXCEPTION', REACTABLEPATH . 'Exception/' );

// otomatik yükleyiciyi dahil edelim
require 'autoloader.php';

// Reactable kurulumunu yapalım
Reactable::init();
