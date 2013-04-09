<?
class SkypeEmoticons {
	
	private $skypeAppSrc = '/Applications/Skype.app';
	private $emoticons = Array('mooning', 'fubar', 'phone', 'finger', 'poolparty', 'brokenheart', 'drunk', 'swear', 'movie', 'smoking', 'wtf', 'time', 'toivo', 'zilmer','mail','rock','punch','skype','headbang','call','wfh','bug','talk','hollest','oliver','bartlett', 'waiting');
	private $dom;
	private $xmlStr;

	function __construct(){
		$this->xml = new DOMDocument('1.0');
		$this->xml->encoding = 'UTF-8';

		if ( !file_exists( $this->skypeAppSrc ) ){
			echo "no Skype";
			return false;
		}else{
			$this->creatXml();
		}
	}

	private function creatXml(){
		$root = $this->xml->createElement('items');
		$this->xml->appendChild($root);

		foreach ($this->emoticons as $key => $val) {
			$item = $this->xml->createElement('item');
			$item->setAttribute('uid', 'emo');
			$item->setAttribute('arg', "($val)");
			$root->appendChild($item);

			$title = $this->xml->createElement('title');
			$title->appendChild($this->xml->createTextNode($val));
			$item->appendChild($title);

			$subtitle = $this->xml->createElement('subtitle');
			$subtitle->appendChild($this->xml->createTextNode($val));
			$item->appendChild($subtitle);

			$icon = $this->xml->createElement('icon');
			if ($val == 'zilmer') {
				$imgname = 'priidu';
			}else if ($val == 'talk') {
				$imgname = 'talking';
			}else{
				$imgname = $val;
			}
			$icon->appendChild($this->xml->createTextNode($this->skypeAppSrc . '/Contents/Resources/' . $imgname . '@2x.png'));
			$item->appendChild($icon);
		}
	}

	public function pxml(){
		return $xmlStr = $this->xml->saveXML();
	}
}
?>