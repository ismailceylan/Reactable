<?php

use Reactable\Core\Feeder;

require "bootstrap.php";

/**
 * ----------------------------------------------------------------------------
 * TYPES
 * ----------------------------------------------------------------------------
 * Eklenti çok biçimliliği desteklediği için sizden reaksiyon verilebilir her
 * web öğesi için bir ID bilgisi ile bu öğenin türünü ister. Böylece türler
 * arası ID çakışması önlenebilir. Eklentinin javascript tarafında bu bilgileri
 * her ID bilgisinin karşısına türünü yazarak göndermeyiz. Bu http iletişim
 * gövdesini şişirecektir. Bunun yerine türleri virgülle birbirinden ayırdığımız
 * bir tür haritası değişkenine yazarız. Öğe IDlerini de başka bir değişkende
 * dizi olarak tanımlar ve karşısına türünü yazmak yerine tür haritasındaki
 * kendine ait olan türün sıra numarasını yazarız. Böylece veriyi mümkün
 * olduğunca sıkıştırıp yollamış oluruz.
 * 
 */
$types = $_GET[ 'types' ];

/**
 * ----------------------------------------------------------------------------
 * ITEMS
 * ----------------------------------------------------------------------------
 * Burada, sorgulanacak web nesnelerinin IDlerini içeren virgülle ayrılmış
 * ifadeyi oluşturur.
 * 
 */
$items = $_GET[ 'items' ];

/**
 * ----------------------------------------------------------------------------
 * USER ID
 * ----------------------------------------------------------------------------
 * Burada, sorgulamayı yapan kullanıcının ID numarasını eklentiye sağlamalıyız.
 * Bu bilgi eklentiye sağlanırsa sorgu sonucuna o kullanıcının o web öğesi
 * için önceden verdiği bir reaksiyon varsa bunu ekler. ID sağlanmazsa veya
 * kullanıcının bir reaksiyonu yoksa bu bilgi null olacak şekilde yine sonuca
 * yerleştirilir. Böylece örneğin kullanıcı sitenize ait sayfaları 2 veya daha
 * fazla sekmede kullanıyorsa ve sekmelerin birinde işlem yapmışsa yaptığı
 * işlemin diğer sekmelerde de hemen görünebilmesi sağlanmış olur.
 * 
 */
$userID = ! empty( $_COOKIE[ 'guestID' ])? $_COOKIE[ 'guestID' ] : NULL;

/**
 * ----------------------------------------------------------------------------
 * CONNECTION GROUP
 * ----------------------------------------------------------------------------
 * Burada sorgulama işlemi için config dosyasında tanımlı olan veritabanı
 * bağlantı ayarları gruplarından hangisinin kullanılacağını belirleriz.
 * 
 */
$connName = 'default';

// besleyici sınıfı örnekleyebiliriz
$feeder = new Feeder( $types, $items, $userID, $connName );

// istemciye bir yanıt dönelim
$feeder->response();
