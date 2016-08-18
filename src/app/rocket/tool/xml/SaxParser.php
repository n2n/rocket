<?php
namespace rocket\tool\xml;

use rocket\tool\xml\SaxHandler;
use n2n\io\IoUtils;
use n2n\io\fs\AbstractPath;
class SaxParser {
	private $saxHandler;
	/**
	 * 
	 * @param \n2n\is\fs\AbstractPath $xmlPath
	 * @param \rocket\tool\xml\SaxHandler $saxHandler
	 * @throws \rocket\tool\xml\SaxParsingException
	 */
	public function parse(AbstractPath $xmlPath, SaxHandler $saxHandler) {
		$parser = xml_parser_create();
		$this->saxHandler = $saxHandler;
		xml_set_object($parser, $this);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($parser, "startElement", "endElement");
		xml_set_character_data_handler($parser, "cdata");
		
		$fileRes = IoUtils::fopen($xmlPath, 'rb');
		while(null != ($data = IoUtils::fread($fileRes, 4096))) {
			if (!xml_parse($parser, $data, feof($fileRes))) {
				throw new SaxParsingException(sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($parser)),
					xml_get_current_line_number($parser)));
			}
		}
	}
	/**
	 * 
	 * @param resource $parser
	 * @param string $tag
	 * @param array $attrs
	 */
	private function startElement($parser, $tagName, array $attrs) {
		$this->saxHandler->startElement($tagName, $attrs);
	}
	/**
	 * 
	 * @param unknown_type $parser
	 * @param unknown_type $cdata
	 */
	private function cdata($parser, $cdata) {
		$this->saxHandler->cdata($cdata);
	}
	/**
	 * 
	 * @param resource $parser
	 * @param string $tag
	 */
	private function endElement($parser, $tagName) {
		$this->saxHandler->endElement($tagName);
	}
}