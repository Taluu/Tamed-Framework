<?php
/**
 * Definition of the Router Class
 *
 * Handles all the requests made towards the front controller.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @package Talus' Works
 * @copyright Copyleft (c) 2010+, Baptiste Clavié, Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.gnu.org/licenses/lgpl.html LGNU Public License 2+
 */

if (!defined('SAFE')) exit;

class Router {
  protected
    $_command = null,
    $_params = array(),
    $_namedParams = array();

  public function __construct() {
    $p = trim($_SERVER['REQUEST_URI']); $qs = '';

    if (strstr($p, '?') !== false) {
      list($p, $qs) = explode('?', $_SERVER['REQUEST_URI'], 2);
    }

    // -- array_values is here used because array_filter preserves the keys
    $this->_params = array_values(array_filter(explode('/', $p)));

    // -- Query String recuperation if it's there
    if (strlen($qs) > 0) {
      $vars = array(); parse_str($qs, $vars);

      /*
       * Cleaning $_REQUEST, $_GET... And rebuilding them.
       *
       * @todo Check with the &request_order value to rebuild $_REQUEST
       *       (Even if $_REQUEST is never used on the whole project, just to
       *        make sure that it is correctly rebuilt !)
       */
      $_REQUEST = array(); $_GET = array();

      foreach ($vars as $var => &$val) {
        $_GET[$var] = $val;
      }

      $_REQUEST = array_merge($_GET, $_POST);
    }

    // -- Request URI interpretation.. Without the query string.
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
