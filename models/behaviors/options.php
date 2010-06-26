<?php

class OptionsBehavior extends ModelBehavior {
	
	var $settings = array();
	var $defaultSettings = array(
		'setupProperty' => true,
		'defaultOption' => false,
		'optionName' => 'options',
	);

	function setup(&$Model, $settings = array()) {
		$this->settings = array_merge($this->defaultSettings, (array)$settings);
		$optionName = $this->settings['optionName'];
		if ($this->settings['setupProperty']) {
			if (empty($Model->{$optionName})) {
				$Model->{$optionName} = array();
			}
			if (empty($Model->defaultOption)) {
				$Model->defaultOption = $this->settings['defaultOption'];
			}
		}
		return true;
	}

	function beforeFind(&$Model, $query = array()) {
		if (isset($query['options'])) {
			$options = $query['options'];
			unset($query['options']);
			$query = Set::pushDiff($query, $this->options($Model, $options));
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
				$option = Set::merge($option, $this->options(&$Model, $t));
			}
		} else {
			$optionName = $this->settings['optionName'];
			$option = isset($Model->{$optionName}[$type]) ? $Model->{$optionName}[$type] : array();
			$default = array();
			if ($Model->defaultOption) {
				$default = $this->_getDefault($Model->defaultOption, $Model->{$optionName});
			}
			$options = array();
			if (isset($option['options']) && !empty($option['options'])) {
				$options = $this->_intelligentlyMerge(array(), $option['options'], $Model->{$optionName});
				unset($option['options']);
			}
			$option = Set::merge($default, $options, $option);
		}
		return $option;
	}

	function _getDefault($defaultOption, $options) {
		$default = array();
		if ($defaultOption === true && !empty($options['default'])) {
			$default = $options['default'];
		} elseif (is_array($defaultOption)) {
			$default = $this->_intelligentlyMerge($default, $defaultOption, $options);
		} elseif (!empty($options[$defaultOption])) {
			$default = $options[$defaultOption];
		}
		return $default;
	}

	function _intelligentlyMerge($data, $merges, $options) {
		$merges = (array)$merges;
		if (Set::numeric(array_keys($merges))) {
			foreach($merges as $merge) {
				if (!empty($options[$merge])) {
					$data = Set::merge($data, $options[$merge]);
				}
			}
		} else {
			$data = Set::merge($data, $merges);
		}
		return $data;
	}

}