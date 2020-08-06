<?php defined( 'REACTABLEPATH' ) OR exit( 'No direct script access allowed.' );

/*
|-----------------------------------------------------------
| Database Settings
|-----------------------------------------------------------
| Bu alanda Reactable.js eklentisinin veritabanı ayarlamaları
| bulunur.
|
| Eğer dağıtık bir veritabanı kurulumunuz varsa aşağıdaki dizi
| değişken grubunu kopyalayıp ortadaki segmentine yeni bir isim
| vererek çeşitli durumlar için farklı konfigürasyonlar izlenmesini
| sağlayabilirsiniz.
| 
*/
$config[ 'database' ][ 'default' ][ 'platform'    ] = 'mysql';
$config[ 'database' ][ 'default' ][ 'host'        ] = 'localhost';
$config[ 'database' ][ 'default' ][ 'unix-socket' ] = NULL;
$config[ 'database' ][ 'default' ][ 'port'        ] = 3306;
$config[ 'database' ][ 'default' ][ 'username'    ] = 'root';
$config[ 'database' ][ 'default' ][ 'password'    ] = '';
$config[ 'database' ][ 'default' ][ 'database'    ] = 'reactions';
$config[ 'database' ][ 'default' ][ 'table'       ] = 'reactions';
$config[ 'database' ][ 'default' ][ 'charset'     ] = 'UTF8';

//$config[ 'database' ][ 'slave connection name' ][ 'platform'    ] = 'mysql';
//$config[ 'database' ][ 'slave connection name' ][ 'host'        ] = NULL;
//$config[ 'database' ][ 'slave connection name' ][ 'unix-socket' ] = NULL;
//$config[ 'database' ][ 'slave connection name' ][ 'port'        ] = NULL;
//$config[ 'database' ][ 'slave connection name' ][ 'username'    ] = NULL;
//$config[ 'database' ][ 'slave connection name' ][ 'password'    ] = NULL;
//$config[ 'database' ][ 'slave connection name' ][ 'database'    ] = NULL;
//$config[ 'database' ][ 'slave connection name' ][ 'table'       ] = NULL;
//$config[ 'database' ][ 'slave connection name' ][ 'charset'     ] = 'UTF8';

/*
|--------------------------------------------------------------------------
| Supported Reactions
|--------------------------------------------------------------------------
|
| Supported reactions and their names with order.
|
*/
$config[ 'supported reactions' ] =
[ 
	'angry',
	'upset',
	'whoa',
	'haha',
	'love',
	'like'
];
