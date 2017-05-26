<?php
class Lamb_App_NormalRouter extends Lamb_App_Router_Abstract
{
	public function __construct()
	{
		
	}
	
	public function parse($query = '')
	{			
		if ($query && is_string($query)) {
			$this->_mParams = parse_str($query);
			$this->_mControllorName = isset($this->_mParams[$this->_mControllorKey]) ? $this->_mParams[$this->_mControllorKey] : 'index';
			$this->_mActionName = isset($this->_mParams[$this->_mActionKey]) ? $this->_mParams[$this->_mActionKey] : 'index';
		} else {
			if ($query instanceof Lamb_App_Request) {
				$request = $query;			
			} else {
				$request = Lamb_App::getGlobalApp()->getRequest();
			}
			
			$this->_mControllorName = isset($request->{$this->_mControllorKey}) ? $request->{$this->_mControllorKey} : 'index';
			$this->_mActionName = isset($request->{$this->_mActionKey}) ? $request->{$this->_mActionKey} : 'index';
		}
		
		return $this;
	}
	
	public function url($params, $encode = true)
	{
		if (is_string($params)) {
			return $params;
		}
		
		$ret = array();
		
		if (isset($params[$this->$_mControllorKey])) {
			$params[$this->$_mControllorKey] = 'index';
		}
		
		if (isset($params[$this->_mActionKey])) {
			$params[$this->_mActionKey] = 'index';
		}
		
		foreach ($params as $key => $val) {
			if ($encode) {
				$ret[] = urlencode($key) . '=' . urlencode($val);
			} else {
				$ret[] = $key . '=' . $val;
			}
		}
		
		return implode('&', $ret);
	}
}
