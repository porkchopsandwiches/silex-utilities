<?php


use PorkChopSandwiches\Silex\Utilities\Arrays;

class ArraysIndexableTestClass {
	private $index_by;

	public function __construct ($index_by) {
		$this -> index_by = $index_by;
	}

	public function getIndexBy () {
		return $this -> index_by;
	}
}

class ArraysTest extends PHPUnit_Framework_TestCase {

	/**
	 * @return Arrays
	 */
	private function getInstance () {
		return new Arrays();
	}


	public function testExists () {
		$instance = $this -> getInstance();
		$this -> assertTrue($instance instanceof Arrays);
	}

	 /**
     * @depends testExists
     */
    public function testIndexBy () {
		$instance = $this -> getInstance();

		/** @var ArraysIndexableTestClass[] $indexed */
		$array = array(
			new ArraysIndexableTestClass(1),
			new ArraysIndexableTestClass(2),
			new ArraysIndexableTestClass("c"),
			new ArraysIndexableTestClass("d"),
		);

		/** @var ArraysIndexableTestClass[] $indexed */
		$indexed = $instance -> index($array, function (ArraysIndexableTestClass $i) {
			return $i -> getIndexBy();
		});

		$this -> assertArrayHasKey(1, $indexed);
		$this -> assertArrayHasKey(2, $indexed);
		$this -> assertArrayHasKey("c", $indexed);
		$this -> assertArrayHasKey("d", $indexed);
		$this -> assertEquals($indexed["c"] -> getIndexBy(), "c");
    }

	public function testReset () {
		$instance = $this -> getInstance();
		$this -> assertEquals($instance -> reset(array(3, 2, 1)), 3);
	}

	public function testEnd () {
		$instance = $this -> getInstance();
		$this -> assertEquals($instance -> end(array(1, 2, 3)), 3);
	}
}
