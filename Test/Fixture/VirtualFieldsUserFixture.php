<?php

class VirtualFieldsUserFixture extends CakeTestFixture {
	var $name = 'VirtualFieldsUser';

	var $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'first_name' => array('type' => 'string', 'length' => 255, 'null' => false),
		'last_name' => array('type' => 'string', 'length' => 255, 'null' => false),
	);

	var $records = array(
		array('id' => 1, 'first_name' => 'yamada', 'last_name' => 'ichirou'),
		array('id' => 2, 'first_name' => 'tanaka', 'last_name' => 'jirou'),
		array('id' => 3, 'first_name' => 'kaneda', 'last_name' => 'saburou'),
		array('id' => 4, 'first_name' => 'shimizu', 'last_name' => 'shinou'),
	);
}