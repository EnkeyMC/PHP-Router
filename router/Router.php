<?php 
/**
 * @package PHP-Router
 * @author Martin Omacht
 * @date 8.4.2017
 */

require_once __DIR__ . "/exceptions.php";
require_once __DIR__ . '/Route.php';

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
	public static function getParamURL()
	{
		$subdir = strtolower(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])));
		$url = str_replace('/'.$subdir, '', strtolower($_SERVER['REQUEST_URI']));
		return $url;
	}

	/**
	 * Parse URL and invoke controllers
	 * @throws RoutingException, RouteSyntaxException
	 * @return Retruns parameters
	 */
	public static function route()
	{
		self::$invoked = false;
		$url = self::getParamURL();
		$params = array();

		// Explode to SEF and GET part
		$sef_get = explode('?', $url);

		$sef = self::parseSEF($sef_get[0]);
		if (sizeof($sef_get) > 1) {
			$params = self::parseGET($sef_get[1]);
		}

		$matched = false;
		foreach (self::$routes as $route) {
			if ($route->matches($sef)) {
				$matched = true;
				$sefParams = $route->getParamArray($sef);
				$params = array_merge($sefParams, $params);

				if (isset($sefParams[Route::controller_id])) {
					$controller = $sefParams[Route::controller_id];
					$namespaceName = "none";
					if (isset($sefParams[Route::namespace_id])) {
						$namespaceName = $sefParams[Route::namespace_id];
					}
					$controllerInstance = new self::$controllers[$namespaceName][$controller]();
					$controllerInstance->invoke($params);
					self::$invoked = true;
				}
				break;
			}
		}

		if (!$matched)
			throw new RoutingException("Error: URL '$url' does not match any route.");

		return $params;
	}

	/**
	 * Parse SEF URL to array
	 * @param  string $sefURL SEF URL (e.g. "/foo/bar")
	 * @return array         Parsed array (e.g. array("foo", "bar"))
	 */
	protected static function parseSEF($sefURL)
	{
		$sefURL = trim($sefURL, " /");
		$sefArr = explode('/', $sefURL);

		return $sefArr;
	}

	/**
	 * Parse GET URL 
	 * @param  string $getURL GET URL (e.g. "view=home&task=edit")
	 * @return array         associative array of parameters (e.g. array("view" => "home", "task" => "edit"))
	 * @throws RoutingException, RouteSyntaxException
	 */
	protected static function parseGET($getURL)
	{
		$params = explode('&', $getURL);
		$getArr = array();

		foreach ($params as $param) {
			$kv = explode('=', $param);

			if (sizeof($kv) === 1) {
				$getArr[$kv[0]] = NULL;
			} else if (sizeof($kv) === 2) {
				$getArr[$kv[0]] = $kv[1];
			} else {
				throw new RoutingException("Error: Could not parse '$param' parameter from GET URL.");				
			}
		}

		return $getArr;
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
	 * @param  array $assoc_arr associative array of aliases and controller classes (alias is changed to lowercase)
	 * e.g.:
	 * 'home' => HomeController::class
	 * 'login' => LoginController::class
	 *
	 * '' => DefaultController::class - sets a default controller
	 *
	 * @param  string $namespace specifies the namespace of controller (e.g.: "administration"). Default: "none"
	 */
	public static function registerControllers($assoc_arr, $namespace = "none")
	{
		if (!is_array(self::$controllers)) {
			self::$controllers = array();
		}

		foreach ($assoc_arr as $alias => $controllerClass) {
			self::$controllers[$namespace][strtolower($alias)] = $controllerClass;			
		}
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
	 *
	 * "/<controller:home,about>/"
	 * 		* <controller:home,about> - applies only to 'home' and 'about' controller
	 *
	 * @throws RouteSyntaxException
	 */
	public static function registerRoute($route)
	{
		if (!is_array(self::$routes)) {
			self::$routes = array();
		}

		self::$routes[] = new Route($route);
	}

	public static function getControllers()
	{
		return self::$controllers;
	}

	public static function namespaceExists($namespaceName)
	{
		return isset(self::$controllers[$namespaceName]);
	}

	public static function controllerRegistered($controller, $namespaceName = "none")
	{
		return isset(self::$controllers[$namespaceName][$controller]);
	}
}