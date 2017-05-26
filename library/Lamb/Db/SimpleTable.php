<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Db
 */
class Lamb_Db_SimpleTable implements Serializable,Lamb_Db_RecordSet_CustomInterface
{
	/**
	 * @var string
	 */
	public $mSavePath = null;
	
	/**
	 * @var array 
	 */
	protected $mEntity = array();
	
	/**
	 * @var int
	 */
	protected $mRowCount = 0;
	
	/**
	 * @var int
	 */
	protected $mCurrentPos = 0;
	
	/**
	 * @param array $columns = array(
	 *			string | array, .....
	 *			濡傛灉鏄瓧绗︿覆鍒椤彧鏄垪鍚?
	 *			濡傛灉鏄暟缁勫垯鎸夌収濡备笅镙煎纺锛歛rray('name' =>string, auto_incrment => boolean[false], 
	 *			incremnt_feed => int[1], incremnt_step => int[1], unique_index=>boolean[false],
	 *			default => mixed)
	 * 		)
	 */
	public function __construct(array $columns = null)
	{
		if ($columns) {
			$this->addColumnsBatch($columns);
		}
	}
	
	/**
	 * @param string $columnName
	 * @return boolean
	 */
	public function hasColumn($columnName)
	{
		return array_key_exists(strtolower($columnName), $this->mEntity);
	}
	
	/**
	 * @param array $columns
	 * @return void
	 */
	public function addColumnsBatch(array $columns)
	{
		foreach ($columns as $column) {
			$this->addColumn($column);
		}	
	}
	
	/**
	 * @param string | array $column = array(string | array('name' => string, auto_incrment => boolean[false], 
	 *	incremnt_feed => int[1], incremnt_step => int[1], unique_index=>boolean[false], default => mixed[null])))
	 * @return boolean
	 */
	public function addColumn($column)
	{
		$defaultColumn = array(
				'auto_increment' => false,
				'increment_feed' => 1,
				'increment_step' => 1,
				'unique_index' => false,
				'data' => array(),
				'default' => null
			);
		if (is_string($column)) {
			if ($this->hasColumn($column)) {
				return false;
			}
			$this->mEntity[$column] = $defaultColumn;
			return true;
		} else if (is_array($column) && !$this->hasColumn($column['name'])) {
			Lamb_Utils::setOptions($defaultColumn, $column);
			if ($defaultColumn['auto_increment']) {
				$defaultColumn['auto_index'] = $defaultColumn['increment_feed'];
			}
			unset($defaultColumn['name']);
			$this->mEntity[$column['name']] = $defaultColumn;
			return true;
		}
		return false;
	}
	
	/**
	 * @param array $columnOptions = array(
	 *		0 => 鍒楀悕
	 *		1 => null 鍒犻櫎 | array(auto_increment =>, increment_feed =>, increment_step =>, unique_index=>, default=>)	
	 *	)
	 */
	public function editColumn(array $columnOptions)
	{
		if ($this->hasColumn($columnOptions[0])) {
			if (!isset($columnOptions[1]) || null === $columnOptions[1]) {
				unset($this->mEntity[$columnOptions[0]]);
			} else {
				$column = &$this->mEntity[$columnOptions[0]];
				if (isset($columnOptions['auto_increment'])) {
					$column['auto_increment'] = $columnOptions['auto_increment']; 
				}
				if (isset($columnOptions['increment_feed'])) {
					$column['increment_feed'] = $columnOptions['increment_feed']; 
				}
				if (isset($columnOptions['increment_step'])) {
					$column['increment_step'] = $columnOptions['increment_step']; 
				}
				if (isset($columnOptions['unique_index'])) {
					$column['unique_index'] = $columnOptions['unique_index']; 
				}
				if (isset($columnOptions['default'])) {
					$column['default'] = $columnOptions['default']; 
				}				
				unset($column);								
			}
		}
		return $this;
	}
	
	/**
	 * @return Lamb_Db_SimpleTable
	 */
	public function truncate()
	{
		foreach (array_keys($this->mEntity) as $column) {
			$this->mEntity[$column]['data'] = array();
			unset($this->mEntity[$column]['auto_index']);
		}
		$this->mRowCount = 0;
		return $this;
	}
	
	/** 
	 * @param array $values = array('column1' => val1, column2=>val2)
	 * @param array $condition = array(
	 * 		array('or', 'column1=val1'),
	 *		array('and', 'column2=va2')
	 * )
	 * @return int the effect rows
	 * @throws Lamb_Db_Exception
	 */
	public function update(array $values, array $condition = null)
	{
		$effectRows = 0;
		if ($this->getRowCount() > 0) {
			foreach (array_keys($values) as $column) {
				if (!$this->hasColumn($column)) {
					throw new Lamb_Db_Exception("Column \"{$column}\" is not exists");
					return $effectRows;
				}
			}
			if ($condition) {
				$indexs = $this->where($condition);
				foreach ($indexs as $i) {
					foreach ($values as $column => $val) {
						$this->mEntity[$column]['data'][$i] = $val;
					}				
				}
				$effectRows = count($indexs);
			} else {
				for ($i = 0; $i < $this->mRowCount; $i ++) {
					foreach ($values as $column => $val) {
						$this->mEntity[$column]['data'][$i] = $val;
					}
				}
				$effectRows = $this->mRowCount;
			}
		}
		return $effectRows;
	}
	
	/**
	 * @param array $data = array(
	 *			'column1' => data1, 'column2' => data2,....
	 * 		)
	 * @return Lamb_Db_SimpleTable
	 * @throws Lamb_Db_Exception
	 */
	public function insert(array $data)
	{
		$newdata = array();
		$identities = array();
		
		foreach (array_keys($this->mEntity) as $column) {
			$columnEntity = $this->mEntity[$column];
			if (array_key_exists($column, $data)) {
				if ($columnEntity['auto_increment']) {
					throw new Lamb_Db_Exception("the \"{$column}\" column is auto increment,can not be edit");
				}
				if ($columnEntity['unique_index'] && 
					array_search($data[$column], $columnEntity['data']) !== false) {
					throw new Lamb_Db_Exception("the \"{$column}\" column is a unique column,the value \"{$data[$column]}\" is exits");
				}
				$newdata[$column] = $data[$column];
			} else {
				if ($columnEntity['auto_increment']) {
					$newdata[$column] = isset($columnEntity['auto_index']) ? $columnEntity['auto_index'] : $columnEntity['increment_feed'];
					$identities[$column] = $newdata[$column] + $columnEntity['increment_step'];
				} else {
					$newdata[$column] = $columnEntity['default'];
				}
			}
		}
		
		foreach ($newdata as $key => $val) {
			array_push($this->mEntity[$key]['data'], $val);
			if (array_key_exists($key, $identities)) {
				$this->mEntity[$key]['auto_index'] = $identities[$key];
			}
		}
		$this->mRowCount ++;
		return $this;
	}
	
	/**
	 * @param string $columns eg: column1,column2 | *
	 * @param array $condition = array(
	 * 		array('or', 'column1=val1'),
	 *		array('and', 'column2=va2')
	 * )
	 * @return array	 
	 * @throws Lamb_Db_Exception
	 */
	public function select($columns = '*', array $condition = null, &$count = 0)
	{
		$aRet = array();
		if ($this->getRowCount() > 0) {
			if ($columns == '*') {
				$columns = array_keys($this->mEntity);
			} else {
				$columns = explode(',', $columns);
				foreach ($columns as $column) {
					if (!$this->hasColumn($column)) {
						throw new Lamb_Db_Exception("Column \"{$column}\" is not exists");
						return $aRet;
					}
				}
			}
			if ($condition) {
				foreach ($this->where($condition) as $i) {
					$temp = array();
					foreach ($columns as $column) {
						$temp[$column] = $this->mEntity[$column]['data'][$i];
					}
					$aRet[] = $temp;					
				}
			} else {
				$column = $columns[0];
				$columns = array_slice($columns, 1);
				foreach ($this->mEntity[$column]['data'] as $key => $item) {
					$temp = array($column => $item);
					foreach ($columns as $_column) {
						$temp[$_column] =  $this->mEntity[$_column]['data'][$key];
					}
					$aRet[] = $temp;
				}
			}
			$count = count($aRet);
		}
		return $aRet;
	}
	
	/**
	 * @param array $condition = array(
	 * 		array('or', 'column1=val1'),
	 *		array('and', 'column2=va2')
	 * )
	 * @return int the effect rows
	 */
	public function delete(array $condition = null)
	{
		$effectRows = 0;
		if ($condition) {
			$indexs = $this->where($condition);			
			$effectRows = count($indexs);
			foreach ($indexs as $i) {
				foreach (array_keys($this->mEntity) as $column) {
					unset($this->mEntity[$column]['data'][$i]);
				}
				$this->mRowCount --;
			}
		} else {
			$effectRows = $this->getRowCount();
			$this->truncate();
		}
		return $effectRows;
	}
	
	/**
	 * @param array $condition = array(
	 * 		array('or', 'column1=val1'),
	 *		array('and', 'column2=va2')
	 * )
	 * @return array the index of result
	 * @throws Lamb_Db_Exception
	 */
	public function where(array $conditions)
	{
		$aRet = array();
		foreach ($conditions as $condition) {
			$logic = $condition[0];
			$expr = $condition[1];
			if (($pos = strpos($expr, '=')) === false || $pos + 1 >= strlen($expr)) {
				throw new Lamb_Db_Exception("Expression \"{$expr}\" is illegal");
			}
			$column = substr($expr, 0, $pos);
			$val = substr($expr, $pos + 1);
			if (!$this->hasColumn($column)) {
				throw new Lamb_Db_Exception("Expression \"{$expr}\" is illegal,column \"{$column}\" is not exists");
			}
			$columnEntity = $this->mEntity[$column];
			$aSearchResult = array();
			if ($columnEntity['unique_index']) {
				if (($ret = array_search($val, $columnEntity['data'])) !== false) {
					$aSearchResult[] = $ret;				
				}
			} else {
				foreach ($columnEntity['data'] as $key => $dataval) {
					if ($val == $dataval) {
						$aSearchResult[] = $key;
					}
				}
			}
			$intersect = array_intersect($aRet, $aSearchResult);;
			if ($logic == 'or') {
				$aRet = array_merge($aRet, array_diff($aSearchResult, $intersect));
			} else {
				$aRet = $intersect;
			}
		}
		return $aRet;
	}
	
	/** 
	 * @return array
	 */
	public function getRawData()
	{
		return $this->mEntity;
	}
	
	/**
	 * Lamb_Db_RecordSet_Interface implemention
	 */
	public function getRowCount()
	{
		return $this->mRowCount;
	}
	
	/**
	 * Lamb_Db_RecordSet_Interface implemention
	 */	
	public function getColumnCount()
	{
		return count(array_keys($this->mEntity));
	}
	
	/**
	 * Lamb_Db_RecordSet_Interface implemention
	 */
	public function toArray()
	{
		return $this->select('*');
	}	
	
	/**
	 * Lamb_Db_RecordSet_Interface implemention
	 */
	public function count()
	{
		return $this->getRowCount();
	}
	
	/**
	 * Iterator implemention
	 */
	public function current()
	{
		return $this->getRowByIndex($this->mCurrentPos);
	}	

	/**
	 * Iterator implemention
	 */	
	public function rewind()
	{
		$this->mCurrentPos = 0;
	}
	
	/**
	 * Iterator implemention
	 */	
	public function key()
	{
		return $this->mCurrentPos;
	}
	
	/**
	 * Iterator implemention
	 */	
	public function next()
	{
		$this->mCurrentPos ++;
	}
	
	/**
	 * Iterator implemention
	 */	
	public function valid()
	{
		return $this->mCurrentPos < $this->getRowCount();
	}
	
	/**
	 * @param string $path if $path = null then will use $this->mSavePath
	 * @return boolean
	 */
	public function save($path = null)
	{
		$ret = false;
		if (!$path) {
			$path = $this->mSavePath;
		}
		if ($path) {
			$ret = Lamb_IO_File::putContents($path, serialize($this));
		}
		return $ret;
	} 
	
	/**
	 * Serializable implemention
	 */
	public function serialize()
	{
		return serialize(array('data' => $this->mEntity, 'len' => $this->mRowCount));
	}
	
	/**
	 * Serializable implemention
	 */
	public function unserialize($source)
	{	
		$temp = unserialize($source);
		if (is_array($temp) && isset($temp['data'], $temp['len'])) {
			$this->mEntity = $temp['data'];
			$this->mRowCount = 0;
			if (Lamb_Utils::isInt($temp['len'], true)) {
				$this->mRowCount = $temp['len'];
			}
		}
	}
	
	/**
	 * @param int $index
	 * @return array
	 */
	protected function getRowByIndex($index)
	{
		$aRet = array();
		if (Lamb_Utils::isInt($index, true) && $index < $this->getRowCount()) {
			foreach (array_keys($this->mEntity) as $column) {
				$aRet[$column] = $this->mEntity[$column]['data'][$index];
			}
		}
		return $aRet;
	}
} 