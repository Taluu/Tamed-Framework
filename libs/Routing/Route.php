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

namespace Routing;

if (!defined('SAFE')) exit;

/**
 * Represents a route.
 *
 * A route is defined by a specific pattern, having specific arguments :
 *  :alpha represents a parameter allowing only alpha (a-zA-Z) values
 *  :num represents a parameter allowing only numeric (0-9) values
 *  :alphanum represents a parameter allowing only alphanumeric (a-zA-Z0-9) values
 *  :slug represents a slug (this-is-a-slug) ; note, a slug is __always__ in lower case
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
 *
 * @property-read string $controller The requested controller
 * @property-read string $action The requested action
 * @property-read string $command The requested command
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

    $_patterns = null,

    $_vars = array(),
    $_matchResult = array(),
    $_params = array();

  /**
   * Constructor
   *
   * @param string $_controller Controller's name
   * @param string $_action Action's name
   * @param string $_pattern Pattern matching this route
   */
  function __construct($_controller, $_action, $_pattern) {
    if (!is_array($_pattern)) {
      $_pattern = array($_pattern);
    }

    $this->_action = $_action;
    $this->_controller = $_controller;
    $this->_patterns = $_pattern;

    $this->_addVar(':alphanum', '[a-zA-Z0-9]+');
    $this->_addVar(':alpha', '[a-zA-Z]+');
    $this->_addVar(':slug', '[a-z0-9-]+');
    $this->_addVar(':num', '[0-9]+');
    $this->_addVar(':any', '.+');

    $this->_parse();
  }

  /**
   * Parses the simplified pattern into a valid one
   *
   * @return void
   */
  protected function _parse() {
    foreach ($this->_patterns as &$pattern) {
      $pattern = preg_quote($pattern, '`');
      $pattern = preg_replace('`/\\\\\[(' . self::REGEX_PHP_ID . ')\\\\\]([^/]?)`', '/(?P<$1>$2)', $pattern);
      $pattern = str_replace(array_keys($this->_vars), array_values($this->_vars), $pattern);

      $pattern = '`^' . $pattern . '$`';
    }
  }

  /**
   * Matches the pattern against a string
   *
   * @param string $_requestUri URI to be checked
   * @return bool true if the uri is matching the route
   */
  public function match($_requestUri) {
    if (isset($this->_matchResult[$_requestUri])) {
      return $this->_matchResult[$_requestUri];
    }

    $this->_matchResult[$_requestUri] = false;

    // -- Testing every patterns
    foreach ($this->_patterns as &$pattern) {
      $matches = array();
      $this->_matchResult[$_requestUri] = preg_match($pattern, $_requestUri, $matches, PREG_OFFSET_CAPTURE);

      if ($this->_matchResult[$_requestUri]) {
        break;
      }
    }

    if (!$this->_matchResult[$_requestUri]) {
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
      $this->_params[$key] = $val[0];
      $this->_command .= $tmp;
    }

    return true;
  }

  /**
   * @ignore
   */
  public function __get($n) {
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
  public function get($n) {
    if (in_array($n, array('action', 'command', 'controller'))) {
      return $this->${'_' . $n};
    }

    if (isset($this->_params[$n])) {
      return $this->_params[$n];
    }

    return null;
  }

  /**
   * Adds a possibility to parse a parameter type :something in the pattern
   *
   * @param string $param Parameters name
   * @param string $regex Regex interpreting this parameter
   * @param boolean $force Declare this parameter with the new regex if it exists ?
   */
  private function _addVar($param, $regex, $force = false) {
    if ($param[0] !== ':') {
      $param = ':' . $param;
    }

    $param = preg_quote($param, '`');

    if (!isset($this->_vars[$param]) || $force === true) {
      $this->_vars[$param] = $regex;
    }
  }
}

/*
 * EOF
 */
