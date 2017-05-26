<?php
/**
 * Lamb Framework
 * @package Lamb
 * @author 灏忕緤
 */
class Lamb_Loader
{
	/**
	 * @var Lamb_Loader singleton instance
	 */
	private static $sInstance = null;
	
	/**
	 * @var array Callback for internal autoloader implemetion
	 */
	protected $_mAInternalAutoloader;
	
	/**
	 * @var array Callback for default class loader
	 */
	protected $_mADefaultClassAutoloader = array(__CLASS__, 'loadClass');
	
	/**
	 * @var array Supported namespaces 'Lamb' by default
	 */
	protected $_mANamespaces = array('Lamb_' => true);
	
	/**
	 * @var array namespaces-specific autoloaders
	 */
	protected $_mANamespacesAutoloaders = array();
	
	/**
	 * @var boolean whether or not to supperss file not found warnings 
	 */
	protected $_mBSuppressNotFoundWarnings = false;
	
	/**
	 * @var boolean whether or not to acting fallback autoloader
	 */
	 protected $_mBFallbackAutoloader = false;
	
	/**
	 * Retrieve singleton instance
	 *
	 * @return Lamb_Loader
	 */
	public static function getInstance()
	{	
		if (null === self::$sInstance) {
			self::$sInstance = new self();
		}
		return self::$sInstance;
	}
	
	/**
	 * Reset the singleton instance
	 *
	 * @return void
	 */
	public static function resetInstance()
	{
		self::$sInstance = null;
	}
	
	/**
	 * Load class from directory
	 * 
	 * @throws Lamb_Loader_Exception
	 * @param string $strClass
	 * @param string $dir
	 * @return void
	 */
	public static function loadClass($strClass, $dirs = null)
	{
		if (class_exists($strClass, false) || interface_exists($strClass, false)) {
			return ;
		}
		
		if ( (null !== $dirs) && !is_string($dirs) && !is_array($dirs) ) {
			require_once 'Lamb/Loader/Exception.php';
			throw new Lamb_Loader_Exception('directory argument must be a string or an array');
		}
		
		$file = str_replace('_', DIRECTORY_SEPARATOR, $strClass) . '.php';
		
		if (!empty($dirs)) {
			$dirpath = dirname($file); //銮峰彇绫荤殑褰揿墠鐩綍
			if (is_string($dirs)) {
				$dirs = explode(PATH_SEPARATOR, $dirs);
			}
			foreach ($dirs as $key => $dir) { //鐢熸垚璺缎鏁扮粍
				if ($dir == '.') {
					$dirs[$key] = $dirpath;
				} else {
					$dir = rtrim($dir, '\\/');
					$dirs[$key] = $dir . DIRECTORY_SEPARATOR . $dirpath;
				}
			}
			self::loadFile(basename($file), $dirs); //銮峰彇鏂囦欢鍚?
		}
		else {
			self::loadFile($file);
		}
		
		if (!class_exists($strClass, false) && !interface_exists($strClass, false)) {
			require_once 'Lamb/Loader/Exception.php';
			throw new Lamb_Loader_Exception("File \"$file\" does not exist or class \"$strClass\" not found in the file");
		}
	}
	
	/**
	 * Load a php file, this is a wrapper for PHP's include function
	 *
	 * $filename must be the complete filename,including any extendsion such as '.php'
	 *
	 * if $dir is a string or an array,it will search the direactories in the order supplied.
	 * and attempt to load the first matching file.
	 *
	 * if no $dir were specified,it will attempt to load it from php's include_path
	 *
	 * @param string $filename
	 * @param string|array $dir 
	 * @param boolean $once
	 * @return void
	 */
	public static function loadFile($filename, $dir = null, $once = false)
	{
		$incPath = false;
		
		if (!empty($dir) && (is_array($dir) || is_string($dir))) {
			if (is_array($dir)) {
				$dir = implode(PATH_SEPARATOR, $dir);
			}
			$incPath = get_include_path();
			set_include_path($dir . PATH_SEPARATOR. $incPath);
		}
		
		//try to load file
		if ($once) {
			include_once $filename;
		} else {
			include $filename;
		}
		
		//reset to include path
		if ($incPath) {
			set_include_path($incPath);
		}
	}
	
	/**
	 * The enter point of spl_autoload
	 *
	 * @param string $strClass
	 * @return boolean
	 */
	public static function autoload($strClass)
	{
		$self = self::getInstance();
		foreach ($self->getClassLoader($strClass) as $autoloader) { //澶氢釜锷犺浇鍣ㄥ彧璋幂敤涓€娆?
			if ($autoloader instanceof Lamb_Loader_Interface) {
				if ($autoloader->autoload($strClass)) {
					return true;
				}
			} else if (is_array($autoloader)) {
				if (call_user_func($autoloader, $strClass)) {
					return true;
				}
			} else if (is_string($autoloader) || is_callable($autoloader, false, $autoloader)) {
				if ($autoloader($strClass)) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Get a list of specific class autoloaders
	 *
	 * @param string $strClass
	 * @return array
	 */
	public function getClassLoader($strClass)
	{
		$aRetAutoloaders = array();
		$namespace = false;
		
		//銮峰彇瀵瑰簲锻藉悕绌洪棿镄勫姞杞藉櫒(autoloaders)锛屽懡鍚岖┖闂撮昵搴﹂昵镄勪紭鍏?
		foreach (array_keys($this->_mANamespacesAutoloaders) as $ns) {
			if ('' == $ns) {
				continue;
			}
			if (0 === strpos($strClass, $ns)) {
				if ( (false === $namespace) || (strlen($ns) > strlen($namespace)) ) {
					$namespace = $ns;
					$aRetAutoloaders = $this->getNamespaceAutoloaders($ns);
				}
			}
		}
		
		//銮峰彇娉ㄥ唽镄勫懡鍚岖┖闂达紝閲囩敤鍐呴儴锷犺浇鍣╛mAInternalAutoloader
		foreach ($this->getRegisteredNamespaces() as $ns) {
			if (0 === strpos($strClass, $ns)) {
				$namespace = $ns;
				$aRetAutoloaders[] = $this->_mAInternalAutoloader;
				break;
			}
		}
		
		//銮峰彇镞犲懡鍚岖┖闂村姞杞藉櫒
		$autoloadersNonNamespace = $this->getNamespaceAutoloaders('');
		if (count($autoloadersNonNamespace)) {
			foreach ($autoloaderNonNamespace as $ns) {
				$aRetAutoloaders[] = $ns;
			}
		}
		unset($autoloadersNonNamespaces);
		
		//濡傛灉娌℃湁瀵瑰簲镄勫懡鍚岖┖闂村姞杞藉櫒锛屽苟涓旇缃简鍙潬锷犺浇鍣ㄦ爣蹇楋紝鍒欎娇鐢ㄥ唴閮ㄥ姞杞藉櫒
		if (false !== $namespace && $this->isFallbackAutoloader()) {
			$aRetAutoloaders[] = $this->_mAInternalAutoloader;
		}
		
		return $aRetAutoloaders;
	}
	
	/**
	 * Set value of the "_mBFallbackAutoloader" whether or not acting fallback autoloader
	 *
	 * @param boolean $bFlag
	 * @return Lamb_Loader
	 */
	public function setFallbackAutoloader($bFlag)
	{
		$this->_mBFallbackAutoloader = (boolean)$bFlag;
		return $this;
	}
	
	/**
	 * Retrieve the value of _mBFallbackAutoloader
	 *
	 * @return boolean
	 */
	public function isFallbackAutoloader()
	{
		return $this->_mBFallbackAutoloader;
	}
	
	/**
	 * Set the default class autoload implemention
	 *
	 * @throws Lamb_Loader_Exception 
	 * @return Lamb_Loader
	 */
	public function setDefaultClassAutoloader($fCallback)
	{
		if (!is_callable($fCallback, false, $fCallback)) {
			throw new Lamb_Loader_Exception('Invaild callback specified for default class autoloader');
		}
		$this->_mADefaultClassAutoLoader = $fCallback;
		return $this;
	}
	
	/**
	 * Retrieve the default class autoload callback
	 *
	 * @return string|array PHP callback
	 */
	public function getDefaultClassAutoloader()
	{
		return $this->_mADefaultClassAutoloader;
	}
	
	/**
	 * Register a namespace to autoload
	 *
	 * @throws Lamb_Loader_Exception
	 * @param string|array $namespaces
	 * @return Lamb_Loader
	 */
	public function registerNamespaces($namespaces)
	{
		if (is_string($namespaces)) {
			$namespaces = (array)$namespaces;
		} else if (!is_array($namespaces)) {
			throw new Lamb_Loader_Exception('Invaild namespaces provided');
		}
		
		foreach ($namespaces as $ns) {
			if (!isset($this->_mANamespaces[$ns])) {
				$this->_mANamespaces[$ns] = true;
			}
		}
		return $this;
	}
	
	/**
	 * Unload a registered autoload namespace
	 *
	 * @throws Lamb_Loader_Exception 
	 * @param string|array $namespaces
	 * @return Lamb_Loader
	 */
	 public function unregisterNamespaces($namespaces)
	 {
	 	if (is_string($namespaces)) {
			$namespaces = (array)$namespaces;
		} else if (!is_array($namespaces)) {
			throw new Lamb_Loader_Exception('Invaild namespaces provided');
		}
		
		foreach ($namespaces as $ns) {
			if (isset($this->_mANamespaces[$ns])) {
				unset($this->_mANamespaces[$ns]);
			}
		}
		return $this;
	 }
	 
	 /**
	  * Add an autoload to the beginning of the autoloader stack
	  *
	  * @param string|array|object $callback PHP callbacks or Lamb_Loader_Interface implemention
	  * @param string|array $namespaces
	  * @return Lamb_Loader
	  */
	 public function unsiftNamespacesAutoloaders($callback, $namespaces = '')
	 {
	 	$namespaces = (array)$namespaces;
		foreach ($namespaces as $ns) {
			$_autoloaders = $this->getNamespaceAutoloaders($ns);
			array_unshift($_autoloaders, $callback);
			$this->_setNamespaceAutoloaders($_autoloaders, $ns);
		}
		return $this;
	 }
	 
	 /**
	  * Append an autoloader to the autoloader stack
	  * 
	  * @param string|array|object $callback PHP callbacks or Lamb_Loader_Interface implemention
	  * @param string|array $namespaces
	  * @return Lamb_Loader
	  */
	 public function pushNamespaceAutoloaders($callback, $namespaces = '')
	 {
	 	$namespaces = (array)$namespaces;
		foreach ($namespaces as $ns) {
			$_autoloaders = $this->getNamespaceAutoloaders($ns);
			array_push($_autoloaders, $callback);
			$this->_setNamespaceAutolaoders($_autoloaders, $ns);
		}
		return $this;
	 }
	 
	 /** 
	  * Remove an autoloader from the autoloader stack
	  *
	  * @param string|object|array $callback PHP callbacks or Lamb_Loader_Interface implemention
	  * @param string|array $namespace
	  * @return Lamb_Loader
	  */
	 public function removeNamcespaceAutoloader($callback, $namespaces = '')
	 {
	 	$namespaces = (array)$namespaces;
		foreach ($namespaces as $ns) {
			$_autoloaders = $this->getNamespaceAutoloaders($ns);
			if (false !== ($nIndex = array_search($callback, $_autoloaders, true))) {
				unset($_autoloaders[$nIndex]);
				$this->_setNamespaceAutoloaders($_autoloaders, $ns);
			}
		}
		return $this;
	 }
	 
	 /**
	  * Get a list of specified namespace autoloaders
	  *
	  * @param string $strNamespace
	  * @return array
	  */
	 public function getNamespaceAutoloaders($strNamespace)
	 {
	 	$strNamespace = (string)$strNamespace;
	 	if (!array_key_exists($strNamespace, $this->_mANamespacesAutoloaders)) {
			return array();
		}
		return $this->_mANamespacesAutoloaders[$strNamespace];
	 }
	 
	 /**
	  * Get a list of registered namespaces
	  * 
	  * @return array
	  */
	 public function getRegisteredNamespaces()
	 {
	 	return array_keys($this->_mANamespaces);
	 }
	
	/**
	 * Set the value of the "_mBSuppressNotFoundWarings" falg
	 *
	 * @param boolean $bFlag
	 * @return Lamb_Loader
	 */
	public function supperssNotFoundWarnings($bFlag)
	{
		$this->_mBSuppressNotFoundWarings = (boolean)$bFlag;
		return $this;
	}
	
	/**
	 * Retrieve whether or not to suppress file not found warnings
	 *
	 * @return boolean
	 */
	public function isSuppressNotFoundWarnings()
	{
		return $this->_mBSuppressNotFoundWarnings;
	}
	
	/**
	 * Construct the Lamb_Loader
	 *
	 * @return void
	 */
	protected function __construct()
	{
		spl_autoload_register(array(__CLASS__, 'autoload'));
		$this->_mAInternalAutoloader = array($this, '_defaultInternalAutoload');
	}
	
	/**
	 * internal autoload implemention
	 *
	 * return boolean
	 */
	protected function _defaultInternalAutoload($strClass)
	{
		$fClassLoader = $this->getDefaultClassAutoloader();
		try {
			if ($this->isSuppressNotFoundWarnings()) {
				@call_user_func($fClassLoader, $strClass);
			} else {
				call_user_func($fClassLoader, $strClass);
			}
			return $strClass;
		}
		catch (Lamb_Exception $e) {
			return false;
		}
	}
	
	/**
	 * Set autoloaders for a specific namespace
	 *
	 * @param array $callbacks PHP callbacks or Lamb_Loader_Interface implemention
	 * @param string $namespace
	 * @return Lamb_Loader
	 */
	protected function _setNamespaceAutoloaders(array $callbacks, $namespace = '')
	{
		$namespace = (string)$namespace;
		$this->_mANamespacesAutoloaders[$namespace] = $callbacks;
		return $this;
	}	
}