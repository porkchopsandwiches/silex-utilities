<?php

namespace PorkChopSandwiches\Silex\Utilities\Config;

use ArrayIterator;
use PorkChopSandwiches\Silex\Utilities\Config\Exceptions\InvalidValueException;
use PorkChopSandwiches\Silex\Utilities\Config\Exceptions\InvalidKeyException;
use PorkChopSandwiches\Silex\Utilities\Config\Exceptions\NonExistentKeyException;
use PorkChopSandwiches\Silex\Utilities\Config\Exceptions\ImmutableTreeException;

class Tree implements TreeInterface {

	/** @var mixed[] $data */
	protected $data = array();

	/** @var string $classname */
	protected $classname;

	/** @var TreeInterface $parent */
	protected $parent;

	/** @var SchemaInterface $schema */
	protected $schema;

	# -----------------------------------------------------
	# Static
	# -----------------------------------------------------

	const SEPARATOR = ".";

	/**
	 * @param string	$path
	 *
	 * @return string[]
	 */
	static function getPathElements ($path) {
		return explode(self::SEPARATOR, $path);
	}

	/**
	 * @param string	$path
	 *
	 * @return string[]
	 */
	static function getFirstAndRestPathElements ($path) {
		$elements = explode(self::SEPARATOR, $path, 2);

		if (count($elements) < 2) {
			$elements[] = null;
		}

		return $elements;
	}

	static public function isValidValue ($value) {
		return ($value instanceof TreeInterface) || is_array($value) || is_scalar($value);
	}

	# -----------------------------------------------------
	# Tree Interface
	# -----------------------------------------------------

	/**
	 * @param $key
	 *
	 * @return Schema|null
	 */
	protected function getSchemaForKey ($key) {
		if ($this -> schema) {
			if ($this -> schema -> has($key)) {
				$schema = $this -> schema -> get($key);
				return $schema instanceof Schema ? $schema : null;
			}
		}

		return null;
	}

	protected function validateValue ($key, $value) {
		if (self::isValidValue($value)) {
			if ($this -> schema) {
				if ($this -> schema -> has($key)) {
					$schema_value = $this -> schema -> get($key);
					if (!Schema::isValidType($value, $schema_value)) {
						throw new InvalidValueException("Data has a value at `" . $key . "` which is not of the expected type.");
					}
				} else {
					throw new InvalidKeyException("Data has a key `" . $key . "` that is not in the schema.");
				}
			}
		} else {
			throw new InvalidValueException("Value at key `" . $key . "` is not valid.");
		}
	}

	protected function applyValue ($key, $value, $tree_class = false) {
		$class = $tree_class ?: $this -> classname;

		$this -> validateValue($key, $value);

		$this -> data[$key] = is_array($value) ? new $class($value, $this -> getSchemaForKey($key), $this) : $value;
	}

	protected function apply (array $data, $tree_class = false) {
		array_walk($data, function ($value, $key) use ($tree_class) {
			$this -> applyValue($key, $value, $tree_class);
		});
	}

	public function __construct (array $data = array(), SchemaInterface $schema = null, TreeInterface $parent = null) {
		$this -> schema = $schema;
		$this -> parent = $parent;
		$this -> classname = $class = get_class($this);

		$this -> apply($data);
	}

	/**
	 * @param string $path
	 *
	 * @throws InvalidKeyException
	 * @throws NonExistentKeyException
	 *
	 * @return mixed
	 */
	public function get ($path) {
		list ($first, $rest) = self::getFirstAndRestPathElements($path);

		if (array_key_exists($first, $this -> data)) {
			$value = $this -> data[$first];
			if (is_null($rest)) {
				return $value;
			} else if ($value instanceof TreeInterface) {
				return $value -> get($rest);
			} else {
				throw new InvalidKeyException("Value at key `" . $first . "` is a scalar; cannot step further down the requested path `" . $rest . "`");
			}
		} else {
			throw new NonExistentKeyException("Key `" . $first . "` does not exist.");
		}
	}

	/**
	 * @param $path
	 *
	 * @return bool
	 */
	public function has ($path) {
		try {
			$this -> get($path);
			return true;
		} catch (InvalidKeyException $e) {
			return false;
		} catch (NonExistentKeyException $e) {
			return false;
		}
	}

	/**
	 * @return Tree|null
	 */
	public function getParent () {
		return $this -> parent;
	}

	/**
	 * @return Tree
	 */
	public function getRoot () {
		$parent = $this -> parent;
		while ($parent instanceof TreeInterface) {
			$next = $parent -> getParent();
			if (!$next) {
				return $parent;
			} else {
				$parent = $next;
			}
		}

		return $this;
	}

	/**
	 * @return mixed[]
	 */
	public function asArray () {
		return $this -> preserialise();
	}

	# -----------------------------------------------------
	# Preserialisable
	# -----------------------------------------------------

	/**
	 * @param array $args
	 * @return array
	 */
	public function preserialise (array $args = array()) {
		return array_map(function ($value) {
			return ($value instanceof TreeInterface) ? $value -> preserialise() : $value;
		}, $this -> data);
	}

	# -----------------------------------------------------
	# Countable
	# -----------------------------------------------------

	/**
	 * @return int
	 */
	public function count () {
		return count($this -> data);
	}

	# -----------------------------------------------------
	# IteratorAggregate
	# -----------------------------------------------------

	/**
	 * @return ArrayIterator
	 */
	public function getIterator () {
		return new ArrayIterator($this -> data);
	}

	# -----------------------------------------------------
	# ArrayAccess
	# -----------------------------------------------------

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 * @throws ImmutableTreeException
	 *
	 * @return Tree
	 */
	public function offsetSet($offset, $value) {
		throw new ImmutableTreeException("Cannot set a value on an immutable Tree.");
    }

	/**
	 * @param mixed $offset
	 * @return bool
	 */
    public function offsetExists($offset) {
		return $this -> has($offset);
    }

	/**
	 * @param mixed $offset
	 *
	 * @throws ImmutableTreeException
	 *
	 * @return Tree
	 */
    public function offsetUnset($offset) {
		throw new ImmutableTreeException("Cannot delete a value on an immutable Tree.");
    }

	/**
	 * @param string $offset
	 *
	 * @throws InvalidKeyException
	 * @throws NonExistentKeyException
	 *
	 * @return mixed
	 */
    public function offsetGet($offset) {
		return $this -> get($offset);
    }

	# -----------------------------------------------------
	# Object-style support
	# -----------------------------------------------------

	public function __get ($key) {
		return $this -> get($key);
	}

	public function __set ($key, $value) {
		throw new ImmutableTreeException("Cannot set a value on an immutable Tree.");
	}

	public function __isset ($key) {
		return $this -> has($key);
	}

	public function __unset ($key) {
		throw new ImmutableTreeException("Cannot delete a value on an immutable Tree.");
	}
}
