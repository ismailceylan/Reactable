<?php

/**
 * Kütüphane için otomatik yükleme senaryosunu oluşturur.
 */
spl_autoload_register( function( $namespace )
{
	// dizin yapısı içinde Reactable yoksa istek bizi ilgilendirmiyordur
	if( strpos( $namespace, 'Reactable' ) == -1 )

		return;

	// işletim sistemine uydurulmuş dosya yolunu elde edelim
	$path = str_replace( "\\", DIRECTORY_SEPARATOR, $namespace );
	// çalıştırılabilir dosya yolunu ayarlayalım
	$file = __DIR__ . DIRECTORY_SEPARATOR . "../$path.php";

	// dosya mevcut mu
	if( is_readable( $file ))

		require $file;
});
