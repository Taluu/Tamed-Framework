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

namespace Controller;

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
 * @package twk.controller
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
abstract class Front {
  protected
    $_template = null,
    $_vars = array(),
    $_status = \Http\Response::OK;

  protected
    /**
     * View Engine
     *
     * @var \View\iView
     */
    $_view = null,

    /**
     * @var \Http\Request
     */
    $_request = null,

    /**
     * @var Http\Response
     */
    $_response = null;


  /**
   * Starts and render the frame
   *
   * @param \Http\Request $_request HTTP Request handler
   * @param \Http\Response $_response HTTP Response handler
   * @param \View\Bridge $_view View engine
   */
  final protected function  __construct(\Http\Request $_request, \Http\Response $_response, \View\Bridge $_view) {
    \Obj::$controller = $this;

    $this->_request = $_request;
    $this->_response = $_response;
    $this->_view = $this->_response->view($_view);

    \Debug::info('Loading main content');
    $this->_main();

    \Debug::info('Sending the response to the user');
    $this->_response->status($this->_status);
    echo $this->_response->render($this->_template);
  }

  /**
   * Will be runned before the treatment
   *
   * If it has to be overridden, don't forget to call __this__ method !
   * <code>protected function _prepend() { parent::_prepend(); }</code>
   * ... Or at least, do the gist of what it is doing.
   *
   * @return void
   */
  protected function _prepend() {}

  /**
   * Will be runned after the treatment
   * If it has to be overridden, don't forget to call __this__ method !
   * <code>protected function _append() { parent::_append(); }</code>
   * ... Or at least, do the gist of what it is doing.
   *
   * @return void
   */
  protected function _append() {}

  /**
   * Main function
   *
   * @return bool True if it si correct, false otherwise
   */
  final private function _main() {
    $action = \Obj::$router->get('action');

    if (!method_exists($this, $action)) {
      \Debug::warning(sprintf('Action %1$s nonexistent', $action));
      $this->_response->redirect404('/error/notfound_404');
      $this->_status = \Http\Response::NOT_FOUND;

      exit;
    }

    \Debug::info('Loading action "' . $action . '"');
    $this->_prepend();
    $this->$action();
    $this->_append();
  }

  /**
   * Prepare the page, matching a route if any
   *
   * @param \Http\Request $_request HTTP Request Object
   * @param \Http\Response $_request HTTP Response Object
   * @param \View\Bridge $_view View engine
   * @param self $_controller Controller to be used. null if it has to guess it.
   * @return self
   *
   * @todo Review the routing mechanism
   */
  final public static function getController(\Http\Request $_request = null, \Http\Response $_response = null, \View\Bridge $_view = null, self $_controller = null) {
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

    \Debug::info('Routing');
    \Obj::$router->route($_request);

    $_controller = \Obj::$router->get('controller');
    $file = sprintf('%1$s/../../apps/%2$s/controller.%3$s', __DIR__, $_controller, PHP_EXT);

    // @todo handle correctly when the controller does not exist
    if (!\is_file($file)) {
      \Debug::fatal(sprintf('Controller %1$s not found', $_controller));
      $_response->redirect404('/error/notfound_404');
      return;
    }

    require $file;

    $_controller = \mb_convert_case($_controller, \MB_CASE_TITLE);
    $_controller = '\Controller\Sub\\' . $_controller;

    \Debug::info('Starting the subcontroller');
    return new $_controller($_request, $_response, $_view);
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

  /**
   * Returns the current HTTP Request object
   *
   * @return \Http\Request
   */
  public function getRequest() {
    return $this->_request;
  }

  /**
   * Returns the current HTTP Response object
   *
   * @return \Http\Request
   */
  public function getResponse() {
    return $this->_response;
  }
}
