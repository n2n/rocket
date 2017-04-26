<?php
namespace rocket\spec\ei\manage\gui;

use rocket\spec\ei\manage\gui\DisplayStructure;
use rocket\spec\ei\manage\EiFrame;
use n2n\web\ui\view\View;

interface EiGui {
	
	public function getEiFrame(): EiFrame;
	
	public function getDisplayStructure(): DisplayStructure;
	
	public function createView(): View;
}