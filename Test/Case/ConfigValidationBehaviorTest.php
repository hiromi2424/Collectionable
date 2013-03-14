<?php

class ConfigValidationBehaviorTest extends CakeTestCase {

	public $data = array(
		'ConfigValidaitonMockModel' => array(
			'nickname' => '0123456789012',
		),
	);

	protected function _configure() {

		Configure::write('TestValidation', array(
			'parameters' => array(
				'ConfigValidaitonMockModel' => array(
					'nickname' => array(
						'min' => 10,
						'max' => 100,
						'multi' => array(-100, 900),
					),
				),
			),
			'messages' => array(
				'default' => array(
					'required' => '必須項目です',
					'max' => '%s文字以内で入力してください。',
					'hoge' => '%sです',
					'fuga' => 'でふぉると',
				),
				'ConfigValidaitonMockModel' => array(
					'nickname' => array(
						'required' => '必ず入力してください。',
						'piyo' => 'うりりりー',
						'fuga' => 'してい',
					),
				),
			),
		));

	}

	public function setUp() {
		parent::setUp();
		$this->_configure();
		$this->_attach();

	}

	public function tearDown() {
		$this->_clear();
		Configure::delete('TestValidation');
		parent::tearDown();
	}
	public function _attach($settings = array()) {
		App::uses('Model', 'Model');
		App::import('TestSuite/Mock', 'Collectionable.ConfigValidaitonMockModel');
		$this->Model = ClassRegistry::init('ConfigValidaitonMockModel');
		$defaults = array(
			'configName' => 'TestValidation',
			'convertFormat' => false,
			'' => '',
		);
		$this->Model->Behaviors->unload('Collectionable.ConfigValidation');
		$this->Model->Behaviors->load('Collectionable.ConfigValidation', $settings + $defaults);

	}

	public function _clear() {
		unset($this->Model);
		ClassRegistry::flush();
	}

	public function _reattach($settings = array()) {

		$this->_clear();
		$this->_attach($settings);

	}

	public function testAll() {

		$this->Model->Behaviors->ConfigValidation->convertFormat = true;
		$this->Model->Behaviors->ConfigValidation->beforeValidate($this->Model);

		$result = $this->Model->validate;
		$expects = array(
			'nickname' => array(
				'required' => array(
					'rule' => array('notempty'),
					'message' => '必ず入力してください。',
				),
				'min' => array(
					'rule' => array('minlength', 10),
					'message' => '10文字ください',
				),
				'max' => array(
					'rule' => array('maxlength', 32),
					'message' => '32文字',
				),
				'hoge' => array(
					'rule' => array('userdefined', 2000),
					'message' => '2000です',
				),
				'piyo' => array(
					'rule' => array('userdefined'),
					'message' => 'うりりりー',
				),
				'fuga' => array(
					'rule' => array('userdefined'),
					'message' => 'ふがー',
				),
				'moge' => array(
					'rule' => array('userdefined', 5000, 'hoge'),
					'message' => '5000 hoge',
				),
				'multi' => array(
					'rule' => array('userdefined', -100, 900),
				),
			),
		);

		$this->_reattach();
		$this->Model->Behaviors->ConfigValidation->convertFormat = true;
		$this->Model->Behaviors->ConfigValidation->overwrite = 'parameters';
		$this->Model->Behaviors->ConfigValidation->beforeValidate($this->Model);

		$result = $this->Model->validate;
		$expects = array(
			'nickname' => array(
				'required' => array(
					'rule' => array('notempty'),
					'message' => '必ず入力してください。',
				),
				'min' => array(
					'rule' => array('minlength', 10),
					'message' => '10文字ください',
				),
				'max' => array(
					'rule' => array('maxlength', 100),
					'message' => '32文字',
				),
				'hoge' => array(
					'rule' => array('userdefined', 2000),
					'message' => '2000です',
				),
				'piyo' => array(
					'rule' => array('userdefined'),
					'message' => 'うりりりー',
				),
				'fuga' => array(
					'rule' => array('userdefined'),
					'message' => 'ふがー',
				),
				'moge' => array(
					'rule' => array('userdefined', 5000, 'hoge'),
					'message' => '5000 hoge',
				),
				'multi' => array(
					'rule' => array('userdefined', -100, 900),
				),
			),
		);

		$this->_reattach();
		$this->Model->Behaviors->ConfigValidation->convertFormat = true;
		$this->Model->Behaviors->ConfigValidation->overwrite = 'messages';
		$this->Model->Behaviors->ConfigValidation->beforeValidate($this->Model);

		$result = $this->Model->validate;
		$expected = array(
			'nickname' => array(
				'required' => array(
					'rule' => array('notempty'),
					'message' => '必ず入力してください。',
				),
				'min' => array(
					'rule' => array('minlength', 10),
					'message' => '10文字ください',
				),
				'max' => array(
					'rule' => array('maxlength', 32),
					'message' => '32文字',
				),
				'hoge' => array(
					'rule' => array('userdefined', 2000),
					'message' => '2000です',
				),
				'piyo' => array(
					'rule' => array('userdefined'),
					'message' => 'うりりりー',
				),
				'fuga' => array(
					'rule' => array('userdefined'),
					'message' => 'してい',
				),
				'moge' => array(
					'rule' => array('userdefined', 5000, 'hoge'),
					'message' => '5000 hoge',
				),
				'multi' => array(
					'rule' => array('userdefined', -100, 900),
				),
			),
		);
		$this->assertEqual($result, $expected);

		$this->_reattach();
		$this->Model->Behaviors->ConfigValidation->convertFormat = true;
		$this->Model->Behaviors->ConfigValidation->overwrite = true;
		$this->Model->Behaviors->ConfigValidation->beforeValidate($this->Model);

		$result = $this->Model->validate;
		$expected = array(
			'nickname' => array(
				'required' => array(
					'rule' => array('notempty'),
					'message' => '必ず入力してください。',
				),
				'min' => array(
					'rule' => array('minlength', 10),
					'message' => '10文字ください',
				),
				'max' => array(
					'rule' => array('maxlength', 100),
					'message' => '32文字',
				),
				'hoge' => array(
					'rule' => array('userdefined', 2000),
					'message' => '2000です',
				),
				'piyo' => array(
					'rule' => array('userdefined'),
					'message' => 'うりりりー',
				),
				'fuga' => array(
					'rule' => array('userdefined'),
					'message' => 'してい',
				),
				'moge' => array(
					'rule' => array('userdefined', 5000, 'hoge'),
					'message' => '5000 hoge',
				),
				'multi' => array(
					'rule' => array('userdefined', -100, 900),
				),
			),
		);
		$this->assertEqual($result, $expected);

		$this->_reattach();
		$this->Model->validate = true;
		$this->assertTrue($this->Model->Behaviors->ConfigValidation->beforeValidate($this->Model));
		$this->assertTrue($this->Model->validate);

		$this->_reattach();
		$this->Model->validate = array();
		$this->assertTrue($this->Model->Behaviors->ConfigValidation->beforeValidate($this->Model));
		$this->assertEqual($this->Model->validate, array());

		$this->_reattach();
		$this->Model->validate = array('hoge' => array('monyomonyo' => array('hoge' => '%s')));
		$this->assertTrue($this->Model->Behaviors->ConfigValidation->beforeValidate($this->Model));
		$this->assertEqual($this->Model->validate, array('hoge' => array('monyomonyo' => array('hoge' => '%s'))));

	}

	public function testSetValidationParameters() {

		$this->Model->setValidationParameters();

		$this->assertEqual($this->Model->validate['nickname']['min']['rule'], array('minlength', 10));
		$this->assertEqual($this->Model->validate['nickname']['max']['rule'], array('maxlength', 32));
		$this->assertEqual($this->Model->validate['nickname']['multi']['rule'], array('userdefined', -100, 900));

		$this->_reattach();
		$this->Model->Behaviors->ConfigValidation->overwrite = true;
		$this->Model->setValidationParameters();

		$this->assertEqual($this->Model->validate['nickname']['min']['rule'], array('minlength', 10));
		$this->assertEqual($this->Model->validate['nickname']['max']['rule'], array('maxlength', 100));
		$this->assertEqual($this->Model->validate['nickname']['multi']['rule'], array('userdefined', -100, 900));

	}

	public function testSetValidationMessages() {

		$this->Model->setValidationMessages();
		$this->assertEqual($this->Model->validate['nickname']['required']['message'], '必ず入力してください。');
		$this->assertEqual($this->Model->validate['nickname']['max']['message'], '32文字');
		$this->assertEqual($this->Model->validate['nickname']['hoge']['message'], '%sです');
		$this->assertEqual($this->Model->validate['nickname']['piyo']['message'], 'うりりりー');
		$this->assertEqual($this->Model->validate['nickname']['fuga']['message'], 'ふがー');

		$this->_reattach();
		$this->Model->Behaviors->ConfigValidation->overwrite = true;
		$this->Model->setValidationMessages();

		$this->assertEqual($this->Model->validate['nickname']['fuga']['message'], 'してい');

	}

	public function testConvertValidationFormat() {

		$this->Model->Behaviors->ConfigValidation->convertFormat = true;
		$this->Model->convertValidationFormat();

		$this->assertEqual($this->Model->validate['nickname']['moge']['message'], '5000 hoge');

	}

	public function testGetValidationParameter() {

		try {
			$this->Model->getValidationParameter('nickname', null);
			$this->fail('Expected Exception was not thrown');
		} catch (Exception $e) {
			$this->assertEqual($e->getMessage(), __d('collectionable', 'getValidationParameter() requires 2 arguments as $field and $rule'));
		}

		$this->assertNull($this->Model->getValidationParameter('nickname', 'undefined'));

		$this->assertEqual($this->Model->getValidationParameter('nickname', 'max'), 32);
		$this->assertEqual($this->Model->getValidationParameter('nickname', 'multi'), array(-100, 900));

	}

	public function testGetValidationMessage() {

		$this->Model->Behaviors->ConfigValidation->convertFormat = true;

		try {
			$this->Model->getValidationMessage(null);
			$this->fail('Expected Exception was not thrown');
		} catch (Exception $e) {
			$this->assertEqual($e->getMessage(), __d('collectionable', 'getValidationMessage() requires a argument as $rule'));
		}

		$this->assertNull($this->Model->getValidationMessage('not defined'));

		$this->assertEqual($this->Model->getValidationMessage('fuga'), 'でふぉると');
		$this->assertEqual($this->Model->getValidationMessage('max'), '%s文字以内で入力してください。');
		$this->assertEqual($this->Model->getValidationMessage('nickname', 'min'), '10文字ください');

		$this->Model->validate['nickname']['emptyMessage'] = array(
			'rule' => array('ruleName'),
		);
		$this->assertEqual($this->Model->Behaviors->ConfigValidation->getValidationMessage($this->Model, 'nickname', 'emptyMessage'), 'emptyMessage');

	}

}
