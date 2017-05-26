<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_App
 */
class Lamb_App_Response
{
	/**
	 * @param boolean 
	 */
	public function __construct($useOutputBuffer = true)
	{
		if ($useOutputBuffer) {
			ob_start();
		}
	}
	
	/**
	 * Send http status to client
	 *
	 * @param int $status
	 * @return Lamb_App_Response
	 */
	public function sendHttpStatus($status)
	{
		static $_status = array(
			// Informational 1xx
			100 => 'Continue',
			101 => 'Switching Protocols',
			// Success 2xx
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			// Redirection 3xx
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Moved Temporarily ',  // 1.1
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			// 306 is deprecated but reserved
			307 => 'Temporary Redirect',
			// Client Error 4xx
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			// Server Error 5xx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			509 => 'Bandwidth Limit Exceeded'
		);
		if(array_key_exists($status, $_status)) {
			header('HTTP/1.1 '.$status.' '.$_status[$status]);
		}
		return $this;	
	}
	
	/**
	 * Redirect to target url 
	 *
	 * @param string $url
	 * @return void
	 */
	public function redirect($url)
	{
		header('Location:'. $url);
		exit();	
	}
	
	/**
	 * Echo buffer and flush
	 *
	 * @param string $data
	 * @return Lamb_App_Response
	 */
	public function fecho($data)
	{
		echo $data;
		ob_flush();
		flush();
		return $this;	
	}
	
	/**
	 * Echo data and exit
	 *
	 * @param string $data
	 * @return Lamb_App_Response
	 */
	public function eecho($data)
	{
		echo $data;
		exit;
	}
	
	/**
	 * @param string $name
	 * @param string $val
	 * @param int $life second
	 * @param string $domain 
	 * $param string $path
	 * @return boolean
	 */
	public function setcookie($name, $val, $life=0, $domain = '', $path = '/')
	{
		return setcookie($name, $val, (time() + $life), $path, $domain);
	}

	/**
	 * @param string $name
	 * @param string $val
	 * @param int $life second
	 * @param string $domain 
	 * $param string $path
	 * @return boolean
	 */	
	public function P3PSetcookie($name, $val, $life = 0, $domain = '', $path = '/')
	{
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		return $this->setcookie($name, $val, $life, $domain, $path);
	}
	
	/**
	 * 鍚孞avascript镄别ncodeURIComponent
	 *
	 * @param string
	 * @return string
	 */
	public static function encodeURIComponent($source)
	{
		$charset = Lamb_App::getGlobalApp()->getCharset();
		if ($charset === Lamb_App::CHARSET_UTF8) {
			return rawurlencode($source);
		} else {
			//return $source;
			return rawurlencode(iconv($charset, 'utf-8//IGNORE', $source));
		}
	}

	/**
	 * 鍚孞avascript镄刣ecodeURIComponent
	 *
	 * @param string
	 * @return string
	 */	
	public static function decodeURIComponent($source)
	{
		$charset = Lamb_App::getGlobalApp()->getCharset();
		if ($charset === Lamb_App::CHARSET_UTF8) {
			return rawurldecode($source);
		} else {
			return iconv('utf-8', $charset.'//IGNORE', rawurldecode($source));
		}		
	}
		
}