<?php

namespace PorkChopSandwiches\Silex\Utilities\Config;

use PorkChopSandwiches\Silex\Utilities\Config\Exceptions\InvalidValueException;
use PorkChopSandwiches\Silex\Utilities\Config\Exceptions\InvalidKeyException;
use PorkChopSandwiches\Silex\Utilities\Config\Exceptions\NonExistentKeyException;

class MutableTree extends Tree implements MutableTreeInterface {

	protected function applyValue ($key, $value, $tree_class = false) {
		$class = $tree_class ?: $this -> classname;

		$this -> validateValue($key, $value);

		# If value already exists as a sub tree
		if (array_key_exists($key, $this -> data) && $this -> data[$key] instanceof MutableTreeInterface) {
			/** @var MutableTreeInterface $tree */
			$tree = $this -> data[$key];
			if (is_array($value)) {
				$tree -> mergeWithArray($value);
			} else {
				throw new InvalidValueException("Existing value at `" . $key . "` is non-scalar, cannot overwrite with scalar value.");
			}
		} else {
			$schema = $this -> getSchemaForKey($key);
			$this -> data[$key] = is_array($value) ? new $class($value, $schema, $this) : $value;
		}
	}

	/**
	 * @param string	$path
	 * @param mixed		$value
	 *
	 * @throws InvalidKeyException
	 *
	 * @return MutableTree
	 */
	public function set ($path, $value) {
		list ($first, $rest) = self::getFirstAndRestPathElements($path);

		# If we are going still deeper...
		if (!is_null($rest)) {
			if (!array_key_exists($first, $this -> data)) {
				$this -> applyValue($first, array());
			} else if (!($this -> data[$first] instanceof MutableTreeInterface)) {
				throw new InvalidKeyException("Value at key `" . $first . "` is a scalar; cannot step further down the requested path `" . $rest . "`");
			}

			/** @var MutableTreeInterface $tree */
			$tree = $this -> data[$first];

			$tree -> set($rest, $value);
		} else {
			$this -> applyValue($first, $value);
		}

		return $this;
	}

	/**
	 * @param TreeInterface $tree
	 *
	 * @return MutableTree
	 */
	public function merge (TreeInterface $tree) {
		$this -> apply($tree -> asArray(), $this -> classname);

		return $this;
	}

	/**
	 * @param array $array
	 *
	 * @return MutableTree
	 */
	public function mergeWithArray (array $array) {
		$this -> apply ($array, $this -> classname);
		return $this;
	}

	/**
	 * @param string	$path
	 *
	 * @throws InvalidKeyException
	 * @throws NonExistentKeyException
	 *
	 * @return MutableTree
	 */
	public function delete ($path) {
		list ($first, $rest) = self::getFirstAndRestPathElements($path);

		# If we are going still deeper...
		if (!is_null($rest)) {
			if (array_key_exists($first, $this -> data)) {
				if ($this -> data[$first] instanceof MutableTreeInterface) {
					/** @var MutableTreeInterface $tree */
					$tree = $this -> data[$first];
					$tree -> delete($rest);
				} else {
					throw new InvalidKeyException("Value at key `" . $first . "` is a scalar; cannot step further down the requested path `" . $rest . "`");
				}
			} else {
				throw new NonExistentKeyException("Key `" . $first . "` does not exist.");
			}
		} else {
			unset($this -> data[$first]);
		}

		return $this;
	}

	# -----------------------------------------------------
	# ArrayAccess
	# -----------------------------------------------------

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 * @return MutableTree
	 */
	public function offsetSet($offset, $value) {
		return $this -> set($offset, $value);
    }

	/**
	 * @param mixed $offset
	 *
	 * @return MutableTree
	 */
	public function offsetUnset($offset) {
		return $this -> delete($offset);
	}

	# -----------------------------------------------------
	# Object-style support
	# -----------------------------------------------------

	/**
	 * @param string	$key
	 * @param mixed		$value
	 *
	 * @throws InvalidKeyException
	 */
	public function __set ($key, $value) {
		$this -> set($key, $value);
	}

	/**
	 * @param string	$key
	 *
	 * @throws InvalidKeyException
	 * @throws NonExistentKeyException
	 */
	public function __unset ($key) {
		$this -> delete($key);
	}
}
