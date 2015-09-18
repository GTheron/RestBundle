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
 * Represents a resource that can be exposed through a REST API
 *
 * @package GTheron\RestBundle\Model;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
interface ResourceInterface
{
    /**
     * @return boolean
     */
    public function isDeleted();

    /**
     * @return string
     */
    public function getUid();

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @return \DateTime
     */
    public function getDeletedAt();

    /**
     * @param boolean $deleted
     */
    public function setDeleted($deleted);

    /**
     * @param \DateTime $deletedAt
     */
    public function setDeletedAt(\DateTime $deletedAt);

    /**
     * Updates createdAt and updatedAt timestamps.
     */
    public function updateTimestamps();

    /**
     * @param string $prefix
     * @return string
     */
    public function generateUid($prefix);
}