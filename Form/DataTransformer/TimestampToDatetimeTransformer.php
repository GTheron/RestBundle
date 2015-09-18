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
 * Simple \Datetime to timestamp transformer
 *
 * @package GTheron\RestBundle\Form\DataTransformer;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class TimestampToDatetimeTransformer
{
    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     */
    public function transform($value)
    {
        if(method_exists($value, 'getTimestamp')) return $value->getTimestamp();
        else return '';
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     */
    public function reverseTransform($value)
    {
        $date = new \DateTime();
        return $date->setTimestamp($value);
    }
}