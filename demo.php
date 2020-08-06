<?php
	
	use Reactable\Core\Reactable;

	require 'bootstrap.php';

	// arayüz ayarları
	$layoutOptions =
	[
		'layout'      => 'standard',
		'position'    => 'bottom',
		'value'       => 'whoa',
		'service_uri' => 'Reactable/action.php',
		'sound'       => 'asset/sound/cork-effect.mp3',
		'update'      => 'yes',
	];

	// reactable yaptığımız nesnenin bilgileri
	$subjectOptions =
	[
		'id' => "ID-12",
		'type' => null,
		'user_id' => isset( $_COOKIE[ 'guestID' ])? $_COOKIE[ 'guestID' ] : NULL
	];

	// veritabanı ayarları
	$dbOptions =
	[
		'connection' => 'default',
//		'feed' => TRUE
	];

	// reaction nesnesini örnekleyelim
	$item = new Reactable( $layoutOptions, $subjectOptions, $dbOptions );

	echo $item;
