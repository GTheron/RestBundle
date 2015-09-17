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
 * /!\ Opinionated /!\
 * An implementation of ResourceInterface to reduce redundant code in Resources
 *
 * @package GTheron\RestBundle\Model;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
abstract class Resource implements ResourceInterface
{
    /**
     * @var string $uid
     *
     * @ORM\Column(type="string", length=255, nullable=false, unique=true)
     *
     * @JMS\Expose
     */
    protected $uid;

    /**
     * @var boolean $deleted
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $deleted;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @JMS\Expose
     * @JMS\SerializedName("createdAt")
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @JMS\Expose
     * @JMS\SerializedName("updatedAt")
     */
    protected $updatedAt;

    /**
     * @var \DateTime $deletedAt
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @JMS\Expose
     * @JMS\SerializedName("deletedAt")
     */
    protected $deletedAt;

    public function __construct()
    {
        $this->deleted = false;
    }

    /**
     * Returns uid value.
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param boolean $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Returns deleted value.
     *
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Returns createdAt value.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Returns updatedAt value.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $deletedAt
     * @return $this
     */
    public function setDeletedAt(\DateTime $deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Returns deleteAt value.
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Updates createdAt and updatedAt timestamps.
     */
    public function updateTimestamps()
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime('now');
        }

        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Generates a random uid with a prefix
     *
     * @param string $prefix
     * @return string
     */
    public function generateUid($prefix)
    {
        return $prefix.uniqid();
        //return $prefix.md5(uniqid());
    }

}