<?php

namespace PorkChopSandwiches\Silex\Utilities\Config;

use Countable; # Implements a count() function
use ArrayAccess; # Values can be accessed like an array
use IteratorAggregate; # Can be iterated via foreach()
use PorkChopSandwiches\Preserialiser\Preserialisable;

interface TreeInterface extends Countable, ArrayAccess, IteratorAggregate, Preserialisable {

	/**
	 * @param string	$path
	 *
	 * @return mixed
	 */
	public function get ($path);

	/**
	 * @param string	$path
	 *
	 * @return bool
	 */
	public function has ($path);

	/**
	 * @return TreeInterface
	 */
	public function getParent ();

	/**
	 * @return TreeInterface
	 */
	public function getRoot ();

	/**
	 * @return array
	 */
	public function asArray ();
}
