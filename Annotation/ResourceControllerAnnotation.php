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
 * ResourceControllerAnnotation
 *
 * @package GTheron\RestBundle\Annotation;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
 *
 * @Annotation
 * @Target("CLASS")
*/
class ResourceControllerAnnotation
{
    public $resourceClass;

    /**
     * @return string
     */
    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * @param string $resourceClass
     */
    public function setResourceClass($resourceClass)
    {
        $this->resourceClass = $resourceClass;
    }
}