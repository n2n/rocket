<?php
namespace rocket\script\entity\field\impl\string\wysiwyg\bbcode\definitionset;

use rocket\script\entity\field\impl\string\wysiwyg\bbcode\validators\CssColorValidator;

use rocket\script\entity\field\impl\string\wysiwyg\bbcode\validators\UrlValidator;

use rocket\script\entity\field\impl\string\wysiwyg\bbcode\CodeDefinitionSet;

use rocket\script\entity\field\impl\string\wysiwyg\bbcode\CodeDefinitionBuilder;

class PhpbbDefinitionSet implements CodeDefinitionSet {
	
	/* The default code definitions in this set. */
	protected $definitions = array();
	
	/**
	* Constructs the default code definitions.
	*/
	public function __construct()
	{
		 /* [b] tag */
        $builder = new CodeDefinitionBuilder('b', '<strong>{param}</strong>');
        array_push($this->definitions, $builder->build());
        
        /* [u] tag */
        $builder = new CodeDefinitionBuilder('u', '<u>{param}</u>');
                array_push($this->definitions, $builder->build());
        
        /* [i] tag */
        $builder = new CodeDefinitionBuilder('i', '<em>{param}</em>');
        array_push($this->definitions, $builder->build());
        
        /* [color] tag */
        $builder = new CodeDefinitionBuilder('color', '<span style="color: {option}">{param}</span>');
        $builder->setUseOption(true)->setOptionValidator(new CssColorValidator());
        array_push($this->definitions, $builder->build());
        
        /* [size] tag */
        $builder = new CodeDefinitionBuilder('size', '<span style="font-size: {option}">{param}</span>');
        $builder->setUseOption(true);
        array_push($this->definitions, $builder->build());
        
        /* [quote] tag */
        $builder = new CodeDefinitionBuilder('quote', '<blockquote>{param}</blockquote>');
        array_push($this->definitions, $builder->build());
        
        /* [code] tag */
        $builder = new CodeDefinitionBuilder('code', '<code>{param}</code>');
        array_push($this->definitions, $builder->build());
        
        /* [email] tag */
        $builder = new CodeDefinitionBuilder('email', '<a href="mailto:{param}">{param}</code>');
        array_push($this->definitions, $builder->build());
    
        $urlValidator = new UrlValidator();
        
        /* [url] link tag */
        $builder = new CodeDefinitionBuilder('url', '<a href="{param}">{param}</a>');
        $builder->setParseContent(false)->setBodyValidator($urlValidator);
        array_push($this->definitions, $builder->build());

        /* [url=http://example.com] link tag */
        $builder = new CodeDefinitionBuilder('url', '<a href="{option}">{param}</a>');
        $builder->setUseOption(true)->setParseContent(true)->setOptionValidator($urlValidator);
        array_push($this->definitions, $builder->build());

        /* [img] image tag */
        $builder = new CodeDefinitionBuilder('img', '<img src="{param}" />');
        $builder->setUseOption(false)->setParseContent(false)->setBodyValidator($urlValidator);
        array_push($this->definitions, $builder->build());

        /* [img=alt text] image tag */
        $builder = new CodeDefinitionBuilder('img', '<img src="{param} alt="{option}" />');
        $builder->setUseOption(true);
        array_push($this->definitions, $builder->build());

        /* [list] ul tag */
        $builder = new CodeDefinitionBuilder('list', '<ul>{param}</ul>');
        array_push($this->definitions, $builder->build());
        
        /* [list] ul tag */
        $builder = new CodeDefinitionBuilder('list', '<ol>{param}</ol>');
        $builder->setUseOption(true);
        array_push($this->definitions, $builder->build());
        
        /* [*] * tag */
        $builder = new CodeDefinitionBuilder('*', '<li>{param}</li>');
        array_push($this->definitions, $builder->build());
        
	}
	
	/**
	* Returns an array of the default code definitions.
	*/
	public function getCodeDefinitions()
	{
		return $this->definitions;
	}
}