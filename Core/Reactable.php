<?php

namespace Reactable\Core;
	  use Exception;
	  use Reactable\Core\Support\Eventise;
	  use Reactable\Model\Reactions AS ReactionModel;
	  use Reactable\Exceptions\AuthException;
	  use Reactable\Exceptions\BadRequestException;

/**
 * -------------------------------------------------------------------------
 * Reactable
 * -------------------------------------------------------------------------
 * Ana Reactable sınıfıdır. Genel işlemleri gerçekleştirir.
 * 
 */
final class Reactable
{
	use Eventise;

	/**
	 * Tek örneği istenen şeyleri tutacak olan havuz.
	 * @var Array
	 */
	public static $singletons = [];

	/**
	 * Mevcut reaksiyon durumu.
	 * @var Array
	 */
	public $feelings;

	/**
	 * Arayüz ayarları.
	 * @var Array
	 */
	public $layoutOptions;

	/**
	 * Hedef nesne (post, comment etc) ayarları.
	 * @var Array
	 */
	public $subjectOptions;

	/**
	 * Bağlantı ayarları.
	 * @var Array
	 */
	public $dbOptions;

	/**
	 * Reaksiyon işlemlerinden sorumlu model.
	 * @var System\Model\Reactions
	 */
	public $reactionModel;

	/**
	 * İstek dinleme modundayken temsil edilen öğeye
	 * istemci tarafından önceden bir reaksiyon
	 * verilmişse bu reaksiyon bilgilerini tutar.
	 * 
	 * @var stdClass
	 */
	public $oldReaction;

	/**
	 * Kurulumu yapar.
	 */
	public static function init()
	{
		// ana config dosyası yüklensin
		Config::load( 'main' );
	}

	/**
	 * Kurulumu yapar.
	 * 
	 * @param Array $layoutOptions arayüz ayarları
	 * @param Array $subjectOptions hedef nesne (post, comment etc) ayarları
	 * @param Array $dbOptions bağlantı ayarları
	 */
	public function __construct( $layoutOptions = [], $subjectOptions = [], $dbOptions = [])
	{
		$this->layoutOptions = $layoutOptions;
		$this->subjectOptions = $subjectOptions;
		$this->dbOptions = $dbOptions;

		// reaksiyon modelini örnekleyelim
		$this->reactionModel = new ReactionModel( $dbOptions[ 'connection' ]);
	}

	/**
	 * Reactable olarak örneklenen öğenin kullanıcı arayüzü olarak
	 * görüntülenebilmesi için gerekli olan HTML iskeleti döndürür.
	 * 
	 * @return String
	 */
	public function createHTML()
	{
		$this->layoutOptions[ 'subject_id' ] = $this->subjectOptions[ 'id' ];
		$this->layoutOptions[ 'subject_type' ] = $this->subjectOptions[ 'type' ];

		// reactable öğenin eski reaksiyon bilgileri istenmişse bunu sağlayalım
		if( isset( $this->dbOptions[ 'feed' ]) && $this->dbOptions[ 'feed' ] === TRUE )

			$this->feelings = 
			$this->layoutOptions[ 'feelings' ] = $this->reactionModel->summaries->get
			(
				$this->subjectOptions[ 'id' ],
				$this->subjectOptions[ 'type' ]
			)->summaries;

		$skeleton = View::load
		(
			$this->layoutOptions[ 'layout' ] ?? 'standard',
			$this->layoutOptions
		);

		return $skeleton->render();
	}

	/**
	 * Değişken saklaması yapar, sadece tek örneği olması
	 * istenen nesneleri saklamak için kullanışlıdır.
	 * 
	 * @param String $name değişken adı
	 * @param Mixed $value değer
	 * @return FALSE|Mixed
	 */
	public static function singleton( $name, $value = NULL )
	{
		if( $value === NULL )
		{
			if( array_key_exists( $name, static::$singletons ))

				return static::$singletons[ $name ];

			return FALSE;
		}

		return static::$singletons[ $name ] = $value;
	}

	/**
	 * createHTML metodunun kısayoludur.
	 */
	public function __toString()
	{
		return $this->createHTML();
	}

	/**
	 * Outputs the reaction summary of the item.
	 */
	private function responseJSON()
	{
		$response[ 'feelings' ] = $this->reactionModel->summaries->get
		(
			$this->subjectOptions[ 'id' ],
			$this->subjectOptions[ 'type' ]
		)->summaries;

		header( 'Content-Type: text/json' );
		echo json_encode(( Object ) $response );
	}

	/**
	 * Öğeyi server moduna geçirip istekleri dinler kullanıcı
	 * ilk defa reaksiyon veriyorsa bunu ekler, daha önceden
	 * reaksiyon vermişse ve eskisiyle aynıysa bunu siler, değilse
	 * ekler. Her durumda öğenin mevcut reaksiyon bilgilerini
	 * json olarak çıktılar.
	 */
	public function listen()
	{
		// form bilgilerini doğrulayalım
		$this->validate();

		// have already been reacted?
		if( $this->isReacted())
		{
			// reacted, are they same?
			if( $this->isReactionsSame())
			{
				// same as before, let's remove
				$this->removeReaction();
			}
			// they aren't same?
			else
			{
				$this->updateReaction();
			}
		}
		// not reacted before
		else
		{
			$this->saveReaction();
		}

		// is it an ajax request?
		if( array_key_exists( 'HTTP_X_REQUESTED_WITH', $_SERVER ))

			// send JSON summary
			$this->responseJSON();

		// regular request (no js browser etc.)
		else
		{
			header( 'Location: ' . $_SERVER[ 'HTTP_REFERER' ]);
		}
	}

	/**
	 * Its validate form variables.
	 *
	 * @throws Reactable\Exceptions\BadRequestException throws if the variable named "id" does not come
	 * @throws Reactable\Exceptions\BadRequestException throws if the variable named "reaction" does not come or contain not supported emotion name
	 * @throws Reactable\Exceptions\AuthException auth-check olayı için yazılan olay dinleyici FALSE döndüğünde fırlatılır
	 */
	private function validate()
	{
		if
		(
			! isset( $this->subjectOptions[ 'id' ]) ||
			( $subjectID = $this->subjectOptions[ 'id' ]) === FALSE ||
			empty( $subjectID )
		)

			throw new BadRequestException( "'id' is required for \$subjectOptions." );

		if( ! isset( $_REQUEST[ 'reaction' ]))

			throw new BadRequestException( "The parameter 'reaction' is required." );

		if( ! in_array( $reaction = $_REQUEST[ 'reaction' ], Config::main( 'supported reactions' )))

			throw new BadRequestException( "Unsupported reaction name: $reaction" );

		if( ! $this->trigger( 'auth-check' ))

			throw new AuthException( "Yetkisiz erişim." );			
	}

	/**
	 * It tells whether the user has previously
	 * been reacted to the item.
	 * 
	 * @return Boolean
	 */
	private function isReacted()
	{
		$reaction = $this->oldReaction = $this->reactionModel->get
		(
			$this->subjectOptions[ 'id' ],
			$this->subjectOptions[ 'type' ],
			$this->subjectOptions[ 'user_id' ]
		);

		return $reaction
			? TRUE
			: FALSE;
	}

	/**
	 * It tells whether the current reaction is the
	 * same as the user's previous reaction.
	 * 
	 * @return Boolean
	 */
	private function isReactionsSame()
	{
		return $_REQUEST[ 'reaction' ] == $this->oldReaction->emotion;
	}

	/**
	 * The old reaction is updated with the new reaction.
	 */
	private function updateReaction()
	{
		$this->reactionModel->update
		(
			$this->oldReaction->id,
			$this->oldReaction->item_id,
			$this->oldReaction->item_type,
			$this->oldReaction->emotion,
			$_REQUEST[ 'reaction' ]
		);
	}

	/**
	 * Clears the user's reaction.
	 */
	private function removeReaction()
	{
		$this->reactionModel->remove
		(
			$this->oldReaction->id,
			$this->oldReaction->item_id,
			$this->oldReaction->item_type,
			$this->oldReaction->emotion
		);
	}

	/**
	 * It saves the current reaction.
	 */
	private function saveReaction()
	{
		$this->reactionModel->react
		(
			$this->subjectOptions[ 'id' ],
			$this->subjectOptions[ 'type' ],
			$this->subjectOptions[ 'user_id' ],
			$_REQUEST[ 'reaction' ]
		);
	}

}
