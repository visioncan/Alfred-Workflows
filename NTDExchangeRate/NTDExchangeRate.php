<?php

/**
* get NTD Exchange Rate form Bank Of Taiwan
* 
* Author: visioncan@gmail.com
* web: http://blog.visioncan.com/
* 
* Flag icons made by www.IconDrawer.com
* Workflows Utility Class credit by David Ferguson (@jdfwarrior)
* 
*/
require_once('workflows.php');

class NTDExchangeRate
{
	const BOT_HOST   = 'http://rate.bot.com.tw';
	private $rateUrl = '/Pages/Static/UIP003.zh-TW.htm';
	private $csvUrl;
	private $csvDate;
	private $csvOutput;
	private $exRateData;
	private $workflows;
	private $Currency = array(
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


	public function __construct()
	{
		$this->workflows = new Workflows();
		$this->getBotWebPage();
	}

	private function getBotWebPage()
	{
		$botHTML = $this->curlGet(self::BOT_HOST . $this->rateUrl);

		$resint1 = preg_match('/id ?= ?["|\']DownloadCsv["|\'] ?.*>/', $botHTML, $match1);
		if ($resint1 !== 0)
		{
			$resint2 = preg_match('/\.href ?= ?\'(.+)\'/', $match1[0], $match2);
			if ($resint1 !== 0)
			{
				$this->csvUrl = $match2[1];
			}
			else
			{
				$this->printError('NO_MATCH_HREF');
			}
		}
		else
		{
			$this->printError('NO_MATCH_ELEMENT');
		}
		
		$botHTML = null;
		preg_match('/date=(.*):/', $this->csvUrl, $match_date);
		$this->csvDate = preg_replace('/T/', ' ', $match_date[1]);
		$this->getCSVAndConvert();
	}
	
	private function getCSVAndConvert()
	{
		$this->csvOutput = $this->curlGet(self::BOT_HOST . $this->csvUrl);
		if ($this->csvOutput != '很抱歉，本次查詢找不到任何一筆資料！')
		{
			$this->exRateData = $this->convertCsv();
		}
		else
		{
			$this->printError('NO_RESULT', $this->csvOutput);
		}
	}

	/**
	 * read web data
	 * @param  string $url
	 * @return string $output
	 */
	private function curlGet($url)
	{
		$ch = curl_init();
		$options = array(
			CURLOPT_URL => htmlspecialchars_decode($url),
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => "Google Bot",
			CURLOPT_FOLLOWLOCATION => true
		);
		curl_setopt_array($ch, $options);
		$output = curl_exec($ch);
		$error  = curl_error($ch);
		curl_close($ch);
		if ($error)
		{
			$this->printError('CURL_ERROR', $error);
		}
		else
		{
			return $output;
		}
	}

	/**
	 * convert Exchange Rate Csv to array
	 * @return array
	 */
	private function convertCsv()
	{
		$result;
		$wholeCSV = str_getcsv($this->csvOutput, "\n");
		for ($i = 1; $i < sizeof($wholeCSV); $i++) { 
			$row = explode(",", preg_replace('/\s+/', '', $wholeCSV[$i]));
			$result[array_shift($row)] = array(
				'Buying'  => array_slice($row, 1, 9),
				'Selling' => array_slice($row, 11, 9)
			);
		}
		return $result;
	}

	/**
	 * Print Error
	 * @param  String $err  Error String type
	 * @param  String $info deisplay error information , default is null
	 */
	private function printError($err, $info = null)
	{
		switch ($err) {
			case 'CURL_ERROR':
				print_r('Curl Error: empty');
				break;
			case 'NO_MATCH_HREF':
				print_r('Match Error: not match download href');
				break;
			case 'NO_MATCH_ELEMENT':
				print_r('Match Error: not match element');
				break;
			case 'NO_RESULT':
				# code...
				break;
		}
		exit;
	}

	public function pAllExRate()
	{
		$results = array();
		$items = array();
		foreach ($this->exRateData as $key => $val) {
			$items[] = array(
				'uid'      => $key,
				'arg'      => $key,
				'title'    =>  '⬆🔼⏬'. $val['Selling'][0],
				'subtitle' => $this->Currency[$key]['name'] . ' 前10天：' . $val['Selling'][2],
				'icon'     => 'flags/' . $this->Currency[$key]['flag']
			);
		}
		//array_push( $results, $items );
		//print_r($items);
		echo $this->workflows->toxml( $items );
	}
}


$rate = new NTDExchangeRate();
$rate ->pAllExRate();
?>