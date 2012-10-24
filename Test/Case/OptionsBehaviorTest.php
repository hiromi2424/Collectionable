<?php

class OptionsBehaviorTest extends CakeTestCase {

	public $Model;
	public $fixtures = array(
		'plugin.collectionable.virtual_fields_user',
		'plugin.collectionable.virtual_fields_post',
	);
	public $autoFixtures = false;

	protected $_backupConfig;

	public function setUp() {

		App::uses('Model', 'Model');
		App::import('TestSuite/Mock', 'Collectionable.OptionsBehaviorMockModel');
		$this->Model = ClassRegistry::init('OptionsBehaviorMockModel');
		$this->_reset();

	}

	protected function _reset($settings = array()) {

		unset($this->Model->defaultOption);
		unset($this->Model->options);
		$this->Model->Behaviors->attach('Collectionable.Options', $settings);

	}

	public function startTest($method) {
		$this->_reset(false);
	}

	public function endTest() {
		Configure::delete('OptionsBehaviorTestConfig');
		if (class_exists('CakeSession')) {
			CakeSession::delete('OptionsBehaviorTestSession');
		}
	}

	public function testArguments() {

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

	public function testDefaults() {

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

	public function testMerge() {

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

	public function testOptions() {

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

	public function testFindOptions() {

		$this->loadFixtures('VirtualFieldsUser');
		$User = ClassRegistry::init(array('alias' => 'User', 'class' => 'VirtualFieldsUser'));
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

	public function testRecursiveMerge() {

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

	public function testMagickValue() {

		Configure::write('OptionsBehaviorTestConfig', 'test value');
		$this->Model->options = array(
			'one' => array(
				'order' => '!config:OptionsBehaviorTestConfig!',
			),
		);
		$this->assertEqual(array('order' => 'test value'), $this->Model->options('one'));

		Configure::write('OptionsBehaviorTestConfig', array('testKey' => 'test value'));
		$this->Model->options = array(
			'one' => array(
				'order' => '!config:OptionsBehaviorTestConfig.testKey!',
			),
		);
		$this->assertEqual(array('order' => 'test value'), $this->Model->options('one'));

		Configure::write('OptionsBehaviorTestConfig.testKey2', 'test value2');
		$this->Model->options = array(
			'one' => array(
				'limit' => '!config:OptionsBehaviorTestConfig.testKey!',
			),
			'two' => array(
				'order' => '!config:OptionsBehaviorTestConfig.testKey2!',
			),
			'three' => array(
				'options' => array('one', 'two'),
			),
		);
		$expected = array(
			'limit' => 'test value',
			'order' => 'test value2',
		);
		$this->assertEqual($expected, $this->Model->options('three'));

		Configure::write('OptionsBehaviorTestConfig', array(
			'testKey' => 'OptionsBehaviorTestConfig.testKey2',
			'testKey2' => 'hogehoge',
		));
		$this->Model->options = array(
			'one' => array(
				'limit' => '!config:config:OptionsBehaviorTestConfig.testKey!',
			),
		);
		$expected = array(
			'limit' => 'hogehoge',
		);
		$this->assertEqual($expected, $this->Model->options('one'));


		$this->Model->options = array(
			'one' => array(
				'limit' => '!testMagick:argument!',
			),
		);
		$expected = array(
			'limit' => 'returned argument',
		);
		$this->assertEqual($expected, $this->Model->options('one'));

		$this->Model->options = array(
			'one' => array(
				'limit' => '!testMagick:!',
			),
		);
		$expected = array(
			'limit' => 'test magick method',
		);
		$this->assertEqual($expected, $this->Model->options('one'));

		$this->Model->options = array(
			'one' => array(
				'limit' => '!testMagick!',
			),
		);
		$expected = array(
			'limit' => 'test magick method',
		);
		$this->assertEqual($expected, $this->Model->options('one'));


		$this->_reset(array(
			'magick' => array(
				'enclosure' => '%',
			),
		));
		$this->Model->options = array(
			'one' => array(
				'limit' => '%testMagick%',
			),
		);
		$expected = array(
			'limit' => 'test magick method',
		);
		$this->assertEqual($expected, $this->Model->options('one'));

		$this->_reset(array(
			'magick' => array(
				'separator' => '->',
			),
		));
		$this->Model->options = array(
			'one' => array(
				'limit' => '!testMagick->argument!',
			),
		);
		$expected = array(
			'limit' => 'returned argument',
		);
		$this->assertEqual($expected, $this->Model->options('one'));


		$this->Model->data = array(
			'User' => array(
				'id' => 1,
				'username' => 'hiromichan',
			),
			'Group' => array(
				'id' => 2,
			),
		);
		$this->Model->options = array(
			'one' => array(
				'conditions' => array(
					'$alias.name' => '$User.username',
				),
			),
		);
		$expected = array(
			'conditions' => array(
				'OptionsBehaviorMockModel.name' => 'hiromichan',
			),
		);
		$this->assertEqual($expected, $this->Model->options('one'));

		$this->Model->options = array(
			'one' => array(
				'conditions' => array(
					'$name.name' => 'default',
				),
			),
			'two' => array(
				'conditions' => array(
					'$alias.name' => '$User.username',
				),
				'options' => 'one',
			),
		);
		$expected = array(
			'conditions' => array(
				'OptionsBehaviorMockModel.name' => 'hiromichan',
			),
		);
		$this->assertEqual($expected, $this->Model->options('two'));


		$this->_reset(false);
		Configure::write('OptionsBehaviorTestConfig.Group.role.2', 'admin');
		$this->Model->options = array(
			'one' => array(
				'conditions' => array(
					'Group.role' => '!config:OptionsBehaviorTestConfig.Group.role.$Group.id!',
				),
			),
		);
		$expected = array(
			'conditions' => array(
				'Group.role' => 'admin',
			),
		);
		$this->assertEqual($expected, $this->Model->options('one'));


		$this->Model->options = array(
			'one' => array(
				'limit' => '!noDefinedMethod!',
			),
		);
		$this->setExpectedException('BadMethodCallException');
		$this->Model->options('one');

	}

	public function testConfigOption() {

		$this->assertEqual(null, $this->Model->configOption('OptionsBehaviorTestConfig.hoge'));

		Configure::write('OptionsBehaviorTestConfig.hoge', 'test value');
		$this->assertEqual('test value', $this->Model->configOption('OptionsBehaviorTestConfig.hoge'));

		$this->_reset(array('baseConfig' => 'OptionsBehaviorTestConfig.'));
		$this->assertEqual('test value', $this->Model->configOption('hoge'));

		$this->_reset(array('baseConfig' => 'OptionsBehaviorTestConfig'));
		$this->assertEqual('test value', $this->Model->configOption('hoge'));

	}

	public function testSessionOption() {

		$this->assertEqual(null, $this->Model->sessionOption('OptionsBehaviorTestSession.hoge'));

		CakeSession::write('OptionsBehaviorTestSession.hoge', 'test value');
		$this->assertEqual('test value', $this->Model->sessionOption('OptionsBehaviorTestSession.hoge'));

		$this->_reset(array('baseSessionKey' => 'OptionsBehaviorTestSession.'));
		$this->assertEqual('test value', $this->Model->sessionOption('hoge'));

		$this->_reset(array('baseSessionKey' => 'OptionsBehaviorTestSession'));
		$this->assertEqual('test value', $this->Model->sessionOption('hoge'));

	}

	public function testOptionsReturnOriginalValue() {
		$this->Model->options = array(
			'one' => array('conditions' => array('one' => true, 'two' => 2, 'three' => '3')),
		);
		$expects = array('one' => true, 'two' => 2, 'three' => '3');

		$result = $this->Model->options('one');
		$this->assertSame($expects, $result['conditions']);
	}
}