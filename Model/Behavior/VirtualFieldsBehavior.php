<?php

class VirtualFieldsBehavior extends ModelBehavior {

	public $settings = array();
	public static $defaultSettings = array(
		'setupProperty' => true,
		'collectionName' => 'virtualFieldsCollection',
	);
	private $__virtualFieldsBackup = array();

	public function setup($Model, $settings = array()) {

		$this->settings[$Model->alias] = Set::merge(self::$defaultSettings, $settings);
		extract($this->settings[$Model->alias]);

		if (!isset($Model->{$collectionName}) && $this->settings[$Model->alias]['setupProperty']) {
			$Model->{$collectionName} = array();
		}

		return true;

	}

	public function beforeFind($Model, $query = array()){

		extract($this->settings[$Model->alias]);

		if (!isset($query['virtualFields'])) {
			return true;
		}

		$virtualFields = Set::normalize($query['virtualFields']);
		unset($query['virtualFields']);

		$blackList = array();
		foreach ($virtualFields as $key => $sql) {
			if (($sql !== false && empty($sql)) || $sql === true) {
				if (isset($Model->{$collectionName}[$key])) {
					$virtualFields[$key] = $Model->{$collectionName}[$key];
				} else {
					unset($virtualFields[$key]);
				}
			} else if (!empty($sql)) {
				$virtualFields[$key] = $sql;
			} else {
				$blackList[] = $key;
				unset($virtualFields[$key]);
			}
		}

		if (!empty($virtualFields) || !empty($blackList)){
			$this->__virtualFieldsBackup[$Model->alias] = $Model->virtualFields;
			$Model->virtualFields = array_merge($Model->virtualFields, $virtualFields);
			if (!empty($blackList)) {
				foreach ($blackList as $key) {
					if (isset($Model->virtualFields[$key])) {
						unset($Model->virtualFields[$key]);
					}
				}
			}
		}

		return $query;

	}

	public function afterFind($Model, $results = array(), $primary = false) {

		if (isset($this->__virtualFieldsBackup[$Model->alias])) {
			$Model->virtualFields = $this->__virtualFieldsBackup[$Model->alias];
			unset($this->__virtualFieldsBackup[$Model->alias]);
		}

		return true;

	}

}