<?php

namespace Reactable\Core\Support;
	  use Closure;
	  use Exception;

/**
 * -------------------------------------------------------------------------
 * Eventise
 * -------------------------------------------------------------------------
 * Sınıflara on ve trigger metotlarıyla olay dinleyiciler eklemeyi ve bunları
 * tetiklemeyi sağlayan mekanizmalar sağlar.
 * 
 */
trait Eventise
{
	use Assignable;

	/**
	 * Çakışma önleyici ön ek.
	 * @var String
	 */
	public $prefix = 'EventListener';

	/**
	 * Olay dinleyici havuzu.
	 * @var Array
	 */
	public $listeners = [];

	/**
	 * Verilen bir olay dinleyici metodu, verilen isimle saklar.
	 * 
	 * @param String $name Olay adı
	 * @param Closure $callback olay dinleyici metot
	 * @param Boolean $overwrite olay dinleyici mevcutsa hepsinin üzerine yazılıp yazılmayacağı
	 * @return $this
	 */
	public function on( $name, Closure $callback, $overwrite = FALSE )
	{
		$name = "{$this->prefix}-$name";

		// ilk defa ekleme durumu
		if( ! isset( $this->{ $name }))
		
			$this->{ $name } = [ $callback ];
		
		else
		{
			if( $overwrite )

				$this->{ $name } = [ $callback ];

			else

				$this->{ $name }[] = $callback;
		}

		return $this;
	}

	/**
	 * Bir olayı çalıştırır. Olay içindeki son
	 * dinleyici metodun döndürdüğü değeri döndürür.
	 * 
	 * @param String $name çalıştırılacak olay adı
	 * @param Array $params olay dinleyicilere geçirilecek parametreler
	 * @return FALSEMixed
	 */
	public function trigger( $name, ...$params )
	{
		$name = "{$this->prefix}-$name";

		// olay dinleyici tanımsızsa
		if( ! isset( $this->{ $name }))

			return;

		$listeners = $this->{ $name };

		foreach( $listeners AS $listener )
		{
			$listener = $listener->bindTo( $this, $this );
			$returned = call_user_func_array( $listener, $params );
		}
		
		return $returned;
	}
}
