<?php
App::import('Behavior', 'Collectionable.ConfigValidation');

class ConfigValidaitonMockModel extends Model {
	var $useTable = false;
	var $validate = array(
		'nickname' => array(
			'required' => array(
				'rule' => array('notempty'),
			),
			'min' => array(
				'rule' => array('minlength'),
				'message' => '%s文字ください',
			),
			'max' => array(
				'rule' => array('maxlength', 32),
				'message' => '32文字',
			),
			'hoge' => array(
				'rule' => array('userdefined', 2000),
			),
			'piyo' => array(
				'rule' => array('userdefined'),
			),
			'fuga' => array(
				'rule' => array('userdefined'),
				'message' => 'ふがー',
			),
			'moge' => array(
				'rule' => array('userdefined', 5000, 'hoge'),
				'message' => '%s %s',
			),
			'multi' => array(
				'rule' => array('userdefined'),
			),
		),
	);
}

class ConfigValidationBehaviorTestCase extends CakeTestCase {
	var $data = array(
		'ConfigValidaitonMockModel' => array(
			'nickname' => '0123456789012',
		),
	);

	function start() {
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
		parent::start();
	}

	function startTest() {
		$this->_attach();
	}

	function endTest() {
		$this->_clear();
	}

	function _attach() {
		$this->Behavior = new ConfigValidationBehavior;
		$this->Behavior->configName = 'TestValidation';
		$this->Behavior->convertFormat = false;
		$this->Model =& ClassRegistry::init('ConfigValidaitonMockModel');
	}

	function _clear() {
		unset($this->Behavior);
		unset($this->Model);
		ClassRegistry::flush();
	}

	function _reattach() {
		$this->_clear();
		$this->_attach();
	}

	function testAll() {
		$this->Behavior->convertFormat = true;
		$this->Behavior->beforeValidate($this->Model);

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
		$this->Behavior->convertFormat = true;
		$this->Behavior->overwrite = 'parameters';
		$this->Behavior->beforeValidate($this->Model);

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
		$this->Behavior->convertFormat = true;
		$this->Behavior->overwrite = 'messages';
		$this->Behavior->beforeValidate($this->Model);

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
		$this->Behavior->convertFormat = true;
		$this->Behavior->overwrite = true;
		$this->Behavior->beforeValidate($this->Model);

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
		$this->assertTrue($this->Behavior->beforeValidate($this->Model));
		$this->assertTrue($this->Model->validate);

		$this->_reattach();
		$this->Model->validate = array();
		$this->assertTrue($this->Behavior->beforeValidate($this->Model));
		$this->assertEqual($this->Model->validate, array());

		$this->_reattach();
		$this->Model->validate = array('hoge' => array('monyomonyo' => array('hoge' => '%s')));
		$this->assertTrue($this->Behavior->beforeValidate($this->Model));
		$this->assertEqual($this->Model->validate, array('hoge' => array('monyomonyo' => array('hoge' => '%s'))));
	}

	function testSetParameters() {
		$this->Behavior->_setParameters($this->Model);

		$this->assertEqual($this->Model->validate['nickname']['min']['rule'], array('minlength', 10));
		$this->assertEqual($this->Model->validate['nickname']['max']['rule'], array('maxlength', 32));
		$this->assertEqual($this->Model->validate['nickname']['multi']['rule'], array('userdefined', -100, 900));

		$this->_reattach();
		$this->Behavior->overwrite = true;
		$this->Behavior->_setParameters($this->Model);

		$this->assertEqual($this->Model->validate['nickname']['min']['rule'], array('minlength', 10));
		$this->assertEqual($this->Model->validate['nickname']['max']['rule'], array('maxlength', 100));
		$this->assertEqual($this->Model->validate['nickname']['multi']['rule'], array('userdefined', -100, 900));
	}

	function testSetMessages() {
		$this->Behavior->_setMessages($this->Model);
		$this->assertEqual($this->Model->validate['nickname']['required']['message'], '必ず入力してください。');
		$this->assertEqual($this->Model->validate['nickname']['max']['message'], '32文字');
		$this->assertEqual($this->Model->validate['nickname']['hoge']['message'], '%sです');
		$this->assertEqual($this->Model->validate['nickname']['piyo']['message'], 'うりりりー');
		$this->assertEqual($this->Model->validate['nickname']['fuga']['message'], 'ふがー');

		$this->_reattach();
		$this->Behavior->overwrite = true;
		$this->Behavior->_setMessages($this->Model);

		$this->assertEqual($this->Model->validate['nickname']['fuga']['message'], 'してい');
	}

	function testConvertFormat() {
		$this->Behavior->convertFormat = true;
		$this->Behavior->_convertFormat($this->Model);

		$this->assertEqual($this->Model->validate['nickname']['moge']['message'], '5000 hoge');
	}

	function testGetValidationParameter() {
		$this->expectError();
		$this->assertNull($this->Behavior->getValidationParameter($this->Model, 'nickname', null));
		$this->assertNull($this->Behavior->getValidationParameter($this->Model, 'nickname', 'undefined'));
		$this->assertEqual($this->Behavior->getValidationParameter($this->Model, 'nickname', 'max'), 32);
		$this->assertEqual($this->Behavior->getValidationParameter($this->Model, 'nickname', 'multi'), array(-100, 900));
	}

	function testGetConfigMessage() {
		$this->Behavior->convertFormat = true;

		$this->expectError();
		$this->assertNull($this->Behavior->getValidationMessage($this->Model, null));
		$this->assertNull($this->Behavior->getValidationMessage($this->Model, 'not defined'));
		$this->assertEqual($this->Behavior->getValidationMessage($this->Model, 'fuga'), 'でふぉると');
		$this->assertEqual($this->Behavior->getValidationMessage($this->Model, 'max'), '%s文字以内で入力してください。');
		$this->assertEqual($this->Behavior->getValidationMessage($this->Model, 'nickname', 'min'), '10文字ください');
	}
}
