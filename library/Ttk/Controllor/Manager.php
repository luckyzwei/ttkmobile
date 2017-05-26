<?php
abstract class Ttk_Controllor_Manager extends Lamb_Controllor_Abstract
{
	/**
	 * 用于登录服务端的加密sess_key的auth_key
	 */
	const SERVER_LOGIN_AUTH_ENCODE_KEY = 'm3a9b12af/8ecad93ef';
	
	/**
	 * e3mn0a6ef18ae59bi
	 */
	const SESSION_MECRYPT_KEY = 'e3mn0a6ef18ae59bi';
	
	/**
	 * 用于编辑检测字段合法性的正则表达式
	 */
	const CHK_EDIT_FIELD_PREG_BOOLEAN = '/^0|1$/is';
	
	/**
	 * 验证字段不能为空
	 */
	const CHK_EDIT_FIELD_PREG_STR_NOT_EMPTY = '/[\s\S]+/is';
	
	/**
	 * 验证字段是正整数
	 */
	const CHK_EDIT_FIELD_PREG_INT_POS = '/^\d+$/is';
	
	/**
	 * 验证字段是否为正数字
	 */
	const CHK_EDIT_FIELD_PREG_NUMBER_POS = '/^((\d+\.\d+)|(\d+))$/s';
	
	public function __construct()
	{
		parent::__construct();
		$this->mSiteCfg = Lamb_Registry::get(CONFIG);
		$this->mPublicCfg = Lamb_Registry::get(PUBLIC_CFG);
		
		@session_start();	
	}
	
	/**
	 * 向客户端输出结果的函数
	 * 
	 * @param int $code 错误码
	 * @param array $data 要输出的结果信息
	 * @param boolean $exit 输出错误信息后是否直接退出
	 * @return void
	 */
	public function showResults($code, array $data = null, $exit = true)
	{
		$ret = array('s' => $code);
		/*$extra = $this->mRequest->extra;
		
		if ($extra) {
			if (!$data) {
				$data = array();
			}
			$data['extra'] = $extra;
		}*/
		
		if ($data) {
			$ret['d'] = $data;
		}
		
		$ret = json_encode($ret);
		if ($exit) {
			$this->mResponse->eecho($ret);
		}
		
		return $ret;
	}	
	
	/**
	 * 验证字段是否为合法的字段
	 * 
	 * @param array & $allowFields合法的字段 = array(
	 * 		'字段名' => 1 或者字符串
	 * )
	 * @param string $fields 多个字段以逗号隔开
	 * @param string $default 如果$fields为空则会返回默认值
	 * @return string 如果字符串返回为空则表示$fields字段存在不支持的字段
	 */
	public function checkFields(&$allowFields, $fields, $default = '')
	{
		if (is_array($default)) {
			$newdefault = array();
			foreach ($default as $field) {
				if (!isset($allowFields[$field])) {
					unset($allowFields);
					return '';
				}
				
				if ($allowFields[$field] == 1) {
					$newdefault[] = $field;
				} else {
					$newdefault[] = $allowFields[$field];
				}
			}
			$default = implode(',', $newdefault);
		}
		
		if (!$fields) {
			return $default;
		}
		
		$isAppend = false;
		if ('+' == substr($fields, 0, 1)) {
			$isAppend = true;
			$fields = substr($fields, 1);
		}
		$fields = explode(',', $fields);
		$ret = array();
		
		foreach ($fields as $field) {
			if (!isset($allowFields[$field])) {
				unset($allowFields);
				return '';
			}
			
			if ($allowFields[$field] == 1) {
				$newField = $field;
			} else {
				$newField = $allowFields[$field];
			}
			
			if ($isAppend && strpos($default, $newField) !== false){
				continue;
			}
			$ret[] = $newField;
		}
		
		if (!count($ret)) {
			return $default;
		}
		
		unset($allowFields);
		$ret = implode(',', $ret);
		if ($isAppend) {
			$ret = "{$default},{$ret}";
		}
		return $ret;
	}
	
	/**
	 * 验证是否登录
	 */
	public function isLogin()
	{
		return isset($_SESSION['admin_auth']);
	}
	
	/**
	 * 获取当前登录用户的权限
	 * 
	 * @return 如果没有权限则返回null，如果是顶级管理员则返回true且$isTopAdmin为true 
	 * 		如果是普通管理员返回array
	 */
	public function getPurview(&$isTopAdmin = false)
	{
		$isTopAdmin = false;
		if (!$this->isLogin()) {
			return false;
		}
		
		$auth = explode(',', $_SESSION['admin_auth']);
		
		if ($auth[1] == 1) {
			$isTopAdmin = true;
			return true;
		}
		
		$model = new Ttk_Model_Adminer;
		if (!($admin = $model->get($auth[0]))) {
			return null;
		}

		return $admin['purview'] ? json_decode($admin['purview'], true) : null;	
	}
	
	/**
	 * 验证当前的用户是否有权限访问
	 * 
	 * @param string $url 如果为空的话，默认为controllor/action
	 * @param boolean $exit 如果为True的话，则该函数自动会输出错误的代码，代码为-2，为false的话
	 * 		则会返回boolean
	 */
	public function checkPurview($url = '', $exit = true)
	{
		if (!$url) {
			$url = $this->C . '/' . $this->A;
		}
		$url = strtolower($url);
		
		$purview = $this->getPurview($isadmin);
		
		if (false === $purview) {
			$this->showResults(-1);
		}
		
		if (!$purview) {
			if ($exit) {
				$this->showResults(-2);
			}
			return false;
		}
		
		if (
			!$isadmin && 
			(isset($this->mSiteCfg['purview'][$url]) && !isset($purview[$url]) )
			) {
			if ($exit) {
				$this->showResults(-2);
			}
			return false;
		} 
		
		return true;
	}
	
	/**
	 * 验证码
	 */
	public function codeAction()
	{
		$width = trim($this->mRequest->w);
		$height = trim($this->mRequest->h);
		$r = trim($this->mRequest->r);
		$g = trim($this->mRequest->g);
		$b = trim($this->mRequest->b);
		
		if (!Lamb_Utils::isInt($width, true)) {
			$width = 120;
		}
		
		if (!Lamb_Utils::isInt($height, true)) {
			$height = 48;
		}
		
		$img = new Lamb_CodeFile();
		$img->SetCheckImageWH($width, $height)->setBgRGB($r, $g, $b);
		$img ->OutCheckImage();			
	}
	
	/**
	 * 生存登录服务端的authkey
	 * 
	 * @param array $data = array(
	 * 		'ip' => string 客户端的ip,
	 * 		'id' => int 管理员的用户ID，
	 * 		'username' => string 管理员的用户名
	 * ) 如果为null，则会自动获取
	 * @return string
	 */
	protected function generateAuthkey($data = null)
	{
		if (!$this->isLogin()) {
			return '';
		}
		
		if (null === $data) {
			$adminid = explode(',', $_SESSION['admin_auth']);
			$data = array(
				'username' => $_SESSION['admin_name'],
				'id' => $adminid[0],
				'ip' => $this->mRequest->getClientIp()
			);
		}
		
		$str = "{$data['ip']},{$data['id']},{$data['username']}";
		return Ttk_Utils::auth_encode($str, self::SERVER_LOGIN_AUTH_ENCODE_KEY, 360);
	}	
	
	/**
	 * 通用检测输入的数据，是否允许被修改
	 * 
	 * @param array & $inputData 客户端输入的数据
	 * @param array $fieldsRule 字段规则，其结构 = array(
	 * 		'字段的名字' => array(
	 * 			'exec' => mixed,根据type的值不同，含义也不同
	 * 			'type' => int 类型，1表示exec字段为函数，2表示exec为正则表达式
	 * 							当type=1时，
	 * 								还会有以下几个字段
	 * 								param键，代表传入到exec的参数，可以允许为空
	 * 									如果为空的话，会自动代入$inputData，和$val作为exec的参数
	 * 									占位符，:inptdata:  :val:
	 * 									这2个占位符，代表$inputData和$val的位置，可选
	 * 							对于exec的返回值，如果检测成功则返回1，失败则返回<1
	 * 									<1，如果为0，则输出的错误码由系统自动累计得出，
	 * 										如果<0，则代表错误码，系统将会将其值输出
	 * 			'code' => int 错误码，如果为空，则会接着上一次错误码累减
	 * 								
	 * 		),
	 * 		......
	 * )
	 * @param int $errorCode 起始的错误码，主要用于自动累计错误码
	 */
	public function commCheckCanEditFields(&$inputData, $fieldsRule, $errorCode = -4)
	{
		$inputData = (object)$inputData;
		
		if ($errorCode != 0) {
			foreach ($fieldsRule as $rule) {
				if (!isset($rule['code'])) {
					$rule['code'] = $errorCode;
				} else {
					$errorCode = $rule['code'];
				}
				
				$errorCode --;
			}
		}
		
		foreach ($inputData as $filedname => $val) {
			if (!array_key_exists($filedname, $fieldsRule)) {
				$this->showResults(0);
			}
			
			$rule = $fieldsRule[$filedname];
			
			if (null === $rule || !$rule['exec']) {
				continue;
			}
			
			if ($rule['type'] == 2 && !preg_match($rule['exec'], $val)) {
				$this->showResults($rule['code']);
			}
			
			if ($rule['type'] == 1 && is_callable($rule['exec'])) {
				if (!isset($rule['param']) || !$rule['param']) {
					$rule['param'] = array($inputData, $val);
				} else {
					foreach ($rule['param'] as $pk => $pv) {
						if ($pv == ':inptdata:') {
							$rule['param'][$pk] = $inputData;
						} else if ($pv == ':val:') {
							$rule['param'][$pk] = $val;
						}
					}
				}
				
				if (($code = call_user_func_array($rule['exec'], $rule['param'])) < 1) {
					$this->showResults($code == 0 ? $rule['code'] : $code);
				}
			}			
		}
		
		$inputData = (array)$inputData;
		unset($inputData);
	}

	/**
	 * 通用删除处理函数
	 * 
	 * @param {Ttk_Model} $model
	 */
	public function commDelete($model)
	{
		if ($this->mRequest->isPost()) {
			$ids = $this->mRequest->getPost('id');
		} else {
			$ids = array(trim($this->mRequest->id));
		}
		if (!count($ids)) {
			$this->showResults(-1);
		}

		foreach ($ids as $id) {
			if (Lamb_Utils::isInt($id, true)) {
				$model->delete($id);	
			}
		}
		$this->showResults(1);		
	}
	
	public function uploadBase64File($data, $savePath)
	{
		if (($pos = strpos($data, ',')) === false) {
			return '';
		}
		$header = substr($data, 0, $pos);
		$mime = str_replace(';base64', '', str_replace('data:', '', $header));
		$ext = array(
			'image/jpeg' => '.jpg',
			'image/png' => '.png',
			'image/gif' => '.gif'
		);
		
		if (!array_key_exists($mime, $ext)) {
			return '';
		}
		$suffix = $ext[$mime];
		$filename = Lamb_Utils::crc32FormatHex(microtime(true) . rand(0, 1000)) . $suffix;
		file_put_contents($savePath . $filename, base64_decode(substr($data, $pos + 1)));
		
		return $filename;
	}
	
	/**
	 * 获取上传图片所需的参数
	 * req_data : 
	 * 		
	 * res_data = array(
	 * 		's' =>  1    删除成功
	 * 		'd' => string 
	 * )
	 */
	public function getParam()
	{
		$ret = '1,2.0,1122334455,' . Ttk_Utils::auth_encode(10000, self::SESSION_MECRYPT_KEY, 1800);
		return $ret;
	}
	
	public function debug($str)
	{
		Lamb_Debuger::debug($str);
	}
	
    /**
	 * 上传图片
	 * req_data:
	 * 	$file:string经过base64编码的图片数据字符串
	 *  $type 1-圈圈 2-虚拟礼物 3-积分礼物 4-虚拟礼物大图
	 * res_data:
	 * 	's' => 0 系统错 
	 *		   1 成功
	 *		  -1 没有 登录 
	 *		  -2 没有权限
	 * 		  -3 没有可用的上传
	 *        -4 type错误
	 * 		  -5 上传失败	
	 * 	'd' => array(
	 * 			'path' => string 上传成功后会返回同步的地址
	 * 		)
	 */
	public function uploadAction()
	{	
		if (!$this->mRequest->isPost()) {
			$this->showResults(-3);
		}
	
		$type = trim($this->mRequest->type);
		$file = Lamb_App_Response::decodeURIComponent($this->mRequest->getPost('file'));
		
		if (!$file) {
			$this->showResults(-3);
		}
		//echo $type;die;
		if ($type != 1 && $type !=2 && $type != 3 && $type != 4) {
			$this->showResults(-4);
		}
		$savePath = ROOT . 'temp_pics\\';
		
		if (!($path = $this->uploadBase64File($file, $savePath))) {
			$this->showResults(-5);
		}

		$path = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] . '/temp_pics/' . $path;
		
		$data= $this->syncRemote(urlencode($path), $type);
		$this->showResults(1, array('url' => $data));
	}
	
	/**
	 * 远程同步图片
	 * $type 1-圈圈 2-虚拟礼物 3-积分礼物
	 */
	public function syncRemote($path, $type)
	{	
		return Lamb_Http::quickGet("http://192.168.8.100:811/index.php?c=sync&a=index&path={$path}&type={$type}");
	}
	
	public function download($pic)
	{
		return Lamb_Http::quickPost("http://img.m.ttkvod.com/?s=index/index", array('site' => $pic));
	}
}
