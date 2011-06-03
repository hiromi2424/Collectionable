<?php

class ConfigValidaitonMockModel extends Model {

	public $useTable = false;
	public $validate = array(
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
