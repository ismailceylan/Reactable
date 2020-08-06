<?php

namespace Reactable\Core;
	  use Exception;

/**
 * -------------------------------------------------------------------------
 * Config
 * -------------------------------------------------------------------------
 * Konfigürasyon işlemlerini gerçekleştirir.
 * 
 */
class Config
{
	/**
	 * Kaonfigürasyon girdilerini tutar.
	 * @var Array
	 */
	public static $config = [];

	/**
	 * Configürasyon dosyasını yükler.
	 *
	 * @param String $filename yüklenecek config dosyası adı
	 * @param String $item yüklenen dosya içinden değeri hemen istenen bir config değişkeni adı
	 */
	public static function load( $filename, $item = NULL )
	{
		$path = REACTABLECONFIG . "$filename.php";

		if( ! is_readable( $path ))

			throw new Exception( 'Config dosyası mevcut değil: ' . $path );
		
		require $path;

		if( isset( $config ))
		{
			static::$config[ $filename ] = $config;
			
			if( $item )

				return static::$filename( $item );

			return $config;
		}
		else

			throw new Exception( '$config değişkeni mevcut değil:' . $path );
	}

	/**
	 * Adı verilen bir config değerini döndürür veya set eder.
	 *
	 * @param String $filename istenen config ayarının bulunduğu dosya adı
	 * @param String $name config değişkeni adı
	 * @param Mixed $value config değeri
	 * @param Mixed $deflt get modundayken adı verilen değişken bulunamazsa döndürülecek bir değer
	 * @return FALSE|Array|Mixed
	 */
	public static function configs( $filename, $name = NULL, $value = NULL, $deflt = '<nulled>' )
	{
		if( ! array_key_exists( $filename, static::$config ))

			return FALSE;

		$group = static::$config[ $filename ];

		if( $name !== NULL && $value === NULL )
		{
			return array_key_exists( $name, $group )
				? $group[ $name ]
				: ( $deflt == '<nulled>'
					? FALSE
					: $deflt );
		}
		else if( $name === NULL && $value === NULL )

			return $group;

		else

			return $group[ $name ] = $value;
	}

	/**
	 * configs metodunun kısayoludur. Metodun ilk parametresini
	 * bu metoda erişmek için kullanılan fonksiyon adı ile oluşturur.
	 * 
	 * @param String $fn fonksiyon adı
	 * @param Array $params fonksiyon parametreleri
	 * @return FALSE|Array|Mixed
	 */
	public static function __callStatic( $fn, $params )
	{
		return call_user_func_array([ __NAMESPACE__ . '\\Config', 'configs' ], array_merge([ $fn ], $params ));
	}
}
