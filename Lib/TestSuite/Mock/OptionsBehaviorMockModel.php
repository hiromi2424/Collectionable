<?php

class OptionsBehaviorMockModel extends CakeTestModel {
	public $useTable = false;

	public function testMagickOption($argument = null) {
		return $argument ? 'returned ' . $argument : 'test magick method';
	}
}

