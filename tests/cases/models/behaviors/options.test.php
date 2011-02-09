<?php

class OptionsBehaviorMockModel extends CakeTestModel {
	var $useTable = false;
}

if (!class_exists('VirtualFieldsUser')) {
	class VirtualFieldsUser extends CakeTestModel {
		var $name = 'VirtualFieldsUser';
		var $alias = 'User';
		var $hasMany = array(
			'Post' => array(
				'className' => 'VirtualFieldsPost',
				'table' => 'Virtual_fields_posts',
				'foreignKey' => 'user_id',
			),
		);
	}
}

class OptionsBehaviorTest extends CakeTestCase {
	var $Model;
	var $fixtures = array('plugin.collectionable.virtual_fields_user', 'plugin.collectionable.virtual_fields_post');
	var $autoFixtures = false;

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
		$expects = array('conditions' => array('one', 'merging'), 'order' => 'merging');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('single_array');
		$expects = array('conditions' => array('one', 'merging'), 'order' => 'merging');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('multi_array');
		$expects = array('conditions' => array('one', 'merging'), 'order' => 'merging', 'group' => 'two');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('extra_options');
		$expects = array('conditions' => array('extra', 'merging'), 'order' => 'merging');
		$this->assertEqual($result, $expects);

		$result = $this->Model->options('chaos');
		$expects = array('one', 'four', 'conditions' => array('chaos', 'merging'), 'order' => 'merging');
		$this->assertEqual($result, $expects);
	}

	function testFindOptions() {
		$this->loadFixtures('VirtualFieldsUser');
		$User =& ClassRegistry::init(array('alias' => 'User', 'class' => 'VirtualFieldsUser'));
		$User->recursive = -1;
		$User->options = array(
			'limitation' => array(
				'limit' => 1,
				'fields' => array('User.id', 'User.first_name')
			),
		);
		$User->Behaviors->attach('Collectionable.options');

		$result = $User->find('all', array('options' => 'limitation', 'limit' => 2, 'order' => 'User.id'));
		$expects = array(
			array('User' => array('id' => 1, 'first_name' => 'yamada')),
			array('User' => array('id' => 2, 'first_name' => 'tanaka')),
		);
		$this->assertEqual($result, $expects);
	}

	function testRecursiveMerge() {
		$this->Model->options = array(
			'one' => array('conditions' => array('one')),
			'two' => array('conditions' => array('two'), 'options' => 'one'),
			'three' => array('conditions' => array('three'), 'options' => 'two'),
			'four' => array('conditions' => array('four')),
		);

		$result = $this->Model->options('three');
		$expects = array('one', 'two', 'three');
		$this->assertEqual($result['conditions'], $expects);

		$this->Model->defaultOption = 'three';
		$result = $this->Model->options('four');
		$expects = array('one', 'two', 'three', 'four');
		$this->assertEqual($result['conditions'], $expects);
	}
}