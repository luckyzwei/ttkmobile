Lamb Framework v1.0
2012-07-14 21:18
author:灏忕緤
description:
	姝ゆ鏋舵槸浠屿end framework寰楀埌鍚彂锛岄噰鐢╖end镄勫懡鍚岖┖闂存牸寮忓嵆锛歂amespace1_Namespace2_Classname锛岀被鏂囦欢Classname.php鏄互
	Namespace1/Namespace2/Classname.php镙煎纺镄勮矾寰勫瓨鏀剧殑銆侺amb Framework镄勫懡鍚岖┖闂存槸Lamb涓哄紑澶达紝绫讳技涓屿end鏄笌Zend寮€澶?
	Lamb Framework鏄竴娆捐交閲忓瀷镄凪VC妗嗘灦锛岄噰鐢≒DO鍏煎澶氢釜鐗堟湰镄勬暟鎹簱锛岃嚜甯︽ā鐗埚紩鎿庯紝宸插疄鐜颁简鍩烘湰镙囩浠ュ强镊畾涔夋爣绛撅紝妯＄増镄?
	瑙ｆ瀽镊甫链夌紦瀛桡紝浣跨敤姝ゆ鏋舵帹钻愮殑璺缎缁撴瀯
	application
		--controllors
		--views
	library
		--Lamb
	public
		--css
		--html
		--~runtime
	Hello world:
	  -public/index.php
		//璁剧疆Lamb framework璺缎浣岖疆
		set_include_path('Lamb framework镓€鍦ㄧ殑鐩綍锛屽锛?./library/'
			PATH_SEPARATOR . get_include_path);
		//銮峰彇绫诲姞杞藉櫒	
		require_once 'Lamb/Loader.php';
		//瀹炰緥鍖栧姞杞藉櫒
		$loader = Lamb_Loader::getInstance();
		//鍒濆鍖朅pp瀵硅薄
		$app = Lamb_App::getInstance();
		$app->setControllorPath('Controllor镓€鍦ㄧ殑璺缎锛屽锛?./application/controllors/')
			->setViewPath('濡傛灉瑕佺敤鍒版ā鐗埚紩鎿庡垯蹇呴』瑕佽皟鐢ㄦ鍑芥暟鍜宻etViewRuntimePath锛屽锛?./application/views/')
			->setViewRuntimePath('璁剧疆妯＄増鏂囦欢瑙ｆ瀽镄勭紦瀛樿矾寰勶紝濡傦细./~runtime/')
			//濡傛灉瑕佷娇鐢ㄦ暟鎹簱瀵硅薄鍒椤繀椤昏皟鐢╯etSqlHelper璁剧疆SQL宸ュ叿镙忓拰setDbCallback銮峰彇Db瀵硅薄瀹炵幇鏁版嵁搴撴搷浣?
			->setSqlHelper('Lamb_Db_Sql_Helper_Abstract 鎶借薄绫荤殑瀛愮被瀵硅薄')
			->setDbCallback('Php鍚堟硶镄勫洖璋冨嚱鏁版垨钥呮槸瀹炵幇Lamb_Db_Callback_Interfaced鎺ュ彛镄勫璞?)
			->run()//杩愯;
		-application/controllors/indexControllor.php
		 class indexControllor extends Lamb_Controllor_Abstract //榛樿镄刢ontrollor鏄酸ndex
		 {
		 	public function indexAction() //榛樿镄刟ction鏄酸ndex
			{
				echo 'hello world';
				//鎴栬€呬娇鐢╒iew镄勬ā鐗埚紩鎿?
				include $this->view->load('hello');
			}
		 }
		 -application/views/hello.html
		 <h1>Hello world</h1>
	浠ヤ笅鏄悇涓被镄勪粙缁嶏细
	-Lamb_Loader
		绫诲姞杞藉櫒锛屽ぇ閮ㄥ垎鏄痁end_Loader_Autoloader鍜孼end_Loader绫荤殑缁揿悎
		_defaultInternalAutoload鏄被镄勯粯璁ゅ姞杞藉櫒锛岃锷犺浇鍣ㄥ唴閮ㄨ皟鐢╣etDefaultClassAutoloader銮峰彇鐪熸镄刢lass锷犺浇鍣?
		榛樿镄勮绫荤殑闱欐€佹柟娉昹oadClass锛屽綋铹朵綘涔熷彲浠ュ湪寰楀埌Loader瀵硅薄鍚庤皟鐢╯etDefaultClassAutoloader璁剧疆榛樿镄勫姞杞藉櫒
		璇ユ柟娉曞皢瀹炵幇锻藉悕绌洪棿鍚岀被浠ヤ笅鍒掔嚎_鍒嗗壊镄勬柟寮忥紝榛樿鍙姞杞戒互Lamb_寮€澶寸殑绫伙紝濡傛灉setFallbackAutoloader(true),
		鍒欎细灏濊瘯锷犺浇涓嶅甫链夊懡鍚岖┖闂寸殑绫伙紝濡傛灉鐢ㄦ埛鎯冲镊繁镄勫懡鍚岖┖闂翠篃閲囩敤榛樿镄勫姞杞藉櫒锛屽垯鍙互璋幂敤registerNamespaces
		娉ㄥ唽锻藉悕绌洪棿鍗冲彲锛屽鏋灭敤鎴锋兂瀵硅嚜宸辩殑锻藉悕绌洪棿閲囩敤镊畾涔夊姞杞藉櫒锛屽垯鍙互璋幂敤unsiftNamespacesAutoloaders鎴栬€?
		pushNamespaceAutoloaders鏂规硶锛屾兂瀵逛笉甯﹀懡鍚岖┖闂撮噰鐢ㄨ嚜瀹氢箟镄勫姞杞藉櫒鍒栾皟鐢ㄤ互涓?涓嚱鏁版棤闇€浼犱汉绗簩涓弬鏁?
		濡傛灉涓€涓懡鍚岖┖闂存敞鍐屾敞鍐屽涓姞杞藉櫒锛屽垯鍙鍏朵腑涓€涓姞杞藉櫒锷犺浇绫绘垚锷燂紝鍒欎笉浼氩啀璋幂敤涓嬮溃镄?
	-Lamb_App 
		搴旗敤绋嫔簭绫伙紝鏄暣涓簲鐢ㄧ▼搴忕殑涓荤被锛屽搴旗敤绋嫔簭镄刅iew,Dispatcher,Router,Request,Response,Db,SqlHelper缁勪欢
		杩涜绠＄悊鍜岀淮鎶わ紝Lamb_App閲囩敤鍗曚緥妯″纺阃氲绷璋幂敤Lamb_App::getInstance()銮峰彇App瀵硅薄
		App鍦ㄦ瀯阃犵殑镞跺€欎细璁剧疆榛樿镄刅iew,Dispatcher,Router,Request,Response瀵硅薄锛屽苟灏呜嚜韬敞鍐屽埌Lamb_Registry绫?
		涓紝浠ヤ究鍦ㄧ▼搴忕殑浠讳綍鍦版柟閮藉彲浠ラ€氲绷璋幂敤Lamb_App::getGlobalApp()銮峰彇鍏ㄥ眬App瀵硅薄
		褰撶劧鐢ㄦ埛鍙互鍦ㄥ缑鍒痨pp瀵硅薄鍚庨€氲绷璋幂敤SetView,SetDispatcher,SetRouter,SetReuqest,SetResponse绛夋柟娉曡缃嚜宸辩殑缁勪欢
		濡傛灉鐢ㄦ埛瑕佸疄鐜拌嚜宸辩殑App锛屽垯闇€缁ф圹Lamb_App锛岀埗绫藤amb_App浼氩湪鍏舵瀯阃犲嚱鏁颁腑镊姩灏嗗叾娉ㄥ唽鍒癓amb_Registry绫讳腑
		浣犱篃鍙互璋幂敤Lamb_App::setGlobalApp()璁剧疆Lamb_App::getGlobalApp()杩斿洖镄勭被
		鍦ㄨ皟鐢↙amb_App::getInstance()寰楀埌App瀵硅薄鍚庯紝蹇呴』瑕佽皟鐢↙amb_App::setControllorPath璁剧疆Controllor镓€鍦ㄧ殑璺缎
		濡傛灉搴旗敤绋嫔簭瑕佷娇鐢╒iew鍒椤繀椤昏璋幂敤Lamb_App::setViewPath璁剧疆妯＄増鏂囦欢镄勮矾寰勶紝Lamb_App::setViewRuntimePath璁剧疆
		妯＄増瑙ｆ瀽缂揿瓨鏂囦欢淇濆瓨镄勮矾寰?
		濡傛灉绋嫔簭涓浣跨敤鍒版暟鎹簱锛屽垯蹇呴』鍦ㄥ缑鍒痨pp瀵硅薄鍚庯紝璋幂敤Lamb_App::setSqlHelper鍜孡amb_App::setDbCallback
		璁剧疆SQL宸ュ叿绫伙紝sqlHelper鏄疞amb_Db_Sql_Helper_Abstract瀛愮被姣忎釜涓嶅悓鐗堟湰镄勬暟鎹簱宸ュ叿绫荤殑瀹炵幇涓崭竴镙凤紝锲犳灏嗗叾鎶借薄
		setDbCallback鏄缃幏鍙朙amb_Db_Abstract瀵硅薄镄勫洖璋冨嚱鏁帮紝鐢ㄤ簬鍦ㄧ▼搴忎腑闇€瑕佺敤鍒癓amb_Db_Abstract瀵硅薄镞堕€氲绷璋幂敤
		Lamb_App::getDb銮峰彇鏁版嵁搴揿璞?
		鍦ˋpp浣跨敤Router,Dispatcher缁勪欢镞堕兘闅惧厤浼氩嚭阌欐姏鍑哄纾甯革紝浣犲彲浠ヨ皟鐢↙amb_App::setErrorHandle璁剧疆澶勭悊杩欎簺阌栾镄勭被
		榛樿灏嗙洿鎺ユ姏鍑哄纾甯窵amb_App::setErrorHandle璁剧疆镄勬槸Lamb_App_ErrorHandle_Interfaces瀹炵幇镄勫瓙绫汇€?
	-Lamb_App_Router
		璺敱绫?鍙互鍦ㄧ▼搴忕殑浠讳綍鍦版柟璋幂敤Lamb_App::getGlobalApp()->getRouter()寰楀埌鍏ㄥ眬Router
		榛樿镄勬牸寮忔槸?s=controllor/action/val1/name1/var2/name2锛岃绫讳细灏呜В鏋愯矾鐢辩殑鍙傛暟锛屽彲璋幂敤injectRequest
		灏嗗弬鏁版敞鍏ュ埌Request瀵硅薄涓紝鍙互璋幂敤setRouterParamName璁剧疆璺敱鍙傛暟鍚嶏紝榛樿鏄痵锛岃皟鐢╯etUrlDelimiter璁剧疆鍙傛暟鍒嗛殧绗﹂粯璁ゆ槸/
		parse鏂规硶涓鸿绫荤殑瑙ｆ瀽鏂规硶锛屽皢璺敱镄勫弬鏁拌В鏋愩€倁rl鏂规硶灏嗗弬鏁拌浆鎹㈡垚璺敱镙煎纺镄勮矾寰?
	-Lamb_App_Dispatcher
		鍒嗗彂绫?鍙互鍦ㄧ▼搴忕殑浠讳綍鍦版柟璋幂敤Lamb_App::getGlobalApp()->getDispatcher()寰楀埌鍏ㄥ眬Router
		浠嶭amb_App_Router瀵硅薄銮峰彇淇℃伅锛岃皟鐢ㄥ搴旗殑controllor锛屾墽琛岃瀵瑰簲镄刟ction鏂规硶銆?
		姝ょ被瑕佹眰璁剧疆controllor镄勮矾寰勶紝璋幂敤setControllorPath璁剧疆锛屽綋铹朵篃鍙互璋幂敤Lamb_App::setControllorPath
		瑕佹眰镓€链夌殑controllor绫婚兘瑕佷互controllor缁揿熬锛屽鏋渢estControllor鍒栾矾鐢卞弬鏁颁负test
		镓€链夌殑action閮借浠ction缁揿熬锛屽鏋渢estAction锛岄粯璁ょ殑controllor鏄酸ndexControllor,榛樿镄刟ction
		鏄酸ndexAction锛屽綋铹剁敤鎴蜂篃鍙互璋幂敤setOrGetDefaultControllor鍜宻etOrGetDefaultAction璁剧疆榛樿镄刢ontrollor鍜宎ction
		鍙﹀杩桦彲浠ヨ缃甤ontrollor鍜宎ction镄勫埆锛岃皟鐢╯etAlias锛屼篃灏辨槸璇村亣濡傝矾鐢卞弬鏁颁负s=index/test
		浣犲彲浠ヨindexControllor瀹为台鏄皟鐢╥ndexAliasControllor,testAction瀹为台璋幂敤鏄痶estAliasAction
	-Lamb_App_Request
		Http璇锋眰绫?鍙互鍦ㄧ▼搴忕殑浠讳綍鍦版柟璋幂敤Lamb_App::getGlobalApp()->getRequest()寰楀埌鍏ㄥ眬Request
		澶ч儴鍒嗘槸镙规嵁Zend涓殑Request鏀瑰啓镄勶紝Request绫诲疄鐜颁简__get鍜宊_isset鏂规硶锛屽洜姝よ幏鍙?_userParams,$_GET,$_POST,$_COOKIE,$_SERVER,$_ENV
		镄勫€硷紝鍙互镀忕被镄勫睘镐т竴镙疯鍙栵紝銮峰彇链肩殑鍏埚悗椤哄簭灏辨槸鎸夌収涓婅堪镄勯『搴忋€?
		Request绫绘湁涓€涓猆serParams板嗗悎锛岃板嗗悎涓昏淇濆瓨镄勬槸Router瑙ｆ瀽鍚庣殑璺敱鍙傛暟鍜岃嚜瀹氢箟URI镞惰В鏋愮殑鍙傛暟锛屽锛歴=index/test/v1/n1/v2/n2锛?
		缁忚绷Router瑙ｆ瀽鍚庡皢浼氭妸v1=>n1,v2=>n2杩欐牱镄勯敭链煎淇濆瓨鍒癛equest镄刄serParams板嗗悎涓?
		璇ョ被鍏抽敭镄勬槸setRequestUri鏂规硶锛岀敤鎴峰彲浠ヨ嚜琛岃缃瑙ｆ瀽镄刄RI鍦板潃锛屽鏋滆缃简锛屽皢璋幂敤parse_url瑙ｆ瀽鍙傛暟锛屽皢瑙ｆ瀽鍚庣殑鍙傛暟娉ㄥ叆鍒癠serParams板嗗悎
		涓紝濡傛灉榛樿涓崭紶鍙傛暟鍒椤皢涓嶅仛浠讳綍浜嬫儏锛岀洿鎺ュ紩鐢≒HP铡熸湁镄凣ET,POST绛夐泦鍚?
	-Lamb_App_Response
		Http鍝嶅簲绫伙紝鍙互鍦ㄧ▼搴忕殑浠讳綍鍦版柟璋幂敤Lamb_App::getGlobalApp()->getResponse()寰楀埌鍏ㄥ眬Response
		璇ョ被姣旇缉绠€鍗曪紝灏辨槸鎶妔etCookie,setHeader,redirect绛夋柟娉曞皝瑁呬简涓?
	-Lamb_View
		瑙嗗浘绫伙紝鍙互鍦ㄧ▼搴忕殑浠讳綍鍦版柟璋幂敤Lamb_App::getGlobalApp()->getView()寰楀埌鍏ㄥ眬View
		璇ョ被镄勪綔鐢ㄦ槸璐熻矗瑙ｆ瀽妯＄増鏂囦欢锛岃绫昏В鏋?绉岖被鍨嬬殑镙囩锛?
		绗竴绉嶆槸鍩烘湰镙囩锛屽熀链爣绛惧彧瀹炵幇2绉嶏紝1锛屾槸鍙橀噺镙囩锛屽叾镙煎纺锛殁$var},{$arrvar[index]}2锛屾槸layout镙囩浣灭敤鏄姞杞藉苟瑙ｆ瀽鍏跺畠
			妯＄増鏂囦欢锛屽叾镙煎纺{layout template}銆?
			镓╁睍锛氲绫讳缭鐣欎简PHP镙囩镄勪綔鐢紝鍚屾椂鐢ㄦ埛涔熷彲浠ュ畾涔夎嚜宸辩殑鍩烘湰镙囩锛屽叾姝ラ鏄紝1.缁ф圹Lamb_View绫?2锛岃皟鐢ㄧ埗绫荤殑setBaseTagParseMap
			鏂规硶锛屾敞鍐屽熀链爣绛捐В鏋愮殑姝ｅ垯琛ㄨ揪寮忥紝娉ㄦ剰璋幂敤璇ユ柟娉曚紶鍗旷殑绗竴涓弬鏁?key锛屽叾瀛愮被涓€瀹氲瀹炵幇parse_basetag_$key鏂规硶锛孷iew鍖归厤鍒颁简
			鍩烘湰镙囩镄勫瓧绗︿覆锛屼细璋幂敤鐩稿簲镄刾arse_basetag_$key鏉ュ叿浣撹В鏋愭镙囩
		绗簩绉嶆槸镊畾涔夋爣绛撅细
			镊畾涔夋爣绛剧殑镙煎纺{tag:璇ユ爣绛捐В鏋愮殑绫诲叏鍚嶅寘鎷懡鍚岖┖闂村锛歀amb_View_Tag_List[灞炴€у尯]}do something{/tag:Lamb_View_Tag_List}
			镊畾涔夋爣绛捐瀹炵幇Lamb_View_Tag_Interface鎺ュ彛鎴栬€呯户镓縇amb_View_Tag_Abstractc鎶借薄绫?
			Lamb framework宸茬粡榛樿瀹炵幇浜哃amb_View_Tag_List鍒楄〃镙囩 Lamb_View_Tag_Page镙囩锛岃2涓爣绛惧疄鐜颁简缁濆ぇ閮ㄥ垎鍒嗛〉浠ュ强鍒楄〃镙囩
			钥屼笖鍙互璁剧疆缂揿瓨锛屽叿浣揿彲鍙傝Lamb_View_Tag_List鍜孡amb_View_Tag_Page鏂囨。
	-Lamb_Db_Abstract
		鏁版嵁搴撴搷浣沧娊璞＄被锛岃绫荤户镓缒DO锛岃绫诲棰勫鐞嗘煡璇㈠凡缁忚幏鍙栬褰旷殑镐绘暟杩涜浜嗕紭鍖栵紝鍦ㄥ疄闄呯殑搴旗敤涓紝鐢ㄦ埛蹇呴』镙规嵁涓嶅悓镄勬暟鎹簱寮曟搸
		缁ф圹骞跺疄鐜拌绫绘湭瀹炵幇镄勬娊璞℃柟娉曪紝鍦↙amb framework涓紝鍙Lamb_Db_Abstract绫诲瀷涓墍链夊疄鐜扮殑鏂规硶銆?
		浣犲彲浠ュ湪寰楀埌App瀵硅薄镞讹紝璋幂敤setDbCallback璁剧疆銮峰彇璇ュ瓙绫荤殑瀵硅薄锲炶皟鏂规硶锛岃繖镙风▼搴忓氨鍙互鍦ㄤ换浣曞湴鏂硅皟鐢?
		Lamb_App::getGlobalApp()->getDb()銮峰彇鏁版嵁鎿崭綔瀵硅薄锛屽鏋滀负璁剧疆Dbcallback锛屾垨钥呰皟鐢ㄥ洖璋冩柟娉曪紝getDb鏂规硶灏嗘姏鍑哄纾甯?
		鍦ㄥ师濮嬬殑PDO璋幂敤PDO::query鏂规硶灏呜繑锲炰竴涓狿DOStatement瀵硅薄锛屼絾Lamb framework瑙勫畾锛屽湪寰楀埌db瀵硅薄浠ュ悗锛屼竴寰嬭皟鐢?
		PDO::setAttribute(PDO::ATTR_STATEMENT_CLASS,array('Lamb_Db_RecordSet', array($objInstance)))鏂规硶锛岃缃甈DO::query
		杩斿洖镄勫璞℃槸Lamb_Db_RecordSet绫绘垨钥呭叾瀛愮被镄勫璞°€?
		鐩墠Lamb framework鍙疄鐜板瓙绫藤amb_Mssql_Db
	-Lamb_Db_RecordSet
		璁板綍板嗗璞★紝璇ュ璞′笉鑳界洿鎺ュ疄渚嫔寲锛屽彧鑳芥槸Lamb_Db_Abstract::query鎴栬€卲repare鏂规硶杩斿洖璇ュ璞?
		璇ョ被缁ф圹浜哖DOStatement绫伙紝骞跺疄鐜颁简Lamb_Db_RecordSet_Interface鎺ュ彛涓殑鏂规硶锛岃绫讳紭鍖栦简
		rowCount鏂规硶锛屽挨鍏舵槸瀵逛簬镆ヨ镄勮褰曢泦姣旇缉澶х殑镞跺€欙紝阃氲绷璋幂敤getRowCount鏂规硶銮峰彇璁板綍板嗙殑镐绘暟
		姝ょ被链変釜涓崭究涔嫔锛屽氨鏄浜庢湁union鍏抽敭瀛楃殑SQL璇彞镞犳硶100%鏅鸿兘鍒ゆ柇锛屽洜姝ゅ湪链夋椂链欑敤鎴烽渶瑕佽皟鐢?
		setHasUnion鏂规硶璁剧疆璇QL璇彞鏄惁鍚湁Union鍏抽敭瀛?
		濡傛灉鐢ㄦ埛闇€瑕佸疄鐜拌嚜宸辩殑RecordSet锛屽彲浠ュ疄鐜癓amb_Db_RecordSet_CustomInterface鎺ュ彛鏉ュ畾涔夋暟鎹簮鎴栬€呯洿鎺?
		缁ф圹Lamb_Db_RecordSet绫?
	-Lamb_Db_Sql_Helper_Abstract
		SQL宸ュ叿鎶借薄绫伙紝瀵笋QL涓殑Select璇彞鎿崭綔镄勫伐鍏风被锛岀敱浜庝笉鍚岀殑鏁版嵁搴揿紩鎿庡彲鑳藉惈链変笉鍚岀殑鏁版嵁搴撹娉曪紝锲犳姝ょ被涓?
		鎶借薄绫伙紝鐢ㄦ埛鍦ㄥ疄闄呯殑搴旗敤涓紝蹇呴』缁ф圹姝ょ被锛屽疄鐜板叾链疄鐜扮殑鏂规硶锛屽苟鍦ㄥ缑鍒痨pp瀵硅薄鍚庯紝璋幂敤Lamb_App::setSqlHelper
		璁剧疆镊畾涔塖QL宸ュ叿绫伙紝绋嫔簭鍙互鍦ㄤ换浣曞湴鏂硅皟鐢↙amb_App::getGlobalApp()->getSqlHelper()寰楀埌sqlHelper瀵硅薄
		Lamb framework榛樿鍙疄鐜颁简Lamb_Mssql_Sql_Helper瀛愮被
	-Lamb_Db_Table
		鏁版嵁搴撹〃绫伙紝灏佽浜嗗熀链殑鏁版嵁搴撶殑镆ヨ锛屼慨鏀癸紝鎻掑叆璇彞锛屽缓璁娇鐢ㄦ绫昏繘琛孲QL镄勬煡璇紝淇敼锛屾彃鍏?
	-Lamb_Db_Select
		鏁版嵁搴撴煡璇㈢被锛岃绫诲皝瑁呬简鏁版嵁搴撶殑鎻掓搷浣滐紝鍖呮嫭鏅€氭煡璇紝甯︾紦瀛樻煡璇紝鍒嗛〉镆ヨ锛岄澶勭悊镆ヨ锛岄澶勭悊鍒嗛〉镆ヨ
	-Lamb_Cache_File
		鏂囦欢缂揿瓨
	-Lamb_Cache_Memcached
		Memcached缂揿瓨绫?
	-Lamb_Registry
		娉ㄥ唽鍏ㄥ眬绫伙紝阃氲绷璋幂敤Lamb_Registry::set()鏂规硶锛岀劧鍚庡湪绋嫔簭镄勪换浣曞湴鏂归兘鍙互璋幂敤Lamb_Registry::get()寰楀埌
		姣旇缉绠€鍗旷殑涓€涓被
	-Lamb_Upload
		涓娄紶绫伙紝鍙互瀹炵幇涓€涓垨澶氢釜鏂囦欢涓娄紶锛屽彲浠ラ檺鍒朵笂浼犳枃浠剁殑镓╁睍鍚嶏紝澶у皬绛?
	-Lamb_Utils
		宸ュ叿绫伙紝瀵瑰父鐢ㄧ殑涓€浜涘彉閲忕殑鍒ゆ柇锛屾暟鎹殑锷犲瘑绛?
	-Lamb_Debuger
		璋冭瘯绫?