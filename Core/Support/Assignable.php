<?php

namespace Reactable\Core\Support;

/**
 * -------------------------------------------------------------------------
 * Assignable
 * -------------------------------------------------------------------------
 * Sınıflara değişken kaydetme ve geri getirme yeteneği kazandırır.
 * 
 */
trait Assignable
{
	/**
	 * Değişken havuzu.
	 * @var Array
	 */
	public $assignments = [];

	/**
	 * Bir değişken kaydeder.
	 * 
	 * @param String $name değişken adı.
	 * @param Mixed $val değişken değeri.
	 * @param Boolean $overwrite üzerine yazılıp yazılmayacağı
	 * @return Boolean
	 */
	public function assign( $name, $val, $overwrite = TRUE )
	{
		if( ! $overwrite && array_key_exists( $name, $this->assignments ))

			return FALSE;
		
		$this->assignments[ $name ] = $val;
		
		return TRUE;
	}

	/**
	 * Assign edilmiş değişkenleri döndürür.
	 * 
	 * @param String $name değişken adı
	 * @return FALSE|Mixed
	 */
	public function __get( $name )
	{
		if( ! array_key_exists( $name, $this->assignments ))

			return FALSE;

		return $this->assignments[ $name ];
	}

	/**
	 * assign metodunun kısayoludur.
	 * 
	 * @param String $name değişken adı
	 * @param Mixed $val değer
	 */
	public function __set( $name, $val )
	{
		$this->assign( $name, $val );
	}

	/**
	 * Miras alan sınıfın etki alanı ($this) üzerinde 
	 * bir isset çağrısı yapıldığında tetiklenir.
	 * 
	 * @param String $name özellik adı
	 * @return Boolean
	 */
	public function __isset( $name )
	{
		return $this->{ $name }
			? TRUE
			: FALSE;
	}
}
