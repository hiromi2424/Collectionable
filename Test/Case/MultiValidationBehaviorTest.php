<?php


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

		App::uses('Model', 'Model');
		App::import('TestSuite/Mock', 'Collectionable.MultiValidaitonMockModel');
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

	protected function _prepareMock($mock = 'default') {
		$methods = array(
			'default' => array(
				'validates',
				'useValidationSet',
			),
			'beforeValidate' => array(
				'useValidationSet',
			),
		);
		$modelClass = $this->getMockClass('MultiValidaitonMockModel', $methods[$mock]);
		$model = ClassRegistry::init($modelClass);
		$model->__construct();
		$model->Behaviors->attach('Collectionable.MultiValidation');
		return $model;
	}

	public function testValidatesFor() {
		$model = $this->_prepareMock();
		$model->expects($this->once())
			->method('useValidationSet')
			->with('bestAnswer', true)
		;
		$model->expects($this->once())
			->method('validates')
			->with(array())
		;
		$model->validatesFor('bestAnswer');
	}

	public function testValidatesFor_useBaseOption() {
		$model = $this->_prepareMock();
		$model->expects($this->once())
			->method('useValidationSet')
			->with('bestAnswer', false)
		;
		$model->expects($this->once())
			->method('validates')
			->with(array())
		;
		$model->validatesFor('bestAnswer', array('useBase' => false));
	}

	public function testValidatesFor_booleanOptions() {
		$model = $this->_prepareMock();
		$model->expects($this->once())
			->method('useValidationSet')
			->with('bestAnswer', false)
		;
		$model->expects($this->once())
			->method('validates')
			->with(array())
		;
		$model->validatesFor('bestAnswer', false);
	}

	public function testBeforeValidate() {
		$model = $this->_prepareMock('beforeValidate');
		$model->expects($this->once())
			->method('useValidationSet')
			->with('edit', true)
		;
		$model->validates(array('validator' => 'edit'));
	}

	public function testBeforeValidate_useBaseOption() {
		$model = $this->_prepareMock('beforeValidate');
		$model->expects($this->once())
			->method('useValidationSet')
			->with(array('edit'), false)
		;
		$model->validates(array('validator' => array('edit', false)));
	}

}