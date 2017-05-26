<?php
class Ttk_Model_Movie
{
	protected $mDb;
	
	public function __construct()
	{
		$this->mDb = Ttk_Db::get('movie');
	}
	
	/**
	 * 删除影片
	 * 
	 * @param int $id
	 * @return array | null
	 */
	public function delete($id)
	{
		$this->mDb->exec('delete from movie where id = ' . $id);
		$this->mDb->exec('delete from movie_source where mid =' . $id);	
		$this->mDb->exec('delete from tagrelation where mid =' . $id);	
		return $this;
	}
}
