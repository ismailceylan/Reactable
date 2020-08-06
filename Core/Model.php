<?php

namespace Reactable\Core;
	  use Reactable\Core\DB;
	  use Reactable\Core\Reactable;
	  use Reactable\Core\Support\Assignable;

/**
 * -------------------------------------------------------------------------
 * Model
 * -------------------------------------------------------------------------
 * Veri tarafını temsil eder.
 * 
 */
class Model
{
	use Assignable;
	
	/**
	 * Veritabanı bağlantısı.
	 * @var Reactable\Core\Database
	 */
	public $db;

	/**
	 * Model kurulumunu yapar.
	 * @param string $configGroup [description]
	 */
	public function __construct( $configGroup = 'default' )
	{
		// veritabanı bağlantısı varsa mevcut olanı döndürelim
		if( $db = Reactable::singleton( $configGroup ))

			$this->db = $db;

		// bağlantı yok, oluşturup döndürelim
		$this->db = Reactable::singleton( $configGroup, new DB( $configGroup ));

	}
}
