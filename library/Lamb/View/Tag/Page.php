<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_Db
 * @usage 
 *		{tag:Lamb_View_Tag_Page [page_num='@int | @var'] [style='@int | @var default:1']
 *		[listid='@int | @var |string'] [page='@int | @var'] [pagesize='@int | @var'] [data_num = '@int | @var']
 *		[max_page_count='@int | @var']
 *		濡傛灉鎸囧畾浜唋istid灞炴€э紝灏嗕粠娉ㄥ唽镄刬d涓幏鍙杙age,pagesize,data_num绛夊€硷紝锅囧镞㈡寚瀹氢简listid钥屽张鎸囧畾浜唒age,pagesize,data_num
 *		绛夊€硷紝灏呜鐩杔istid涓幏鍙栫殑链?
 *		}
 *			<html>@$phpvar@ 
 *			#lastPage# - 链€鍚庨〉镰?	#nextPage# - 涓嬩竴椤电爜 #prevPage# 涓娄竴椤电爜
 *			#pageCount# - 镐婚〉鏁?#pageSize# - 姣忎竴椤靛灏?#currentPage# 褰揿墠椤电爜
 *			#num# 鏁版嵁镐绘暟
 *			{pageitem focus='褰揿墠椤垫墍鏄剧ず镄删tml, #page#琛ㄧず褰揿墠椤电爜 鍙互链堾$php_var@' nofocus='涓嶆槸褰揿墠椤靛叾瀹冮〉镰佹墍鏄剧ず镄删tml, #page#琛ㄧず褰揿墠椤电爜 鍙互链堾$php_var@'
 *			more='鍙style灞炴€у€间负2镞舵墠链夋晥锛?page#琛ㄧず褰揿墠椤电爜 鍙互链堾$php_var@'}
 *		{/tag:Lamb_View_Tag_Page}
 */
class Lamb_View_Tag_Page extends Lamb_View_Tag_Abstract
{
	
	/**
	 * Lamb_View_Tag_Interface implemention
	 */
	public function parse($content, $property)
	{
		//page_num
		if (($page_num = self::getTagAttribute('page_num', $property, true, false)) === false) {
			$page_num = '5';
		}
		//style
		if (($style = self::getTagAttribute('style', $property, true, false)) === false) {
			$style = '1';
		}
		//listid
		if (($listid = self::getTagAttribute('listid', $property)) === false) {
			$listid = "''";
		} else {
			$listid = "'{$listid}'";
		}
		//page
		if (($page = self::getTagAttribute('page', $property, true, false)) === false) {
			$page = 'null';
		}
		//pagesize
		if (($pagesize = self::getTagAttribute('pagesize', $property, true, false)) === false) {
			$pagesize = 'null';
		}
		//data_num
		if (($data_num = self::getTagAttribute('data_num', $property, true, false)) === false) {
			$data_num = 'null';
		}				
		//max_page_count
		if (($max_page_count = self::getTagAttribute('max_page_count', $property, true, false)) === false) {
			$max_page_count = '0';
		}

		$strPageItemPatt=	'/\{pageitem\b(.*?)\}/is';
		$strPageStart	=	$strPageEnd	=	$strPageMore	=	$strPageFocus = $strPageNoFocus	= "'";
		$isAttrExists	=	false;
		$nPageItemStart	=	0;
		$strQuoteFlag	=	self::codeAddslashes("'");
			
		if (preg_match($strPageItemPatt, $content, $aPageItemMatch, PREG_OFFSET_CAPTURE)) {
		
			$nPageItemStart	=	$aPageItemMatch[0][1];
			$strPageStart	.=	self::parseVar(self::parseVar(self::codeAddslashes(substr($content, 0, $nPageItemStart))), true, "'", true);
			$nPageItemStart +=	strlen($aPageItemMatch[0][0]);

			if($aPageItemMatch[1]){
				$isAttrExists		=	true;
				$strPageItemAttr	=	$aPageItemMatch[1][0];
				if ( ($strPageFocusTemp = self::getTagAttribute('focus', $strPageItemAttr)) === false) {
					$strPageFocusTemp	=	'';
				}
				if ( ($strPageNoFocusTemp = self::getTagAttribute('nofocus', $strPageItemAttr)) === false) {
					$strPageNoFocusTemp	=	'';
				}
				if ( ($strPageMoreTemp = self::getTagAttribute('more',$strPageItemAttr)) === false) {
					$strPageMoreTemp	=	'';
				}
				$strPageFocus		.=	$strPageFocusTemp;
				$strPageNoFocus		.=	$strPageNoFocusTemp;
				$strPageMore		.=	$strPageMoreTemp;
			}
		}
			
		if ($isAttrExists){
			$strPageEnd	.=	self::parseVar(self::parseVar(self::codeAddslashes(substr($content, $nPageItemStart))), true, "'", true);
		}
		else{
			$strPageStart.=	self::parseVar(self::parseVar(self::codeAddslashes(substr($content,$nPageItemStart))), true, "'", true);
		}
			
		$strPageStart	.=	"'";
		$strPageEnd		.=	"'";
		$strPageMore	.=	"'";
		$strPageFocus	.=	"'";
		$strPageNoFocus	.=	"'";
	
		$strParam		=	"array(
			'page_num'		=>	$page_num,
			'page_style'	=>	$style,
			'listid'		=>	$listid,
			'page_start_html'=>	$strPageStart,
			'page_end_html'	=>	$strPageEnd,
			'more_html'		=>	$strPageMore,
			'focus_html'	=>	$strPageFocus,
			'nofocus_html'	=>	$strPageNoFocus,
			'max_page_count' => $max_page_count,
			'page' => $page,
			'pagesize' => $pagesize,
			'data_num' => $data_num
		)";
		$strSrc	= '<?php ' . __CLASS__ . "::page($strParam)?>";

		return $strSrc;				
	}
		
	/**
	 * @param array $aOptions = array(
	 *		['style' => @int(default:1)]
	 *		['list_id' => @int | string]
	 *		['page_num' => @int(default:5)]
	 *		['page' => @int]
	 *		['pagesize' => @int]
	 *		['data_num' => @int]
	 *		['page_start_html' => @string(default:'')]
	 *		['page_end_html' => @string(default:'')]
	 *		['more_html' => @string(default:'')]
	 *		['focus_html' => @string(default:'')]
	 *		['nofocus_html' => @string(default:'')]
	 *		['max_page_count' => @int(default:0)]	
	 *		['return' => @boolean(default:false)] 	 	 
	 *	)
	 * @return void | string
	 */
	public static function page(array $aOptions)
	{
		$options = array(
			'style' => 1,
			'page_num' => 5,
			'page_start_html' => '',
			'page_end_html' => '',
			'more_html' => '',
			'focus_html' => '',
			'nofocus_html' => '',
			'return' => false,
			'listid' => '',
			'page' => '',
			'pagesizse' => '',
			'max_page_count' => 0
		);
		Lamb_Utils::setOptions($options, $aOptions);
		//get the value of page and pageszie and data_num
		if (($pageParam = self::getRegisterdById($options['listid'])) === null) {
			if (!Lamb_Utils::isInt($options['page']) || !Lamb_Utils::isInt($options['pagesize']) || !Lamb_Utils::isInt($options['data_num'])) {
				return '';
			}
		} else {
			$options['page'] = $pageParam['page'];
			$options['pagesize'] = $pageParam['pagesize'];
			$options['data_num'] = $pageParam['num'];
		}

		if (!Lamb_Utils::isInt($options['data_num'], true) || $options['data_num'] <= 0 
			|| !Lamb_Utils::isInt($options['page'], true)
			|| !Lamb_Utils::isInt($options['pagesize'], true)
			|| $options['pagesize'] <= 0 ) {//return if data_num <= 0
			return '';
		}
		
		if (! Lamb_Utils::isInt($options['style'], true)) {
			$options['style'] = 1;
		}
		
		$options['page_count'] = ceil($options['data_num'] / $options['pagesize']);
		if ($options['max_page_count'] > 0 && $options['max_page_count'] < $options['page_count']) {
			$options['page_count'] = $options['max_page_count'];
		}		
				
		$objPage = new Lamb_View_PageFunc(array(
				'page_count' => $options['page_count'],
				'current_page' => $options['page'],
				'page_num' => $options['page_num'],
				'page_style' => $options['style'],
				'record_count' => $options['data_num'],
				'focus_html' => $options['focus_html'],
				'nofocus_html' => $options['nofocus_html'],
				'more_html' => $options['more_html'],
			));
		$aReplace			=	array(
			'#lastPage#'	=>	$objPage->getLastPage(),
			'#nextPage#'	=>	$objPage->getNextPage(),
			'#prevPage#'	=>	$objPage->getPreviousPage(),
			'#pageCount#'	=>	$options['page_count'],
			'#pageSize#'	=>	$options['pagesize'],
			'#currentPage#'	=>	$options['page'],
			'#num#'			=>	$options['data_num']
		);
		$aVariables	= array(&$options['page_start_html'], &$options['page_end_html'],
						&$options['focus_html'], &$options['nofocus_html'], &$options['more_html']);
		foreach ($aReplace as $k => $val){
			for ($i=0,$j=count($aVariables);$i<$j;$i++){
				$aVariables[$i]		=	str_replace($k, $val, $aVariables[$i]);
			}
		}	
		unset($aVariables);
		$objPage->m_strHtmlFocus = $options['focus_html'];
		$objPage->m_strHtmlNoFocus = $options['nofocus_html'];
		$objPage->m_strHtmlMore = $options['more_html'];				
		$html = $options['page_start_html'] . $objPage->showHtmlPage() . $options['page_end_html'];
		if ($options['return']) {
			return $html;
		}
		echo $html;
	}
}