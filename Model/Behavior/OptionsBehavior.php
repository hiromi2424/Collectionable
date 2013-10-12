<?php

App::uses('CakeSession', 'Model/Datasource');

class OptionsBehavior extends ModelBehavior {

	public $settings = array();
	public static $defaultSettings = array(
		'setupProperty' => true,
		'defaultOption' => false,
		'optionName' => 'options',
		'baseConfig' => '',
		'baseSessionKey' => '',
		'magick' => array(
			'enclosure' => '!',
			'separator' => ':',
		),
		'magickParams' => array(
			'before' => '$',
			'after' => null,
		),
		'autoDefault' => false,
	);

	public static $defaultQuery = array(
		'conditions' => null, 'fields' => null, 'joins' => array(), 'limit' => null,
		'offset' => null, 'order' => null, 'page' => null, 'group' => null, 'callbacks' => true
	);

	private $__regex;
	private $__params;
	private $__Model;

	public function setup(Model $Model, $settings = array()) {

		$this->settings[$Model->alias] = Set::merge(self::$defaultSettings, (array)$settings);

		$optionName = $this->settings[$Model->alias]['optionName'];
		if ($this->settings[$Model->alias]['setupProperty']) {
			if (empty($Model->{$optionName})) {
				$Model->{$optionName} = array();
			}
			if (empty($Model->defaultOption)) {
				$Model->defaultOption = $this->settings[$Model->alias]['defaultOption'];
			}
		}

		foreach (array('baseConfig', 'baseSessionKey') as $base) {
			if (!empty($this->settings[$Model->alias][$base]) && substr($this->settings[$Model->alias][$base], -1) !== '.') {
				$this->settings[$Model->alias][$base] .= '.';
			}
		}

		return true;

	}

	public function beforeFind(Model $Model, $query = array()) {

		$optionName = $this->settings[$Model->alias]['optionName'];
		$autoDefault = $this->settings[$Model->alias]['autoDefault'];
		if (isset($query[$optionName]) || $autoDefault) {
			$options = isset($query[$optionName]) ? $query[$optionName]: array();
			unset($query[$optionName]);

			$query = Set::merge($query, self::$defaultQuery, $this->options($Model, $options), array_filter($query, [$this, '_filter']));
		}

		return $query;

	}

	protected function _filter($value) {
		return !empty($value) || $value === '0' || $value === 0;
	}

	public function options(Model $Model, $type = null){

		$args = func_get_args();
		if (func_num_args() > 2) {
			array_shift($args);
			$type = $args;
		}

		$optionName = $this->settings[$Model->alias]['optionName'];
		$default = array();
		if ($Model->defaultOption) {
			$default = $this->_getDefault($Model, $Model->defaultOption, $Model->{$optionName});
		}

		$option = array();
		if (is_array($type)) {
			$options = $this->_intelligentlyMerge($Model, array(), $type, $Model->{$optionName});
		} else {
			$option = isset($Model->{$optionName}[$type]) ? $Model->{$optionName}[$type] : array();

			$options = array();
			if (is_array($option) && !empty($option[$optionName])) {
				$options = $this->_intelligentlyMerge($Model, array(), $option[$optionName], $Model->{$optionName});
				unset($option[$optionName]);
			}

		}
		$option = Set::merge(Set::merge($default, $options), $option);

		$this->__Model = $Model;

		$option = $this->_magickParamsRecurively($option);
		$option = $this->_magickConvertRecursively($option);

		$this->__regex = $this->__params = $this->__Model = null;

		return $option;

	}

	protected function _magickConvertRecursively($option) {

		if (is_string($option)) {

			if (null === $this->__regex) {
				$this->__regex = sprintf('|%1$s(.+?)%1$s|', preg_quote($this->settings[$this->__Model->alias]['magick']['enclosure'], '|'));
			}

			return preg_replace_callback($this->__regex, array($this, '_magickConvert'), $option);

		}

		if (is_array($option)) {
			foreach ($option as $key => $val) {
				$option[$key] = $this->_magickConvertRecursively($val);
			}
		}

		return $option;

	}

	protected function _magickConvert($matches) {

		$methods = explode($this->settings[$this->__Model->alias]['magick']['separator'], $matches[1]);
		$argument = count($methods) === 1 ? '' : array_pop($methods);

		foreach ($methods as $method) {

			$method = $method . 'Option';
			if (!$this->__Model->hasMethod($method)) {
				throw new BadMethodCallException(__d('collectionable', '%s model doesn\'t have %s() method.', $this->__Model->name, $method));
			}

			$argument = $this->__Model->$method($argument);

		}

		return $argument;

	}

	protected function _magickParamsRecurively($option) {

		if (!is_array($option)) {
			return $this->_magickParams($option);
		}

		foreach ($option as $key => $val) {
			$option[$key] = $this->_magickParamsRecurively($val);
			$convertedKey = $this->_magickParams($key);
			if ($convertedKey !== $key) {
				if (isset($option[$convertedKey])) {
					$option = Set::merge($option, array($convertedKey => $option[$key]));
				} else {
					$option[$convertedKey] = $option[$key];
				}
				unset($option[$key]);
			}
		}

		return $option;

	}

	protected function _magickParams($string) {

		if (is_numeric($string)) {
			return $string;
		}

		$before = $this->settings[$this->__Model->alias]['magickParams']['before'];
		$after = $this->settings[$this->__Model->alias]['magickParams']['after'];
		if (
			($before && strpos($string, $before) === false) ||
			($after && strpos($string, $after) === false)
		) {
			return $string;
		}

		if (null === $this->__params) {
			$this->__params = $this->_getParams();
		}

		return String::insert($string, $this->__params, $this->settings[$this->__Model->alias]['magickParams']);

	}

	protected function _getParams() {

		$params = !empty($this->__Model->data) ? Set::flatten($this->__Model->data) : array();
		$params = array_merge($params, array(
			'id' => $this->__Model->id,
			'alias' => $this->__Model->alias,
			'name' => $this->__Model->name,
		));

		return $params;

	}

	public function configOption(Model $Model, $configName) {
		$baseConfig = $this->settings[$Model->alias]['baseConfig'];
		return Configure::read($baseConfig . $configName);
	}

	public function sessionOption(Model $Model, $sessionKey) {
		$baseSessionKey = $this->settings[$Model->alias]['baseSessionKey'];
		return CakeSession::read($baseSessionKey . $sessionKey);
	}

	protected function _getDefault(Model $Model, $defaultOption, $options) {

		$default = array();

		if ($defaultOption === true && !empty($options['default'])) {
			$default = $options['default'];
		} elseif (is_array($defaultOption)) {
			$default = $this->_intelligentlyMerge($Model, $default, $defaultOption, $options);
		} elseif (!empty($options[$defaultOption])) {
			$default = $this->_intelligentlyMerge($Model, $default, $options[$defaultOption], $options);
		}

		return $default;

	}

	protected function _intelligentlyMerge(Model $Model, $data, $merges, $options) {

		$merges = (array)$merges;
		if (Set::numeric(array_keys($merges))) {

			foreach($merges as $merge) {
				if (!empty($options[$merge])) {
					$data = $this->_intelligentlyMerge($Model, $data, $options[$merge], $options);
				}
			}

		} else {

			$optionName = $this->settings[$Model->alias]['optionName'];
			if (array_key_exists($optionName, $merges)) {
				$data = $this->_intelligentlyMerge($Model, $data, $merges[$optionName], $options);
				unset($merges[$optionName]);
			}

			$data = Set::merge($data, $merges);

		}

		return $data;

	}

}
