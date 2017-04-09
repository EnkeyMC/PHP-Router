<?php 
/**
 * @package PHP-Router
 * @author Martin Omacht
 * @date 8.4.2017
 */

require_once __DIR__ . '/exceptions.php';
require_once __DIR__ . '/Router.php';

/**
* Class for route parsing and handling
*/
class Route
{
	const valid_name_chars = '[a-zA-Z0-9\-_]';
	const namespace_regex = '/^'.self::valid_name_chars.'+$/';
	const controller_regex = "/^<".self::controller_id."(:(".self::valid_name_chars."+,?)+)?>\??$/";
	const parameter_regex = '/^<'.self::valid_name_chars.'+>\??$/';

	// Segment types
	const controller_id = 'controller';
	const namespace_id = 'namespace';
	const param_id = 'param';

	protected $segments;

	/**
	 * Construct new Route object
	 * @param string $route route (@see Router::registerRoute)
	 * @warning $route is changed to lower case
	 */
	function __construct($route)
	{
		$route = strtolower($route);
		$this->parseRoute($route);
	}

	/**
	 * Parse route
	 * @param  string $route route (@see Router::registerRoute)
	 */
	protected function parseRoute($route)
	{
		$route = trim($route, " /");
		$segments = explode('/', $route);

		$order = 0;
		foreach ($segments as $segment) {
			if (strlen($segment) !== 0) {
				// Check for namespace
				if ($order == 0 && preg_match(self::namespace_regex, $segment)) {
					$this->segments[] = array(self::namespace_id => $segment);
				} 
				// Check for controller
				else if (preg_match(self::controller_regex, $segment)) { 
					$this->segments[] = array(self::controller_id => $this->parseController($segment));
				} 
				// Check for normal parameter
				else if (preg_match(self::parameter_regex, $segment)) { 
					$this->segments[] = array(self::param_id => $this->parseParam($segment));
				}
				else {
					throw new RouteSyntaxException("Error parsing route: '$route'. Invalid segment: '$segment'.");
				}
				$order++;
			}
		}
	}

	/**
	 * Parse controller segment (e.g. "<controller:home,about>" = array("home", "about"), "<controller>" = array())
	 * @param  string $controller_seg Controller segment
	 * @return array                 array of controllers
	 */
	protected function parseController($controller_seg)
	{
		$controller_seg = $this->strip($controller_seg);

		$data = array();

		$parts = explode(':', $controller_seg);
		if (sizeof($parts) > 1) {
			$data = explode(',', $parts[1]);
		}
		return $data;
	}

	/**
	 * Parse parameter segment (e.g. "<view>" = "view")
	 * @param  string $param_seg Parameter segment
	 * @return string            stripped segment
	 */	
	protected function parseParam($param_seg)
	{
		return $this->strip($param_seg);
	}

	protected function strip($str)
	{
		$str = trim($str, " <>");
		return $str;
	}

	public function getSegmentCount()
	{
		return sizeof($this->segments);
	}

	public function matches($sef)
	{
		$namespaceName = 'none';
		if (sizeof($sef) === $this->getSegmentCount()) { // have to be same size
			foreach ($sef as $key => $segment) {
				$routeSegment = $this->segments[$key];
				// Checks depending on segment type
				if (isset($routeSegment[self::namespace_id])) { // Namespace check
					if ($routeSegment[self::namespace_id] !== $segment)
						return false;
					if (!Router::namespaceExists($segment)) {
						throw new RouteSyntaxException("Error: namespace $namespaceName does not exist.");
					}
					$namespaceName = $segment;
				} else if (isset($routeSegment[self::controller_id])) { // Controller check
					if (sizeof($routeSegment[self::controller_id]) > 0) {
						foreach ($routeSegment[self::controller_id] as $controller) {
							if (!Router::controllerRegistered($controller, $namespaceName))
								throw new RouteSyntaxException("Error: controller '$controller' is not registered.");
						}

						if (!in_array($segment, $routeSegment[self::controller_id]))
							return false;
					} else {
						if (!Router::controllerRegistered($segment, $namespaceName))
							return false;
					}
				} else {
					// Params don't need checking
				}
			}
		} else {
			return false;
		}
		return true;
	}

	public function getParamArray($sef)
	{
		$params = array();
		$i = 0;

		foreach ($this->segments as $value) {
			if (array_keys($value)[0] === self::param_id) {
				$params[$value[self::param_id]] = $sef[$i];
			} else {
				$params[array_keys($value)[0]] = $sef[$i];
			}
			$i++;
		}
		return $params;
	}
}
