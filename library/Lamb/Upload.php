<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb
 */
class Lamb_Upload
{
	const SUFFIX_CHECK_ALLOWS = 1;
	
	const SUFFIX_CHECK_UNALLOWS = 2;
	
	/**
	 * @var array 鍏佽涓娄紶镄勬墿灞曞悕
	 */
	protected $_allowSuffixs = array(
					'.gif', '.jpg', '.png', '.jpeg'
				);
	/**
	 * @var array 涓嶅厑璁镐笂浼犵殑镓╁睍鍚?浼桦厛绾ф渶楂?
	 */
	protected $_unallowSuffixs = array();
	
	/**
	 * @var int 涓娄紶鏂囦欢链€澶у皬锛?涓轰笉闄愬埗 KB
	 */
	protected $_maxFilesize = 0; 
	
	/**
	 * @var int
	 */
	protected $_mCheckSuffixType = self::SUFFIX_CHECK_ALLOWS;
	
	
	public function __construct()
	{
	
	}
	
	/**
	 * @return array
	 */
	public function getAllowSuffixs()
	{
		return $this->_allowSuffixs;
	}
	
	/**
	 * @param string
	 *銆€@return Lamb_Upload
	 */
	public function addAllowSuffix($ext)
	{
		if (!in_array($ext, $this->getAllowSuffixs())) {
			$this->_allowSuffixs[] = $ext;
		}
		return $this;
	}
	
	/**
	 * @param string $ext
	 * @return boolean | int if exists
	 */
	public function hasAllowSuffix($ext)
	{
		return in_array(strtolower($ext), $this->getAllowSuffixs());
	}
	
	/**
	 * 璁剧疆镓€链夌殑鍚庣紑鍚?
	 * @param string | array 鍚庣紑鍚?
	 * @return this
	 */
	public function setAllAllowSuffix($ext)
	{
		if (is_string($ext)) {
			$ext = explode(',', $ext);
		}
		$this->_allowSuffixs = $ext;
		return $this;
	}
	
	/**
	 * @param string $ext
	 * @return Lamb_Upload
	 */
	public function removeAllowSuffix($ext)
	{
		if (false !== ($index = $this->hasAllowSuffix($ext))) {
			unset($this->_allowSuffixs[$index]);
		}
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getUnallowSuffixs()
	{
		return $this->_unallowSuffixs;
	}
	
	/**
	 * @param string
	 *銆€@return Lamb_Upload
	 */
	public function addUnallowSuffix($ext)
	{
		if (!in_array($ext, $this->getUnallowSuffixs())) {
			$this->_unallowSuffixs[] = $ext;
		}
		return $this;
	}
	
	/**
	 * @param string $ext
	 * @return boolean | int if exists
	 */
	public function hasUnallowSuffix($ext)
	{
		return in_array($ext, $this->getUnallowSuffixs());
	}
	
	/**
	 * @param string $ext
	 * @return Lamb_Upload
	 */
	public function removeUnallowSuffix($ext)
	{
		if (false !== ($index = $this->hasUnallowSuffix($ext))) {
			unset($this->_unallowSuffixs[$index]);
		}
		return $this;
	}
	
	/**  
	 * @param int $size
	 * @return int | Lamb_Uplaod
	 */
	public function setOrGetMaxFilesize($size = null)
	{
		if (null === $size) {
			return $this->_maxFilesize;
		}
		$this->_maxFilesize = (int)$size;
		return $this;
	}
	
	/**
	 * @param int $checkSuffixType
	 * @return int | Lamb_Upload
	 */
	public function setOrGetCheckSuffixType($checkSuffixType = null)
	{
		if (null === $checkSuffixType) {
			return $this->_mCheckSuffixType;
		}
		$this->_mCheckSuffixType = (int)$checkSuffixType;
		return $this;
	}
	
	/**
	 * @param array $aOptions = array(
	 *					'varname' => '', http镙囱瘑鍚?濡傛灉涓虹┖鍒欎负澶氭枃浠朵笂浼?
	 *					'is_keepname' => false, 鏄惁淇濆瓨铡熸枃浠跺悕
	 *					'save_path' => '',淇濆瓨镄勮矾寰?
	 *					'is_safe_check' => true 鏄惁闇€瑕佹墿灞曞悕锛屾枃浠跺ぇ灏忕瓑妫€娴?
	 *				)
	 * @return int | array 濡傛灉鍑洪敊鍒欎负int鍨?-1涓烘病链夊彲涓娄紶镄勬枃浠?>=0鍒欎负娌℃湁阃氲绷瀹夋镄勬枃浠剁储寮?
	 * 						濡傛灉鎴愬姛鍒栾繑锲炴垚锷熺殑鏂囦欢鏁扮粍锛屾疮涓厓绱犻兘鏄枃浠跺悕
	 */
	public function upload(array $aOptions)
	{
		$options = array(
			'varname' => '',
			'is_keepname' => false,
			'save_path' => '',
			'is_safe_check' => true
		);
		Lamb_Utils::setOptions($options, $aOptions);
		$attachments = $this->getAttachments($options['varname']);

		if (false === $attachments) { //娌℃湁鍙笂浼犵殑鏂囦欢
			return -1;
		}
		if ($options['is_safe_check']) {//瀹夋
			if (($errorno = $this->checkSuffix($attachments)) >= 0) {//镓╁睍鍚嶆娴嫔け璐?
				return $errorno;
			}
			if (($errorno = $this->checkSize($attachments)) >= 0) {//鏂囦欢澶у皬妫€娴嫔け璐?
				return $errorno;
			}
		}
		$aFiles = $this->_upload($options['save_path'], $attachments, $options['is_keepname']);
		return count($aFiles) ? $aFiles : -1;
	}	
	
	/**
	 * @param array $files 鏂囦欢鍚嶆垨钥呮枃浠跺悕鏁扮粍
	 * @return int -1 sucss >= 0 浠ｈ〃鍝釜鏂囦欢涓娄紶澶辫触
	 */
	public function checkSuffix(array $files)
	{
		$suffixType = $this->setOrGetCheckSuffixType();	

		foreach ($files as $key => $file) {
			$suffix = Lamb_IO_File::getFileExt($file['name']);
			if ($suffixType === self::SUFFIX_CHECK_ALLOWS && !$this->hasAllowSuffix($suffix)) {
				return $key;
			}
			if ($suffixType === self::SUFFIX_CHECK_UNALLOWS && $this->hasUnallowSuffix($suffix)) {
				return $key;
			}
		}
		return -1;
	}

	/**
	 * @param array $files 鏂囦欢鍚嶆垨钥呮枃浠跺悕鏁扮粍
	 * @return int -1 sucss >= 0 浠ｈ〃鍝釜鏂囦欢涓娄紶澶辫触
	 */	
	public function checkSize(array $files)
	{
		$maxsize = $this->setOrGetMaxFilesize() * 1024;
		if ($maxsize > 0) {
			foreach ($files as $key => $file) {
				if ($maxsize < $file['size']) {
					return $key;
				}
			}
		}
		return -1;
	}
	
	/**
	 * @param string $varname 濡傛灉$varmae涓虹┖鍒欎负澶氭枃浠朵笂浼?
	 * @return array | false if not found
	 */
	public static function getAttachments($varname = '')
	{
		$ret = array();
		if(!$varname) {
			foreach ($_FILES as $v) {
				!empty($v['name']) && $v['error'] == 0 ? $ret[] = $v : '';
			}
			if (count($ret)<=0) {
				return false;
			}
		} else {
			if (!isset($_FILES[$varname]) || !is_array($_FILES[$varname])) {				
				return false;
			}
			if (is_array($_FILES[$varname]['error'])) {
					if ($_FILES[$varname]['error'][$key] === 0) {
						$ret[] = array(
								'name' => $_FILES[$varname]['name'][$key],
								'tmp_name' => $_FILES[$varname]['tmp_name'][$key],
								'type' => $_FILES[$varname]['type'][$key],
								'size' => $_FILES[$varname]['size'][$key]
							);
					}
			} else if ($_FILES[$varname]['error'] === 0){
				$ret[0] = $_FILES[$varname];
			}			
		}
		return $ret;	
	}
	
	/**
	 * 涓娄紶鏂囦欢
	 *
	 * @param string $path
	 * @param array $attachments froms $_FILES
	 * @param boolean $isKepp
	 * @return array
	 */
	protected function _upload($path, array $attachments, $isKeep = false)
	{
		$aFileName=array();
		foreach($attachments as $k => $data) {		
			if (!$isKeep) {
				$filepath = Lamb_IO_File::generateCrc32EncodeFileNamePath($path . microtime(true) . rand(0, 1000), 
								Lamb_IO_File::getFileExt($data['name']));
			} else {
				$filepath = $path . $data['name'];
			}
			$filepath = Lamb_IO_File::getUniqueName($filepath);
			move_uploaded_file($data['tmp_name'], $filepath);
			@unlink($data['tmp_name']);
			$aFileName[] = $filepath;
		}
		return $aFileName;	
	}
}