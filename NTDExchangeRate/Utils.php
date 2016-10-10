<?php

class Utils {

  /**
   * fetch CSV file by url
   * @param  String $url
   * @return Array with csv and updateTime
   */
  public function fetchCSV($url)
  {
    $header = [
      'Accept-language: en',
      'Host: rate.bot.com.tw'
    ];

    $opts = array(
      'http' => array(
        'method' => "GET",
        'header' => implode("\r\n", $header)
      )
    );

    $context = stream_context_create($opts);
    $csv = file_get_contents($url, false, $context);
    $updateTime = self::parseHeaderUpdateTime($http_response_header);

    return [
      'raw' => $csv,
      'updateTime' => $updateTime
    ];
  }

  /**
   * Parse header to get update time
   * @param  Array $headers HTTP Header
   * @return String         Datetime
   */
  private static function parseHeaderUpdateTime($headers)
  {
    $matches = [];
    foreach($headers as $header) {
      if( strpos($header, 'attachment; filename') === false ) continue;
      preg_match('/ExchangeRate\@(\d*).csv/', $header, $matches);
    }

    if (isset($matches[1])) {
      $d = DateTime::createFromFormat('YmdHi', $matches[1]);
      return $d->format('Y-m-d H:i');
    }
  }

  /**
   * Up and down Emoji
   * 
   * HTML Entity (hex)
   */
  private static $emo = array(
    'up' => '&#x1f53c;',
    'down' => '&#x1f53d;'
  );

  private static function emoji($symbol)
  {
    return html_entity_decode(self::$emo[$symbol], ENT_NOQUOTES, 'UTF-8');
  }

  public static function EMOJI_UP()
  {
    return self::emoji('up');
  }

  public static function EMOJI_DOWN()
  {
    return self::emoji('down');
  }

  /**
   * Format price、date
   */
  public static function price($price)
  {
    return (float)$price == 0 ? '-' :  (float)$price;
  }

  public static function formateDate($date)
  {
    $d = DateTime::createFromFormat('Ymd', $date);
    return $d->format('Y-m-d');
  }

  /**
   * Check currency parameter is avaiable
   * @param  String  $currency 
   * @return boolean          
   */
  public function isCurrencyAvaiable($currency)
  {
    return strlen($currency) == 3 && array_key_exists(strtoupper($currency), self::$Currency);
  }

  /**
   * Get currency name
   * @param  String $key currency short name ex: USD
   * @return String
   */
  public function currencyName($key)
  {
    return self::$Currency[$key]['name'];
  }

  /**
   * Get currency flag image name
   * @param  String $key currency short name ex: USD
   * @return String
   */
  public function currencyFlag($key)
  {
    return self::$Currency[$key]['flag'];
  }

  public static $Currency = array(
    'AUD' => array(
      'name' => '澳幣',
      'flag' => 'Australia.png'
    ),
    'CAD' => array(
      'name' => '加拿大幣',
      'flag' => 'Canada.png'
    ),
    'CHF' => array(
      'name' => '瑞士法郎',
      'flag' => 'Switzerland.png'
    ),
    'CNY' => array(
      'name' => '人民幣',
      'flag' => 'China.png'
    ),
    'EUR' => array(
      'name' => '歐元',
      'flag' => 'European-Union.png'
    ),
    'GBP' => array(
      'name' => '英鎊',
      'flag' => 'United-Kingdom(Great-Britain).png'
    ),
    'HKD' => array(
      'name' => '港幣',
      'flag' => 'Hong-Kong.png'
    ),
    'IDR' => array(
      'name' => '印尼幣',
      'flag' => 'Indonezia.png'
    ),
    'JPY' => array(
      'name' => '日圓',
      'flag' => 'Japan.png'
    ),
    'KRW' => array(
      'name' => '韓元',
      'flag' => 'South-Korea.png'
    ),
    'MYR' => array(
      'name' => ' 馬來幣',
      'flag' => 'Malaysia.png'
    ),
    'NZD' => array(
      'name' => '紐元',
      'flag' => 'New-Zealand.png'
    ),
    'PHP' => array(
      'name' => '菲國比索',
      'flag' => 'Philippines.png'
    ),
    'SEK' => array(
      'name' => '瑞典幣',
      'flag' => 'Sweden.png'
    ),
    'SGD' => array(
      'name' => '新加坡幣',
      'flag' => 'Singapore.png'
    ),
    'THB' => array(
      'name' => '泰銖',
      'flag' => 'Thailand.png'
    ),
    'USD' => array(
      'name' => '美金',
      'flag' => 'United-States-of-America(USA).png'
    ),
    'VND' => array(
      'name' => '越南盾',
      'flag' => 'Viet-Nam.png'
    ),
    'ZAR' => array(
      'name' => '南非幣',
      'flag' => 'South-Africa.png'
    ),
    'NTD' => array(
      'name' => '新台幣',
      'flag' => 'Taiwan.png'
    )
  );
}