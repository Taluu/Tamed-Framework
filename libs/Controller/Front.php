<?php
/**
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright ©Talus, Talus' Works 2010+
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.opensource.org/licenses/BSD-3-Clause Modified BSD License
 * @version $Id$
 */

namespace Tamed\Controller;

use Tamed\Http\Response, Tamed\Http\Request;
use Tamed\View\Bridge;
use Tamed\Configuration\Loader;
use Tamed\Debug, Tamed\Obj;

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
 * @property-read \View\Bridge $view View engine
 * @property-read \Http\Request $request Request Handler
 * @property-read \Http\Response $response Response Handler
 * @property-read \Routing\Route $route Route used
 * @property-read \Configuration\Loader $configuration Loaded configuration
 *
 * @package tamed.controller
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
abstract class Front {
  protected
    $_template = null,
    $_status = Response::OK,
    $_isForwarded = false,
    $_appDir = null,

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
    $_route = null,

    /**
     * @var \Configuration\Loader
     */
    $_config = null;


  /**
   * Starts and render the frame
   *
   * @param \Http\Request $_request HTTP Request handler
   * @param \Http\Response $_response HTTP Response handler
   * @param \View\Bridge $_view View engine
   */
  final protected function  __construct(Request $_request, Response $_response, Bridge $_view) {
    Obj::$controller = $this;

    $this->_view = $_view;
    $this->_request = $_request;
    $this->_response = $_response;
    $this->_route = Obj::$router->route($_request->getRequestUri());

    $this->_appDir = sprintf('%1$s/../../apps/%2$s', __DIR__, mb_strtolower($this->_route->controller));
    $this->_config = is_dir($this->_appDir . '/conf')
       ? new Loader($this->_appDir . '/conf', Obj::$config->getEnv(), Obj::$config)
       : Obj::$config;
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
      Debug::info('Loading main content');
      $this->_main($_action);
    } catch (Exception $e) {

    }

    list($engineName, $engineVersion) = $this->_view->getEngineInfos(Bridge::INFO_NAME | Bridge::INFO_VERSION);

    Debug::info('Rendering the view %1$s using %2$s (version %3$s)', Obj::$controller->_template, $engineName, $engineVersion);
    $content = $this->_view->render(Obj::$controller->_template);

    Debug::info('Sending the response to the user');
    $this->_response->status(Obj::$controller->_status);
    $this->_response->setContent($content);

    echo $this->_response->render();

    return $this;
  }

  /**
   * Actions to be runned before the treatment
   *
   * @return void
   */
  protected function _prepend() {}

  /**
   * Actions to be runned after the treatment
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
      Debug::warning('Action %1$s nonexistent', $_action);
      throw new Exception(sprintf('%1$s->%2$s Not found', get_class($this), $action));
    }

    Debug::info('Loading "%1$s->%2$s"', get_class($this), $_action);
    $this->_prepend();
    $this->{$action}();
    $this->_append();
  }

  /**
   * Prepare the page, matching a route if any
   *
   * Note that the controller must be in its full qualified name form
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
      'view' => new \Tamed\View\PHP
     ), $_options);

    Debug::info('Routing');
    $route = Obj::$router->route($options['request']->getRequestUri());

    if (isset($options['apploader']) && \is_callable($options['apploader'])) {
      \spl_autoloader_register($options['apploader']);
    }

    /*
     * NOTE : This will be removed when the ClassLoader will be effective.
     */
    \spl_autoload_register(function($controller) {
      // -- Verifying that this is a controller : it must have a \...\Controller\Name form
      $parts = \array_values(\array_filter(\explode('\\', $controller)));
      $count = count($parts);

      if ($parts[$count - 1] !== 'Controller') {
        return false;
      }

      $file = \sprintf('%1$s/../../Apps/%2$s/Controller.%3$s', __DIR__, $parts[$count - 2], \PHP_EXT);

      if (\is_file($file)) {
        require $file;
        return true;
      }

      return false;
     });

    Debug::info('Trying to start the subcontroller %1$s', $route->controller);
    return new $route->controller($options['request'], $options['response'], $options['view']);
  }

  /**
   * Forwards to a new controller and a new action
   *
   * Note : the controller should be in in full qualified form (namespaces included)
   *
   * @param string $_controller Controller's name
   * @param string $_action Action's name
   */
  final protected function _forward($_controller, $_action) {
    Debug::info('Forwarding to "%1$s->%2$s"', $_controller, $_action);

    /**
     * @var Front
     */
    $controller = new $_controller($this->_request, $this->_response, $this->_view);

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
   * Magic method, gets one of the authorized properties
   *
   * @param $name Name of the attribute
   * @return mixed
   */
  final public function __get($name) {
    if (\in_array($name, array('view', 'request', 'response', 'route'))) {
      return $this->{'_' . $name};
    }

    throw new \Exception($name . ' is not a recognized attribute for \Tamed\Controller\Front');
  }
}
