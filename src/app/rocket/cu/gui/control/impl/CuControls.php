<?php

namespace rocket\cu\gui\control\impl;

use rocket\si\control\SiButton;

class CuControls {

	static function callback(string $id, SiButton $siButton, \Closure $callback): CallbackCuControl {
		return new CallbackCuControl($id, $callback, $siButton);
	}

}