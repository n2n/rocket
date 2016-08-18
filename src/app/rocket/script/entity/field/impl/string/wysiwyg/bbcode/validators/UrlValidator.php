<?php
namespace rocket\script\entity\field\impl\string\wysiwyg\bbcode\validators;

use rocket\script\entity\field\impl\string\wysiwyg\bbcode\InputValidator;

/**
 * An InputValidator for urls. This can be used to make [url] bbcodes secure.
 *
 * @author jbowens
 * @since May 2013
 */
class UrlValidator implements InputValidator
{

    /**
     * Returns true iff $input is a valid url.
     *
     * @param $input  the string to validate
     */
    public function validate($input)
    {
        $valid = filter_var($input, FILTER_VALIDATE_URL);
        return !!$valid;
    }

}
