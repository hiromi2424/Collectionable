<?php

class VirtualFieldsPostFixture extends CakeTestFixture {
	var $name = 'VirtualFieldsPost';

	var $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'key' => 'index'),
		'title' => array('type' => 'string', 'length' => 255, 'null' => false),
		'body' => 'text',
	);

	var $records = array(
		array('id' => 1, 'title' => 'title1', 'body' => 'article 1', 'user_id' => 1),
		array('id' => 2, 'title' => 'title2', 'body' => 'article 2', 'user_id' => 2),
		array('id' => 3, 'title' => 'title3', 'body' => 'article 3', 'user_id' => 1),
		array('id' => 4, 'title' => 'title4', 'body' => 'article 4', 'user_id' => 3),
	);
}