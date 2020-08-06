<?php

namespace Reactable\Core;
	  use PDO;
	  use Exception;
	  use PDOException;
	  use PDOStatement;
	  use stdClass;

/**
 * -------------------------------------------------------------------------
 * DB
 * -------------------------------------------------------------------------
 * Veritabanı işlemleri yapar.
 * 
 */
class DB
{
	/**
	 * Bağlantı ayarları.
	 * @var Array
	 */
	public $configs;

	/**
	 * Ön hazırlanmış bir PDO sorgusu.
	 * @var PDOStatement
	 */
	public $prepared;

	/**
	 * Son çalıştırılan normalize edilen sorgu sonuçları.
	 * @var Array
	 */
	public $executed = [];

	/**
	 * Bu bağlantının adı.
	 * @var String
	 */
	public $connectionName = 'default';

	/**
	 * Kullanılacak bağlantı.
	 * @var PDO
	 */
	public $connection;

	/**
	 * Kurulumu yapar.
	 * @param String $connConfigName bağlantı grubu ayarlarını tutan config değişkeni adı
	 */
	public function __construct( $connConfigName = 'default' )
	{
		if( ! ( $this->configs = Config::main( 'database' )))

			throw new Exception( 'Config/main.php dosyasında "database" isimli değişken tanımlanmamış.' );
			
		if( ! array_key_exists( $connConfigName, $this->configs ))

			throw new Exception( "Config/main.php dosyasındaki \"database\" değişkeninde".
				" \"$connConfigName\" adında alt bağlantı grubu tanımlanmamış." );
		
		$this->connectionName = $connConfigName;
		$this->configs = $this->configs[ $connConfigName ];

		$this->connect();
	}

	/**
	 * Veritabanı ile bağlantıyı kurar.
	 */
	public function connect()
	{
		// config dosyasında tanımlanan bağlantı grubu için
		// daha önceden zaten bir bağlantı oluşturmuşsak
		// bir işlem yapmayacağız
		if( $this->connection = Reactable::singleton( $connName = "DBConnection-{$this->connectionName}" ))

			return;

		// bağlantı yok, bir tane oluşturalım
		// bağlantı metnini oluşturalım
		$dsn = $this->configs[ 'platform' ] . ':';

		// veritabanı adresi veya soket yolu
		$dsn .= $this->makeDSN( 'host' );
		$dsn .= $this->makeDSN( 'unix-socket', 'unix_socket' );

		// bağlanılacak veritabanı adı
		$dsn .= $this->makeDSN( 'database', 'dbname' );

		// port numarası
		$dsn .= $this->makeDSN( 'port' );

		// charset
		$dsn .= $this->makeDSN( 'charset' );

		// PDO kullanacağız
		$this->connection = Reactable::singleton( $connName, new PDO
		(
			$dsn,
			$this->configs[ 'username' ],
			$this->configs[ 'password' ]
		));

		$this->connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    	$this->connection->setAttribute( PDO::ATTR_EMULATE_PREPARES, FALSE );
	}

	/**
	 * Verilen config değerlerinden DSN sözdizimi oluşturur.
	 * 
	 * @param String $configName config değişkeni adı
	 * @param String $dsnName dsn değişken adı
	 * @return String
	 */
	public function makeDSN( $configName, $dsnName = NULL )
	{
		if( $dsnName === NULL )

			$dsnName = $configName;

		return array_key_exists( $configName, $this->configs ) && $this->configs[ $configName ] !== NULL
			? "$dsnName=" . $this->configs[ $configName ] . ';'
			: '';
	}

	/**
	 * Verilen bir PDO sonuç nesnesinde bulunan sorgu
	 * sonuçlarını temiz bir çıktı haline getirip döndürür.
	 * 
	 * @param  PDOStatement $result PDO tarafından üretilen sonuç nesnesi
	 * @return stdClass|array
	 */
	public function normalize( PDOStatement $result )
	{
		$tmp = [];

		while( $row = $result->fetch( PDO::FETCH_OBJ ))

			$tmp[] = $row;

		return $tmp;
	}

	/**
	 * Verilen sql cümlesini çalıştırır, aldığı
	 * sonuçları nesne olarak döndürür.
	 * 
	 * @param String $SQL çalıştırılacak bir sql cümlesi
	 * @return stdClass
	 */
	public function query( $SQL )
	{
		return $this->normalize( $this->connection->query( $SQL ));
	}

	/**
	 * Farklı parametrelerle defalarca çalıştırılacak bir sorgu
	 * hazırlar ve panoya kaydeder ve bu sınıfı döndürür. Böylece
	 * hemen peşinden bir execute metodu çağrısı yapılabilir.
	 * 
	 * @param String $SQL çalıştırılacak parametreli sorgu
	 * @return Reactable\Core\DB
	 */
	public function prepare( $SQL )
	{
		$this->prepared = $this->connection->prepare( $SQL );
		return $this;
	}

	/**
	 * Panodaki hazırlanmış sorguyu verilen 
	 * parametrelerle çalıştırıp sonuçlarını döndürür.
	 * 
	 * @param Array $params parametreler
	 * @return stdClass
	 */
	public function execute( $params = NULL )
	{
		$this->prepared->execute( $params );
		$this->executed = $this->normalize( $this->prepared );

		return $this;
	}

	/**
	 * Son çalıştırılan sorgu sonuçlarını dizi olarak döner.
	 * @return Array
	 */
	public function result()
	{
		$tmp = $this->executed;
		$this->executed = [];

		return $tmp;
	}

	/**
	 * Son çalıştırılan sorgu sonucunu nesne olarak döndürür.
	 * @return stdClass|Array
	 */
	public function row()
	{
		if( $this->executed )
		{
			$tmp = $this->executed[ 0 ];
			$this->executed = [];

			return $tmp;
		}

		return [];
	}
}
