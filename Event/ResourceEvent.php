<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Event;

use GTheron\RestBundle\Model\ResourceInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Represents an event in a Resource lifecycle
 *
 * @package GTheron\RestBundle\Event;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class ResourceEvent extends Event
{
    private $resource;

    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

}