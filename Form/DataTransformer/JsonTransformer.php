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
 * A simple from/to json format transformer
 *
 * @package GTheron\RestBundle\Form\DataTransformer;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class JsonTransformer
{
    /**
     * Transforms a value from the original representation to a transformed representation.
     */
    public function transform($value)
    {
        return json_decode($value);
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     */
    public function reverseTransform($value)
    {
        return json_encode($value);
    }
}