<?php
/**
 * 用户列表检测操作的fields
 * 
 */
class Ttk_Fields_CheckForList
{
	/** 
	 * @var array
	 * 可支持的列
	 */
	protected $allowFields = array(); 
	
	/**
	 * @var string
	 * 待返回的列
	 */
	protected $fields = '';
	
	/**
	 * @var array 
	 * 默认返回的列
	 */
	protected $default = array();
	
	/**
	 * @var array
	 * 构造好的列
	 */
	protected $_builder = null;
	
	/**
	 * @var array 
	 * 被移除的列
	 */
	protected $_removed = array();
	
	public function __construct($fields = null, $allowFields = null, $default = null)
	{
		if ($fields) {
			$this->setFields($fields);
		}
		
		if ($allowFields) {
			$this->setAllowFields($allowFields);
		}
		
		if ($default) {
			$this->setDefault($default);
		}
	}
	
	/**
	 * 添加可允许字段的分段数组
	 * 
	 * @param array $allowFieldsSection 可允许获取数组的分段
	 * @return self
	 */
	public function addAllowFieldsSection(array $allowFieldsSection)
	{
		$this->allowFields[] = $allowFieldsSection;
		$this->_builderFields();
		return $this;
	}
	
	/**
	 * 直接替换可允许获取数组
	 * 
	 * @param array $allowFields
	 * @return self
	 */
	public function setAllowFields(array $allowFields)
	{
		$this->allowFields = $allowFields;
		$this->_builderFields();
		return $this;
	}
	
	/**
	 * 设置待返回的所有列
	 * @param string $fields
	 * @return self
	 */
	public function setFields($fields)
	{
		$this->fields = $fields;
		$this->_builderFields();
		return $this;
	}
	
	/**
	 * 设定默认返回的列
	 * @param array | string $default
	 * @return self
	 */
	public function setDefault($default)
	{
		if (is_string($default)) {
			$default = explode(',', $default);
		}
		$this->default = $default;
		$this->_builderFields();
		return $this;
	}
	
	/**
	 * 获取默认的字段
	 * @return array
	 */
	public function getDefault()
	{
		return $this->default;
	}
	
	
	/**
	 * 检测fields是否某个字段($search)，并且替换
	 * @param string $search 要查找的字段
	 * @return boolean 是否存在该字段
	 */	
	public function findAndReplace($search)
	{
		if (!$this->_builder) {
			return false;
		}
		
		foreach ($this->_builder as $index => $item) {
			if (array_key_exists($search, $item)) {
				if (!in_array($search, $this->_removed)) {
					$this->_removed[] = $search;
				}
				unset($this->_builder[$index][$search]);
				return true;
			}
		}
		return false;		
	}
	
	/**
	 * 获取字符串型的字段
	 * @return string | null | 空字符
	 */
	public function toString()
	{
		$ret = '';

		if ($this->_builder) {
			$ret = array();
			foreach ($this->_builder as $item) {
				$ret = array_merge($ret, array_values($item));
			}	
			$ret = implode(',', $ret);
		}
		return $ret;			
	}
	
	/**
	 * 根据分段的allowFields转换成对应复合条件的fields
	 * 每个复合分段的结果索引同allowFields的索引一样
	 * 
	 * @return array
	 */
	public function toArray()
	{
		$ret = null;
		if ($this->_builder) {
			$ret = array();
			foreach ($this->_builder as $index => $item) {
				$ret[$index] = implode(',', array_values($item));
			}
		}
		return $ret;
	}

	
	/**
	 * 是否含有指定的字段
	 * @param string $field
	 * @return boolean
	 */
	public function hasField($field)
	{
		if (!$this->_builder) {
			return false;
		}
		
		foreach ($this->_builder as $item) {
			if (array_key_exists($field, $item)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 追加字段
	 * @param string $field
	 * @return self
	 */
	public function appendField($field)
	{
		$key = array_search($field, $this->_removed);
		if ($key !== false) {
			unset($this->_removed[$key]);
		}
		
		if ($this->hasField($field)) {
			return $this;
		}
		
		if (!$this->fields) {
			$this->default[] = $field;
		} else {
			$this->fields .= ',' . $field;
		}
		
		$this->_builderFields();
		return $this;
	}	
	
	/**
	 * 构造有效的列
	 */
	protected function _builderFields()
	{
		$result = array();
		if (!$this->allowFields) {
			$this->_builder = null;
			return null;
		}
		
		if (!$this->fields && !$this->default) {
			$this->_builder = null;
			return null;
		}

		if ($this->fields) {
			if ('+' == substr($this->fields, 0, 1)) {
				$fields = array();
				
				foreach (explode(',', substr($this->fields, 1)) as $item) {
					if (!in_array($item, $this->default)) {
						$fields[] = $item;
					}
				} 
				
				$fields = array_merge($fields, $this->default);
			} else {
				$fields = explode(',', $this->fields);
			}
		} else {
			$fields = $this->default;
		}	
		
		if (!count($fields)) {
			$this->_builder = null;
			return null;
		}
		
		$isEmpty = true;
		foreach ($fields as $field) {
			$isFind = false;
			
			if (in_array($field, $this->_removed)) {
				continue;
			}
			
			foreach ($this->allowFields as $index => $_fields) {
				if (isset($_fields[$field])) {
					if (!isset($result[$index])) {
						$result[$index] = array();
					}
					
					if ($_fields[$field] == 1) {
						$newfield = $field;
					} else {
						$newfield = $_fields[$field];
					}
					
					$result[$index][$field] = $newfield;
					$isFind = true;
					$isEmpty = false;
				}
			}	
			
			if (!$isFind) {
				$this->_builder = null;
				return null;
			}
		}

		if ($isEmpty) {
			$this->_builder = null;
			return null;			
		}
		
		$this->_builder = $result;
		return $result;			
	}	
}
