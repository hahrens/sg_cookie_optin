<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A collection of helpers for a Mustache instance.
 */
class Mustache_HelperCollection {
	private $helpers = [];

	/**
	 * Helper Collection constructor.
	 *
	 * Optionally accepts an array (or Traversable) of `$name => $helper` pairs.
	 *
	 * @throws Mustache_Exception_InvalidArgumentException if the $helpers argument isn't an array or Traversable
	 *
	 * @param array|Traversable $helpers (default: null)
	 */
	public function __construct($helpers = NULL) {
		if ($helpers === NULL) {
			return;
		}

		if (!is_array($helpers) && !$helpers instanceof Traversable) {
			throw new Mustache_Exception_InvalidArgumentException('HelperCollection constructor expects an array of helpers');
		}

		foreach ($helpers as $name => $helper) {
			$this->add($name, $helper);
		}
	}

	/**
	 * Magic mutator.
	 *
	 * @see Mustache_HelperCollection::add
	 *
	 * @param string $name
	 * @param mixed  $helper
	 */
	public function __set($name, $helper) {
		$this->add($name, $helper);
	}

	/**
	 * Add a helper to this collection.
	 *
	 * @param string $name
	 * @param mixed  $helper
	 */
	public function add($name, $helper) {
		$this->helpers[$name] = $helper;
	}

	/**
	 * Magic accessor.
	 *
	 * @see Mustache_HelperCollection::get
	 *
	 * @param string $name
	 *
	 * @return mixed Helper
	 */
	public function __get($name) {
		return $this->get($name);
	}

	/**
	 * Get a helper by name.
	 *
	 * @throws Mustache_Exception_UnknownHelperException If helper does not exist
	 *
	 * @param string $name
	 *
	 * @return mixed Helper
	 */
	public function get($name) {
		if (!$this->has($name)) {
			throw new Mustache_Exception_UnknownHelperException($name);
		}

		return $this->helpers[$name];
	}

	/**
	 * Magic isset().
	 *
	 * @see Mustache_HelperCollection::has
	 *
	 * @param string $name
	 *
	 * @return bool True if helper is present
	 */
	public function __isset($name) {
		return $this->has($name);
	}

	/**
	 * Check whether a given helper is present in the collection.
	 *
	 * @param string $name
	 *
	 * @return bool True if helper is present
	 */
	public function has($name) {
		return array_key_exists($name, $this->helpers);
	}

	/**
	 * Magic unset().
	 *
	 * @see Mustache_HelperCollection::remove
	 *
	 * @param string $name
	 */
	public function __unset($name) {
		$this->remove($name);
	}

	/**
	 * Check whether a given helper is present in the collection.
	 *
	 * @throws Mustache_Exception_UnknownHelperException if the requested helper is not present
	 *
	 * @param string $name
	 */
	public function remove($name) {
		if (!$this->has($name)) {
			throw new Mustache_Exception_UnknownHelperException($name);
		}

		unset($this->helpers[$name]);
	}

	/**
	 * Clear the helper collection.
	 *
	 * Removes all helpers from this collection
	 */
	public function clear() {
		$this->helpers = [];
	}

	/**
	 * Check whether the helper collection is empty.
	 *
	 * @return bool True if the collection is empty
	 */
	public function isEmpty() {
		return empty($this->helpers);
	}
}
