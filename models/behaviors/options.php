<?php

class OptionsBehavior extends ModelBehavior {

	public $settings = array();
	public static $defaultSettings = array(
		'setupProperty' => true,
		'defaultOption' => false,
		'optionName' => 'options',
	);

	public static $defaultQuery = array(
		'conditions' => null, 'fields' => null, 'joins' => array(), 'limit' => null,
		'offset' => null, 'order' => null, 'page' => null, 'group' => null, 'callbacks' => true
	);

	public function setup($Model, $settings = array()) {

		$this->settings[$Model->alias] = array_merge(self::$defaultSettings, (array)$settings);

		$optionName = $this->settings[$Model->alias]['optionName'];
		if ($this->settings[$Model->alias]['setupProperty']) {
			if (empty($Model->{$optionName})) {
				$Model->{$optionName} = array();
			}
			if (empty($Model->defaultOption)) {
				$Model->defaultOption = $this->settings[$Model->alias]['defaultOption'];
			}
		}

		return true;

	}

	public function beforeFind($Model, $query = array()) {

		$optionName = $this->settings[$Model->alias]['optionName'];
		if (isset($query[$optionName])) {
			$options = $query[$optionName];
			unset($query[$optionName]);

			$query = Set::merge(self::$defaultQuery, $this->options($Model, $options), Set::filter($query));
		}

		return $query;

	}

	public function options($Model, $type = null){

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

		return $option;

	}

	protected function _getDefault($Model, $defaultOption, $options) {

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

	protected function _intelligentlyMerge($Model, $data, $merges, $options) {

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