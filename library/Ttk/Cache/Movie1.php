<?php
class Ttk_Cache_Movie
{
	/**
	 * 公用的配置
	 */
	protected $mPublicCfg;
	
	/**
	 * @var array
	 * 要缓存的列
	 */
	protected static $sColumns = array(
		'id' => 1, 'type' => 1, 'name' => 1, 'pic' => 1,
		'year' => 1, 'actors' => 1, 'directors' => 1, 'area' => 1,
		'mark' => 1, 'is_end' => 1, 'point' => 1, 'point_num' => 1,
		'point_all' => 1, 'month_point' => 1, 'is_lock' => 1, 
		'view_num' => 1, 'week_num' => 1, 'month_num' => 1,
		'sort_id' => 1, 'gemo' => 1, 'update_time' => 1, 'tag' => 1,
		'status' => 1,'pinyin' => 1, 'description' => 1
	);
	
	public function __construct()
	{
		$this->mPublicCfg = Lamb_Registry::get(PUBLIC_CFG);
	}
	
	/**
	 * 获取多个影片数据
	 * 
	 * @param string | array $mids 多个影片的mid，如果是字符串，以英文逗号隔开id，如果是字符串，以英文逗号隔开
	 * @param string | array $fields 指定返回的列，如果是字符串，以英文逗号隔开
	 * 
	 * @return array(
	 * 	影片1的信息，array，如果没有则为空
	 * 	影片2的信息，array
	 * 	....
	 * )
	 * 
	 * 性能分析，假如$mids的个数为m，$fields的个数为n个
	 * 常量：可缓存的字段数目i，不可缓存的字段数目j，附加表的数目k
	 * 	1.对于只获取可以缓存的列：
	 * 		a.最少的循环次数为全部缓存了，m*n
	 * 		b.最多的循环次数为全部都没缓存，m + m*i*n
	 * 	2.如果要获取可缓存的列：
	 * 		a.当可缓存的字段全部缓存了 时候，m*n+2+i+j+m*2
	 * 		b.当可缓存字段都没缓存：m+2+i+j+m*(n+2)
	 */
	public function get($mids, $fields, $isNotExistsFillNull = true)
	{
		if (is_string($fields)) {
			$fields = explode(',', $fields);
		}
		
		if (is_string($mids)) {
			$mids = explode(',', $mids);
		}
		
		$result = array();
		
		//还没有缓存的影片
		$noCacheMovies = array();	
		foreach ($mids as $index => $mid) {
			
			$cache = $this->getCache($mid);
			
			//if ($cache->isCached())
			if (1 == 2) {
				$data = $cache->read();
				
				//只返回指定列
				$newdata = array();				
				foreach ($fields as $_fields) {
					if (array_key_exists($_fields, $data)) {
						$newdata[$_fields] = $data[$_fields];
					}	
				}
				$result[$index] = $newdata;
			} else {
				$noCacheMovies[$index] = $mid;
			}
		}
		
		$allFields = implode(',', array_keys(self::$sColumns));		
		
		if (count($noCacheMovies)) {
			$noCacheMids = implode(',', array_values($noCacheMovies));
			$sql = "select {$allFields} from movie a where id in ({$noCacheMids})";	
			$db = Ttk_Db::get('movie');
			$noCacheData = $db->query($sql)->toArray();
			
			foreach ($noCacheMovies as $index => $mid) {
				$isFind = false;
				
				foreach ($noCacheData as $i => $_data) {
					if ($_data['id'] == $mid) {
						$isFind = true;
						$cache = $this->getCache($mid);
						$cache->write($noCacheData[$i]);
						
						//只返回指定列						
						$newdata = array();
						foreach ($fields as $_fields) {
							$newdata[$_fields] = $_data[$_fields];	
						}
						$result[$index] = $newdata;							
						unset($noCacheData[$i]);
						break;
					}
				}
				
				if (!$isFind && $isNotExistsFillNull) {
					$result[$index] = null;
				}
			}
		}
		return array_values($result);
	}
	
	/**
	 * 获取存放影片的缓存
	 */
	public function getCache($mid) 
	{
		return Ttk_Cache_Factory::getCache()->setIdentity("MOVIE_{$mid}")
					->setCacheTime($this->mPublicCfg['movie_cache_expire']);	
	}
	
	/**
	 * 清除影片缓存
	 */
	public static function clear($mid)
	{
		$obj = new self;
		$cache = $obj->getCache($mid);
		$cache->isCached() && $cache->flush();
	}	
}