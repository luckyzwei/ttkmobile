<?php
class testControllor extends Ttk_Controllor_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getControllorName()
	{
		return 'test';
	}
	
	
	public function importAction()
	{
		$page = trim($this->mRequest->page);
		if (!Lamb_Utils::isInt($page, true)) {
			$page = 1;
		}

		$ret = $this->getTtkvodDb()->query("select top 500 * from (select top 500 ROW_NUMBER() OVER(order by uid) as RowNum, uid,nickname,username,status,salt,password,email,registerTime,regip,level,qq from member where uid>=680485 )as t where RowNum > 0")->toArray();
		
		$this->d($ret);
		
		if (empty($ret)) {
			exit('over');
		}
		
		foreach($ret as $key => $item) {
			$username = iconv("GBK", "UTF-8//IGNORE", $item['username']);
			$nickname = iconv("GBK", "UTF-8//IGNORE", $item['nickname']);
			$this->getDb('ttk_api')->query("insert into member (id,nickname,username,status,salt,password,email,regtime,regip,level,qq) values ({$item['uid']},'{$nickname}', '{$username}', {$item['status']}, '{$item['salt']}', '{$item['password']}','{$item['email']}',{$item['registerTime']},'{$item['regip']}',{$item['level']},'{$item['qq']}')");
		}
		
		
		
	}
	
	public function getTtkvodDb()
	{
		$dsn = 'sqlsrv:Database=ttkvod;Server=42.121.14.26,1543;MultipleActiveResultSets=true;LoginTimeout=10;TransactionIsolation=' . PDO::SQLSRV_TXN_READ_UNCOMMITTED;
		$username = 'ttkvod_beta_1.0';
		$password = 'benben2003~!';
				
		try{
			$objInstance	=	new Lamb_Mssql_Db($dsn, $username, $password, array(
										PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_SYSTEM,
										PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NAMED
									));
			$objInstance->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Lamb_Db_RecordSet', array($objInstance)));
		}catch (Exception $e){
			var_dump($e);
			die('Connect database error');
		}
		return $objInstance;
	}
	
	public function redirect($url)
	{
		echo "<script>
				function redirect(){
					window.location.replace('{$url}');
				}
				window.setTimeout('redirect();', 1000);
		</script>";
	}
	
	
	public function testAction()
	{
		$client = TopClient::getInstance();
		$req = new CloudpushNoticeIosRequest;
		$req->setSummary('您有一条新的公告');
		$req->setTarget('all');
		$req->setTargetValue('all');
		$req->setEnv('PRODUCT');
		$req->setExt('{"sound":"xy.mp3"}');
		$client->execute($req);
		
		/*
		$userApi = new Ttk_UserApi;
		$ret = $userApi->addUser(array(
			'username' => 'xxxxxxxx',
			'nickname' => '123123',
			'salt' => '123',
			'password' => '234234234234',
			'regip' => '192.168.8.58'
		), true);
		//$ret = $userApi->getInfoByUIds('1');
		$this->d($ret);
		//$ret = $userApi->getInfoByUsername('18679158318');
		*/
	}
	
	
}