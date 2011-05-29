<?php
/**
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright ©Talus, Talus' Works 2010+
 * @link http://www.talus-works.net Talus' Works
 * @license http://creativecommons.org/licenses/by-sa/3.0/ CC-BY-SA 3.0+
 * @version $Id$
 */

namespace Routing;

if (!defined('SAFE')) exit;

/**
 * Definition of the Router Class
 *
 * Handles all the requests made towards the front controller.
 *
 * @package twk.routing
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 *
 * @todo Review the routing mechanism
 */
class Router {
  protected
    $_command = null,

    $_params = array(),
    $_namedParams = array(),

    $_routes = array(),

    /**
     * @var Route
     */
    $_matchedRoute = null;

  private $_started = false;

  /**
   * Route the current URI
   *
   * @param \Http\Request $_request Request object
   */
  public function route(\Http\Request $_request) {
    if ($this->hasStarted()) {
      return $this->_matchedRoute;
    }

    $p = $_request->requestUri();

    /**
     * Verifies for each route if it can be determined. If it can't, the default
     * will then be used.
     *
     * @var &$route Route
     */
    foreach ($this->_routes as $name => &$route) {
      if ($route->matches($p)) {
        \Debug::info('Route %s matched', $name);
        $this->_matchedRoute = $route;
      }
    }

    if ($this->_matchedRoute === null) {
      if (!isset($this->_routes['default'])) {
        $this->addRoute('default', new Route('home', 'index', '/'));
      }

      \Debug::info('No route found : using the default');
      $this->_matchedRoute = $this->_routes['default'];
    }
    
    return $this->_matchedRoute;
  }

  /**
   * Adds a route to the stack
   *
   * @param string $_name Name of the route
   * @param string $_route Route to add
   * @return void
   */
  public function addRoute($_name, Route $_route) {
    $this->_routes[$_name] = $_route;
  }

  /**
   * Fetchs one or more parameters
   *
   * @param string $p,... Parameters to get
   * @return mixed A string if there is only one parameter, null if the parameter
   *               was not found, or an array if multi-parameters.
   */
  public function get($p) {
    if (!$this->hasStarted()) {
      throw new Exception('The routing engine has not yet started !');
    }

    if (func_num_args() > 1) {
      $p = array();

      foreach (func_get_args() as $n) {
        $p[$n] = $this->get($n);
      }

      return $p;
    }

    return $this->_matchedRoute->get($p);
  }

  /**
   * Checks whether if a route was determined or not
   *
   * @return bool true if the route was determined
   */
  public function hasStarted() {
    return $this->_matchedRoute !== null;
  }
}

/*
 * EOF
 */
