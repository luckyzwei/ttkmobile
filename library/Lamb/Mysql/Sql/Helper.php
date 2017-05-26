<?php
/**
 * Lamb Framework
 *
 * @author 灏忕緤
 * @package Lamb_Mysql_Sql
 */
class Lamb_Mysql_Sql_Helper extends Lamb_Db_Sql_Helper_Abstract
{
	/**
	 * @override
	 */
	public function getLimitSql($sql, $nLimit, $nOffset = 0, $bIncludeUnion=false)
	{
		return $sql .= " limit $nOffset,$nLimit";	
	}
	
	/**
	 * @override
	 */
	public function escape($sql)
	{
		return mysql_escape_string($sql);
	}
	
	/**
	 * @override
	 */
	public function escapeBlur($sql)
	{
		return preg_replace('/([\[\]%^_-])/is','[$1]', $sql);
	}
	
	/**
	 * @override
	 */
	public function escapeBlurEncoded($sql)
	{
		return preg_replace('/([\[\]%^_-])/is','[$1]', $this->escape($sql));
	}
	
	/**
	 * @override
	 */	
	public function getPrePareLimitSql($sql, $bIncludeUnion = false)
	{
		return $sql .= ' limit :g_offset,:g_limit';
	}
	
	/**
	 * @override
	 */
	public function escapeField($field)
	{
		return '`' . $field . '`';
	}	 	 	 	 
}