<?php
/**
 * Main Controller
 *
 * This is the Entry Point, choosing which controller we'll be using, and how
 * to make it all work. It appends every needed things, like how to get a correct
 * header and a correct footer.
 *
 * To make it simple, this is the basis of all the sub-controllers ; in order to
 * make them work, they MUST extend this class.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @package Talus' Works
 * @copyright Copyleft (c) 2010+, Baptiste Clavié, Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @link http://www.talus-works.net Talus' Works
 * @license http://creativecommons.org/licenses/by-sa/3.0/ CC-BY-SA 3.0+
 * @version $Id$
 */

namespace Controller;

define('SAFE', true);
if (!defined('PHP_EXT')) define('PHP_EXT', \pathinfo(__FILE__, \PATHINFO_EXTENSION));

require __DIR__ . '/../libs/__init.' . PHP_EXT;

abstract class Front {
  protected
    /**
     * @var string
     */
    $_template = null,

    /**
     * View Engine
     *
     * @var \View\iView
     */
    $_view = null,

    /**
     * @var array
     */
    $_vars = array(),

    /**
     * @var \Http\Request
     */
    $_request = null,

    /**
     * @var Http\Response
     */
    $_response = null,

    /**
     * @var \Router
     */
    $_router = null;

  /**
   * Starts and render the frame
   *
   * @param \Http\Request $_request HTTP Request handler
   * @param \Http\Response $_response HTTP Response handler
   * @param \View\iView $_view View engine
   */
  final protected function  __construct(\Http\Request $_request, \Http\Response $_response, \View\iView $_view) {
    $this->_request = $_request;
    $this->_response = $_response;
    $this->_view = $this->_response->view($_view);

    $this->_main();

    $this->_response->render($this->_template);
  }

  /**
   * Will be runned before the treatment
   *
   * If it has to be overridden, don't forget to call __this__ method first !
   * <code>protected function _prepend() { parent::_prepend(); }</code>
   * ... Or at least, do the gist of what it is doing.
   *
   * @return void
   */
  protected function _prepend() {}

  /**
   * Will be runned after the treatment
   * If it has to be overridden, don't forget to call __this__ method first !
   * <code>protected function _append() { parent::_append(); }</code>
   * ... Or at least, do the gist of what it is doing.
   *
   * @return void
   */
  protected function _append() {}

  /**
   * Main function
   */
  final public function main() {
    $this->_prepend();
    $this->_dispatch();
    $this->_append();
  }

  /**
   * Dispatcher
   */
  abstract protected function dispatch();

  /**
   * Prepare the page, matching a route if any
   *
   * @param \Http\Request $_request HTTP Request Object
   * @param \Http\Response $_request HTTP Response Object
   * @param \View\iView $_view View engine
   * @param self $_controller Controller to be used. null if it has to guess it.
   * @return self
   *
   * @todo Review the routing mechanism
   */
  final public static function getController($_request = null, $_response = null, $_view = null, self $_controller = null) {
    if ($_controller !== null) {
      return $_controller;
    }

    if ($_request === null) {
      $_request = new \Http\Request;
    }

    if ($_response === null) {
      $_response = new \Http\Response;
    }

    if ($_view === null) {
      $_view = new \View\Talus_TPL;
    }

    // $router = new Router($_response);
    // $_controller = \mb_convert_case($router->get('controller') ?: 'home', \MB_CASE_TITLE);

    // require sprintf('%1$s/../apps/%2$s/controller.%3$s', __DIR__, $_controller, PHP_EXT);
    //
    // $_controller = 'Sub\\' . $_controller;
    // return new $_controller($_request, $_response, $_view);
  }

  /**
   * Sets a variable $var for the view
   *
   * @param string $var Variable's name
   * @param mixed $val  Variable's value
   * @return void
   */
  final public function __set($var, $val) {
    $this->_vars[$var] = $val;
  }

  /**
   * Unsets the variable $name
   *
   * @param string $name Variable's name
   * @return void
   */
  public function __unset($name) {
    unset($this->_vars[$name]);
  }

  /**
   * Checks if the variable $name is set
   *
   * @param string $name Variable's name
   * @return boolean
   */
  public function __isset($name) {
    return isset($this->_vars[$name]);
  }
}

$p = Front::getController();
$p->main();

/*
 * EOF
 */
