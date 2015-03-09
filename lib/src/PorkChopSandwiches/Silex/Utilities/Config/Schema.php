<?php

namespace PorkChopSandwiches\Silex\Utilities\Config;

class Schema extends Tree implements SchemaInterface {
	const TYPE_ANY			= 0;
	const TYPE_BOOL			= 1;
	const TYPE_INT			= 2;
	const TYPE_FLOAT		= 3;
	const TYPE_STRING		= 4;
	const TYPE_COLLECTION	= 5;

	/**
	 * @param mixed $var
	 * @throws \Exception
	 *
	 * @return int
	 */
	static public function getType ($var) {
		switch (true) {
			case is_bool($var):
				return self::TYPE_BOOL;
			case is_float($var):
				return self::TYPE_FLOAT;
			case is_int($var):
				return self::TYPE_INT;
			case is_string($var):
				return self::TYPE_STRING;
			case is_array($var):
				return self::TYPE_COLLECTION;
			default:
				throw new \Exception("Invalid type of var");
		}
	}

	/**
	 * @param $var
	 * @param $type
	 * @return bool
	 * @throws \Exception
	 */
	static public function isValidType ($var, $type) {
		return ($type === self::TYPE_ANY) || (is_array($var) && $type instanceof SchemaInterface) || (self::getType($var) === $type);
	}
}
