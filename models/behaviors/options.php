<?php

class OptionsBehavior extends ModelBehavior {
	
	var $settings = array();
	var $defaultSettings = array(
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
	);

	var $defaultQuery = array(
		'conditions' => null, 'fields' => null, 'joins' => array(), 'limit' => null,
		'offset' => null, 'order' => null, 'page' => null, 'group' => null, 'callbacks' => true
	);

	var $__regex;
	var $__params;
	var $__Model;

	function setup(&$Model, $settings = array()) {
		$this->settings[$Model->alias] = Set::merge($this->defaultSettings, (array)$settings);

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

	function beforeFind(&$Model, $query = array()) {
		$optionName = $this->settings[$Model->alias]['optionName'];
		if (isset($query[$optionName])) {
			$options = $query[$optionName];
			unset($query[$optionName]);

			$query = Set::merge($this->defaultQuery, $this->options($Model, $options), Set::filter($query));
		}
		return $query;
	}

	function options(&$Model, $type = null){
		$args = func_get_args();
		if (func_num_args() > 2) {
			array_shift($args);
			$type = $args;
		}

		$option = array();
		if (is_array($type)) {
			foreach ($type as $t) {
				$option = Set::merge($option, $this->options($Model, $t));
			}
		} else {
			$optionName = $this->settings[$Model->alias]['optionName'];
			$option = isset($Model->{$optionName}[$type]) ? $Model->{$optionName}[$type] : array();
			$default = array();
			if ($Model->defaultOption) {
				$default = $this->_getDefault($Model, $Model->defaultOption, $Model->{$optionName});
			}
			$options = array();
			if (isset($option[$optionName]) && !empty($option[$optionName])) {
				$options = $this->_intelligentlyMerge($Model, array(), $option[$optionName], $Model->{$optionName});
				unset($option[$optionName]);
			}
			$option = Set::merge($default, $options, $option);
		}

		$this->__Model =& $Model;

		$option = $this->_magickParamsRecurively($option);
		$option = $this->_magickConvertRecursively($option);

		$this->__regex = $this->__params = null;

		return $option;
	}

	function _magickConvertRecursively($option) {

		if (!is_array($option)) {

			if (null === $this->__regex) {
				$this->__regex = sprintf('|%1$s(.+?)%1$s|', preg_quote($this->settings[$this->__Model->alias]['magick']['enclosure'], '|'));
			}

			return preg_replace_callback($this->__regex, array($this, '_magickConvert'), $option);

		}

		foreach ($option as $key => $val) {
			$option[$key] = $this->_magickConvertRecursively($val);
		}

		return $option;

	}

	function _magickConvert($matches) {

		$methods = explode($this->settings[$this->__Model->alias]['magick']['separator'], $matches[1]);
		$argument = count($methods) === 1 ? '' : array_pop($methods);

		foreach ($methods as $method) {
			$method = $method . 'Option';
			$argument = $this->__Model->$method($argument);
		}

		return $argument;

	}

	function _magickParamsRecurively($option) {

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

	function _magickParams($string) {

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

	function _getParams() {

		$params = !empty($this->__Model->data) ? Set::flatten($this->__Model->data) : array();
		$params = array_merge($params, array(
			'id' => $this->__Model->id,
			'primaryKey' => $this->__Model->primaryKey,
			'alias' => $this->__Model->alias,
			'name' => $this->__Model->name,
		));

		return $params;

	}

	function configOption(&$Model, $configName) {
		$baseConfig = $this->settings[$Model->alias]['baseConfig'];
		return Configure::read($baseConfig . $configName);
	}

	function _getDefault(&$Model, $defaultOption, $options) {
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

	function _intelligentlyMerge(&$Model, $data, $merges, $options) {
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