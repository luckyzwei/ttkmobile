<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_View_Tag
 */
abstract class Lamb_View_Tag_Abstract implements Lamb_View_Tag_Interface
{
	/**
	 * @var array id 娉ㄥ唽琛?
	 */
	protected static $sRegistryMap = array();
	
	/**
	 * 镙规嵁鎸囧畾镄勫睘镐у悕锛屼粠灞炴€ф簮鏁版嵁涓幏鍙栧睘镐у€?
	 * 骞跺彲浠ヨВ鏋愬睘镐у€间腑镄凯HP鍙橀噺
	 * 
	 * @param string $strAttrName 灞炴€у悕
	 * @param string $strAttrs 灞炴€ф簮鏁版嵁
	 * @param boolean $bParseVar 鏄惁瑙ｆ瀽灞炴€у€间腑镄凯HP鍙橀噺
	 * @param boolean $hasPrev 瑙ｆ瀽鍚庣殑鍙橀噺鍓嶅悗鏄惁瑕佸姞鍗曞紩鍙锋垨钥呭弻寮曞佛
	 *							濡傛灉銮峰彇镄勫睘镐у€煎彧浼氩嚭鐜板彉閲忥紝鍒椤彲浠ュ皢鍏跺€艰涓篺alse
	 *							濡傦细sql='@$abc@' $hasPrev=false瑙ｆ瀽鍚?'sql' => $abc
	 *							濡傛灉灞炴€у€煎彲鑳戒细鍑虹幇鍙橀噺涓庡瓧绗︿覆镄勭粨鍚堬紝鍒栾灏嗗叾链艰true
	 *							濡傦细sql='select * from @$table@' $hasPrev=true瑙ｆ瀽鍚?'sql' => 'select * from'.$table.''
	 * @return string
	 */
	public static function getTagAttribute($strAttrName, $strAttrs, $bParseVar = true, $hasPrev = true)
	{
		$strPatt		=	'/\b'.$strAttrName.'=([\'"])(.*?)\1/is';
		if(!preg_match($strPatt,$strAttrs,$aMatches)) return false;
		$strAttrValue	=	self::codeAddslashes(trim($aMatches[2]));
		if(!$bParseVar) return $strAttrValue;
		return self::parseVar($strAttrValue, $hasPrev);	
	}
	
	/**
	 * 瑙ｆ瀽灞炴€у€间腑镄凯HP鍙橀噺
	 * 
	 * @param string $strAttrValue 瑙ｆ瀽鍚庣殑灞炴€у€?
	 * @param boolean $isNumber 灞炴€у€兼槸鍚︽槸鏁板瓧
	 * @param string $hasPrev 瀵逛簬灞炴€у€兼槸瀛楃涓查渶瑕佸姞瀹氱晫绗?
	 * @return string
	 */
	public static function parseVar($strAttrValue ,$hasPrev = true, $strPrev="'", $isFunc = false)
	{
		$strVarPatt = $isFunc ? '/\{\#(.*?)\}/is' : '/@(\$.*?)@/is';
		return preg_replace($strVarPatt, $hasPrev ? $strPrev . '.$1.' . $strPrev : '$1', $strAttrValue);
	}
	
	/**
	 * 杞箟灞炴€у€间腑镄刓 '
	 * 
	 * @param string $str 瑕佹搷浣灭殑鏁版嵁
	 * @param int $num 鍙嶆枩鏉犵殑涓暟锛屽疄闄呬釜鏁版槸$num*2 
	 * @return string
	 */
	public static function codeAddslashes($str,$num=1)
	{
		return preg_replace('/(\')/s',str_repeat('\\',$num*2).'$1',preg_replace('/\\\(?!\')/s','\\\\\\',$str));
	}
	
	/**
	 * 阃氲绷ID鎶婃暟鎹敞鍐屼互澶囧彟涓€涓爣绛捐皟鐢紝濡傛灉$dataParam 涓簄ull 
	 * 涓?id瀛桦湪锛屽垯鍒犻櫎姝D镄勬敞鍐?
	 *
	 * @param string | int $id
	 * @param mixed $dataParam
	 * @return boolean true -> success false -> fail or exists
	 */
	public static function registerById($id, $dataParam)
	{
		if ($dataParam === null) {
			if (array_key_exists($id, self::$sRegistryMap)) {
				unset(self::$sRegistryMap[$id]);
			}
		} else {
			self::$sRegistryMap[$id] = $dataParam;
		}
		return true;
	}
	
	/**
	 * 銮峰彇宸茬粡娉ㄥ唽镄勫弬鏁?
	 *
	 * @param string | id $id
	 * @return mixed if not found return null
	 */
	public static function getRegisterdById($id)
	{
		$ret = null;
		if (array_key_exists($id, self::$sRegistryMap)) {
			$ret = self::$sRegistryMap[$id];
		}
		return $ret;
	}
}