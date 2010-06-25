<?php

class OptionsBehaviorMockModel extends CakeTestModel {
	var $useTable = false;
}

class OptionsBehaviorTest extends CakeTestCase {
	var $Model;

	function startCase() {
		$this->Model =& ClassRegistry::init('OptionsBehaviorMockModel');
		$this->_reset();
	}

	function _reset($settings = array()) {
		unset($this->Model->defaultOption);
		unset($this->Model->options);
		$this->Model->Behaviors->attach('Collectionable.Options', $settings);
	}

	function startTest($method) {
		$this->_reset(false);
	}

	function testArguments() {
		$result = $this->Model->options();
		$expects = array();
		$this->assertIdentical($result, $expects);

		$result = $this->Model->options('no sence');
		$expects = array();
		$this->assertIdentical($result, $expects);

		$result = $this->Model->options(array('one', 'two', 'three'));
		$expects = array();
		$this->assertIdentical($result, $expects);

		$this->_reset(array('defaultOption' => true));
		$result = $this->Model->defaultOption;
		$expects = true;
		$this->assertIdentical($result, $expects);
	}

	function testDefaults() {
		$this->Model->options = array(
			'default' => array('order' => 'default'),
			'one' => array('conditions' => array('one')),
			'two' => array('order' => 'two'),
			'three' => array('order' => 'three'),
		);

		$result = $this->Model->options('one');
		$expects = array('conditions' => array('one'));
		$this->assertEqual($result, $expects);

		$this->Model->defaultOption = true;
		$result = $this->Model->options('one');
		$expects = array('conditions' => array('one'), 'order' => 'default');
		$this->assertEqual($result, $expects);

		$this->Model->defaultOption = 'three';

		$result = $this->Model->options('one');
		$expects = array('conditions' => array('one'), 'order' => 'three');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('two');
		$expects = array('order' => 'two');
		$this->assertEqual($result, $expects);

		$this->Model->defaultOption = array('one', 'three');

		$result = $this->Model->options('two');
		$expects = array('conditions' => array('one'), 'order' => 'two');
		$this->assertEqual($result, $expects);

	}

	function testMerge() {
		$this->Model->options = array(
			'one' => array('conditions' => array('one')),
			'two' => array('order' => 'two', 'group' => 'two'),
			'three' => array('order' => 'three'),
			'four' => array('conditions' => array('four'), 'group' => 'four'),
		);

		$result = $this->Model->options('one', 'two', 'three');
		$expects = array('conditions' => array('one'), 'group' => 'two', 'order' => 'three');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('three', 'four', 'one');
		$expects = array('conditions' => array('four', 'one'), 'order' => 'three', 'group' => 'four');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('one', 'two', 'four');
		$expects = array('conditions' => array('one', 'four'), 'order' => 'two', 'group' => 'four');
		$this->assertEqual($result, $expects);
	}

	function testOptions() {
		$this->Model->options = array(
			'one' => array('conditions' => array('one')),
			'two' => array('order' => 'two', 'group' => 'two'),
			'three' => array('order' => 'three'),
			'four' => array('conditions' => array('four'), 'group' => 'four'),
			'string' => array(
				'conditions' => array('merging'),
				'order' => 'merging',
				'options' => 'one',
			),
			'single_array' => array(
				'conditions' => array('merging'),
				'order' => 'merging',
				'options' => array('one'),
			),
			'multi_array' => array(
				'conditions' => array('merging'),
				'order' => 'merging',
				'options' => array('one', 'two'),
			),
			'extra_options' => array(
				'conditions' => array('merging'),
				'order' => 'merging',
				'options' => array(
					'conditions' => array('extra'),
				),
			),
			'chaos' => array(
				'conditions' => array('merging'),
				'order' => 'merging',
				'options' => array(
					'one',
					'four',
					'conditions' => array('chaos'),
				),
			),
		);

		$result = $this->Model->options('string');
		$expects = array('conditions' => array('merging', 'one'), 'order' => 'merging');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('single_array');
		$expects = array('conditions' => array('merging', 'one'), 'order' => 'merging');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('multi_array');
		$expects = array('conditions' => array('merging', 'one'), 'order' => 'two', 'group' => 'two');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('extra_options');
		$expects = array('conditions' => array('merging', 'extra'), 'order' => 'merging');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('chaos');
		$expects = array('one', 'four', 'conditions' => array('merging', 'chaos'), 'order' => 'merging');
		$this->assertEqual($result, $expects);
	}
}