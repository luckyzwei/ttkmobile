<?php
/**
 * Lamb Framework
 * Lamb_Db_Sql_Helper_Abstract是SQL处理工具抽象类，主要用于
 * 获取分页SQL，编码SQL语句，由于每个SQL引擎处理的方式不一样
 * 因此将其抽象出来，具体引擎都要继承此抽象类
 *
 * @author 小羊
 * @package Lamb_Db_Sql_Helper
 */
abstract class Lamb_Db_Sql_Helper_Abstract
{

	/**
	 * 生成分页的SQL，传入的SQL语句不用包括分页的语句
	 *
	 * @param string $strSql
	 * @param int $nPageSize
	 * @param int $nPage
	 * @param boolean $bIncludeUnion
	 * @return string
	 */
	public function getPageSql($strSql, $nPageSize, $nPage = 1, $bIncludeUnion=false)
	{
		return $this->getLimitSql($strSql, $nPageSize, ($nPage-1) * $nPageSize, $bIncludeUnion);
	}
	
	/**
	 * 获取SQL语句中的所有列名
	 *
	 * @param string $sql
	 * @return string
	 */
	public function getSqlField($sql)
	{
		$aMatchs	=	array();
		$strFields	=	'';
		if(preg_match('/^select(.+?)from(.+?)/is', $sql, $aMatchs)){
			$strFields	=	$aMatchs[1];
		}
		return $strFields;
	}
	
	/**
	 * 判断SQL语句中是否含有UNION关键字
	 * 注：此方法不太可靠
	 *
	 * @param string $sql
	 * @return boolean 
	 */
	public function hasUnionKey($sql)
	{
		return strpos(strtolower($sql), ' union ') ? true : false;
	}	
	
	/**
	 * 生成获取指定offset以及固定长度记录的SQL语句 
	 *
	 * @param string $sql
	 * @param int $nLimit
	 * @param int $nOffset
	 * @param boolean $bIncludeUnion
	 * @return string
	 */
	abstract public function getLimitSql($sql, $nLimit, $nOffset = 0, $bIncludeUnion=false);
	
	/**
	 * 转义SQL语句中的非法字符
	 *
	 * @param string $sql
	 * @return string
	 */
	abstract public function escape($sql);
	
	/**
	 * 只转义模糊搜索的非法字符，不会调用escape转义
	 *
	 * @param string $sql
	 * @return string
	 */
	abstract public function escapeBlur($sql);
	
	/**
	 * 转义模糊搜索的非法字符，并调用escape转义
	 *
	 * @param string $sql
	 * @return string
	 */
	abstract public function escapeBlurEncoded($sql);
	
	/**
	 * 生成获取指定offset以及固定长度记录的预处理SQL语句 
	 * 注：SQL预处理语句中使用:g_limit做为设置固定记录长度参数名
	 * :g_offset作为设置偏移位置参数名
	 *
	 * @param string $sql
	 * @param boolean $bIncludeUnion
	 */
	abstract public function getPrePareLimitSql($sql, $bIncludeUnion = false);

	/** 
	 * 对列名进行转义，防止特殊关键字同列名同名
	 *
	 * @param string $field
	 * @return string 
	 */	
	abstract public function escapeField($field);
}