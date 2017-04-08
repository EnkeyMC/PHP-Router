<?php 
/**
 * @package PHP-Router
 * @author Martin Omacht
 * @date 8.4.2017
 */

/**
* URL routing class
*/
class Router
{
	protected static $routes;
	protected static $controllers;
	protected static $invoked = false;

	/**
	 * Get URL for parameter parsing. Removes subfolders.
	 * @return string Reqested URL (/foo/bar)
	 */
	public static function getURL()
	{
		$subdir = strtolower(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])));
		$url = str_replace('/'.$subdir, '', strtolower($_SERVER['REQUEST_URI']));
		return $url;
	}

	/**
	 * Parse URL and invoke controllers
	 * @return Retruns parameters
	 */
	public static function route()
	{
		
	}

	/**
	 * Check if controller was invoked during route() call
	 * @return boolean wheter some controller was invoked or not
	 */
	public static function controllerInvoked()
	{
		return self::$invoked;
	}

	/**
	 * Register available controllers
	 * @param  array $key_value associative array (key gets lower cased)
	 * e.g.:
	 * 'home' => HomeController::class
	 * 'login' => LoginController::class
	 *
	 * First registred controller is default
	 *
	 * @param  string $namespace specifies the namespace of controller (e.g.: "administration")
	 */
	public static function registerControllers(array $assoc_arr, string $namespace = "none")
	{
		
	}

	/**
	 * Register route. Routes are evaluated in order of registering.
	 * @param  string $route route to register
	 * 
	 * e.g.: 
	 * "/<controller>/<action>/<id>"
	 * 		* <controller> - substitutes controller to invoke
	 * 		* <action> - creates parameter called 'action'
	 * 		* <id> - creates parameter called 'id'
	 * 	
	 * "/admin/<controller>/<view>"
	 * 		* admin - namespace 'admin'
	 * 		* <controller> - substitutes controller to invoke (only from namespace 'admin')
	 * 		* <view> - creates parameter called 'view'
	 */
	public static function registerRoute(string $route)
	{
		
	}
}