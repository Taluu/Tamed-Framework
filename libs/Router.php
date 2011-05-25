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

if (!defined('SAFE')) exit;

/**
 * Definition of the Router Class
 *
 * Handles all the requests made towards the front controller.
 *
 * @package twk
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 *
 * @todo Review the routing mechanism
 */
class Router {
  protected
    $_command = null,
    $_params = array(),
    $_namedParams = array(),

    $_routes = array();

  /**
   * Route the current URI
   *
   * @param \Http\Request $_request Request object
   */
  public function route(\Http\Request $_request) {
    $p = strstr(trim($_request->requestUri()), '?', true);
    $p = $p ?: trim($_request->requestUri());

    $this->_params = array_filter(explode('/', $p));
    $this->_command = implode('/', $this->_params);

    $this->name('controller', 'home');
    $this->name('action', 'index');

    // @todo analyze the URI, and get all the correct parameters
  }

  /**
   * Give a name to the next parameter.
   *
   * If there is not enough parameters to give it a name, the names will have a
   * the $default value. It names the parameters in the order they are presented
   * in the URI.
   *
   * @param string $_name Name of this parameter
   * @param mixed $_default Default Value
   * @return Router
   */
  public function name($_name, $_default = null) {
    $_name = strtolower($_name);

    if ($_name == 'controller' && isset($this->_namedParams['controller'])) {
      throw new Exception('controller is a special parameter and can not be used outside its context.');
      continue;
    }

    if ($_name == 'action' && isset($this->_namedParams['action'])) {
      throw new Exception('action is a special parameter and can not be used outside its context.');
      continue;
    }

    $param = array_shift($this->_params);
    $this->_namedParams[$_name] = empty($param) ? $_default : $param;

    // -- Refreshing the command special parameter
    $commands = array();

    foreach ($this->_namedParams as $param => $val) {
      $commands[] = $param . ':' . $val;
    }

    $this->_command = implode('/', array_merge($commands, $this->_params));
    return $this;
  }

  /**
   * Adds a route to the stack
   *
   * Must consider that the first two matches are destined to be the
   *
   * @param string $_name Name of the route
   * @param string $_pattern Pattern of the road (including named parameters)
   * @return void
   */
  public function addRoute($_pattern) {
    /*
     * @todo
     *
     * /myController/myAction => class MyController, method myAction
     *
     * params : /[name:default]:regex/
     * regex type : :any, :alpha, :alphanum, :num
     *
     * if the name is present, it is an important parameter : else, we can simply
     * ignore it.
     */
  }

  /**
   * Fetchs one or more parameters
   *
   * @param string $p,... Parameters to get
   * @return mixed A string if there is only one parameter, null if the parameter
   *               was not found, or an array if multi-parameters.
   */
  public function get($p) {
    if (func_num_args() > 1) {
      $p = func_get_args();
      $return = array();

      foreach ($p as &$n) {
        $return[$n] = $this->get($n);
      }

      return $return;
    }

    if ($p == 'command') {
      return $this->_command;
    }

    if (isset($this->_namedParams[$p])) {
      return $this->_namedParams[$p];
    }

    return null;
  }
}

/*
 * EOF
 */
