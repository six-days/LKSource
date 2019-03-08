<?php
namespace app\index\controller;
use Web3\Contract;
use Web3\Utils;
/**
 * https://github.com/sc0Vu/web3.php
 */
class Index
{
	const PROVIDER = 'http://192.168.1.48:9545';
	const AIBPATH = ROOT_PATH.'\public\contract\Whisper.json';
	const CONSTRACTADDRESS = '0xa772f7178cba4d9165be0cd00d7fc3d634056212';
	private $contract = null;
	public function __construct()
    {
    	$abi = file_get_contents(self::AIBPATH);
    	$this->contract = new Contract(self::PROVIDER, $abi);
    }

    public function index()
    {
    	$res = $this->contract->at(self::CONSTRACTADDRESS)->call('whisperOpen', [], function ($err, $result){
			if ($err !== null) {
				return $err;
			}
			return $result;});
    	// $functionData = $this->contract->at(self::CONSTRACTADDRESS)->getData('whisperOpen', []);
        dump($res);
        return $res;
    }

    public function callSecret()
	{
		$title = input('post.title/s');
		$desc = input('post.desc/s');
		$content = input('post.content/s');
		$tags = input('post.tags/s');
		$coin = input('post.coin/d');
		$closeTime = input('post.closeTime/d');

		if (empty($title) || strlen($title) > 60) {
			return revert_message(1,'标题需要在20个汉字以内！');
		}
		if (strlen($desc) > 1500) {
			return revert_message(1,'内容描述需要在500个汉字以内！');
		}
		if (empty($content) || strlen($content) > 1500) {
			return revert_message(1,'内容需要在500个汉字以内！');
		}
		if (!empty($closeTime) && $closeTime < time()) {
			return revert_message(1,'资源有效期应该大于当前时间！');
		}
		if (empty($closeTime)) {
			$closeTime = '0';
		}
		if ($coin < 0.0001 || $coin > 1000) {
			return revert_message(1,'资源收费应在0.0001~1000链克以内！');
		}
		if (empty($desc)) {
			$desc = ' ';
		}
		$coin = number_format($coin * pow(10,18),0,'',''); //单位转换为wei
		$taglist = explode(';', $tags);
		$title = Utils::toHex($title, true);
		$desc = Utils::toHex($desc, true);
		$content = Utils::toHex($content, true);
		foreach ($taglist as &$v) {
			if (strlen($v) > 15) {
				return revert_message(1,'标签需要在5个汉字以内');
			}
			$v = Utils::toHex($v, true);
		}
		$params = [
			'_title' => $title,
			'_desc' => $desc,
			'_content' => $content,
			'_tag' => $taglist,
			'_coin' => $coin,
			'_closeTime' => $closeTime
		];

		// $functionData = $this->contract->at(self::CONSTRACTADDRESS)->getData('setNewSecret', $title, $desc, $content, $taglist, $coin, $closeTime);

		$this->contract->at(self::CONSTRACTADDRESS)->call('setNewSecret', $title, $desc, $content, $taglist,$coin, $closeTime, ['from' => '0x932ce07e12f04ab9eb770bbd1a27c45601bcc659','gas' => '0x4C4B40'], function ($err, $result){
			if ($err !== null) {
				dump($err);
			}
			dump($result);});
		// return $functionData;
	}

}
