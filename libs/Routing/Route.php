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
 * Represents a route. 
 * 
 * A route is defined by a specific pattern, having specific arguments :
 *  :alpha represents a parameter allowing only alpha (a-zA-Z) values
 *  :num represents a parameter allowing only numeric (0-9) values
 *  :alphanum represents a parameter allowing only alphanumeric (a-zA-Z0-9) values
 *  :any represents a parameter allowing all possible values
 * 
 * Each part (/...) of the pattern must be in one of the following form :
 *  /[name]:arg (where :arg is one of the parameters described above)
 *  /:arg (same as above, but will not be captured)
 *  /[name]random-text (random(text is something fix, so [name] will always have that value)
 *  /random-text (same as above, but will not be captured)
 *
 * @package twk.routing
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class Route {
  /**
   * PHP Identifier
   */
  const REGEX_PHP_ID = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
  
  protected 
    $_action = null, 
    $_controller = null,
    $_command = null,
     
    $_pattern = null,
    
    $_params = array();

  /**
   * Constructor
   * 
   * @param string $_controller Controller's name
   * @param string $_action Action's name
   * @param string $_pattern Pattern matching this route
   */
  function __construct($_controller, $_action, $_pattern) {
    $this->_action = $_action;
    $this->_controller = $_controller;
    $this->_pattern = $_pattern;
  }
  
  /**
   * Parses the simplified pattern into a valid one
   * 
   * @return void
   */
  protected function _parse() {
    static $parsed = false;
    
    if ($parsed === true) {
      return;
    }
    
    $replace = array(
      ':any' => '[^/]+?',
      ':alpha' => '[a-zA-Z]+?',
      ':num' => '[0-9]+?',
      ':alphanum' => '[a-zA-Z0-9]+?'
     );
    
    $this->_pattern = preg_replace('`/\[(' . self::REGEX_PHP_ID . ')\]([^/]+?)`', '/(?P<$1>$2)', $this->_pattern);
    $this->_pattern = str_replace(array_keys($replace), array_values($replace), $this->_pattern);
    $this->_pattern = '`^' . $this->_pattern . '$`';
  }
    
  /**
   * Matches the pattern against a string
   * 
   * @param string $_requestUri URI to be checked
   * @return bool true if the uri is matching the route
   */
  public function match($_requestUri) {
    static $matched = array();
    
    if (isset($matched[$_requestUri])) {
      return $matched[$_requestUri];
    }
    
    $this->_parse();
    $matches = array();
    
    $matched[$_requestUri] = preg_match($this->_pattern, $_requestUri, $matches, PREG_OFFSET_CAPTURE);
    
    if (!$matched[$_requestUri]) {
      return false;
    }
    
    // -- Removing the first "match" (which is everything, in fact)
    array_shift($matches);
    $previous = null;
    
    foreach ($matches as $key => &$val) {
      $tmp = '/' . $key . ':' . $val[0];
      
      if (filter_var($key, FILTER_VALIDATE_INT) !== false) {
        if ($previous !== null && $previous === $val) {
          continue;
        }
        
        $tmp = '/' . $val[0];
      }
      
      $previous = $val;
      $this->_matches[$key] = $val[0];
      $this->_command .= $tmp;
    }
    
    return true;
  }
  
  /**
   * Magic method, gets one of the special attributes (controller, command, action)
   * 
   * @param string $n parameter to be fetched
   * @return string value of the parameter
   * @throws \Exception
   */
  function __get($n) {
    if (in_array($n, array('action', 'command', 'controller'))) {
      $n = '_' . $n;
      return $this->$n;
    }
    
    throw new \Exception('Fetching an unknown attribute (%s)', $n);
  }
  
  /**
   * Gets the parameter $n
   *   
   * @param string $n Parameter to fetch
   * @return mixed value of the parameter if set, null otherwise
   */
  function get($n) {
    if (in_array($n, array('action', 'command', 'controller'))) {
      return $this->$n;
    }
    
    if (isset($this->_params[$n])) {
      return $this->_params[$n];
    }
    
    return null;
  }
}

/*
 * EOF
 */
