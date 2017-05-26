<?php
class Ttk_Model_Adminer
{
	protected $mDb;
	
	public function __construct()
	{
		$this->mDb = Ttk_Db::get('admin');
	}
	
	/**
	 * 获取管理员用户
	 * 
	 * @param string | int $key
	 * @param boolean $isid 如果该值为true则代表 $key为ID，否则为username
	 * @return null | array
	 */
	public function get($key, $isid = true)
	{
		$sql = 'select * from admin where id=:id';
		$aPrepare = array(':id' => array($key, PDO::PARAM_INT));
		
		if (!$isid) {
			$sql = 'select * from admin where username=:username';
			$aPrepare = array(':username' => array($key, PDO::PARAM_STR));
		}
		
		$data = $this->mDb->getNumDataPrepare($sql, $aPrepare, true);
		
		if ($data['num'] != 1) {
			return null;
		}
		
		return $data['data'];
	}
	
	/**
	 * 修改信息
	 * 
	 * @param array $adata
	 * @param int $id
	 * @return -1修改的用户不存在 -2 用户名已经存在  0执行失败 1成功
	 */
	public function update(array $data, $id = null)
	{
		if (!$id) {
			if (!isset($data['id'])) {
				return -1;
			}
			$id = $data['id'];
		}		
		unset($data['id'], $data['salt']);
		
		if (!($user = $this->get($id, true))) {
			return -1;
		}
		
		if (isset($data['username']) && $data['username'] != $user['username']) {
			
			if ($this->mDb->getNumDataPrepare(
							'select id from admin where username=? and id!=?',
							array(
								1 => array($data['username'], PDO::PARAM_STR),
								2 => array($id, PDO::PARAM_INT)
							)
						)) {
				return -2;				
			}
		}
		
		if (isset($data['password']) && empty($data['password'])) {
			unset($data['password']);
		}else if(isset($data['password']) && !empty($data['password'])) {
			$data['password'] = md5(md5($data['password']) . $user['salt']);
		}
		
		if (isset($data['name']) && empty($data['name'])) {
			unset($data['name']);
		}
		
		if (isset($data['purview']) && is_array($data['purview'])) {
			$data['purview'] = json_encode($data['purview']);
		}
		
		$table = new Lamb_Db_Table('admin', Lamb_Db_Table::UPDATE_MODE);
		return $table->setOrGetDb($this->mDb)->set($data)->setOrGetWhere('id=' . $id)->execute() ? 1 : 0;
	}
	
	/**
	 * 添加管理员
	 * 
	 * @param array $data
	 * @return -1用户名已经存在 0插入错误 >0ID
	 */
	public function add(array $data)
	{
		unset($data['id'], $data['salt']);
		$cfg = Lamb_Registry::get(CONFIG);
		
		if (!isset($data['username'], $data['password'])) {
			return 0;
		}
		
		if ($data['username'] == $cfg['admin']['username']) {
			return -1;
		}
		
		if(
			$this->mDb->getNumDataPrepare(
				'select id from admin where username=?', 
				array(1 => array($data['username'], PDO::PARAM_STR))
			)) {
			return -1;		
		}
			
		$salt = Ttk_Utils::createSalt();
		$data['password'] = md5(md5($data['password']) . $salt);
		
		if (!isset($data['regtime'])) {
			$data['regtime'] = time();
		}
		
		$data['salt'] = $salt;
		
		if (isset($data['purview']) && is_array($data['purview'])) {
			$data['purview'] = json_encode($data['purview']);
		}
		
		$table = new Lamb_Db_Table('admin', Lamb_Db_Table::INSERT_MODE);
		
		return $table->setOrGetDb($this->mDb)->set($data)->execute() ? $this->mDb->lastInsertId() : 0;
	}
	
	/**
	 * 删除记录
	 * 
	 * @param string | int $key ID或者用户名
	 * @param int $isid
	 * @return boolean
	 */
	public function delete($key, $isid = true)
	{
		$sql = "delete from admin where id= ?";
		$aPrepare = array(1 => array($key, PDO::PARAM_INT));
		
		if (!$isid) {
			$sql = "delete from admin where username=?";
			$aPrepare[1] = array($key, PDO::PARAM_STR);
		}

		return $this->mDb->quickPrepare($sql, $aPrepare)->toArray();
	}
}
