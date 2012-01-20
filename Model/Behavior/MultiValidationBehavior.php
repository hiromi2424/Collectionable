<?php

class MultiValidationBehavior extends ModelBehavior {

	public $settings = array();
	public static $defaultSettings = array(
		'restore' => true,
		'saveOptionName' => 'validator',
	);

	public $mapMethods = array(
		'/^use(.+?)Validation$/' => 'useValidationSet',
	);

	protected $_backupValidate = array();

	public function setup($Model, $settings = array()) {

		$this->settings[$Model->alias] = array_merge(self::$defaultSettings, (array)$settings);
		return true;

	}

	public function restoreValidate($Model) {

		if (isset($this->_backupValidate[$Model->alias])) {
			$Model->validate = $this->_backupValidate[$Model->alias];
			unset($this->_backupValidate[$Model->alias]);
		}

	}

	public function afterSave($Model, $created = true, $options = array()) {

		if ($this->settings[$Model->alias]['restore']) {
			$this->restoreValidate($Model);
		}

		return true;

	}

	public function useValidationSet($Model, $method, $useBase = true) {

		if (is_array($method) || !preg_match(key($this->mapMethods), $method, $matches)) {
			$validates = array_map('ucfirst', (array)$method);
		} else {
			$validates = explode('And', $matches[1]);
		}

		$result = array();
		foreach ($validates as $validate) {

			$property = 'validate' . $validate;
			if (!isset($Model->{$property}) && !property_exists($Model, $property)) {
				throw new MultiValidation_PropertyNotFoundException($property);
			}
			$result = $this->mergeValidationSet($Model, $result, $Model->{$property});

		}

		if ($useBase) {
			$result = $this->mergeValidationSet($Model, $Model->validate, $result);
		}

		$this->_backupValidate[$Model->alias] = $Model->validate;
		$Model->validate = $result;

	}

	public function mergeValidationSet($Model) {

		$validationSets = func_get_args();
		/* $Model = */ array_shift($validationSets);

		$result = array();
		foreach ($validationSets as $validationSet) {
			foreach ($validationSet as $field => $ruleSet) {
				foreach ($ruleSet as $name => $rules) {
					if (isset($result[$field][$name])) {
						$result[$field][$name] = array_merge($result[$field][$name], $rules);
					} else {
						$result[$field][$name] = $rules;
					}
				}
			}
		}

		return $result;

	}

	function validatesFor($Model, $set, $options = array()) {
		$useBase = true;
		if (is_bool($options)) {
			$useBase = $options;
			$options = array();
		} else {
			if (isset($options['useBase'])) {
				$useBase = $options['useBase'];
				unset($options['useBase']);
			}
			unset($options['useBase']);
		}

		$Model->useValidationSet($set, $useBase);
		return $Model->validates($options);
	}

	function beforeValidate($Model, $options = array()) {
		$optionName = $this->settings[$Model->alias]['saveOptionName'];

		if (isset($options[$optionName])) {
			$base = true;
			if (is_array($options[$optionName]) && is_bool($end = end($options[$optionName]))) {
				$base = $end;
				array_pop($options[$optionName]);
			}
			$Model->useValidationSet($options[$optionName], $base);
			unset($options[$optionName]);
		}

		return true;
	}

}

class MultiValidation_PropertyNotFoundException extends Exception {

	public function __construct($message = null, $code = 0, $previous = null) {
		parent::__construct(__d('collectionable', 'Unexpected property name: Model::$%s was not found.', $message), $code, $previous);
	}

}
