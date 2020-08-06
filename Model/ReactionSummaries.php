<?php

namespace Reactable\Model;
	  use Reactable\Core\Reactable;
	  use Reactable\Core\Model;
	  use Reactable\Core\Config;

/**
 * -------------------------------------------------------------------------
 * ReactionSummaries
 * -------------------------------------------------------------------------
 * Reaksiyon özetlerinin veri tarafını temsil eder.
 * 
 */
class ReactionSummaries extends Model
{
	/**
	 * Özetin veritabanında bulunup bulunmadığı bilgisini tutar.
	 * @var Boolean
	 */
	public $isExistsOnDB = FALSE;

	/**
	 * Reaksiyon isimleri ve verilme sayısını tutar.
	 * @var Array
	 */
	public $summaries;

	/**
	 * Temsil edilecek öğenin ID bilgisi.
	 * @var Mixed
	 */
	public $itemID;

	/**
	 * Temsil edilecek öğenin türü.
	 * @var String
	 */
	public $itemType;

	/**
	 * Model kurulumunu yapar.
	 * @param String $configGroup config dosyasındaki veritabanı bağlantı ayarı grubu
	 */
	public function __construct( $configGroup = NULL )
	{
		parent::__construct( $configGroup );
	}

	/**
	 * ID'si ve türü verilen nesnenin özetini bulur, yoksa
	 * oluşturur ve ilgili değişkene yazar.
	 * 
	 * @param String|Integer $itemID ID information of item
	 * @param String $itemType item's table name
	 * @return $this
	 */
	public function get( $itemID, $itemType )
	{
		$name = "summary-$itemID-$itemType";

		if( $old = Reactable::singleton( $name ))

			return $old;

		$this->itemID = $itemID;
		$this->itemType = $itemType;

		$itemID = $this->db->connection->quote( $itemID );
		$itemType = $itemType === NULL
			? 'IS NULL'
			: ' = ' . $this->db->connection->quote( $itemType );

		$this->summaries = $this->db->query( "SELECT * FROM reaction_summaries WHERE item_id = $itemID AND item_type $itemType" );

//var_dump($this->summaries);
//var_dump("SELECT * FROM reaction_summaries WHERE item_id = $itemID AND item_type $itemType");
//exit();

		if( $this->summaries )
		{
			$this->summaries = $this->summaries[ 0 ];
			$this->summaries = unserialize( $this->summaries->reactions );
			$this->isExistsOnDB = TRUE;
		}
		else
		{
			$tmp = [];
			$this->isExistsOnDB = FALSE;

			foreach( Config::main( 'supported reactions' ) AS $reaction )

				$tmp[ $reaction ] = 0;

			$this->summaries = $tmp;
		}

		return Reactable::singleton( $name, $this );
	}

	/**
	 * ID => type notasyonuyla verilen öğelerin özetlerini döndürür.
	 * 
	 * @param Array $items özeti istenen öğeler
	 * @return Array
	 */
	public function getBatch( Array $items )
	{
		foreach( $items AS $itemID => $itemType )
		{
			$WHERE = [];

			$WHERE[] = 'item_id = ' . $this->db->connection->quote( $itemID );

			if( $itemType != 'null' )

				$WHERE[] = 'item_type = ' . $this->db->connection->quote( $itemType );

			$WHERES[] = count( $WHERE ) == 1
				? implode( ' AND ', $WHERE )
				: '( ' . implode( ' AND ', $WHERE ) . ' )';
		}

		$SQL = implode( ' OR ', $WHERES );
		$SQL = "SELECT * FROM reaction_summaries WHERE $SQL";

		return $this->db->query( $SQL );
	}

	/**
	 * Adı verilen reaksiyon sayısını arttırır.
	 * 
	 * @param String $reaction sayısı arttırılacak reaksiyonun native adı
	 * @return $this
	 */
	public function increase( $reaction )
	{
		$this->summaries[ $reaction ]++;
		return $this;
	}
	
	/**
	 * Adı verilen reaksiyon sayısını azaltır.
	 * 
	 * @param String $reaction sayısı azaltılacak reaksiyonun native adı
	 * @return $this
	 */
	public function decrease( $reaction )
	{
		$this->summaries[ $reaction ]--;
		return $this;
	}

	/**
	 * Toplam reaksiyon sayısını döndürür.
	 * @return Integer
	 */
	public function getTotal()
	{
		$sum = 0;

		foreach( $this->summaries AS $key => $val )

			$sum += $val;

		return $sum;
	}

	/**
	 * Temsil edilen özeti veritabanına kaydeder.
	 */
	public function save()
	{
		$itemID = $this->db->connection->quote( $this->itemID );
		$reactions = $this->db->connection->quote( serialize( $this->summaries ));

		// ilk defa ekleme modu
		if( $this->isExistsOnDB )
		{
			// hiçbir reaksiyon kalmazsa veritabanından sileceğiz
			if( $this->getTotal() <= 0 )
			{
				$this->remove();
				return;
			}

			$itemType = $this->itemType === NULL
				? 'IS NULL'
				: ' = ' . $this->db->connection->quote( $this->itemType );

			$SQL = "UPDATE reaction_summaries SET reactions = $reactions WHERE item_id = $itemID AND item_type $itemType";
		}
		else
		{
			$itemType = $this->itemType === NULL
				? ' = NULL'
				: ' = ' . $this->db->connection->quote( $this->itemType );

			$SQL = "INSERT INTO reaction_summaries SET item_type $itemType, item_id = $itemID, reactions = $reactions";
		}

		$this->db->query( $SQL );
	}

	/**
	 * Reaksiyon özetini siler.
	 */
	public function remove()
	{
		$itemID = $this->db->connection->quote( $this->itemID );
		$itemType = $this->itemType === NULL
			? 'IS NULL'
			: ' = ' . $this->db->connection->quote( $this->itemType );

		$this->db->query( "DELETE FROM reaction_summaries WHERE item_id = $itemID AND item_type $itemType" );
	}
}
