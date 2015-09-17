<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Model;

/**
 * LinkableResourceInterface
 *
 * @package GTheron\RestBundle\Model;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
interface LinkableResourceInterface extends ResourceInterface
{
    /**
     * Checks whether this resource is linked to the passed resource
     *
     * @param ResourceInterface $resource
     * @return bool
     */
    public function checkLink(ResourceInterface $resource);

    /**
     * Either links or unlinks two resources, depending on whether they were linked before
     *
     * @param ResourceInterface $resource
     * @return mixed
     */
    public function setLink(ResourceInterface $resource);
}