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

namespace Routing;

if (!defined('SAFE')) exit;

/**
 * Definition of the Route Class
 *
 * Represents a route.
 *
 * @package twk.routing
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class Route {
  protected 
    $_action, 
    $_controller,
    
    $_command,
    $_pattern,
    
    $_params = array();
  
  public function __construct($_pattern) {
    
  }
  
  /**
   * Matches the parameter against a specifig URI
   * 
   * @param array $_requestUri URI to be checked
   * @return bool if the uri is matching the route
   */
  public function match($_requestUri) {
    
  }
}

/*
 * EOF
 */
