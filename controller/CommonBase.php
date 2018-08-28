<?php

class CommonBase
{
	function __construct()
	{
		$this->path_info = $this->path_info();
	}

	private function path_info()
	{
		return CONTROLLER_PATH.'/'.str_replace('Controller', '', CONTROLLER_NAME).'#'.ACTION_NAME;
	}
}