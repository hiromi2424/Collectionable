<?php

class MultiValidaitonMockModel extends CakeTestModel {

	public $useTable = false;
	public $validate = array(
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

	public $validateBestAnswer = array(
		'best_answer_id' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
	);

	public $validateEdit = array(
		'email' => array(
			'email' => array(
				'rule' => array('email', true),
			),
		),
	);

}

class MultiValidationBehaviorTestCase extends CakeTestCase {

	public $data = array(
		'MultiValidaitonMockModel' => array(
			'nickname' => '0123456789012',
		),
	);

	public function startTest() {
		$this->_attach();
	}

	public function endTest() {
		$this->_clear();
	}

	protected function _attach($settings = array()) {

		$this->Model = ClassRegistry::init('MultiValidaitonMockModel');
		$this->Model->Behaviors->unload('Collectionable.MultiValidation');
		$this->Model->Behaviors->load('Collectionable.MultiValidation', $settings);

	}

	protected function _clear() {

		unset($this->Model);
		ClassRegistry::flush();

	}

	protected function _reattach($settings = array()) {

		$this->_clear();
		$this->_attach($settings);

	}

	public function testNormalCall() {

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

		$this->expectException('MultiValidation_PropertyNotFoundException');
		$this->Model->useValidationSet('invalidPropertyName');

	}

	public function testMapMethods() {

		$this->Model->useBestAnswerValidation();
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
		$this->Model->useBestAnswerValidation(false);
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
		$this->Model->useBestAnswerAndEditValidation();
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

		$this->expectException('MultiValidation_PropertyNotFoundException');
		$this->Model->useInvalidPropertValidation();

	}

	public function testAfterSave() {

		$this->Model->useBestAnswerValidation(false);
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

		$this->Model->useBestAnswerValidation(false);
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

}
