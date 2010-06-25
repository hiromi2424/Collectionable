<?php

class OptionsBehavior extends ModelBehavior {
	
	var $settings = array();
	var $defaultSettings = array(
		'setupProperty' => true,
		'defaultOption' => false,
	);

	function setup(&$Model, $settings = array()) {
		$this->settings = array_merge($this->defaultSettings, (array)$settings);
		if ($this->settings['setupProperty']) {
			if (empty($Model->options)) {
				$Model->options = array();
			}
			if (empty($Model->defaultOption)) {
				$Model->defaultOption = $this->settings['defaultOption'];
			}
		}
		return true;
	}

	function options(&$Model, $type = null){
		$args = func_get_args();
		if (func_num_args() > 2) {
			array_shift($args);
			$type = $args;
		}

		if (is_array($type)) {
			$option = array();
			foreach ($type as $t) {
				$option = Set::merge($option, $this->options(&$Model, $t));
			}
		} else {
			$option = isset($Model->options[$type]) ? $Model->options[$type] : array();
			$default = array();
			if ($Model->defaultOption) {
				$default = $this->_getDefault($Model->defaultOption, $Model->options);
			}
			$options = array();
			if (isset($option['options']) && !empty($option['options'])) {
				$options = $this->_intelligentlyMerge(array(), $option['options'], $Model->options);
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