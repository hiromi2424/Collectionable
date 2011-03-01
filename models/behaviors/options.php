<?php

class OptionsBehavior extends ModelBehavior {
	
	var $settings = array();
	var $defaultSettings = array(
		'setupProperty' => true,
		'defaultOption' => false,
		'optionName' => 'options',
		'baseConfig' => '',
		'baseSessionKey' => '',
	);

	var $defaultQuery = array(
		'conditions' => null, 'fields' => null, 'joins' => array(), 'limit' => null,
		'offset' => null, 'order' => null, 'page' => null, 'group' => null, 'callbacks' => true
	);

	function setup(&$Model, $settings = array()) {
		$this->settings[$Model->alias] = array_merge($this->defaultSettings, (array)$settings);
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

		$option = $this->_magickConvertRecursively($Model, $option);

		return $option;
	}

	function _magickConvertRecursively(&$Model, $option) {

		if (!is_array($option)) {
			return $this->_magickConvert($Model, $option);
		}

		foreach ($option as $key => $val) {
			$option[$key] = $this->_magickConvertRecursively($Model, $val);
		}

		return $option;

	}

	function _magickConvert(&$Model, $string) {

		if (!preg_match('|!(.+?)!|', $string, $matches)) {
			return $string;
		}

		$methods = explode(':', $matches[1]);
		$argument = count($methods) === 1 ? '' : array_pop($methods);

		foreach ($methods as $method) {
			$method = $method . 'Option';
			$argument = $Model->$method($argument);
		}

		return $argument;

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