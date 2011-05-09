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
    $_template,
     
    /**
     * @var \Http\Request
     */
    $_request = null,
     
    /**
     * @var Http\Response
     */
    $_response = null;

  /**
   * Starts the frame
   * 
   * @param \Http\Request $_request HTTP Request handler
   * @param \Http\Response $_response HTTP Response handler
   */
  final protected function  __construct(\Http\Request $_request, \Http\Response $_response) {
    $this->_request = $_request;
    $this->_response = $_response;
    
    $this->_main();
    $this->_response->render($this->_template);
  }

  /**
   * Will be runned before the treatment
   */
  protected function _prepend() {}
  
  /**
   * Will be runned after the treatment
   */
  protected function _append() {}

  /**
   * Main function
   */
  final public function main() {}

  /**
   * Prepare the page, matching a route if any
   * 
   * @param \Http\Request $_request HTTP Request Object
   * @param \Http\Response $_request HTTP Response Object
   * @param self $controller Controller to be used. null if it has to guess it.
   * @return self
   */
  final public static function dispatch(\Http\Request $_request, \Http\Response $_response, self $controller = null) {
    if ($controller !== null) {
      return $controller;
    }
    
    $controller = $controller ?: \Obj::$router->get('controller');
    $controller = 'Sub\\' . \mb_convert_case($controller ?: 'home', \MB_CASE_TITLE);
    
    // require sprintf('%1$s/../apps/%2$s/controller.%2$s', __DIR__, PHP_EXT);
    // return new $controller($_request, $_response);
  }
}

$p = Front::dispatch(new \Http\Request, new \Http\Response);
$p->main();

/*
 * EOF
 */
