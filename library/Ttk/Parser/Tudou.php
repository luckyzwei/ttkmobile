<?php
class Ttk_Parser_Tudou extends Ttk_Parser_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function parse($url)
	{
        $html = Lamb_Http::quickGet($url, 5, false, $status);
		
        preg_match("#vcode:\s*'([=\w]+)'\s*#ix", $html, $vcode);
        if(!empty($vcode) && !empty($vcode[1])){
            //判断视频是不是来自 优酷
            return parent::_getYouku(trim($vcode[1]));
        }

        $data = array();
        $areaCode = '';
        preg_match('/areaCode:\s*[\'"](\d+)[\'"]/is', $html, $areaCodes); //获得地区id
        $areaCode = $areaCodes[1] ? substr($areaCodes[1], 0, -1) : 10000;       
        preg_match("#segs:\s*'([^']+)'#ms",$html, $segs);

        if(!empty($segs[1])){
            $segs_json = json_decode($segs[1], true);
            foreach($segs_json as $key =>$val){
                foreach ($val as $k =>$v){
                    $api_url = "http://v2.tudou.com/f?id=".$v['k']."&sid={$areaCode}&hd={$k}&sj=1";
                    $v_xml = Lamb_Http::quickGet($api_url, 5, false, $status);
					Lamb_Debuger::debug($api_url);
                    if(empty($v_xml)){
                        return false;
                    }
                    $s_xml = @simplexml_load_string($v_xml);
                    if($key == 2) $data['normal'][] = strval($s_xml);
                    if($key == 3) $data['high'][] = strval($s_xml);
                    if($key == 5) $data['super'][] = strval($s_xml);
                    if($key == 99) $data['original'][] = strval($s_xml);
                }
            }

            return $data;               
        }else{
            return false;
        }	
	}	
}