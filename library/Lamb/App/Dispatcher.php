<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_App
 */
class Lamb_App_Dispatcher extends Lamb_App_Dispatcher_Abstract
{
	/**
	 * Lamb_App_Dispatcher_Interface implemention
	 */
	public function invoke(Lamb_App_Router_Interface $router = null)
	{
		if (null === $router) {
			$router = Lamp_App::getInstance()->getRouter();
		}
		$controllor = $router->getControllor();
		$action = $router->getAction();
		if (!$controllor) {
			$controllor = $this->setOrGetDefaultControllor();
		}
		if (!$action) {
			$action = $this->setOrGetDefaultAction();
		}
		$this->getRealControllorAction($controllor, $action)
			 ->setOrGetControllor($controllor)
			 ->setOrGetAction($action);
		$controllorClass = $controllor . 'Controllor';
		$actionMethod = $action . 'Action';
		Lamb_Loader::loadFile($controllorClass . '.php', $this->getControllorPath(), true);
		if (!class_exists($controllorClass, false)) {
			throw new Lamb_App_Dispatcher_Exception("controllor \"$controllor\" in controllor path");
		}
		$objControllor = new $controllorClass;
		if (!method_exists($objControllor, $actionMethod)) {
			throw new Lamb_App_Dispatcher_Exception("action \"$actionMethod\" not found int controllor \"$controllorClass\"");
		}
		$objControllor->$actionMethod();
	}
}