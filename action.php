<?php

use Reactable\Core\Reactable;

require "./bootstrap.php";

/**
 * ----------------------------------------------------------------------------
 * SUBJECT OPTIONS
 * ----------------------------------------------------------------------------
 * Reaksiyon verilecek olan web nesnesinin bilgilerini burada tanımlayacağız.
 * Eklenti bu bilgileri kullanarak çok biçimli çalışma yeteneği kazanır, eklentiyi
 * hem fotoğraflarda hem de gönderilerde ve belki de özel mesajlarda aynı anda
 * kullanmak için bu ayarlar gereklidir.
 * 
 */
$subjectOptions =
[
	'id' => $_REQUEST[ 'id' ],
	'type' => NULL
];

if( ! empty( $_REQUEST[ 'subject-type' ]))

	$subjectOptions[ 'type' ] = ( $st = $_REQUEST[ 'subject-type' ]) == 'null'
		? NULL
		: $st;

/**
 * ----------------------------------------------------------------------------
 * DATABASE OPTIONS
 * ----------------------------------------------------------------------------
 * Bu alanda veritabanı ayarları yapılmalıdır. Böylece farklı durumlar için
 * farklı bağlantılar oluşturabilir, bunların seçimini otomatize edebiliriz.
 * 
 */
$dbOptions =
[
	'connection' => 'default'
];

// reaksiyon verilebilir nesneyi örnekleyelim
$item = new Reactable( NULL, $subjectOptions, $dbOptions );

/**
 * ----------------------------------------------------------------------------
 * AUTHORIZATION
 * ----------------------------------------------------------------------------
 * Bu alanda kullanıcı oturum açmış mı, açmışsa bu öğe için reaksiyon verme
 * yetkisi var mı bunlara bakılmalıdır.
 * 
 */
$item->on( 'auth-check', function()
{
	if( isset( $_COOKIE[ 'guestID'] ))
	{
		$this->subjectOptions[ 'user_id' ] = $_COOKIE[ 'guestID' ];
		return TRUE;
	}
	else

		return FALSE;
});

// nesnemiz kendisine yapılan ağ isteklerini dinlesin
$item->listen();
