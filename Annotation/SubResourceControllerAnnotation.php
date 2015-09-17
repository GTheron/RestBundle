<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Annotation;

/**
 * SubResourceControllerAnnotation
 *
 * @package GTheron\RestBundle\Annotation;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
 *
 * @Annotation
 * @Target("CLASS")
*/
class SubResourceControllerAnnotation
{
    public $parentClass;

    /**
     * @return string
     */
    public function getParentClass()
    {
        return $this->parentClass;
    }

    /**
     * @param string $parentClass
     */
    public function setParentClass($parentClass)
    {
        $this->parentClass = $parentClass;
    }
}