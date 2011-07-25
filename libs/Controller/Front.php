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

use \Http\Response, Http\Request;
use \View\Bridge;

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
    $_status = \Http\Response::OK,
    $_isForwarded = false,

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
  final protected function  __construct(Request $_request, Response $_response, Bridge $_view) {
    \Obj::$controller = $this;

    $this->_view = $_view;
    $this->_request = $_request;
    $this->_response = $_response;
    $this->_route = \Obj::$router->route($_request);
  }

  /**
   * Renders the controller
   *
   * @param string $_action action to load. if null, tries to load it from the router component
   * @return void
   */
  final public function render($_action = null) {
    if ($_action === null) {
      $_action = $this->_route->action;
    }

    try {
      \Debug::info('Loading main content');
      $this->_main($_action);
    } catch (Exception $e) {

    }

    \Debug::info('Sending the response to the user');
    $this->_response->status(\Obj::$controller->_status);
    echo $this->_response->render(\Obj::$controller->_template);

    return $this;
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
   * @param string $_action action to be called
   * @throws Exception if the action is not found
   */
  final private function _main($_action) {
    $action = $_action . 'Action';

    if (!\method_exists($this, $action)) {
      \Debug::warning('Action %1$s nonexistent', $_action);
      throw new Exception(sprintf('%1$s->%2$s Not found', get_class($this), $action));
    }

    \Debug::info('Loading "%1$s->%2$s"', get_class($this), $action);
    $this->_prepend();
    $this->{$action}();
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

    $options = \array_replace(array(
      'request' => new Request,
      'response' => new Response,
      'view' => new \View\PHP
     ), $_options);

    \Debug::info('Routing');
    $requestURI = $options['request']->requestUri();

    $route = \Obj::$router->route($requestURI['URI']);

    if (isset($options['apploader']) && \is_callable($options['apploader'])) {
      \spl_autoloader_register($options['apploader']);
    }

    \spl_autoload_register(function($controller) {
      // -- Verifying that this is a controller : it must have a \Controller\Sub\Name form
      $parts = \array_values(\array_filter(\explode('\\', $controller)));

      if (\count($parts) !== 3) {
        return false;
      }

      if ($parts[0] !== __NAMESPACE__ || $parts[1] !== 'Sub') {
        return false;
      }

      $controller = \mb_convert_case($parts[2], \MB_CASE_LOWER);
      $file = \sprintf('%1$s/../../apps/%2$s/controller.%3$s', __DIR__, $controller, \PHP_EXT);

      if (\is_file($file)) {
        require $file;
        return true;
      }

      return false;
     });

    $_controller = \mb_convert_case($route->controller, \MB_CASE_TITLE);
    $_controller = __NAMESPACE__ . '\Sub\\' . $_controller;

    \Debug::info('Trying to start the subcontroller %1$s', $_controller);
    return new $_controller($options['request'], $options['response'], $options['view']);
  }

  /**
   * Forwards to a new controller and a new action
   *
   * @param string $_controller Controller's name
   * @param string $_action Action's name
   */
  final protected function _forward($_controller, $_action) {
    \Debug::info('Forwarding to "%1$s->%2$s"', $_controller, $_action);
    $controller = __NAMESPACE__ . '\Sub\\' . \mb_convert_case($_controller, \MB_CASE_TITLE);

    /**
     * @var \Controller\Front
     */
    $controller = new $controller($this->_request, $this->_response, $this->_view);

    $controller->_isForwarded = true;
    $controller->_main($_action);
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
    if (\in_array($name, array('view', 'request', 'response', 'route'))) {
      $name = '_' . $name;
      return $this->$name;
    }

    throw new \Exception($name . ' is not a recognized attribute for \Controller\Front');
  }
}
