<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb
 */
class Lamb_Debuger
{
	/**
	 * @var array $s_aLabels 淇濆瓨璋冭瘯镙囩
	 */
	protected static $s_aLabels = array(); 
	
	/**
	 * 涓€涓皟璇旷殑寮€濮嬶紝鍙互鎸囧畾镙囩鍖哄垎涓嶅悓镄勮皟璇?
	 *
	 * @param string $label
	 * @return void
	 */
	public static function start($label='debuger')
	{
		self::$s_aLabels[$label]['beginTime']	=	microtime(true);
		self::$s_aLabels[$label]['beginMemory']	=	memory_get_usage();
	}
	
	/**
	 * 涓€涓皟璇旷殑缁撴潫锛屽彲浠ユ寚瀹氭爣绛惧尯鍒嗕笉鍚岀殑璋冭瘯
	 *
	 * @param string $label
	 * @return void
	 */
	public static function end($label='debuger')
	{
		self::$s_aLabels[$label]['endTime']		=	microtime(true);
		self::$s_aLabels[$label]['endMemory']	=	memory_get_usage();
		echo '<div style="text-align:center;width:100%">Process '.$label.',Times : <font color="green">'.sprintf('%0.6f',self::$s_aLabels[$label]['endTime']-
		self::$s_aLabels[$label]['beginTime']).' s </font>';
		echo 'Memories <font color="green">'.sprintf('%0.6f',(self::$s_aLabels[$label]['endMemory']-self::$s_aLabels[$label]['beginMemory'])/1024).' KB</font></div>';
	}
	
	/**
	 * 杈揿嚭鍙橀噺镄勪俊鎭?
	 */
	public static function dump($var, $echo=true, $label=null, $strict=true)
	{
		@ob_clean();
	    $label = ($label === null) ? '' : rtrim($label) . ' ';
	    if (!$strict) {
	        if (ini_get('html_errors')) {
	            $output = print_r($var, true);
	            $output = "<pre>" . $label . htmlspecialchars($output, ENT_QUOTES) . "</pre>";
	        } else {
	            $output = $label . print_r($var, true);
	        }
	    } else {
	        ob_start();
	        var_dump($var);
	        $output = ob_get_clean();
	        if (!extension_loaded('xdebug')) {
	            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        }
	    }
	    if ($echo) {
	        echo($output);
			exit();
	    }else
	        return $output;	
	}
	
	/**
	 * 杈揿嚭鍙橀噺镄勪俊鎭?
	 */
	public static function debug($str)
	{
		@ob_clean();
		if(is_array($str))
			print_r($str);
		elseif(is_bool($str))
		{
			if($str)
				echo "this value is true";
			else
				echo "this value is false";
		}
		else
			var_dump($str);		
		exit();		
	}
}