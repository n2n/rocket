<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

use n2n\model\RequestScoped;
use n2n\http\Request;
interface DynamicLinkBuilder extends RequestScoped {
	public function buildLink(Request $request, $characteristics);
}