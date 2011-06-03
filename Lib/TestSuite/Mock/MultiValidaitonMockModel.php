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
