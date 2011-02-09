<?php

class ConfigValidationBehavior extends ModelBehavior {
	var $configName = 'Validation';
	var $overwrite = false; // true or $parameterName or $messageName
	var $convertFormat = true;

	var $parametersName = 'parameters';
	var $messagesName = 'messages';
	function beforeValidate(&$model) {
		if (!$model->validate || !is_array($model->validate)) {
			return true;
		}

		$this->_setParameters($model);
		$this->_setMessages($model);
		$this->_convertFormat($model);
		return true;
	}

	function _setParameters(&$model) {
		$overwrite = $this->overwrite === true || $this->overwrite == $this->parametersName;
		foreach ($model->validate as $field => $elements) {
			foreach ($elements as $name => $element) {
				$parameters = $this->_config($this->parametersName, $model->name, $field, $name);
				if ($parameters === null || !isset($element['rule'])) {
					continue;
				}

				if (count($element['rule']) > 1 && !$overwrite) {
					continue;
				}

				$model->validate[$field][$name]['rule'] = array_merge(array(current($element['rule'])), (array)$parameters);
			}
		}
	}

	function _setMessages(&$model) {
		$overwrite = $this->overwrite === true || $this->overwrite == $this->messagesName;
		foreach ($model->validate as $field => $elements) {
			foreach ($elements as $name => $element) {
				$default = $this->_config($this->messagesName, 'default', $name);
				$desire = $this->_config($this->messagesName, $model->name, $field, $name);
				if ($default === null && $desire === null) {
					continue;
				}

				if (isset($element['message']) && !$overwrite) {
					continue;
				}
				if ($desire !== null) {
					$model->validate[$field][$name]['message'] = $desire;
					continue;
				}
				if (!isset($element['message'])) {
					$model->validate[$field][$name]['message'] = $default;
				}
			}
		}
	}

	function _convertFormat(&$model) {
		if (!$this->convertFormat) {
			return;
		}
		foreach ($model->validate as $field => $elements) {
			foreach ($elements as $name => $element) {
				if (!isset($element['message']) || !isset($element['rule']) || !is_array($element['rule'])) {
					continue;
				}
				if (count($element['rule']) > 1) {
					array_shift($element['rule']);
					array_unshift($element['rule'], $element['message']);
					$model->validate[$field][$name]['message'] = call_user_func_array('sprintf', $element['rule']);
				}
			}
		}
	}

	function _config() {
		$args = func_get_args();
		array_unshift($args, $this->configName);
		return Configure::read(implode('.', $args));
	}

	function getValidationParameter(&$model, $field, $rule) {
		if (is_array($field) || is_array($rule) || $field === null || $rule === null) {
			trigger_error(__d('collectionable', 'getValidationParameter() requires 2 arguments as $field and $rule', true));
			return null;
		}

		$this->beforeValidate($model);
		if (empty($model->validate[$field][$rule]['rule'])) {
			return null;
		}
		$rule = $model->validate[$field][$rule]['rule'];
		array_shift($rule);
		if (empty($rule)) {
			return null;
		}
		$parameters = count($rule) === 1 ? current($rule) : $rule;
		return $parameters;
	}

	function getValidationMessage(&$model, $rule, $field = null) {
		if (is_array($rule) || $rule === null) {
			trigger_error(__d('collectionable', 'getValidationMessage() requires a argument as $rule', true));
			return null;
		}
		if (is_array($field)) {
			$field = null;
		}
		if ($field !== null) {
			$swap = $rule;
			$rule = $field;
			$field = $swap;
		}
		if ($field === null) {
			$default = $this->_config($this->messagesName, 'default', $rule);
			return $default;
		}

		$this->beforeValidate($model);
		if (empty($model->validate[$field][$rule]['message'])) {
			return null;
		}
		return $model->validate[$field][$rule]['message'];
	}
}