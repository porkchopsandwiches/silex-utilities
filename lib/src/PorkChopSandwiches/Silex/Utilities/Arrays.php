<?php

namespace PorkChopSandwiches\Silex\Utilities;

/**
 * Class Arrays
 *
 * @author Cam Morrow
 */
class Arrays implements Utility {

	/**
	 * @param array			$array
	 * @param callable		$callable
	 * @param bool			$reference
	 *
	 * @return array
	 */
	public function index (array $array, callable $callable, $reference = false) {
		$indexed = array();

		if ($reference) {
			foreach ($array as $key => &$value) {
				$indexed[$callable($value, $key)] = &$value;
			}
		} else {
			foreach ($array as $key => $value) {
				$indexed[$callable($value, $key)] = $value;
			}
		}

		return $indexed;
	}

	/**
	 * @param array	$array
	 *
	 * @return mixed
	 */
	public function reset (array $array) {
		return reset($array);
	}

	/**
	 * @param array	$array
	 *
	 * @return mixed
	 */
	public function end (array $array) {
		return end($array);
	}

	/**
	 * @param $array
	 * @param callable|string|array	$callback
	 * @param mixed					$default
	 *
	 * @return mixed
	 */
	public function findOne (array $array, $callback, $default = null) {
		$filtered = array_filter($array, $callback);
		return count($filtered) ? reset($filtered) : $default;
	}

	/**
	 * @param array					$array
	 * @param callable|string|array	$callback
	 * @return array
	 */
	public function walk (array $array, $callback) {
		return array_walk($array, $callback);
	}

	/**
	 * @param array					$array
	 * @param callable|string|array	$callback
	 * @return array
	 */
	public function map (array $array, $callback) {
		return array_map($callback, $array);
	}

	/**
	 * @param array					$array
	 * @param callable|string|array	$callback
	 *
	 * @return array
	 */
	public function sort (array $array, $callback) {
		usort($array, $callback);
		return $array;
	}

	/**
	 * @param mixed		$value
	 * @param integer	$count
	 *
	 * @return array
	 */
	public function fill ($value, $count) {
		$array = array();
		for ($i = 0; $i < $count; $i++) {
			$array[] = $value;
		}
		return $array;
	}

	/**
	 * Performs an array merge, but when both values for a key are arrays, a deep merge will occur, retaining the unique keys of both without changing the value types.
	 *
	 * @example
	 *   $a = array(1 => 2, 3 => array(4 => 5, 6 => 7));
	 *   $b = array(3 => array(4 => "Four"));
	 *   deepMerge($a, $b) == array(1 => 2, 3 => array(4 => "Four", 6 => 7));
	 *
	 * @param array &$mergee		The array whose values will be overridden during merging.
	 * @param array &$merger		The array whose values will override during merging.
	 *
	 * @return array
	 */
	public function deepMerge (array $mergee, array $merger) {
		$merged = $mergee;

		foreach ($merger as $key => &$value) {

			# If key exists an an array on both sides, deep merge
			if (array_key_exists($key, $merged) && is_array($value) && is_array($merged[$key])) {
				$merged[$key] = $this -> deepMerge($merged[$key], $value);

			# Otherwise, simply replace
			} else {
				$merged[$key] = $value;
			}
		}

		return $merged;
	}
}
