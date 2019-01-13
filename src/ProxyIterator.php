<?php

namespace RRule;

class ProxyIterator extends \IteratorIterator
{
	protected $factory;

	public function __construct(\Traversable $iterator, callable $factory)
	{
		$this->factory = $factory;
		parent::__construct($iterator);
	}

	public function current()
	{
		return call_user_func_array($this->factory, [parent::current()]);
	}

}