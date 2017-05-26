<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_App
 * @useage URL镙煎纺 http://www.a.com/a/b.php?s=controllor/action/name1/val1/name2/val2....
 */
class Lamb_App_Router extends Lamb_App_Router_Abstract
{
	
	/**
	 * @var string the delimiter of the request's params eg:s=controllor/action/name1/var1....
	 */
	protected $_mUrlDelimiter = '/';
	
	/**
	 * @var string eg:http://www.a.com/?s=controllor/action the router param's name is s
	 */
	protected $_mRouterParamName = 's';
	
	/**
	 * @var array the url encode map.eg: array('/' => '@:@' controllor/action/name1/(val/1) =>encode controllor/action/name1/val@:@1 )
	 */
	protected $_mEncodeCharsMap;
	 
	 /**
	  * Contruct the Lamb_App_Router
	  */
	 public function __construct()
	 {
	 	$this->_mEncodeCharsMap = array($this->_mUrlDelimiter => '~@:@~');
	 }	
	
	/**
	 * Used the _mEncodeCharsMap to encode url
	 *
	 * @param string $param
	 * @return string
	 */
	public function encode($param)
	{
		foreach ($this->getEncodeCharsMap() as $key => $val) {
			$param = str_replace($key, $val, $param);
		}
		return Lamb_App_Response::encodeURIComponent($param);
	}
	
	/**
	 * Usede the _mEncodeCharsMap to decode url
	 *
	 * @param string $param
	 * @return string
	 */
	public function decode($param)
	{
		$param = Lamb_App_Response::decodeURIComponent($param);
		foreach ($this->getEncodeCharsMap() as $key => $val) {
			$param = str_replace($val, $key, $param);
		}
		return $param;
	}
	
	/**
	 * Set the value of '_mEncodeCharsMap'锛屽鏋?map涓殑鍏幂礌鍦╛mEncodeCharsMap宸茬粡瀛桦湪鍒欎细琚慨鏀癸紝
	 * 鍚﹀垯浼氲娣诲姞锛屽鏋?map瀵瑰簲镄剉alue涓虹┖锛屽垯灏嗗垹闄よ阌€?
	 *
	 * @param array $map
	 * @return Lamp_App_Router
	 */
	public function setEncodeCharsMap(array $map)
	{
		foreach ($map as $key => $val) {
			if (isset($this->_mEncodeCharsMap[$key]) && $val === null) {
				unset($this->_mEncodeCharsMap[$key]);
			} else {
				$this->_mEncodeCharsMap[$key] = $val;
			}
		}
		return $this;
	}
	
	/**
	 * Set or retrieve the _mUrlDelimiter
	 *
	 * @param string $delimiter
	 * @return Lamb_App_Router|string
	 */
	public function setUrlDelimiter($delimiter = null)
	{
		if (null === $delimiter) {
			return $this->_mUrlDelimiter;
		}
		$dlimiter = (string)$delimiter;
		$this->_mUrlDelimiter = $delimiter;
		return $this;
	}
	
	/**
	 * Set or retrieve the _mRouterParamName
	 *
	 * @param string $name
	 * @return Lamb_App_Router | string
	 */
	public function setRouterParamName($name = null) 
	{
		if (null === $name) {
			return $this->_mRouterParamName;
		}
		$name = (string)$name;
		$this->_mRouterParamName = $name;
		return $this;
	}
	
	/**
	 * Retrieve the encode map
	 *
	 * @return array
	 */
	public function getEncodeCharsMap()
	{
		return $this->_mEncodeCharsMap;
	}
	 
	/**
	 * Lamb_App_Router_Interface implement
	 */
	 public function parse($query = '')
	 {
	 	$routerParamName = $this->setRouterParamName();
		if ($query instanceof Lamb_App_Request) {
			$query = $query->$routerParamName;
		} else if (is_string($query)) {
			if (empty($query)) {
				$query = (string)Lamb_App::getGlobalApp()->getRequest()->$routerParamName;
			}
		} else {
			throw new Lamb_App_Router_Exception('Invaild query passed to parse(),query string must be a string or Lamb_App_Request implemention');
			return $this;
		}
		
		if (!empty($query)) {
			$params = explode($this->setUrlDelimiter(), $query);
			if (count($params) >= 2) {
				$this->_mControllorName = $this->decode($params[0]);
				$this->_mActionName = $this->decode($params[1]);
				
				for ($i = 2; $i < count($params);) {
					if (array_key_exists($i + 1, $params)) {
						$this->_mParams[$this->decode($params[$i])] = $this->decode($params[$i+1]);
					}
					$i += 2;
				}
			}
		}
		return $this;
	 }	
	 
	/**
	 * Lamb_App_Router_Interface implement
	 */
	 public function url($params, $encode = true)
	 {
	 	$ret = '';
		
	 	if (is_string($params)) {
			parse_str($params, $params);
		} 
		
		if (is_array($params)) {
			$controllorKey = $this->setControllorKey();
			$actionKey = $this->setActionKey();
			
			$aRetParam = array();
			
			if (isset($params[$controllorKey])) {
				array_push($aRetParam, $encode ? $this->encode($params[$controllorKey]) : $params[$controllorKey]);
				if (isset($params[$actionKey])) {
					array_push($aRetParam, $encode ? $this->encode($params[$actionKey]) : $params[$actionKey]);	
				} else {
					array_push($aRetParam, '');
				}
			}
			
			unset($params[$controllorKey], $params[$actionKey], $controllorKey, $actionKey);
			
			foreach ($params as $key => $val) {
				if ($encode) {
					array_push($aRetParam, $this->encode($key));
					array_push($aRetParam, $this->encode($val));
				} else {
					array_push($aRetParam, $key);
					array_push($aRetParam, $val);
				}
			}
			
			$ret = implode($this->setUrlDelimiter(), $aRetParam);
		}
		return $ret;
	 }
	 
	/**
	 * Retrive the url of controllor and action
	 *
	 * @return string
	 */
	public function getCtrlActUrl($encode = true)
	{
		return '?' . $this->setRouterParamName() . '=' . $this->url(array(
														$this->setControllorKey() => $this->getControllor(),
														$this->setActionKey() => $this->getAction()
													), $encode);
	}
	
	/**
	 * @param string $control
	 * @param string $action
	 * @return array
	 */
	public function getCtrlActUrlArray($control, $action) 
	{
		$ret = array();
		if ($control) {
			$ret[$this->setControllorKey()] = $control;
		}
		if ($action) {
			$ret[$this->setActionKey() ] = $action;
		}
		return $ret;
	}
	
	/**
	 * @param string $control
	 * @param string $action
	 * @param array $param
	 * @return string
	 */
	public function urlEx($control, $action, array $param = null, $encode = true, $full = true)
	{	
		$ret = $this->getCtrlActUrlArray($control, $action);
		if ($param) {
			$ret = $ret + $param;
		}
		return ($full ? '?' . $this->setRouterParamName() . '=' : '') . $this->url($ret, $encode);
	}	 	 	 	 
}