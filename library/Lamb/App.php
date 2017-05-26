<?php
/**
 * Lamb Framework
 * @package Lamb
 * @author 灏忕緤
 */
 class Lamb_App
 {
 	const CHARSET_GBK = 'gbk';
	
	const CHARSET_UTF8 = 'utf-8';
	
	const CHARSET_GB2312 = 'gb2312';
	
	const APP = 'Lamb_App';
	
 	/**
	 * @var Lamb_App singleton instance 
	 */
 	private static $sInstance = null;
	
	/**
	 * @var Lamb_App_Router_Interface 
	 */
	protected $_mRouter;
	
	/**
	 * @var Lamb_App_ErrorHandler_Interface the implemention of handle error
	 */
	protected $_mErrorHandler;
	
	/**
	 * @var Lamb_App_Response_Interface the implemention of Lamb_App_Response_Interface
	 */
	protected $_mResponse;
	
	/**
	 * @var Lamb_App_Request the implemention of Lamb_App_Request_Interface
	 */
	protected $_mRequest;
	
	/**
	 * @var Lamb_App_Dispatcher_Interface the implemention of Lamb_App_Dispatch_Interface
	 */
	protected $_mDispatcher = null;
	
	/**
	 * @var Lamb_View
	 */
	protected $_mView;
	
	/**
	 * @var Lamb_Db_Sql_Helper_Abstract the util's of sql
	 */
	protected $_mSqlHelper = null;
	
	/**
	 * @var PHP callback or Lamb_Db_Callback_Interface implemetion. Get the default database object
	 */
	protected $_mDbCallback = null;
	
	/**
	 * Retrieve singleton instance
	 *
	 * @return Lamb_App
	 */
	public static function getInstance()
	{
		if (null === self::$sInstance) {
			self::$sInstance = new self();
		}
		return self::$sInstance;
	}
	
	/**
	 * Reset singleton instance
	 *
	 * @return void
	 */
	 public static function resetInstance()
	 {
	 	self::$sInstance = null;
	 }
	 
	 /**
	  * Set the global's app object,you can get the app object everywhere to use getGlobalApp()
	  * 
	  * @param Lamb_App
	  */
	 public static function setGlobalApp(Lamb_App $app)
	 {
	 	Lamb_Registry::set(self::APP, $app);
	 }
	 
	 /**
	  * Get the global's app object
	  */
	 public static function getGlobalApp()
	 {
	 	return Lamb_Registry::get(self::APP);
	 }
	 
	 /**
	  * Set the Lamb_Router_Interface implemention
	  *
	  * @param Lamb_App_Router_Interface $rouer 
	  * @return Lamb_App
	  */
	 public function setRouter(Lamb_App_Router_Interface $router)
	 {
	 	$this->_mRouter = $router;
		return $this;
	 }
	 
	 /**
	  * Retrieve the Lamb_App_Router_Interface implemention
	  *
	  * @return Lamb_App_Router_Interface
	  */
	 public function getRouter()
	 {
	 	return $this->_mRouter;
	 }
	 
	 /**
	  * Set the value of "_mErrorHandler"
	  * 
	  * @param Lamb_ErrorHandle_Interface $errorHandler
	  * @return Lamb_App
	  */
	 public function setErrorHandler(Lamb_App_ErrorHandler_Interface $errorHandler)
	 {
	 	$this->_mErrorHandler = $errorHandler;
		return $this;
	 }
	 
	 /**
	  * Get the implemention of handle error
	  *
	  * @return Lamb_App_ErrorHandle_Interface
	  */
	 public function getErrorHandler()
	 {
	 	return $this->_mErrorHandler;
	 }
	 
	 /**
	  * @param Lamb_App_Request 
	  * @return Lamb_App
	  */
	 public function setRequest(Lamb_App_Request $request)
	 {
	 	$this->_mRequest = $request;
	 	return $this;
	 }
	 
	 /**
	  * @return Lamb_App_Request
	  */
	 public function getRequest()
	 {
	 	return $this->_mRequest;
	 }
	 
	 /**
	  * @param Lamb_App_Response_Interface 
	  * @return Lamb_App
	  */
	 public function setResponse(Lamb_App_Response $response)
	 {
	 	$this->_mResponse = $response;
		return $this;
	 }
	 
	 /**
	  * @return Lamb_App_Response_Interface
	  */
	 public function getResponse()
	 {
	 	return $this->_mResponse;
	 }
	 
	 /**
	  * Set the value of '_mSqlHelper'
	  *
	  * @param Lamb_Db_Sql_Helper_Abstract $sqlHelper
	  * @return Lamb_App
	  */
	 public function setSqlHelper(Lamb_Db_Sql_Helper_Abstract $sqlHelper)
	 {
	 	$this->_mSqlHelper = $sqlHelper;
		return $this;
	 }
	 
	 /**
	  * Retrieve the sql helper
	  *
	  * @return Lamb_Db_Sql_Helper_Abstract
	  * @trhows Lamb_App_Exception
	  */
	 public function getSqlHelper()
	 {
	 	if (null === $this->_mSqlHelper) {
			throw new Lamb_App_Exception("Invaild sql helper,be must set the sql helper");
		}
		return $this->_mSqlHelper;
	 }
	 
	 /**
	  * Set the value of '_mDbCallback' 
	  *
	  * @param PHP callback | Lamb_Db_Callback_Interface $callback 
	  * @return Lamb_App
	  * @thorws Lamb_App_Exception
	  */
	 public function setDbCallback($callback)
	 {
	 	if ($callback instanceof Lamb_Db_Callback_Interface || is_callable($callback)) {
			$this->_mDbCallback = $callback;
		} else {
			throw new Lamb_App_Exception("Invaild $callback argument,$callback must be a Lamb_Db_Callback_Interface implemetion or PHP standard callback ");
		}
		return $this;
	 }
	 
	 /**
	  * Get the default db object
	  * 
	  * @return Lamb_Db_Abstract
	  * @throws Lamb_App_Exception
	  */
	 public function getDb()
	 {
	 	if (null === $this->_mDbCallback) {
			throw new Lamb_App_Exception("Invaild db object,be must set the db object");
		}
		if ($this->_mDbCallback instanceof Lamb_Db_Callback_Interface) {
			$db = $this->_mDbCallback->getDb();
		} else {
			$db = call_user_func($this->_mDbCallback);
		}
		return $db;
	 }
	 
	 /**
	  * @param Lamb_App_Dispatcher_Interface
	  * @return Lamb_App
	  */
	 public function setDispatcher(Lamb_App_Dispatcher_Interface $dispatcher)
	 {
	 	$this->_mDispatcher = $dispatcher;
		return $this;
	 }
	 
	 /**
	  * @return Lamb_App_Diaptcher_Interface implemention
	  */
	 public function getDispatcher()
	 {
	 	return $this->_mDispatcher;
	 }
	 
	 /**
	  * @return Lamb_View
	  */
	 public function getView()
	 {
	 	return $this->_mView;
	 }
	 
	 /** 
	  * @param Lamb_View $view
	  * @return Lamb_App
	  */
	 public function setView(Lamb_View $view)
	 {
	 	$this->_mView = $view;
		return $this;
	 }
	 
	 /**
	  * @param string $path
	  * @return Lamb_App
	  * @throws Lamb_App_Exception
	  */
	 public function setViewPath($path)
	 {
	 	if (!$this->_mView) {
			throw new Lamb_App_Exception("The view component is not initilize,you must initliaze view before set the view path");
		}
		$this->_mView->setOrGetViewPath($path);
		return $this;
	 }
	 
	 /**
	  * @param string $path
	  * @return Lamb_App
	  * @throws Lamb_App_Exception
	  */
	 public function setViewRuntimePath($path)
	 {
	 	if (!$this->_mView) {
			throw new Lamb_App_Exception("The view component is not initilize,you must initliaze view before set the view runtime path");
		}
		$this->_mView->setOrGetViewRuntimePath($path);
		return $this;	 
	 }
	 
	 /**
	  * @param string $path
	  * @return  Lamb_App
	  * @throws Lamb_App_Exception
	  */
	 public function setControllorPath($path)
	 {
	 	if (!$this->_mDispatcher) {
			throw new Lamb_App_Exception("The dispatcher component is not initilize,you must initliaze view before set the controllor path");
		}
		$this->_mDispatcher->setControllorPath($path);
	 	return $this;
	 }
	 
	 /**
	  *  @return int
	  */
	 public function getCharset()
	 {
	 	return self::CHARSET_UTF8;
	 }
	 
	 /**
	  * @return boolean
	  */
	 public function isAppUTF8(&$charset = null)
	 {
	 	return ($charset = $this->getCharset()) == self::CHARSET_UTF8;
	 }

	 /**
	  * @return boolean
	  */	 
	 public function isAppGBK(&$charset = null)
	 {
	 	return ($charset = $this->getCharset()) == self::CHARSET_GBK;
	 }

	 /**
	  * @return boolean
	  */		 
	 public function isAppGB2312(&$charset = null)
	 {
	 	return ($charset = $this->getCharset()) == self::CHARSET_GB2312;
	 }
	 
	 /**
	  * The enter point of application
	  *
	  * @return void
	  */
	 public function run()
	 {
		$this->getRouter()->parse()->injectRequest($this->getRequest());
		try {
			$this->getDispatcher()->invoke($this->getRouter());
		} catch (Lamb_Exception $e) {
			$this->invokeErrors($e);
		}
	 }
	 
	 /**
	  * Construct the Lamb_App
	  *
	  * @return void
	  */
	 protected function __construct()
	 {
	 	$this->setRequest(new Lamb_App_Request())
			 ->setRouter(new Lamb_App_Router())
			 ->setDispatcher(new Lamb_App_Dispatcher())
			 ->setResponse(new Lamb_App_Response())
			 ->setView(new Lamb_View());
		self::setGlobalApp($this);
	 }
	 
	 /**
	  * Handle errors int the runtime
	  * @param Lamb_Exception $e
	  * @return void
	  */
	 protected function invokeErrors(Lamb_Exception $e)
	 {
	 	if (null !== $this->_mErrorHandler) {
			$this->_mErrorHandler->handle($e);
		} else {
			throw $e;
		}
	 }
 }