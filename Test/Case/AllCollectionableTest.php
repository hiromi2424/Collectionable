<?php
class AllCollectionableTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {

		$suite = new PHPUnit_Framework_TestSuite('All Collectionable plugin tests');

		$basePath = App::pluginPath('Collectionable') . 'Test' . DS . 'Case' . DS;
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath));

		while ($it->valid()) {

			if (!$it->isDot()) {
				$file = $it->key();
				if (preg_match('|Test\.php$|', $file) && $file !== __FILE__) {
					$suite->addTestFile($file);
				}
			}

			$it->next();
		}

		return $suite;

	}
}