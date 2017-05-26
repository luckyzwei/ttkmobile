<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_View_Tag
 * @useage 
 *		{tag:Lamb_View_Tag_List sql='' | [table='' column='' where='' order='' group='' having=''] 
 			[include_union = '@$var@ | boolean'] 
 *			[is_page='@$var@ | boolean | int' page='@$var@ | int' pagesize='@$var@ | int'] | [offset='@$var@ | int' limit='@$var@ | int']
 *			[prepare_source='@$var@ | string']
 *			[cache_callback='@$var@ | string普通回调 | $string[解析后使用$GLOBALS], string数组回调 如果为null则禁用缓存' cache_time='@$var@ | int' cache_type='@$var@ | int' is_empty_cache='@$var@ | boolean | int' cache_id_suffix='@$var@ | string']
 *			[custom_handle='@$var@ | string普通回调 | $string[解析后使用$GLOBALS], string数组回调' | null]
 *			[db_callback='@var@ | string普通回调 | $string[解析后使用$GLOBALS], string数组回调 | null']
 *			[id='@$var@ | int']
 *			[auto_index_prev='@$var@ | int']
 *			[empty_str='@var@ | string']
 *		}
 *			<html>asdasda<htmltag></htmltag>{field.name function="func(@this - 整条数据， @me - 当前数据) | 语句"}....<html>
 *		{/tag:Lamb_View_Tag_List}
 */
class Lamb_View_Tag_List extends Lamb_View_Tag_Abstract
{
	const CACHE_FILE = 1;
	
	const CACHE_MEM  = 2;
	
	const CACHE_HTML = 4;
	
	const DEFAULT_AUTO_ID = 0x23;
	
	protected static $sDefaultId = self::DEFAULT_AUTO_ID;
	
	public static $sCacheType = array(
						self::CACHE_FILE, self::CACHE_MEM, self::CACHE_HTML
					);
	/**
	 * Lamb_View_Tag_Interface implement
	 */
	public function parse($content, $property)
	{
		$ret = '';
		
		$sql = $is_page = $page = $pagesize = $limit = $offset = $include_union = $prepare_source = 
		$is_empty_cache = $cache_callback = $custom_handle = $db_callback = $cache_time = $cache_type =
		$id = $auto_index_prev = $empty_str = 'null';
		$trueValueArray = array('');
		//sql property
		if ( ($sql = self::getTagAttribute('sql', $property)) === false) {
			//table property
			if (($table = self::getTagAttribute('table', $property)) === false) {
				trigger_error('tag : ' . __CLASS__ . ' must be have sql or table and column property', E_USER_NOTICE);
				return $ret;
			}
			//column property
			if (($column = self::getTagAttribute('column', $property)) === false) {
				trigger_error('tag : ' . __CLASS__ . ' must be hava sql or table and column property', E_USER_NOTICE);
				return $ret;
			}
			
			$sql = 'select ' . $column . ' from ' . $table;
			unset($column, $table);
			//where
			if (($temp = self::getTagAttribute('where', $property)) !== false) {
				$sql .= ' where ' . $temp;
			}
			//group
			if (($temp = self::getTagAttribute('group', $property)) !== false) {
				$sql .= ' group by ' . $temp;
			}
			//having
			if (($temp = self::getTagAttribute('having', $property)) !== false) {
				$sql .= ' having ' . $temp;
			}
			//order
			if (($temp = self::getTagAttribute('order', $property)) !== false) {
				$sql .= ' order by ' . $temp;
			}
			unset($temp);
		}
		$sql = "'{$sql}'";

		//include_union
		if (($include_union = self::getTagAttribute('include_union', $property, true, false)) !== false) {
			$include_union = self::isTrue($include_union) ? 'true' : 'false';
		} else {
			$include_union = 'null';
		}
		
		//is_page
		if (($is_page = self::getTagAttribute('is_page', $property, true, false)) !== false && self::isTrue($is_page)) {
			$is_page = 'true';
			//page
			if (($page = self::getTagAttribute('page', $property, true, false)) === false) {
				trigger_error('tag : ' . __CLASS__ . ' must be hava page property if is_page property is true', E_USER_NOTICE);
				return $ret;
			}
			//pagesize
			if (($pagesize = self::getTagAttribute('pagesize', $property, true, false)) === false) {
				trigger_error('tag : ' . __CLASS__ . ' must be hava pagesize property if is_page property is true', E_USER_NOTICE);
				return $ret;
			}
		} else {
			$is_page = 'false';
			//offset
			if (($offset = self::getTagAttribute('offset', $property, true, false)) === false) {
				$offset = '0';
			}
			//limit
			if (($limit = self::getTagAttribute('limit', $property, true, false)) === false) {
				$limit = 'null';
			}
		}
		
		//prepare_source
		if (($prepare_source = self::getTagAttribute('prepare_source', $property, true, false)) === false) {
			$prepare_source = 'null';
		}
		
		//cache_callback
		if (($cache_callback = self::getTagAttribute('cache_callback', $property, true, false)) === false || $cache_callback == 'null') {
			$cache_callback = 'null';
		} else {
			if (strpos($cache_callback, ',')) {//数组回调
				$cache_callback = "array($cache_callback)";
			} else if (substr($cache_callback, 0, 1) != '$'){
				$cache_callback = "'{$cache_callback}'";
			}
			//cache_time
			if (($cache_time = self::getTagAttribute('cache_time', $property, true, false)) === false) {
				$cache_time = '0';
			}
			//cache_type
			if (($cache_type = self::getTagAttribute('cache_type', $property, true, false)) === false) {
				$cache_type = 'null';
			}
			//is_empty_cache
			if (($is_empty_cache = self::getTagAttribute('is_empty_cache', $property, true, false)) === false || !self::isTrue($is_empty_cache)) {
				$is_empty_cache = 'false';
			} else {
				$is_empty_cache = 'true';
			}
		}
		
		//cache_id_suffix
		if (($cache_id_suffix = self::getTagAttribute('cache_id_suffix', $property, true)) === false) {
			$cache_id_suffix = "''";
		} else {
			$cache_id_suffix = "'{$cache_id_suffix}'";
		}		
		
		//db_callback
		if (($db_callback = self::getTagAttribute('db_callback', $property, true, false)) !== false) {
			if (strpos($db_callback, ',')) {//数组回调
				$db_callback = "array($cache_callback)";
			} else if (substr($db_callback, 0, 1) != '$'){
				$db_callback = "'{$db_callback}'";
			}			
		} else {
			$db_callback = 'null';
		}
		
		//empty_str
		if (($empty_str = self::getTagAttribute('empty_str', $property)) === false) {
			$empty_str = "''";
		} else {
			$empty_str = "'{$empty_str}'";
		}
		
		//auto_index_prev
		if (($auto_index_prev = self::getTagAttribute('auto_index_prev', $property, true, false)) === false) {
			$auto_index_prev = '0';
		}
		
		//id
		if (($id = self::getTagAttribute('id', $property, true, false)) === false) {
			$id = 'null';
		} else {
			$id = "'$id'";
		}
		
		$show_result_callback = 'create_function(\'$item,$index\',\'return str_replace("#autoIndex#",$index,' . self::parseField($content) . ");')";
		$param = "array(
				'sql' => $sql,
				'include_union' => $include_union,
				'prepare_source' => $prepare_source,
				'is_page' => $is_page,
				'page' => $page,
				'pagesize' => $pagesize,
				'offset' => $offset,
				'limit' => $limit,
				'cache_callback' => $cache_callback,
				'cache_time' => $cache_time,
				'cache_type' => $cache_type,
				'cache_id_suffix' => $cache_id_suffix,
				'is_empty_cache' => $is_empty_cache,
				'id' => $id,
				'empty_str' => $empty_str,
				'auto_index_prev' => $auto_index_prev,
				'db_callback' => $db_callback,
				'show_result_callback' => $show_result_callback
			)";
		$ret = '<?php ' . __CLASS__ . "::main($param)?>";
		return $ret;
	}
	
	/**
	 * @param string $val
	 * @return boolean
	 */
	public static function isTrue($val)
	{
		static $aFlaseValue = array('false', '0', 0);
		return !in_array(strtolower($val), $aFlaseValue);
	}
	
	/**
	 * 解析{field.name}标签
	 *
	 * @param string $strContent
	 * @return string
	 */
	public static function parseField($strContent)
	{
		$strQuoteFlag =	self::codeAddslashes("'");
		$strFunctionCode = $strQuoteFlag;
		$strFieldPatt =	'/\{field\.(.*?)(?:\s+function=([\'"])(.*?)\2)?\}/is';
		$nSubstrStart = 0;
		
		if (preg_match_all($strFieldPatt, $strContent, $aFieldMatches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE)) {
			foreach ($aFieldMatches as $aFieldMatchItem) {
				$nSubstrEnd = $aFieldMatchItem[0][1];
				$strHtmlTemp = substr($strContent, $nSubstrStart, $nSubstrEnd - $nSubstrStart);
				$strHtmlTemp = self::parseVar(self::codeAddslashes($strHtmlTemp, 3));
				if (array_key_exists(3, $aFieldMatchItem)){
					$strPhpCode	=	str_replace('@this', '$item[\'' . $aFieldMatchItem[1][0] . '\']', str_replace('@me', '$item', $aFieldMatchItem[3][0]));
					$strHtmlTemp .=	$strQuoteFlag . '.('  . self::parseVar(self::codeAddslashes($strPhpCode)) . ').' . $strQuoteFlag;
				} else {
					$strHtmlTemp .=	$strQuoteFlag . '.$item[\\\''.$aFieldMatchItem[1][0] . '\\\'].' . $strQuoteFlag;	
				}
				$nSubstrStart = $nSubstrEnd + strlen($aFieldMatchItem[0][0]);
				$strFunctionCode .=	$strHtmlTemp;
			}
		}
		$strFunctionCode .=	self::parseVar(self::codeAddslashes(substr($strContent, $nSubstrStart), 3));
		$strFunctionCode .=	$strQuoteFlag;
		return $strFunctionCode;	
	}	
	
	/**
	 * @param array $aOptions = array(
	 * Sql config
	 *				'sql' => string,
	 *				['include_union' => boolean(default:null)]
	 *				['prepare_source' => array (default:null)]	 
	 * Page config
	 *				['is_page' => boolean(default:false)]
	 *				'page' => int(default:1)			
	 *				'pagesize' => int
	 * Non-Page config
	 *				['offset' => int]
	 *				['limit' => int]
	 * Cache config
	 *				'cache_callback' => callback 获取缓存对象 如果为null则表示禁用缓存
	 *									通过调用此callback获取Lamb_Cache_Interface对象，
	 *									将调用CRC32编码identity，由于文件缓存对象是以路径作为identity
	 *									而原生的Lamb_Cache_File::setIdentity设置是路径，但是此方法只是将
	 *									编译后SQL的CRC32值传入，因此需要使用者继承文件缓存对象重写setIdentity方法
	 *									调用该回调函数将传递int cache_type 1 = CACHE_FILE 2-CACHE_MEM
	 *				['cache_time' => int -1 - disabled 0 - default]
	 *				['cache_type' => int CACHE_FILE | CACHE_MEM 或者是CACHE_HTML的组合]
	 *				['cache_id_suffix' => string 缓存标识后缀]
	 *				['is_empty_cache' => boolean(default:true)]
	 * Global config
	 *				['id' => string | int 用于将List同Page链接的ID 默认将递增]
	 *				['empty_str' => string 当没有的数据的显示]  
	 *				['auto_index_prev' => int(default:0)]
	 *				['custom_handle' => callback 将数据库查询全交给此处理器 
	 *				调用该函数将传递一个数组array(sql => string, cache => null | Lamb_Cache_Interface, is_page => boolean
	 *					page=>int,pagesize=int,offset=>int,limit=>int, is_empty_cache=>boolean, prepare_source=array)
	 *				该函数一定要返回Lamb_Db_RecordSet_Interface对象	
	 *				]
	 *				['show_result_callback' => callback 调用该回调函数时候将传递一个包含单条记录的数组，第二个参数是auto_index_prev]
	 *				['db_callback' => callback 调用该回调函数获取数据库对象 为null则使用默认的数据库对象]
	 *				['return'=> false]
	 *			)
	 *
	 * @return void
	 * @throws Lamb_View_Tag_Exception
	 */
	public static function main(array $aOptions)
	{
		//default options
		$options = array(
			'include_union' => null,
			'is_empty_cache' => true,
			'prepare_source' => null,
			'is_page' => false,
			'page' => 1,
			'cache_time' => 0,
			'cache_type' => self::CACHE_FILE | self::CACHE_HTML,
			'cache_id_suffix' => '',
			'auto_index_prev' => 0,
			'return' => false,
			'db_callback' => null,
			'empty_str' => ''
		);
		Lamb_Utils::setOptions($options, $aOptions);
		//sql
		if (!isset($options['sql']) || empty($options['sql'])) {
			throw new Lamb_View_Tag_Exception('Invalid sql passed int Lamb_View_Tag_List::main()');
		}
		if (!$options['is_page'] && Lamb_Utils::isInt($options['offset'], true) && Lamb_Utils::isInt($options['limit'], true)) {
			$sqlHelper = Lamb_App::getGlobalApp()->getSqlHelper();
			$hasUnion = $options['include_union'] === null ? $sqlHelper->hasUnionKey($options['sql']) : $options['include_union'];
			if (is_array($options['prepare_source'])) {
				$options['prepare_source'][':g_limit'] = array($options['limit'] + $options['offset'], PDO::PARAM_INT);
				$options['prepare_source'][':g_offset'] = array($options['offset'], PDO::PARAM_INT);
				$options['sql'] = $sqlHelper->getPrePareLimitSql($options['sql'], $hasUnion);
			} else {
				$options['sql'] = $sqlHelper->getLimitSql($options['sql'], $options['limit'], $options['offset'], $hasUnion);
			}
		}
		//page id
		if ($options['is_page']) {
			$pageParam = array('page' => $options['page'], 'pagesize' => $options['pagesize'], 'num' => 0);
			if (!isset($options['id']) || null === $options['id']) {
				$options['id'] = self::$sDefaultId ++ ;
			}
		}
		//cache
		$cache = null;
		if (isset($options['cache_callback']) && is_callable($options['cache_callback']) && $options['cache_time'] >= 0) {
			$cache_type = null;//self::CACHE_FILE | self::CACHE_HTML;
			if (isset($options['cache_type']) && Lamb_Utils::isInt($options['cache_type'], true)) {
					$cache_type = $options['cache_type'];
			}
			$cache = call_user_func($options['cache_callback'], $cache_type & self::CACHE_HTML ? ($cache_type & ~self::CACHE_HTML) : $cache_type);
			if (!($cache instanceof Lamb_Cache_Interface)) {
				throw new Lamb_View_Tag_Exception('The cache object returned by cache_callback is not instance of Lamb_Cache_Interface');
			}
			if ($options['cache_time'] > 0) {
				$cache->setCacheTime($options['cache_time']);
			}
			if ($options['is_page']) {
				$identity = Lamb_Db_Select::getSqlIdentity($options['sql'], $options['page'], $options['pagesize'], $options['prepare_source']);
			} else {
				$identity = Lamb_Db_Select::getSqlIdentity($options['sql'], null, null, $options['prepare_source']);
			}
			$cache->setIdentity($identity . $options['cache_id_suffix']);
			
			//read cache from html if necessary
			if (($cache_type & self::CACHE_HTML) && null !== ($html = $cache->read())) {
				if ($options['is_page']) {
					$allCount = 0;
					$pos = strpos($html, '|');
					if ($pos !== false) {
						$allCount = (int)substr($html, 0, $pos);
						$html = substr($html, $pos + 1);
						if ($allCount > 0) {
							$pageParam['num'] = $allCount;
							self::registerById($options['id'], $pageParam);
						}
					}
				}
				if ($options['return']) {
					return $html;
				}
				echo $html;
				return ;				
			}
		}

		//get data and cache
		if (isset($options['custom_handle']) && is_callable($options['custom_handle'])) { //use custom handler get data
			$objRecordSet = call_user_func($options['custom_handle'], 
											array(
												array(
													'sql' => $aOptions['sql'],
													//如果缓存类型是HTML，则不传入缓存对象
													'cache' => $cache && !($cache_type & self::CACHE_HTML) ? $cache : null,
													'is_page' => $options['is_page'],
													'limit' => $options['limit'],
													'offset' => $options['offset'],
													'page' => $options['page'],
													'pagesize' => $options['pagesize'],
													'prepare_source' => isset($aOptions['prepare_source']) ? $aOptions['prepare_source'] : null,
													'is_empty_cache' => $options['is_empty_cache']
												),
												&$allCount
											));
			if ($allCount > 0) {
				$pageParam['num'] = $allCount;
				self::registerById($options['id'], $pageParam);	
			}
		} else {//use Lamb_Db_Select get data
			$select = new Lamb_Db_Select($options['sql'], $options['db_callback']);
			//如果缓存类型是HTML，则不传入缓存对象
			if ($cache && !($cache_type & self::CACHE_HTML)) {
				$select->setOrGetCache($cache);
			}
			$select->setSqlHasUnion($options['include_union'])
					->setOrGetIsEmptyCached($options['is_empty_cache']);
			if ($options['is_page']) {
				$allCount = 0;
				$objRecordSet = $select->pageQuery($options['page'], $options['pagesize'], $allCount, $options['prepare_source']);
				if ($allCount > 0) {
					$pageParam['num'] = $allCount;
					self::registerById($options['id'], $pageParam);				
				}
			} else {
				$objRecordSet = $select->query($options['prepare_source']);
			}
		}

		$html = '';
		if ($objRecordSet->getRowCount() > 0) {//如果有数据
			foreach ($objRecordSet as $item) {
				$html .= call_user_func($options['show_result_callback'], $item, $options['auto_index_prev']++);
			}
		} else {//如果没有数据
			$html = $options['empty_str'];
		}
		
		//写入HTML缓存
		if ($cache && ($cache_type & self::CACHE_HTML)) {
			if ($objRecordSet->getRowCount() > 0 || $options['is_empty_cache']) {
				$cache->write( ($options['is_page'] ? $allCount . '|' : '') . $html);
			}
		}
		$objRecordSet = null;
		if ($options['return']) {
			return $html;
		}
		echo $html;
	}
}