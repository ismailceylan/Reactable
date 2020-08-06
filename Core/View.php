<?php

namespace Reactable\Core;
	  use Exception;
	  use Reactable\Core\Support\Assignable;

/**
 * -------------------------------------------------------------------------
 * View
 * -------------------------------------------------------------------------
 * HTML dosyalarını yüklemeyi ve onlarla çalışmayı sağlar.
 * 
 */
class View
{
	use Assignable;

	/**
	 * İşlenmemiş view içeriği.
	 * @var String
	 */
	public $path = '';

	/**
	 * View yolunu alarak kurulumu yapar.
	 * @param String $path view yolu
	 */
	public function __construct( $path )
	{
		$this->path = $path;
	}

	/**
	 * Adı verilen view dosyasını yükler.
	 * 
	 * @param String $filename yüklenecek bir dosyanın .php içermeyen adı
	 * @param Array $vars değişkenler
	 * @return Reactable\Core\View
	 */
	public static function load( $filename, $vars = NULL )
	{
		$path = REACTABLEVIEW . "$filename.php";

		if( ! is_readable( $path ))

			throw new Exception( 'View dosyası mevcut değil: ' . $path );

		$view = static::class;
		$view = new $view( $path );
		$view->assignments = $vars;

		return $view;
	}

	/**
	 * Nesne string olarak ele alındığında tetiklenir.
	 * @return String
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Temsil edilen view içeriğini render eder.
	 * @return String
	 */
	public function render()
	{
		// çıkış akımı başlatalım
		ob_start();
		// dizi değişkenleri doğal değişken yapalım
		extract( $this->assignments );
		// view dosyasını yükleyelim
		include( $this->path );
		// view dosyasının oluşturduğu text içeriği alalım
		$buffer = ob_get_contents();
		// çıktı akımını temizleyelim
		@ob_end_clean();

		// işlenmiş view içeriğini döndürelim
		return $buffer;
	}
}
