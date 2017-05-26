<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_App_Dispatcher
 */
interface Lamb_App_Dispatcher_Interface
{
	/**
	 * Invoke a controllor's action if $router is null,
	 * then will use default router
	 *
	 * @param Lamb_App_Router_Interface
	 * @throws Lamb_App_Dispatcher
	 */
	public function invoke(Lamb_App_Router_Interface $router = null);
	
	/**
	 * Set controllor and action's alias
	 * eg: array(
	 *			'controllor_name' => [ alias_name,
	 *								array(
	 *									'name' => 'controllor_alias'
	 *									'action' => string | array
	 *								)
	 *						 	]
	 *		)
	 *
	 * @param array $alias
	 * @retrn Lamb_App_Dispather_Interface
	 */
	public function setAlias(array $alias);
	
	/**
	 * Get the real controllor and action's name from alias
	 *
	 * @param & string input output
	 * @param & string input output
	 * @return Lamb_App_Dispatcher_Interface
	 */
	public function getRealControllorAction(&$controllor, &$action);
	
	/**
	 * Set or retrieve the default controllor name
	 *
	 * @param string $controllor
	 * @return Lamb_App_Dispatcher_Interface | string [string if get,Lamb_App_Dispatcher_Interface if set]
	 */
	public function setOrGetDefaultControllor($controllor = null);
	
	/**
	 * Set or retrieve the default action name
	 *
	 * @param string $action 
	 * @return Lamb_App_Dispatcher_Interface | string [string if get, Lamb_App_Dispatcher_Interface if get]
	 */
	public function setOrGetDefaultAction($action = null);
	
	/**
	 * Set controllor path
	 * 
	 * @param string $path
	 * @return Lamb_App_Dispatcher_Interface
	 */
	public function setControllorPath($path = null);
	
	/**
	 * Retrieve the controllor path
	 *
	 * @return string
	 */
	public function getControllorPath();
	
	/**
	 * Retrieve the controllor
	 *
	 * @return string | Lamb_App_Dispatcher_Interface
	 */
	public function setOrGetControllor($controllor = null);
	
	/**
	 * Retrieve the action
	 *
	 * @return string | Lamb_App_Dispatcher_Interface
	 */
	public function setOrGetAction($action = null);
}