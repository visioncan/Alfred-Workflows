<?php

/**
* Fetch NTD Foreign Exchange Rate from Bank Of Taiwan
* 
* Author: visioncan@gmail.com
* web: http://blog.visioncan.com/
* Version 1.2.0
*
* changeLog: 
* v1.1.1 : fix download link miss
* v1.2.0 : Download csv directly
* 
* Flag icons made by www.IconDrawer.com
* Workflows Library by David Ferguson (@jdfwarrior)
* 
*/
require_once('libs/Workflows/workflows.php');
require_once('libs/nokogiri/nokogiri.php');
require_once('Utils.php');

class NTDExchangeRate
{
  const BOT_HOST             = 'http://rate.bot.com.tw';
  const CSV_URL              = '/xrt/flcsv/0/day';
  const LAST_3_MONTH_CSV_URL = '/xrt/flcsv/0/L3M/%s';
  const HISTORY_LINK         = '/xrt/history/%s';
  const LIST_HISTORY_LIMIT   = 10;

  private $exchangeData;
  private $workflows;
  private $currentCurency = null;
  private $updateTime = '';
  private $outputItems = array(); // for Alfred

  public function __construct($currency = null)
  {
    date_default_timezone_set('Asia/Taipei');
    mb_internal_encoding('UTF-8');

    $this->workflows = new Workflows();
    if ($currency == null)
    {
      $this->getAllExchange();
    }
    else if(Utils::isCurrencyAvaiable($currency))
    {
      $this->currentCurency = strtoupper($currency);
      $this->getExchangeBy($this->currentCurency);
    }
  }

  private function getAllExchange()
  {    
    $csv = Utils::fetchCSV(self::BOT_HOST.self::CSV_URL);
    
    if (mb_substr($csv['raw'], 1, 5) !== '幣別,匯率') {
      $this->printError('NO_RESULT', $csvOutput);
      return;
    }

    $this->updateTime = $csv['updateTime'];
    $this->exchangeData = $this->convertCsv($csv['raw']);
    $this->generateExchange();
  }

  private function getExchangeBy($currency)
  { 
    $csv = Utils::fetchCSV(sprintf(self::BOT_HOST.self::LAST_3_MONTH_CSV_URL, $currency));
    
    if (mb_substr($csv['raw'], 1, 7) !== '資料日期,幣別') {
      $this->printError('NO_RESULT', $csvOutput);
      return;
    }

    $this->exchangeData = $this->convertCsv($csv['raw']);
    $this->generateExchange();
  }

  /**
   * convert Exchange Rate Csv to array
   * @return array
   */
  private function convertCsv($csv)
  {
    $result;
    $wholeCSV = str_getcsv($csv, "\n");

    for ($i = 1; $i < sizeof($wholeCSV); $i++) { 
      $row = explode(",", preg_replace('/\s+/', '', $wholeCSV[$i]));
      if ($this->currentCurency == null) {
        $result[array_shift($row)] = array(
          'Buying'  => array_slice($row, 1, 9),
          'Selling' => array_slice($row, 11, 9)
        );
      }else{
        $result[] = array(
          'date'    => array_shift($row),
          'Buying'  => array_slice($row, 2, 9),
          'Selling' => array_slice($row, 12, 9)
        );
      }
    }
    return $result;
  }

  /**
   * Print Error
   * @param  String $err  Error String type
   * @param  String $info deisplay error information , default is empty
   */
  private function printError($err, $info = '')
  {
    $displayErr = '';
    switch ($err) {
      case 'CURL_ERROR':
        $displayErr = 'Fetch Error';
        break;
      case 'NO_RESULT':
        $displayErr = 'Fetch data is Empty';
        break;
    }
    $this->generateError($displayErr . ' ' . $info);
    $this->pxml();
    exit;
  }

  /**
   * compare rate history with before the day
   * @param  Number $key index
   * @return Emoji String
   */
  private function compareHistory($key)
  {
    if ($key < count($this->exchangeData) - 1) {
      $old = (float)$this->exchangeData[$key]['Selling'][0];
      $new = (float)$this->exchangeData[$key + 1]['Selling'][0];
      return $old > $new ? Utils::EMOJI_UP() : Utils::EMOJI_DOWN();
    }
  }

  /**
   * generate Exchange Rate to $this->outputItems for Alfred output
   */
  private function generateExchange(){
    $ind = 0;
    if ($this->currentCurency == null)
    {
      foreach ($this->exchangeData as $key => $val)
      {
        $sellingPrice = Utils::price($val['Selling'][0]);
        $buyingPrice = Utils::price($val['Buying'][0]);
        $this->outputItems[] = array(
          'uid'      => $ind ++,
          'arg'      => $sellingPrice,
          'title'    => $sellingPrice,
          'subtitle' => sprintf('%s %s | 現金賣出：%s 現金買入：%s | 掛牌時間：%s',
            Utils::currencyName($key),
            $key,
            $sellingPrice,
            $buyingPrice,
            $this->updateTime),
          'icon'     => 'flags/'.Utils::currencyFlag($key)
        );
      }
    }
    else
    {
      foreach ($this->exchangeData as $key => $val)
      {
        $linkURL = sprintf(self::BOT_HOST.self::HISTORY_LINK, $this->currentCurency);
        $this->outputItems[] = array(
          'uid'      => $ind ++,
          'arg'      => $linkURL,
          'title'    => $this->compareHistory($key).' '.Utils::price($val['Selling'][0]),
          'subtitle' => Utils::formateDate($val['date']),
          'icon'     => 'flags/' . Utils::currencyFlag($this->currentCurency)
        );

        if ($ind == self::LIST_HISTORY_LIMIT) break;
      }
    }
  }

  private function generateError($err){
    $this->outputItems[] = array(
      'uid'      => '0',
      'arg'      => $err,
      'title'    => $err,
      'subtitle' => '',
      'icon'     => 'icon.png'
    );
  }

  /**
   * create xml for Alfred
   */
  public function pxml()
  {
    echo $this->workflows->toxml( $this->outputItems );
  }
}
?>