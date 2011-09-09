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
  const
    ENV_DEV = 0,
    ENV_TEST = 1,
    ENV_PROD = 2;

  protected
    $_dir = null,
    $_env = self::ENV_DEV,

    /**
     * @var Loader
     */
    $_base = null,
    $_loadedConfig = array();

  public function __construct($_dir = './conf', $_env = self::ENV_DEV, Loader $_base = null) {
    if (!is_dir($_dir)) {
      throw new \Exception('Config directory ' . $_dir . ' doesn\'t exists !');
    }

    $this->setEnv($_env);
    $this->_dir = rtrim($_dir, '/') . '/';
    $this->_base = $_base;
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
  public function get($_file, \Closure $_callback = null, $_dir = null) {
    $dir = $_dir ?: $this->_dir;
    $file = sha1($dir . $_file);

    if (!isset($this->_loadedConfig[$file])) {
      $this->_loadedConfig[$file] = $this->_base !== null && $this->_base->hasConfig($_file, $dir)
         ? $this->_base->get($file)->merge($dir . $_file . '.json')
         : new Config($dir . $_file . '.json', $this->_env);
    }

    $c = $this->_loadedConfig[$file];

    if ($_callback !== null) {
      $c = $c->applyCallback($_callback);
    }

    return $c;
  }

  public function hasConfig($_file, $_dir = null) {
    $dir = $_dir ?: $this->_dir;

    return is_file($dir . $_file);
  }

  /**
   * Getter for $this->_env
   *
   * @internal
   */
  public function getEnv() {
    return $this->_env;
  }

  /*
   * Setter for $this->_env
   *
   * @internal
   */
  public function setEnv($_env = self::ENV_DEV) {
    if (!in_array($_env, array(self::ENV_DEV, self::ENV_TEST, self::ENV_PROD))) {
      throw new \Exception('Environnement not recognized');
    }

    $this->_env = $_env;
  }
}

/*
 * EOF
 */
