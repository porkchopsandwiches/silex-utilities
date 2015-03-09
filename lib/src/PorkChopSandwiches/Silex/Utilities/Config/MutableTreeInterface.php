<?php

namespace PorkChopSandwiches\Silex\Utilities\Config;

interface MutableTreeInterface extends TreeInterface {

	/**
	 * @param string	$path
	 * @param mixed		$value
	 *
	 * @return MutableTreeInterface
	 */
	public function set ($path, $value);

	/**
	 * @param string	$path
	 *
	 * @return MutableTreeInterface
	 */
	public function delete ($path);

	/**
	 * @param TreeInterface $tree
	 *
	 * @return MutableTreeInterface
	 */
	public function merge (TreeInterface $tree);

	/**
	 * @param array $array
	 *
	 * @return MutableTreeInterface
	 */
	public function mergeWithArray (array $array);
}
