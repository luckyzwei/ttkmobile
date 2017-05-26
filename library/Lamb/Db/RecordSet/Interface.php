<?php
/**
 * Lamb Framework
 * Lamb_Db_RecordSet_Interface鍙€傜敤浜庡凡缁忓疄鐜颁简Traversable鎺ュ彛镄勫瓙绫?
 * 涓€鑸嚜瀹氢箟镄勭被鏄棤娉曞疄鐜瘪raversable鎺ュ彛锛屽彧链埘HP鍐呴儴绫绘墠琛?
 * 
 * @author 灏忕緤
 * @package Lamb_Db_RecordSet
 */
interface Lamb_Db_RecordSet_Interface extends Traversable,Countable
{
	/**
	 * 銮峰彇褰揿墠璁板綍板嗙殑镐绘暟锛屽綋鍓嶉〉镄勬€绘暟
	 *
	 * @return int
	 */
	public function getRowCount();
	
	/**
	 * 銮峰彇鏁版嵁婧愬垪镄勬暟鐩?
	 *
	 * @return int
	 */
	public function getColumnCount();
	
	/**
	 * 灏嗘暟鎹簮杞崲鎴愭暟缁?
	 *
	 * @return array
	 */
	public function toArray();
}