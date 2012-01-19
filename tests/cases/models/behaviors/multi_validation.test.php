<?php

class MultiValidaitonTestModel extends CakeTestModel {

	var $useTable = false;
	var $validate = array(
		'email' => array(
			'email' => array(
				'rule' => array('email'),
			),
		),
		'title' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
	);

	var $validateBestAnswer = array(
		'best_answer_id' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
	);

	var $validateEdit = array(
		'email' => array(
			'email' => array(
				'rule' => array('email', true),
			),
		),
	);

}

Mock::generatePartial('MultiValidaitonTestModel', null, array('validates', 'useValidationSet'));

class MultiValidationBehaviorTestCase extends CakeTestCase {

	var $data = array(
		'MultiValidaitonTestModel' => array(
			'nickname' => '0123456789012',
		),
	);

	function startTest() {
		$this->_attach();
	}

	function endTest() {
		$this->_clear();
	}

	function _attach($settings = array()) {

		$this->Model = ClassRegistry::init('MultiValidaitonTestModel');
		$this->Model->Behaviors->detach('Collectionable.MultiValidation');
		$this->Model->Behaviors->attach('Collectionable.MultiValidation', $settings);

	}

	function _clear() {

		unset($this->Model);
		ClassRegistry::flush();

	}

	function _reattach($settings = array()) {

		$this->_clear();
		$this->_attach($settings);

	}

	function testUseValidationSet() {

		$this->Model->useValidationSet('BestAnswer');
		$result = $this->Model->validate;
		$expected = array(
			'email' => array(
				'email' => array(
					'rule' => array('email'),
				),
			),
			'title' => array(
				'notempty' => array(
					'rule' => array('notempty'),
				),
			),
			'best_answer_id' => array(
				'notempty' => array(
					'rule' => array('notempty'),
				),
			),
		);
		$this->assertIdentical($result, $expected);

		$this->Model->restoreValidate();
		$this->Model->useValidationSet('bestAnswer');
		$result = $this->Model->validate;
		$this->assertIdentical($result, $expected);

		$this->Model->restoreValidate();
		$this->Model->useValidationSet('BestAnswer', false);
		$result = $this->Model->validate;
		$expected = array(
			'best_answer_id' => array(
				'notempty' => array(
					'rule' => array('notempty'),
				),
			),
		);
		$this->assertIdentical($result, $expected);

		$this->Model->restoreValidate();
		$this->Model->useValidationSet(array('bestAnswer', 'edit'));
		$result = $this->Model->validate;
		$expected = array(
			'email' => array(
				'email' => array(
					'rule' => array('email', true),
				),
			),
			'title' => array(
				'notempty' => array(
					'rule' => array('notempty'),
				),
			),
			'best_answer_id' => array(
				'notempty' => array(
					'rule' => array('notempty'),
				),
			),
		);
		$this->assertIdentical($result, $expected);

		$this->expectError();
		$this->assertFalse($this->Model->useValidationSet('invalidPropertyName'));

	}

	function testAfterSave() {

		$this->Model->useValidationSet('bestAnswer', false);
		$this->Model->Behaviors->MultiValidation->afterSave($this->Model);
		$expected = array(
			'email' => array(
				'email' => array(
					'rule' => array('email'),
				),
			),
			'title' => array(
				'notempty' => array(
					'rule' => array('notempty'),
				),
			),
		);
		$this->assertIdentical($this->Model->validate, $expected);

		$this->_reattach(array('restore' => false));

		$this->Model->useValidationSet('bestAnswer', false);
		$this->Model->Behaviors->MultiValidation->afterSave($this->Model);
		$expected = array(
			'best_answer_id' => array(
				'notempty' => array(
					'rule' => array('notempty'),
				),
			),
		);
		$this->assertIdentical($this->Model->validate, $expected);

	}

	function _prepareMock() {
		$model =& ClassRegistry::init('MockMultiValidaitonTestModel');
		$model->__construct();
		$model->Behaviors->attach('Collectionable.MultiValidation');
		return $model;
	}

	function testValidatesFor() {
		$model = $this->_prepareMock();
		$model->expectOnce('useValidationSet', array('bestAnswer', true));
		$model->expectOnce('validates', array(array()));
		$model->validatesFor('bestAnswer');
	}

	function testValidatesFor_useBaseOption() {
		$model = $this->_prepareMock();
		$model->expectOnce('useValidationSet', array('bestAnswer', false));
		$model->expectOnce('validates', array(array()));
		$model->validatesFor('bestAnswer', array('useBase' => false));
	}

	function testValidatesFor_booleanOptions() {
		$model = $this->_prepareMock();
		$model->expectOnce('useValidationSet', array('bestAnswer', false));
		$model->expectOnce('validates', array(array()));
		$model->validatesFor('bestAnswer', false);
	}

}
