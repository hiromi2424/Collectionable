<?php

class VirtualFieldsUser extends CakeTestModel {

	public $name = 'VirtualFieldsUser';
	public $alias = 'User';

	public $hasMany = array(
		'Post' => array(
			'className' => 'VirtualFieldsPost',
			'table' => 'Virtual_fields_posts',
			'foreignKey' => 'user_id',
		),
	);

}
