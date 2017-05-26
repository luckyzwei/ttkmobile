<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb
 */
class Lamb_View
{
	/**
	 * @var string the path of view
	 */
	protected $_mViewPath;
	
	/**
	 * @var string 妯＄増鏂囦欢瑙ｆ瀽鍚庢墍瀛樻斁镄勭洰褰?
	 */
	protected $_mViewRuntimePath;
	
	/**
	 * @var string the extendsion of view
	 */
	protected $_mViewExtendtion = 'html';
	
	/**
	 * @var array 鍩烘湰镙囩瑙ｆ瀽琛?阌€煎搴旇В鏋愯镙囩镄勫嚱鏁板悕锛?
	 * 绯荤粺灏呜嚜锷ㄥ湪阌€煎悕鍓嶅姞parse_basetag_鏉ュ搴旗殑澶勭悊鍑芥暟鍚?
	 * 瀵瑰簲镄勫€煎氨鏄尮閰嶆爣绛剧殑姝ｅ垯琛ㄨ揪寮?
	 */
	protected $_mBaseTagParseMap = array(
					'var' => '/(?:<!--[\s\r\n\t]*)?\{\$([\w\$\[\]\'\\"->]+?)\}(?:[\s\r\n\t]*-->)?/is',
					'layout' => '/(?:<!--[\s\r\n\t]*)?\{layout\s+(.+?)\}(?:[\s\r\n\t]*-->)?/is',
					'eval' => '/(?:<!--[\s\r\n\t]*)?\{eval\s+(.+?)\}(?:[\s\r\n\t]*-->)?/is'
				);
	/** 
	 * @var string the custom tag regex string
	 */
	protected $_mCustomTagRegex = '/(?:<!--[\s\r\n\t]*)?\{tag:([a-zA-Z_]\w*)(.*?)\}(.*?)\{\/tag:\1\}(?:[\s\r\n\t]*-->)?/is';
	 
	/** 
	 * Construct the Lamb_View
	 *
	 * @param string $viewPath 妯＄増鏂囦欢鐩綍
	 * @param string $viewRuntimePath 妯＄増瑙ｆ瀽鍚庣紦瀛樼洰褰?
	 */
	public function __construct($viewPath = null, $viewRuntimePath = null)
	{
		$this->setOrGetViewPath($viewPath);
		$this->setOrGetViewRuntimePath($viewRuntimePath);
	}
	
	/**
	 * Set or retrivev the value of '_mViewPath'
	 *
	 * @param string $viewPath
	 * @return string|Lamb_View
	 */
	public function setOrGetViewPath($viewPath = null)
	{
		if (null === $viewPath) {
			return $this->_mViewPath;
		}
		$this->_mViewPath = (string)$viewPath;
		return $this;
	}
	
	/**
	 * Set or retrieve the value of '_mViewRuntimePath' 
	 *
	 * @param string $viewRuntimePath
	 * @return string|Lamb_View
	 */
	public function setOrGetViewRuntimePath($viewRuntimePath = null)
	{
		if (null === $viewRuntimePath) {
			return $this->_mViewRuntimePath;
		}
		$this->_mViewRuntimePath = (string)$viewRuntimePath;
		return $this;
	}
	
	/**
	 * Set or retrivev the value of '_mViewExtendtion'
	 *
	 * @param string $extendtion
	 * @return Lamb_View
	 */
	public function setOrGetViewExtendtion($extendtion = null)
	{
		if (null === $extendtion) {
			return $this->_mViewExtendtion;
		}	
		$this->_mViewExtendtion = (string)$extendtion;
		return $this;
	}
	
	/**
	 * 璁剧疆鎴栧垹闄ゅ熀纭€镙囩鏄犲皠琛?
	 * 濡傛灉鍙傛暟$value涓虹┖锛?key涓哄瓧绗︿覆鍒欐槸鍒犻櫎璇ラ敭链硷紝濡傛灉$key涓烘暟缁勶紝
	 * 鍒欐槸镓归噺淇敼 
	 *
	 * @param string $key
	 * @param string $value
	 * @return Lamb_View
	 */
	public function setBaseTagParseMap($key, $value = null)
	{
		if ($value === null && is_string($key)) {
			if (isset($this->_mBaseTagParseMap[$key])) {
				unset($this->_mBaseTagParseMap[$key]);
			}
		} else if ($value === null && is_array($key)) {
			foreach ($key as $k => $v) {
				$this->setBaseTagParseMap($k, $v);
			}
		} else if (is_string($value) && is_string($key)) {
			$this->_mBaseTagParseMap[$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * @param string $key
	 * @return stirng|array
	 */
	public function getBaseTagParseMap($key = null)
	{
		if ($key === null) {
			return $this->_mBaseTagParseMap;
		} else {
			return isset($this->_mBaseTagParseMap[$key]) ? $this->_mBaseTagParseMa[$key] : '';
		}
	}
	
	/**
	 * Set or retrieve the value of '_mCustomTagRegex'
	 *
	 * @param string $regex
	 * @return Lamb_View
	 */
	public function setOrGetCustomTagRegex($regex = null)
	{
		if (null === $regex) {
			return $this->_mCustomTagRegex;
		}
		$this->_mCustomTagRegex = (string)$regex;
		return $this;
	}
	
	/**
	 * @param &string $source
	 * @return Lamb_View
	 */
	public function parseBaseTag(&$source)
	{
		$map = $this->getBaseTagParseMap();
		foreach ($map as $funcName => $regs) {
			$funcName = 'parse_basetag_' . $funcName;
			if (method_exists($this, $funcName)) {
				if (preg_match_all($regs, $source, $matches, PREG_SET_ORDER)) {
					foreach ($matches as $matchesItem) {
						$source = str_replace($matchesItem[0], call_user_func(array($this, $funcName), $matchesItem), $source);
					}
				}
			}
		}
		unset($source);
		return $this;
	}
	
	/**
	 * @param &string $source
	 * @return Lamb_View
	 */
	public function parseCustomTag(&$source)
	{
		if (preg_match_all($this->setOrGetCustomTagRegex(), $source, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $items) {
				if ( isset($items[1]) && class_exists($items[1])
					 && array_key_exists('Lamb_View_Tag_Interface', class_implements($items[1]))) {
					$objTag = new $items[1];
					$source = str_replace($items[0], $objTag->parse($items[3], $items[2]), $source);
				}
			}
		}
		unset($source);
		return $this;
	}
	
	/**
	 * Get the template filename full path
	 *
	 * @param string $filename
	 * @return stirng
	 */
	public function getViewFullPath($filename)
	{
		return $this->setOrGetViewPath() . $filename . '.' . $this->setOrGetViewExtendtion();
	}
	
	/**
	 * 銮峰彇妯＄増鏂囦欢瑙ｆ瀽缂揿瓨镄勫叏璺缎
	 *
	 * @param string $filename
	 * @param mixed $cacheId
	 * @return string
	 */
	public function getViewRuntimeFullPath($filename, $cacheId = '')
	{
		return  $this->setOrGetViewRuntimePath() . $filename . $cacheId . '.php';
	}
	
	/**
	 * @param string $filename
	 * @return string
	 * @throws Lamb_View_Exception
	 */
	public function parseFile($filename)
	{
		$fullpath = $this->getViewFullPath($filename);
		if (!file_exists($fullpath)) {
			throw new Lamb_View_Exception("The view path \"$fullpath\" does not exists.");
		}
		return $this->parseString(file_get_contents($fullpath));
	}
	
	/**
	 * @param stirng $source
	 * @return string
	 */
	public function parseString($source)
	{
		$this->parseBaseTag($source)->parseCustomTag($source);
		return $source;
	}
	
	/**
	 * Parse the view file and include it
	 *
	 * @param stirng $filename
	 * @param mixed $cacheId 鍖哄垎涓嶅悓镄勬ā鐗堢紦瀛樻枃浠讹紝阒叉閲嶅悕瑕嗙洊
	 * @return string
	 * @throws Lamb_View_Exception
	 */
	public function load($filename, $cacheId = '')
	{
		$cachePath = $this->getViewRuntimeFullPath($filename, $cacheId);
		$viewPath = $this->getViewFullPath($filename);
		$lastModifiedTime = 0;
		if (!file_exists($viewPath)) {
			throw new Lamb_View_Exception("The view path \"$viewPath\" does not exists.");
		}
		if (file_exists($cachePath)) {
			$lastModifiedTime = filemtime($cachePath);
		}
		//濡傛灉缂揿瓨杩囨湡锛屽垯瑙ｆ瀽
		if ($lastModifiedTime === 0 || filemtime($viewPath) > $lastModifiedTime) {
			file_put_contents($cachePath, $this->parseFile($filename));
		}
		return $cachePath;
	}
	
	/**
	 * Base Tag Handler
	 *
	 * @param array $matches 瑙ｆ瀽鏄犲皠琛╛mBaseTagParseMap涓鍒栾〃杈惧纺鍖归厤鍒扮殑缁撴灉
	 * @return string
	 */
	public function parse_basetag_var(array $matches)
	{
		$strSrc		=	$matches[0];
		if($matches[1]){
			$strSrc	=	"<?php echo \${$matches[1]};?>";
		}
		return $strSrc;	
	}
	
	public function parse_basetag_layout(array $matches)
	{
		$strSrc		=	$matches[0];
		if ($matches[1]){
			$strSrc	=	"<?php include \$this->mView->load(\"$matches[1]\");?>";
		}
		return $strSrc;	
	}
	
	public function parse_basetag_eval(array $matches)
	{
		$strSrc		=	$matches[0];
		if($matches[1]){
			$strSrc	=	"<?php echo {$matches[1]};?>";
		}
		return $strSrc;	
	}
}