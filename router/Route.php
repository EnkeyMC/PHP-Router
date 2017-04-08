<?php 
/**
 * @package PHP-Router
 * @author Martin Omacht
 * @date 8.4.2017
 */

require_once __DIR__ . '/exceptions.php';

/**
* Class for route parsing and handling
*/
class Route
{
	const controller_id = 'controller';
	const namespace_regex = '/^[a-zA-Z0-9]+$/';
	const controller_regex = "/^<".self::controller_id.":?([a-zA-Z0-9]+,?)*>\??$/";
	const parameter_regex = '/^<[a-zA-Z0-9]+>\??$/';

	/**
	 * Construct new Route object
	 * @param string $route route (@see Router::registerRoute)
	 */
	function __construct($route)
	{
		$this->parseRoute($route);
	}

	/**
	 * Parse route
	 * @param  string $route route (@see Router::registerRoute)
	 */
	protected function parseRoute($route)
	{
		$segments = explode('/', $route);
		var_dump($segments);

		$order = 0;
		foreach ($segments as $segment) {
			if (strlen($segment) !== 0) {
				// Check for namespace
				if ($order == 0 && preg_match(self::namespace_regex, $segment)) {
					var_dump("Namespace");
				} 
				// Check for controller
				else if (preg_match(self::controller_regex, $segment)) { 
					var_dump("Controller");
				} 
				// Check for normal parameter
				else if (preg_match(self::parameter_regex, $segment)) { 
					var_dump("param");
				}
				var_dump($segment);
				$order++;
			}
		}
	}
}
