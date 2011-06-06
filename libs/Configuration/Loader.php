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

namespace Configuration;

/**
 * Definition of the Loader Class
 *
 * Loads config files (from /conf/ directory), and for each returns a new
 * Config object
 *
 * Each config file must be in JSON format.
 *
 * @package twk.configuration
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class Loader {
  protected
    $_dir = null,
    $_loadedConfig = array();

  public function __construct($_dir = './conf') {
    if (!is_dir($_dir)) {
      throw new \Exception('Config directory ' . $_dir . ' doesn\'t exists !');
    }

    $this->_dir = rtrim($_dir, '/') . '/';
  }

  /**
   * Gets the configuration stored in $_file
   *
   * The .json will automatically be prepended.
   *
   * @param string $_file File to be loaded
   * @param \Closure $_callback If filled, applies a callback on the resulting datas
   *
   * @return Config Config object.
   */
  public function get($_file, \Closure $_callback = null) {
    if (!isset($this->_loadedConfig[$_file])) {
      $this->_loadedConfig[$_file] = new Config($this->_dir . $_file . '.json');
    }

    $c = $this->_loadedConfig[$_file];

    if ($_callback !== null) {
      $c = $c->applyCallback($_callback);
    }

    return $c;
  }

  public function __get($_config) {
    return $this->get($_config);
  }
}

/*
 * EOF
 */
