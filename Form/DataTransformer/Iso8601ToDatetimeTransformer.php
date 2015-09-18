<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Form\DataTransformer;

/**
 * Use to convert dates formatted as ISO8601 into php \DateTime
 *
 * @package GTheron\RestBundle\Form\DataTransformer;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class Iso8601ToDatetimeTransformer
{

    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     */
    public function transform($value)
    {
        if(get_class($value) === get_class(new \DateTime())) return $value->format(\DateTime::ISO8601);
        else return '';
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     */
    public function reverseTransform($value)
    {
        $matches = preg_match('/(\d\d\d\d)(-)?(\d\d)(-)?(\d\d)(T)?(\d\d)(:)?(\d\d)(:)?(\d\d)(\.\d+)?(Z|([+-])(\d\d)(:)?(\d\d))/', $value);
        if(!$matches) throw new \Exception('Value "'.$value.'" is not an ISO8601 Datetime string');
        return \DateTime::createFromFormat(\DateTime::ISO8601, $value);
    }
}