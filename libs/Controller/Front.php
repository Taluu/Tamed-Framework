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
    $_status = \Http\Response::OK;

  protected
    /**
     * View Engine
     *
     * @var \View\Bridge
     */
    $_view = null,

    /**
     * @var \Http\Request
     */
    $_request = null,

    /**
     * @var \Http\Response
     */
    $_response = null,

    /**
     * @var \Routing\Route
     */
    $_route = null;


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
    $this->_route = \Obj::$router->route($_request);

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
    $action = $this->_route->action;

    if (!method_exists($this, $action)) {
      \Debug::warning(sprintf('Action %1$s nonexistent', $action));
      $this->_response->redirect404('/error/notfound_404');
      $this->_status = \Http\Response::NOT_FOUND;

      exit;
    }

    \Debug::info('Loading action "%1$s"', $action);
    $this->_prepend();
    $this->$action();
    $this->_append();
  }

  /**
   * Prepare the page, matching a route if any
   *
   * @param self $_controller Controller to be used. null if it has to be guessed.
   * @param array $options Options array (to define new http handlers, view bridges, ...)
   * @return self Matched controller
   */
  final public static function getController(self $_controller = null, array $_options = array()) {
    if ($_controller !== null) {
      return $_controller;
    }
    
    $options = array_replace(array(
        'request' => new \Http\Request,
        'response' => new \Http\Response,
        'view' => new \View\Talus_TPL
      ), $_options);

    \Debug::info('Routing');
    $requestURI = $options['request']->requestUri();

    $route = \Obj::$router->route($requestURI['URI']);
    $file = sprintf('%1$s/../../apps/%2$s/controller.%3$s', __DIR__, $route->controller, PHP_EXT);

    if (!\is_file($file)) {
      \Debug::fatal('Controller %1$s not found', $route->controller);
      $_response->redirect404('/error/notfound_404');
      return;
    }

    require $file;

    $_controller = \mb_convert_case($route->controller, \MB_CASE_TITLE);
    $_controller = '\Controller\Sub\\' . $_controller;

    \Debug::info('Starting the subcontroller %1$s', $_controller);
    return new $_controller($options['request'], $options['response'], $options['view']);
  }

  /**
   * Forwards to a new controller and a new action
   *
   * @param type $_controller Controller's name
   * @param type $_action Action's name
   * @todo Well... Todo.
   */
  final protected function forward($_controller, $_action) {

  }

  /**
   * Sets a variable $var for the view
   *
   * @param string $var Variable's name
   * @param mixed $val  Variable's value
   * @return void
   */
  final public function __set($var, $val) {
    $this->_view->$var = $val;
  }

  /**
   * Magic method, gets one of the authorized method
   *
   * @param $name Name of the attribute
   * @return mixed
   */
  final public function __get($name) {
    if (in_array($name, array('view', 'request', 'response', 'route'))) {
      $name = '_' . $name;
      return $this->$name;
    }

    throw new Exception($name . ' is not a recognized attribute for \Controller\Front');
  }
}
