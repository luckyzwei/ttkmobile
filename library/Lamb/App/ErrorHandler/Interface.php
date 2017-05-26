<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_App_ErrorHandle
 */
interface Lamb_App_ErrorHandler_Interface
{
	/**
	 * @param Exception $e
	 * @return void
	 */
	public function handle(Exception $e);
}