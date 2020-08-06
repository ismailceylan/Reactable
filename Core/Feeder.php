<?php

namespace Reactable\Core;
	  use Reactable\Model\Reactions AS ReactionModel;

/**
 * -------------------------------------------------------------------------
 * Feeder
 * -------------------------------------------------------------------------
 * Toplu reactable nesne sorgulama işlemlerine yanıt verebilmek için gereken
 * işlevselliği sağlar.
 * 
 */
final class Feeder
{
	/**
	 * Öğe IDleri.
	 * @var Array
	 */
	public $items = [];

	/**
	 * Kullanıcı IDsi.
	 * @var Mixed
	 */
	public $userID;

	/**
	 * Reaksiyonların veri modeli.
	 * @var Reactable\Model\Reactions
	 */
	private $reactionModel;

	/**
	 * Kurulumu yapar.
	 * 
	 * @param String $types virgülle aurılmış öğe tür isimleri
	 * @param String $items öğe IDleri
	 */
	public function __construct( $types, $items, $userID, $connGroupName = 'default' )
	{
		$this->reactionModel = new ReactionModel( $connGroupName );
		$this->userID = $userID;

		$this->normalizeItems( $items, $types );
	}

	/**
	 * Öğeleri ve tür haritasını birleştirir.
	 * 
	 * @param String $items öğeID:tip index notasyonlu öğe dizisi
	 * @param String $types virgülle ayrılmış öğe türü listesi
	 */
	public function normalizeItems( $items, $types )
	{
		$types = explode( ',', $types );
		$items = explode( ',', $items );

		foreach( $items AS $item )
		{
			$item = explode( ':', $item );
			$tmp[ $item[ 0 ]] = $types[ $item[ 1 ]];
		}

		$this->items = $tmp;
	}

	/**
	 * Yanıt oluşturur.
	 */
	public function response()
	{
		// önce öğelerin reaksiyon özetlerini alalım
		$summaries = $this->reactionModel->summaries->getBatch( $this->items );

		// kullanıcının öğeler için reaksiyonlarını bulalım
		$userChoices = $this->userID
			? $this->reactionModel->userReactionBatch( $this->userID, $this->items )
			: [];

		$feelings = $this->fullfill( $this->items );

		foreach( $summaries AS $summary )
		{
			$item[ 'feelings' ] = unserialize( $summary->reactions );

			foreach( $userChoices AS $choice )
			{
				if( $choice->item_id == $summary->item_id && $choice->item_type == $summary->item_type )
				{				
					$item[ 'choice' ] = $choice->emotion;

					$feelings
					[
						"$choice->item_id|" .
						( $choice->item_type ?? 'null' )
					] = $item;
				}
			}

			if( is_array( $item ))

				$feelings[ "$summary->item_id|$summary->item_type" ] = $item;
		}

		header( 'Content-Type: text/json' );
		echo json_encode(( Object )[ 'feelings' => $feelings ]);
	}

	/**
	 * İstenen öğeler ve boş reaksiyonların bulunduğu standart bir sonuç
	 * kümesi oluşturup döndürür. Bu küme herhangi bir reaksiyon bulunmadığı
	 * durumlarda istemciye yollanmaya müsait bir kümedir.
	 * 
	 * @param Array $items durumu sorgulanan normalize edilmiş öğeler
	 * @return Array
	 */
	private function fullfill( $items )
	{
		foreach( Config::main( "supported reactions" ) AS $reaction )

			$reactions[ $reaction ] = 0;

		foreach( $items AS $ID => $type )
		
			$r[ "$ID|$type" ] = $reactions;
		
		return $r;
	}
}
