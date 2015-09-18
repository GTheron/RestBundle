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
 * SubResourceInterface
 *
 * @package GTheron\RestBundle\Model;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
interface SubResourceInterface extends ResourceInterface
{
    /**
     * @param ResourceInterface $parent
     * @return ResourceInterface
     */
    public function setParent(ResourceInterface $parent);

    /**
     * @return ResourceInterface
     */
    public function getParent();

}