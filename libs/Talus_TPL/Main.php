<?php
/**
 * Template Engine
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 * @package Talus' TPL
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @copyright ©Talus, Talus' Works 2006+
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.gnu.org/licenses/lgpl.html LGNU Public License 2+
 */

namespace Talus_TPL;

if (!defined('SAFE')) exit;
if (!defined('PHP_EXT')) define('PHP_EXT', pathinfo(__FILE__, PATHINFO_EXTENSION));


class Main {
  protected
    $_root = './',

    $_tpl = '',
    $_last = array(),
    $_included = array(),

    $_blocks = array(),
    $_vars = array(),

    /**
     * @var Interfaces\Parser
     */
    $_parser = null,

    /**
     * @var Interfaces\Cache
     */
    $_cache = null;

  protected static $_autoloadSet = false;

  const
    INCLUDE_TPL = 0,
    REQUIRE_TPL = 1,
    VERSION = 'Custom';

  /**
   * Initialisation.
   *
   * @param string $root Directory where the templates files are.
   * @param string $cache Directory where the php version of the templates will be stored.
   * @param array $dependencies Dependency Injection
   * @param boolean $autoload Set the autoloader (recommanded !) ?
   * @return void
   */
  public function __construct($root, $cache, array $dependencies = array(), $autoload = true){
    // -- Resetting the PHP cache concerning the files' information.
    clearstatcache();

    // -- Setting the autoload for the whole library
    if (self::$_autoloadSet === false) {
      if ($autoload == true) {
        spl_autoload_register('self::_autoload');
      }
      
      self::$_autoloadSet = true;
    }

    // -- Parameters' initialisation
    $this->_last = array();
    $this->_included = array();
    $this->_blocks['.'] = array(array());
    $this->_vars = &$this->_blocks['.'][0];

    // -- Dependency Injection
    $this->dependencies($dependencies);

    // -- Default behaviour if no dependency injection
    if ($this->_parser === null) {
      $this->_parser = new Parser;
    }

    if ($this->_cache === null) {
      $this->_cache = new Cache;
    }

    $this->dir($root, $cache);
  }

  /**
   * Autoloader
   *
   * If the class to load is from this current library, tries a smart load of the
   * file from this directory.If it fails, throws an Exceptions\Autoload
   * exception.
   *
   * @param string $class Class' name
   * @throws Exceptions\Autoload Class declaration not found
   * @return bool False if not a class from this library or file not found, true
   *              if everything went smoothly
   */
  private static function _autoload($class) {
    $dir = explode('\\', $class);

    if ($dir[0] !== __NAMESPACE__) return false;

    \array_shift($dir); \array_unshift($dir, __DIR__);
    $file = implode(DIRECTORY_SEPARATOR, $dir) . '.' . PHP_EXT;

    if (!is_file($file)) {
      throw new Exceptions\Autoload(array('Unrecognized class : %s', $class), 8);
      return false;
    }

    require $file;
    return true;
  }

  /**
   * Set the templates & cache directory.
   *
   * @param string $root Directory containing the original templates.
   * @param string $cache Directory containing the cache files.
   * @throws Exceptions\Dir
   * @return void
   *
   * @since 1.7.0
   */
  public function dir($root = './', $cache = './cache/') {
    // -- Removing the final "/", if it's there.
    $root = rtrim($root, '/');

    if (!is_dir($root)) {
      throw new Exceptions\Dir(array('%s is not a directory.', $root), 1);
    }

    $this->_root = $root;

    // -- Let the cache engine handle his own directory !
    $this->_cache->dir($cache);
  }

  /**
   * Setter (and getter) for the templates variables.
   *
   * @param array|string $vars Var(s)
   * @param mixed $value Var's value if $vars is not an array
   * @return &array
   *
   * @since 1.3.0
   */
  public function &set($vars, $value = null){
    if (is_array($vars)) {
      $this->_vars = array_merge($this->_vars, $vars);
    } elseif ($vars !== null) {
      $this->_vars[$vars] = $value;
    }

    return $this->_vars;
  }

  /**
   * Set a variable $var, referencing $value.
   *
   * @param mixed $var Var's name
   * @param mixed &$value Variable to be referenced by $var
   * @throws Exceptions\Var
   * @return void
   *
   * @since 1.7.0
   */
  public function bind($var, &$value) {
    if (mb_strtolower(gettype($var)) != 'string') {
      throw new Exceptions\Vars('Reference\'s name not valid.', 3);
    }

    $this->_vars[$var] = &$value;
  }

  /**
   * Add an iteration to the block $block
   *
   * Can act as a getter for this block if $vars is null and $block is a
   * root block. If $vars is not null, a reference to this block will be returned ;
   * If $vars is not an array (lets say... a string), $value will be the value of
   * the only variable for this iteration.
   *
   * @param string $block Block's name.
   * @param array|string $vars Variable(s) to be used in this iteration
   * @param string $value $vars value if $vars is a string
   * @return void
   *
   * @since 1.5.1
   */
  public function &block($block, $vars, $value = null) {
    /*
     * If $block is a root block (no parents), and $vars is null, this method acts
     * as a getter for this block, without allowing any extra iteration or variables
     * assigned to this block.
     */
    if ($vars === null) {
      if (strpos($block, '.') === false) {
        $return = array();

        if (isset($this->_blocks[$block])) {
          $return = &$this->_blocks[$block];
        }

        return $return;
      }

      throw new Exceptions\Block('Variable\'s name not valid.');
    }

    if (!is_array($vars)) {
      $vars = array($vars => $value);
    }

    /*
     * Fetching all the blocks (from top parent to bottom child).
     *
     * The purpose is to browse all the descendant tree of the root block, and
     * then access the block which we need to work on, adding a new iteration.
     * A reference will be made in order to simplify everything.
     */
    $blocks = explode('.', $block);
    $curBlock = array_pop($blocks); // Block needed
    $current = &$this->_blocks;
    $cur = array();
    $nbRows = 0;

    foreach ($blocks as &$cur) {
      if (!isset($current[$cur])) {
        throw new Exceptions\Block(array('The <b>%s</b> block is not defined.', $cur), 4);
      }

      $current = &$current[$cur];
      $current = &$current[count($current) -  1];
    }

    if (!isset($current[$curBlock])) {
      $current[$curBlock] = array();
      $nbRows = 0;
    } else {
      $nbRows = count($current[$curBlock]);
    }

    /*
     * Special block variables
     *
     * FIRST : Is this the first iteration (true / false) ?
     * LAST : Is this the last iteration (true/false) ?
     * CURRENT : Current iteration.
     * SIZE_OF : Block's size
     *
     * We could be on the first iteration ; But we are surely on the last
     * iteration.
     *
     * If the number of rows in this block is greater than zero, the new iteration
     * we're creating is NOT the first iteration (it already exists !), and
     * the previous iteration is not the last anymore.
     *
     * Regarding {$block.SIZE_OF}, evrything we need is just to reference it to
     * the first {$block.SIZE_OF} we made with the first iteration, and then
     * increment that value.
     */
    $vars['FIRST'] = true;
    $vars['LAST'] = true;
    $vars['CURRENT'] = $nbRows + 1;
    $vars['SIZE_OF'] = 0;

    if ($nbRows > 0) {
      $vars['FIRST'] = false;
      $current[$curBlock][$nbRows - 1]['LAST'] = false;

      $vars['SIZE_OF'] = &$current[$curBlock][0]['SIZE_OF'];
    }

    ++$vars['SIZE_OF'];
    $current[$curBlock][] = $vars;

    return $current[$curBlock];
  }

  /**
   * Parse and execute the Template $tpl.
   *
   * If $tpl is an array of files, all the files will be parsed.
   *
   * @param mixed $tpl TPL to be parsed & executed
   * @param mixed $cache If the cache exists, use it
   * @throws Exceptions\Parse
   * @return bool
   */
  public function parse($tpl, $cache = true){
    if (func_num_args() > 2 || is_array($tpl)) {
      // -- Removing the second arg ($cache)
      if (func_num_args() > 2) {
        $tpl = func_get_args();
        array_shift($tpl); array_shift($tpl); array_unshift($tpl, func_get_arg(0));
      }

      foreach ($tpl as &$file) {
        $this->parse($file);
      }

      return true;
    }

    // -- Critical error if the argument $tpl is empty
    if (strlen((string) $tpl) === 0) {
      throw new Exceptions\Parse('No template to be parsed.', 5);
    }

    $file = sprintf('%1$s/%2$s', $this->_root, $tpl);

    if (!isset($this->_last[$file])) {
      if (!is_file($file)) {
        throw new Exceptions\Parse(array('The template <b>%s</b> doesn\'t exist.', $tpl), 6);
      }

      $this->_last[$file] = filemtime($file);
    }

    $this->_tpl = $tpl;
    $this->_cache->file($this->_tpl, 0);

    if (!$this->_cache->isValid($this->_last[$file]) || !$cache) {
      $this->_cache->put($this->str(file_get_contents($file), false));
    }

    $this->_cache->exec($this);
    return true;
  }

  /**
   * Parse & execute a string
   *
   * @param string $str String to parse
   * @param bool $exec Execute the result ?
   * @throws Exceptions\Parse
   * @return string PHP Code generated
   */
  public function str($str, $exec = true) {
    if (empty($str)) {
      return '';
    }

    // -- Compilation
    $compiled = $this->parser()->parse($str);

    // -- Cache if need to be executed. Will be destroyed right after the execution
    if ($exec === true) {
      $this->_tpl = sprintf('tmp_%s.html', sha1($str));
      $this->cache()->file($this->_tpl, 0);
      $this->cache()->put($compiled);
      $this->cache()->exec($this);
      $this->cache()->destroy();
    }

    return $compiled;
  }

  /**
   * Parse a TPL
   * Implémention of magic method __invoke() for PHP >= 5.3
   *
   * @param mixed $tpl TPL to be parsed & executed
   * @see Talus_TPL::parse()
   * @return void
   */
  public function __invoke($tpl) {
    return $this->parse($tpl);
  }

  /**
   * Parse and execute a template
   *
   * Do the exact same thing as Talus_TPL::parse(), but instead of just executing
   * the template, returns the final result (already executed by PHP).
   *
   * @param string $tpl Template's name.
   * @param integer $ttl Time to live for the cache 2. Not implemented yet
   * @return string
   *
   * @todo Cache 2 ?
   */
  public function pparse($tpl = '', $ttl = 0){
    ob_start();
    $this->parse($tpl);
    return ob_get_clean();
  }

  /**
   * Include a template into another
   *
   * @param string $file File to include.
   * @param bool $once Allow the inclusion once or several times
   * @param integer $type Inclusion or requirement ?
   * @return void
   *
   * @see Compiler::compile()
   * @throws Exceptions\Runtime
   * @throws Exceptions\Parse
   */
  public function includeTpl($file, $once = false, $type = self::INCLUDE_TPL){
    // -- Parameters extraction
    $qString = '';

    if (strpos($file, '?') !== false) {
      list($file, $qString) = explode('?', $file, 2);
    }

    /*
     * If the file have to be included only once, checking if it was not already
     * included.
     *
     * If it was, we're not treating it ; If not, we add it to the stack.
     */
    if ($once){
      $toInclude = sprintf('%1$s/%2$s', $this->root(), $file);

      if (in_array($toInclude, $this->_included)) {
        return;
      }

      $this->_included[] = $toInclude;
    }

    $data = '';
    $current = array(
      'vars' => $this->_vars,
      'tpl' => $this->_tpl
     );

    try {
      // -- Changing the variables only if there is a QS
      if (!empty($qString)) {
        // -- Parameters recuperation
        $vars = array();
        parse_str($qString, $vars);

        // -- If MAGIC_QUOTES is ON (grmph), Removing the \s...
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
          $vars = array_map('stripslashes', $vars);
        }

        // -- Adding the new variables to this template
        $this->set(array_change_key_case($vars, CASE_UPPER));
      }

      $data = $this->pparse($file);
    } catch (Exceptions\Parse $e) {
      /*
       * If we encounter error n°6 AND it is a require tag, throws an exception
       * Exceptions\Runtime instead of Exceptions\Parse. If not,
       * and still a n°6 error, printing the error message, or else throwing this
       * error back.
       */
      if ($e->getCode() === 6) {
        if ($type == self::REQUIRE_TPL) {
          throw new Exceptions\Runtime(array('That was a "require" tag ; The template <b>%s</b> not existing, the script is interrupted.', $file), 7);
          exit;
        }

        echo $e->getMessage();
      } else {
        throw $e;
      }
    }

    $this->_tpl = $current['tpl'];
    $this->_vars = $current['vars'];

    echo $data;
  }

  /**#@+
   * Getters / Setters
   */

  /**
   * Root
   *
   * @return string
   */
  public function root() {
    return $this->_root;
  }

  /**
   * Compiler
   *
   * @return Interfaces\Parser
   */
  public function parser() {
    return $this->_parser;
  }

  /**
   * Cache
   *
   * @return Interfaces\Cache
   */
  public function cache() {
    return $this->_cache;
  }

  /**
   * Dependency Injection handler.
   *
   * @contributor Jordane Vaspard
   * @param mixed $dependencies,.. Dependencies
   * @return void
   * @throws Exceptions\Dependency
   */
  public function dependencies($dependencies = array()) {
    if (func_num_args() > 1) {
      $dependencies = func_get_args();
    } elseif (!is_array($dependencies)) {
      $dependencies = array($dependencies);
    }

    foreach ($dependencies as &$dependency) {
      if ($dependency instanceof Interfaces\Compiler) {
        $this->_parser = $dependency;
      } elseif ($dependency instanceof Interfaces\Cache) {
        $this->_cache = $dependency;
      } else {
        throw new Exceptions\Dependency(
                array('%s is not an acknowledged dependency.', get_class($dependency)));
      }
    }
  }

  /**#@-*/
}

/*
 * EOF
 */
