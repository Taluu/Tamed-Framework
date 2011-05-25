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
   * @todo If it is not possible to work with REQUEST_URI, try to use the
   *       QUERY_STRING instead... And build another QUERY_STRING then.
   */
  public function __construct(\Http\Request $_request) {
    $p = strstr(trim($_request->requestUri()), '?', true);
    $p = $p ?: trim($_request->requestUri());

    $this->_params = array_filter(explode('/', $p));
    $this->_command = implode('/', $this->_params);

    $this->name('controller');
  }

  /**
   * Give a name to parameters.
   *
   * If there is not enough parameters to give it a name, the names will have a
   * null value. It names the parameters in the order they are presented in the URL.
   *
   * @param string $n,... Names
   * @return Router
   */
  public function name($n) {
    $args = func_get_args(); $args = array_map('mb_strtolower', $args);

    foreach ($args as &$arg) {
      if ($arg == 'controller' && isset($this->_namedParams['controller'])) {
        throw new Exception('controller is a special parameter and can not be used outside its context.');
        continue;
      }

      $this->_namedParams[$arg] = array_shift($this->_params);
    }

    // -- Refreshing the command special parameter
    $commands = array();

    foreach ($this->_namedParams as $param => $val) {
      $commands[] = $param . ':' . $val;
    }

    $this->_command = implode('/', array_merge($commands, $this->_params));
    return $this;
  }

  /**
   *
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
