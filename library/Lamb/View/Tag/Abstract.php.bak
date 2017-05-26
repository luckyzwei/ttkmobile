<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_View_Tag
 */
abstract class Lamb_View_Tag_Abstract implements Lamb_View_Tag_Interface
{
	/**
	 * @var array id 注册表
	 */
	protected static $sRegistryMap = array();
	
	/**
	 * 根据指定的属性名，从属性源数据中获取属性值
	 * 并可以解析属性值中的PHP变量
	 * 
	 * @param string $strAttrName 属性名
	 * @param string $strAttrs 属性源数据
	 * @param boolean $bParseVar 是否解析属性值中的PHP变量
	 * @param boolean $hasPrev 解析后的变量前后是否要加单引号或者双引号
	 *							如果获取的属性值只会出现变量，则可以将其值设为false
	 *							如：sql='@$abc@' $hasPrev=false解析后 'sql' => $abc
	 *							如果属性值可能会出现变量与字符串的结合，则要将其值设true
	 *							如：sql='select * from @$table@' $hasPrev=true解析后 'sql' => 'select * from'.$table.''
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
	 * 解析属性值中的PHP变量
	 * 
	 * @param string $strAttrValue 解析后的属性值
	 * @param boolean $isNumber 属性值是否是数字
	 * @param string $hasPrev 对于属性值是字符串需要加定界符
	 * @return string
	 */
	public static function parseVar($strAttrValue ,$hasPrev = true, $strPrev="'", $isFunc = false)
	{
		$strVarPatt = $isFunc ? '/\{\#(.*?)\}/is' : '/@(\$.*?)@/is';
		return preg_replace($strVarPatt, $hasPrev ? $strPrev . '.$1.' . $strPrev : '$1', $strAttrValue);
	}
	
	/**
	 * 转义属性值中的\ '
	 * 
	 * @param string $str 要操作的数据
	 * @param int $num 反斜杠的个数，实际个数是$num*2 
	 * @return string
	 */
	public static function codeAddslashes($str,$num=1)
	{
		return preg_replace('/(\')/s',str_repeat('\\',$num*2).'$1',preg_replace('/\\\(?!\')/s','\\\\\\',$str));
	}
	
	/**
	 * 通过ID把数据注册以备另一个标签调用，如果$dataParam 为null 
	 * 且$id存在，则删除此ID的注册
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
	 * 获取已经注册的参数
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