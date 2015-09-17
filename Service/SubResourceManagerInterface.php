<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Service;

use GTheron\RestBundle\Model\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SubResourceManagerInterface
 *
 * @package GTheron\RestBundle\Service;
 * @author Gabriel Théron
*/
interface SubResourceManagerInterface
{
    /**
     * Returns all sub resources of the given type that belong to the given parent
     *
     * @param ResourceInterface $resource
     * @param ResourceInterface $parent
     * @return ArrayCollection
     */
    public function findAllByParent(ResourceInterface $resource, ResourceInterface $parent);
}