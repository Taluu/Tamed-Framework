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
 * Definition of Config class
 *
 * Represents a Configuration file, extracted from a JSON file in /conf/
 *
 * @package twk.configuration
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class Config implements \IteratorAggregate, \ArrayAccess {
  protected
    $_file = null,

    $_datas = array(),
    $_env = Loader::ENV_DEV,

    $_const = false,
    $_loaded = false;

  /**
   * Constructor
   *
   * @param string $_file Filename to be loaded
   */
  public function __construct($_file, $_env = Loader::ENV_DEV) {
    if (!in_array($_env, array(Loader::ENV_DEV, Loader::ENV_TEST, Loader::ENV_PROD))) {
      throw new \Exception('Environnement not recognized');
    }

    $env = array('development', 'test', 'production');

    $this->_file = $_file;
    $this->_env = $env[$_env];
    $this->_load();
  }

  /**
   * @ignore
   */
 private function __clone() {
    $this->_datas = $this->_datas;
    $this->_env = $this->_env;
    $this->_loaded = true;
    $this->_const = true;
  }

  /**
   * @ignore
   */
 public function __get($var) {
    $val = '';

    if ($this->get($var, $val) === false) {
      throw new Exception('Unknown configuration value for ' . $var);
    }

    return $val;
  }

  /**
   * @ignore
   */
 public function __set($var, $val) {
    $this->set($var, $val);
  }

  /**
   * @ignore
   */
 public function __isset($var) {
    return isset($this->_datas[$var]);
  }

  /**
   * Gets the `$cfg` prop from the config file
   *
   * @param string $cfg Property to fetch
   * @param mixed &$ret The returning value (null if this method must return the result)
   * @return mixed the data or null if it doesn't exists... Or a bool if `&$ret` is set
   */
 public function get($cfg, &$ret = null) {
    $datas = &$this->_datas;

    if (isset($datas[$this->_env])) {
      $datas = &$datas[$this->_env];
    }

    if ($ret !== null) {
      $ret = &$datas[$var];
      return (bool) $ret;
    }

    return isset($datas[$var]) ? $datas[$var] : null;
  }

  public function set($var, $val) {
    $datas = &$this->_datas;

    if (isset($datas[$this->_env])) {
      $datas = &$datas[$this->_env];
    }

    $datas[$var] = $val;
  }

  /**
   * Applies a callback on the configuration's data.
   *
   * The callback must be compatible with array_walk (the first argument is the
   * value, the second the key... and if there is a third argument, it should be
   * filled within `$_userdata`)
   *
   * @param \Closure $_callback Callback to be called
   * @param mixed $_userdata Datas to be sent to the callback
   * @return Config
   */
  public function applyCallback(\Closure $_callback, $_userdata = null) {
    if ($this->_loaded === false) {
      throw new \Exception('Applying a callback on something not loaded is merely impossible !');
      return null;
    }

    $config = clone $this;
    array_walk($config->_datas, $_callback, $_userdata);

    return $config;
  }

  /**
   * Saves the configuration into the file.. If it is not constant.
   */
  public function save() {
    if ($this->_const) {
      throw new Exception('Can\'t save into configuration file ' . $this->_file . ', because it is constant');
    }

    $datas = json_encode($this->_datas);
    $ex = $this->_checkJson();

    if ($ex !== null) {
      throw new \Exception('Bad JSON (' . $ex . ') in configuration file ' . $this->_file);
    }

    file_put_contents($this->_file, $datas);
  }

  /**
   * Loads from the configuration file
   *
   * @return void
   */
  protected function _load() {
    if ($this->_loaded === true) {
      return;
    }

    if (!is_file($this->_file)) {
      throw new \Exception('Configuration file ' . $this->_file . ' not found');
    }

    $this->_datas = json_decode(file_get_contents($this->_file), true);

    $ex = $this->_checkJson();

    if ($ex !== null) {
      throw new \Exception('Bad JSON (' . $ex . ') in configuration file ' . $this->_file);
    }

    // -- checking for special parameters, at the top of the json file
    if (isset($this->_datas['_isConst'])) {
      $this->_const = (bool) $this->_datas['_isConst'];
      unset($this->_datas['_isConst']);
    }

    $this->_loaded = true;
  }

  /**
   * @ignore
   */
  private function _checkJson() {
    switch (json_last_error()) {
      case JSON_ERROR_DEPTH:
        $ex = 'Maximum stack depth exceeded';
        break;

      case JSON_ERROR_STATE_MISMATCH:
        $ex = 'Invalid or malformed JSON';
        break;

      case JSON_ERROR_CTRL_CHAR:
        $ex = 'Control character error, possibly incorrectly encoded';
        break;

      case JSON_ERROR_SYNTAX:
        $ex = 'Syntax error';
        break;

      case JSON_ERROR_UTF8:
        $ex = 'Malformed UTF-8 characters, possibly incorrectly encoded';
        break;

      default:
      case JSON_ERROR_NONE:
        $ex = null;
    }

    return $ex;
  }


  /**
   * @ignore
   */
  public function offsetExists($offset) {
    return isset($this->$offset);
  }


  /**
   * @ignore
   */
  public function offsetGet($offset) {
    $data = '';

    if ($this->get($offset, $data) === false) {
      trigger_error('Unknown configuration value for ' . $offset, E_USER_NOTICE);
      return null;
    }

    return $data;
  }


  /**
   * @ignore
   */
  public function offsetSet($offset, $value) {
    $this->set($offset, $value);
  }


  /**
   * @ignore
   */
  public function offsetUnset($offset) {
    throw new \Exception('Unsupported operation');
  }

  /**
   * @ignore
   */
  public function getIterator() {
    return new \ArrayObject($this->_datas);
  }
}

/*
 * EOF
 */
