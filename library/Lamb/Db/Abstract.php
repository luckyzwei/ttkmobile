<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Db
 */
abstract class Lamb_Db_Abstract extends PDO
{
	/**
	 * 镓归噺镄勪粠$aPrepareSource涓粦瀹氶澶勭悊链煎埌$stmt瀵硅薄涓?
	 *
	 * @param &PDOStatement $stmt
	 * @param array $aPrepareSource [
	 *									SQL鍙傛暟鍚嶏紝鍙傛暟鍚嶅搴旗殑链硷紝链肩殑绫诲瀷
	 *								]
	 * @return void
	 */
	public static function batchBindValue(PDOStatement &$stmt, array $aPrepareSource)
	{
		foreach ($aPrepareSource as $strKey => $aItem) {
			$stmt->bindValue($strKey, $aItem[0], $aItem[1]);
		}
		unset($stmt);
	}
	
	/**
	 * 浣跨敤婊氩姩镄勬父镙囨煡璇㈣褰曢泦
	 * 娉细姝ゆ柟娉曚细娑堣€椾竴瀹氱殑镐ц兘锛屽父鐢ㄤ簬镞犳硶銮峰彇璁板綍板嗙殑镐绘暟
	 * 杩斿洖镄勮褰曢泦涓岖敤镞惰璁板缑娉ㄩ攒 eg:$recordset = null
	 * 
	 * @param string $strSql
	 * @param &array $aPrepareSource 濡傛灉涓簄ull鍒欎笉浣跨敤棰勫鐞嗘煡璇?
	 * @return Lamb_Db_RecordSet_Interface implemention
	 */
	public function dynamicSelect($strSql, array $aPrepareSource = null)
	{
		$objRecordSet	=	null;
		try{
			if($objRecordSet = $this->prepare($strSql, array(PDO::ATTR_CURSOR=>PDO::CURSOR_SCROLL))){
				if ($aPrepareSource) {
					self::batchBindValue($objRecordSet, $aPrepareSource);
				}
				$objRecordSet->execute();
			}
		}catch(Exception $e){}
		return $objRecordSet;
	}	

	/**
	 * 阃氲绷绫讳技杩欐牱镄凷QL璇彞銮峰彇璁板綍镄勬€绘暟锛?
	 * sql count(*) as num from table [where ....]
	 *
	 * @param string $strSql 
	 * @param string $strNumKey 銮峰彇淇濆瓨璁板綍镐绘暟镄勫垪鍚?
	 * @return int
	 */
	public function getRowCount($strSql,$strNumKey='num')
	{
		$nRowNum		=	-1;
		if($objRecordSet	=	$this->query($strSql)){
			$arr	=	$objRecordSet->fetch();
			$nRowNum=	$arr[$strNumKey];
			$objRecordSet	=	null;
		}
		return $nRowNum;
	}
	
	/**
	 * 璋幂敤dynamicSelect銮峰彇璁板綍板嗙殑镐绘暟
	 *
	 * @param string $strSql
	 * @return int 濡傛灉澶辫触鍒栾繑锲?1
	 */
	public function getRowCountDynamic($strSql)
	{
		$nRowCount		=	-1;
		if($objRecordSet = $this->dynamicSelect($strSql)){
			$nRowCount	=	$objRecordSet->rowCount();
			$objRecordSet->closeCursor();
		}
		return $nRowCount;	
	}
	
	/**
	 * 浣跨敤SQL棰勫鐞呜鍙ヨ幏鍙栬褰曢泦镄勬€绘暟锛屽唴閮ㄨ皟鐢ㄤ简
	 * getRowCountEx锛屽鏋滃け璐ュ垯璋幂敤镐ц兘宸殑dynamicSelect
	 * 
	 * @param string $strSql
	 * @param & array $aPrepareSource
	 * @param boolean $bIncludeUnion
	 * @return int 濡傛灉澶辫触鍒栾繑锲?1
	 */
	public function getPrepareRowCount($strSql, array $aPrepareSource, $bIncludeUnion = false)
	{
		$nRowNum = -1;
		$strNewSql = $this->getRowCountEx($strSql, $bIncludeUnion, true);
		if (Lamb_Utils::isInt($strNewSql, true)) {
			return $strNewSql;
		}
		$stmt = $this->prepare($strNewSql);
		self::batchBindValue($stmt, $aPrepareSource);
		$stmt->execute();
		if (($arr = $stmt->fetch())) {
			$nRowNum = $arr['num'];
		}
		else {
			$stmt = null;
			if ($stmt = $this->dynamicSelect($strSql, $aPrepareSource)) {
				$nRowNum	=	$stmt->rowCount();
				$stmt->closeCursor();				
			}
		}
		$stmt = null;
		return $nRowNum;
	}
	
	/**
	 * 镓ц涓€鏉QL璇彞锛屽苟杩斿洖鏀硅褰曢泦浠ュ强璁板綍镄勬€绘暟
	 * 杩欓噷浣跨敤浜嗘€ц兘宸殑dynamicSelect锛屾病浣跨敤楂樻晥镄刧etRowCountEx
	 * 鍜屽叾瀹幂殑镆ヨ锛屼富瑕佸洜涓鸿皟鐢ㄦ鏂规硶镄勫満鏅竴鑸槸銮峰彇涓€鏉＄殑璁板綍板嗘儏鍐典笅
	 * 鍚庢湡鍙兘浼氩崌绾?
	 * 
	 * @param string $strSql
	 * @param boolean $bGetData
	 * @return array 濡傛灉$bGetData = false 鍒栾繑锲炶褰曟暟缁?
	 *				 濡傛灉涓篓rue锛屽垯杩斿洖涓€涓猘rray('num' => 涓暟锛?data' => 璁板綍鏁扮粍)
	 */
	public function getNumData($strSql, $bGetData=false)
	{
		if(!$bGetData) return $this->getRowCountDynamic($strSql);
		$aResult		=	array('num'=>-1,'data'=>null);
		if($objRecordSet = $this->dynamicSelect($strSql)){
			$aResult['num']	=	$objRecordSet->rowCount();
			$aResult['data']=	$objRecordSet->fetch();
			$objRecordSet->closeCursor();
		}
		return $aResult;
	}
	
	/**
	 * 浣跨敤SQL棰勫鐞呜幏鍙栧悓getNumData涓€镙风殑锷熻兘
	 *
	 * @param string $strSql
	 * @param & array $aPrepareSource
	 * @param boolean $bGetData
	 * @return array 濡傛灉$bGetData = false 鍒栾繑锲炶褰曟暟缁?
	 *				 濡傛灉涓篓rue锛屽垯杩斿洖涓€涓猘rray('num' => 涓暟锛?data' => 璁板綍鏁扮粍)
	 */
	public function getNumDataPrepare($strSql, array $aPrepareSource = null, $bGetData = false)
	{
		$aResult = $bGetData ? array('num' => -1, 'data' => null) : -1;
		$objRecordSet = $this->quickPrepare($strSql, $aPrepareSource);
		if ($objRecordSet) {
			if ($bGetData) {
				$aData = $objRecordSet->fetchAll();
				$aResult['data'] = @$aData[0];
				$aResult['num'] = count($aData);
			}
			else {
				$aResult = count($objRecordSet->fetchAll());
			}
			$objRecordSet = null;
		}
		return $aResult;
	}
	
	/**
	 * 蹇€熶娇鐢⊿QL棰勫鐞嗘墽琛孲QL璇彞
	 * 娉细杩斿洖镄勮褰曢泦涓岖敤镞惰璁板缑娉ㄩ攒 eg:$recordset = null
	 *
	 * @param string $strSql
	 * @param & array $aPrepareSource
	 * @param boolean $bExec 濡傛灉涓篓rue鍒栾皟鐢≒ODStatement::execute涓嶈繑锲炶褰曢泦
	 * @return Lamb_Db_RecordSet_Interface
	 */
	public function quickPrepare($strSql, array $aPrepareSource=null, $bExec = false)
	{
		$objRecordSet = null;
		$objRecordSet = $this->prepare($strSql);
		self::batchBindValue($objRecordSet, $aPrepareSource);
		if ($bExec) {
			$objRecordSet = $objRecordSet->execute();
		}
		else {
			$objRecordSet->execute();
		}
		return $objRecordSet;
	}	
	
	/**
	 * 镆ヨ鎸囧畾锅忕Щ锲哄畾闀垮害镄勮褰曢泦
	 * 娉细杩斿洖镄勮褰曢泦涓岖敤镞惰璁板缑娉ㄩ攒 eg:$recordset = null
	 *
	 * @param string $strSql
	 * @param int $nLimit
	 * @param int $nOffset
	 * @param boolean $bIncludeUnion
	 * @return Lamb_Db_RecordSet_Interface
	 */ 
	public function limitSelect($strSql,$nLimit,$nOffset=0,$bIncludeUnion=false)
	{
		$objRecordSet	=	null;
		if($strNewSql = Lamb_App::getGlobalApp()->getSqlHelper()->getLimitSql($strSql,$nLimit,$nOffset,$bIncludeUnion)){
			$objRecordSet = $this->query($strNewSql);
		}
		return $objRecordSet;
	}			

	/**
	 * @param string $sql
	 * @param int $limit
	 * @param array $aPrepareSource
	 * @param int $offset
	 * @param boolean $hasUnion
	 * @return Lamb_Db_RecordSet_Interface
	 */
	public function limitSelectPrepare($sql, $limit, array $aPrepareSource = null, $offset = 0, $hasUnion =false)
	{
		$sql = Lamb_App::getGlobalApp()->getSqlHelper()->getPrePareLimitSql($sql, $hasUnion);
		if (!$aPrepareSource) {
			$aPrepareSource = array();
		}
		$aPrepareSource['g_offset'] = array($offset, PDO::PARAM_INT);
		$aPrepareSource['g_limit'] = array($limit, PDO::PARAM_INT);
		return $this->quickPrepare($sql, $aPrepareSource);
	}

	/**
	 * 寮€濮嬩竴涓簨锷★紝濡傛灉鎴愬姛鍒栾繑锲潇rue鍚﹀垯false
	 *
	 * @return boolean
	 */
	public function begin()
	{
		return $this->beginTransaction();
	}	
	
	/**
	 * 鎻愪氦鎴栧洖婊氢竴涓け璇紝濡傛灉鎻愪氦鎴栧洖婊氭垚锷熷垯杩斿洖true鍚﹀垯false
	 *
	 * @return boolean
	 */
	abstract public function end();
	
	/**
	 * 鏀硅繘getRowCount锛屽弬鏁癝QL璇彞镞犻渶浣跨敤sql count(*) as num from table杩欐牱镄勬牸寮?
	 * 鏅€氱殑浠讳綍涓€涓猄QL璇彞eg:select * from test 閮戒细镊姩瑙ｆ瀽鎴愪互涓婄殑镙煎纺
	 * 濡傛灉鍙傛暟$bRetSql涓篓rue鍒栾繑锲炶В鏋愬悗镄凷QL鍚﹀垯镓ц瑙ｆ瀽鍚庣殑SQL璇彞骞惰繑锲炵粨鏋?
	 *
	 * @param string $strSql
	 * @param boolean $bIncludeUnion SQL璇彞涓槸鍚湁union鍏抽敭瀛?
	 * @param boolean $bRetSql
	 * @return string | int 濡傛灉$bRetSql涓篓rue鍒栾繑锲炲鐞嗗悗镄凷QL锛屽惁鍒栾繑锲炴€绘暟
	 *						濡傛灉澶辫触鍒栾繑锲?1
	 */
	abstract public function getRowCountEx($strSql,$bIncludeUnion=false, $bRetSql = false);
}