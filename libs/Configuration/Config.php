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

    $_const = false,
    $_loaded = false;

  /**
   * Constructor
   *
   * @param string $_file Filename to be loaded
   */
  public function __construct($_file) {
    $this->_file = $_file;
    $this->_load();
  }

  public function __clone() {
    $this->_datas = $this->_datas;
    $this->_loaded = true;
    $this->_const = true;
  }

  public function __get($var) {
    if (!isset($this->_datas[$var])) {
      throw new Exception('Unknown configuration value for ' . $var);
    }

    return $this->_datas[$var];
  }

  public function __set($var, $val) {
    if ($this->_const === true) {
      throw new Exception('Unable to modify a constant configuration !');
    }

    $this->_datas[$var] = $val;
  }

  public function __isset($var) {
    return isset($this->_datas[$var]);
  }

  public function __unset($name) {
    unset($this->_datas[$name]);
  }

  public function applyCallback(\Closure $_callback) {
    if ($this->_loaded === false) {
      throw new \Exception('Applying a callback on something not loaded is merely impossible !');
      return null;
    }

    $config = clone $this;
    $config->_datas = array_map($_callback, $this->_datas);
    $config->_const = true;

    return $config;
  }

  public function save() {
    if ($this->_const) {
      throw new Exception('Can\'t save into configuration file ' . $this->_file . ', because it is constant');
    }

    $data = json_encode($this->_datas);
    $ex = $this->_checkJson();

    if ($ex !== null) {
      throw new \Exception('Bad JSON (' . $ex . ') in configuration file ' . $this->_file);
    }

    file_put_contents($this->_file, $data);
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
   * {@inheritdoc}
   */
  public function offsetExists($offset) {
    return isset($this->$offset);
  }


  /**
   * {@inheritdoc}
   */
  public function offsetGet($offset) {
    return $this->$offset;
  }


  /**
   * {@inheritdoc}
   */
  public function offsetSet($offset, $value) {
    $this->$offset = $value;
  }


  /**
   * {@inheritdoc}
   */
  public function offsetUnset($offset) {
    unset($this->$offset);
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayObject($this->_datas);
  }
}

/*
 * EOF
 */
