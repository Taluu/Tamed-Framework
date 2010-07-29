<?php
/**
 * Definition of the Router Class
 *
 * Handles all the requests made to the front controller and all the website.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @package Talus' Works
 * @copyright Copyleft (c) 2010+, Baptiste Clavié, Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.gnu.org/licenses/lgpl.html LGNU Public License 2+
 *
 * @todo Need a global revision.
 */

if (!defined('SAFE')) exit;

class Router {
  protected
    $_params = array(),
    $_namedParams = array();

  public function __construct() {
    $this->_params = array_filter(explode('/', trim($_SERVER['REQUEST_URI'])));

    // -- Query String recuperation if it's there
    if (strrpos($this->_params[count($this->_params) - 1], '?') !== false) {
      $last = &$this->_params[count($this->_params) - 1];
      list($last, $qs) = explode('?', $last);

      $vars = array(); parse_str($qs, $vars);
      $_GET = array();

      foreach ($vars as $var => $val) {
        $_GET[$var] = $val;
      }

      $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
    }

    // -- Request URI interpretation.. Without the query string.
    $this->_namedParams['controller'] = array_shift($this->_params);
    $this->_params['command'] = implode('/', $this->_params);
  }

  /**
   * Give a name to parameters.
   *
   * If there is not enough parameters to give it a name, the names will be
   * discarded. It names the parameters in the order they are presented in the URL.
   *
   * @param string $n,... Names
   * @return Router
   */
  public function name($n) {
    $args = func_get_args(); $args = array_map('mb_strtolower', $args);
    $nbArgs = min(count($args), count($this->_params) - 1);

    for ($i = 0; $i < $nbArgs; ++$i) {
      if ($args[$i] == 'controller') {
        throw new Exception('controller is a special parameter and can not be used outside of its context.');
      }

      $this->_namedParams[$args[$i]] = array_shift($this->_params);
    }

    // -- Refreshing the command special parameter
    $commands = array();

    foreach ($this->_namedParams as $param => $val) {
      //if ($param == 'controller') continue; // NOT SURE
      $commands[] = $param . ':' . $val;
    }

    unset($this->_params['command']);
    $this->_params['command'] = implode('/', array_merge($commands, $this->_params));

    return $this;
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
      return $this->_params['command'];
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
