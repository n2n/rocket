<?php
namespace rocket\script\entity\field\impl\string\wysiwyg\bbcode;

/**
 * An interface for sets of code definitons.
 *
 * @author jbowens
 */
interface CodeDefinitionSet
{

    /**
     * Retrieves the CodeDefinitions within this set as an array.
     */
    public function getCodeDefinitions();

}
