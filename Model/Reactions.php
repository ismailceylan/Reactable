<?php

namespace Reactable\Model;
	  use Reactable\Core\Model;

/**
 * -------------------------------------------------------------------------
 * Reactions
 * -------------------------------------------------------------------------
 * Reaksiyonların veri tarafını temsil eder.
 * 
 */
class Reactions extends Model
{
	/**
	 * Reaksiyon özetlerinden sorumlu veri modeli.
	 * @var Reactable\Model\ReactionSummaries
	 */
	public $summaries;

	/**
	 * Model kurulumunu yapar.
	 * @param String $configGroup config dosyasındaki veritabanı bağlantı ayarı grubu
	 */
	public function __construct( $configGroup = NULL )
	{
		parent::__construct( $configGroup );

		// özetleri yöneten modeli örnekleyelim
		$this->summaries = new ReactionSummaries( $configGroup );
	}

	/**
	 * Returns a boolean if user left a reaction for the
	 * given item, otherwise it returns an empty array.
	 *
	 * @param String|Integer $itemID ID information of item
	 * @param String $itemType item's table name
	 * @param String|Integer $userID user's ID information
	 * @return stdClass|[]
	 */
	public function get( $itemID, $itemType, $userID )
	{
		return $this->db->prepare( 'SELECT * FROM reactions WHERE user_id = ? AND item_id = ? LIMIT 1' )
						->execute([ $userID, $itemID ])
						->row();
	}

	/**
	 * Updates the native name of the reaction with the
	 * given ID number.
	 * 
	 * @param String|Integer $itemID ID information of item
	 * @param Integer $id ID information of reaction
	 * @param String $itemType item's table name
	 * @param String $oldReaction old reaction's name
	 * @param String $reaction native reaction name
	 */
	public function update( $id, $itemID, $itemType, $oldReaction, $reaction )
	{
		$this->summaries->get( $itemID, $itemType )
						->decrease( $oldReaction )
						->increase( $reaction )
						->save();

		$this->db->prepare( 'UPDATE reactions SET emotion_before = emotion, emotion = ?, updated_at = ? WHERE id = ?' )
				 ->execute([ $reaction, date( 'Y-m-d h:i:s' ), $id ]);
	}

	/**
	 * Deletes the reaction with the given ID information.
	 * 
	 * @param String|Integer $itemID ID information of item
	 * @param Integer $id ID information of reaction
	 * @param String $itemType item's table name
	 * @param String $reaction native reaction name
	 */
	public function remove( $id, $itemID, $itemType, $reaction )
	{
		$this->db->prepare( 'DELETE FROM reactions WHERE id = ?' )
				 ->execute([ $id ]);

		$this->summaries->get( $itemID, $itemType )
						->decrease( $reaction )
						->save();
	}

	/**
	 * It saves a new reaction.
	 * 
	 * @param String|Integer $itemID ID information of item
	 * @param String $itemType item's table name
	 * @param String|Integer $userID user's ID information
	 * @param String $reaction reaction native name [like,love,haha,...]
	 */
	public function react( $itemID, $itemType, $userID, $reaction )
	{
		$this->db->prepare( 'INSERT INTO reactions SET item_id = ?, item_type = ?, user_id = ?, emotion = ?, useragent = ?, ip = ?' )
				 ->execute([ $itemID, $itemType, $userID, $reaction, $_SERVER[ 'HTTP_USER_AGENT' ], $_SERVER[ 'REMOTE_ADDR' ]]);

		$this->summaries->get( $itemID, $itemType )
						->increase( $reaction )
						->save();
	}

	/**
	 * Bir kullanıcının verilen öğeler
	 * için verdiği reaksiyonları döndürür.
	 * 
	 * @param Mixed $userID kullanıcı ID bilgisi
	 * @param Array $items öğeID => tür notasyonuyla öğe listesi
	 * @return Array
	 */
	public function userReactionBatch( $userID, Array $items )
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
		$userID = $this->db->connection->quote( $userID );
		$items = implode( "','", array_keys( $items ));

		$SQL = "SELECT * FROM reactions WHERE user_id = $userID AND ( $SQL )";

		return $this->db->query( $SQL );
	}
}
