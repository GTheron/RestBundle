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
 * An implementation of the DisableableResourceInterface to reduce code redundancy in
 * Disableable Resources
 *
 * @package GTheron\RestBundle\Model;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
abstract class DisableableResource extends Resource implements DisableableResourceInterface
{
    /**
     * @var boolean $disabled
     *
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @JMS\Expose
     */
    protected $disabled;

    /**
     * @var \DateTime $disabledAt
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @JMS\Expose
     * @JMS\SerializedName("disabledAt")
     */
    protected $disabledAt;

    public function __construct()
    {
        parent::__construct();
        $this->disabled = false;
    }

    /**
     * @param boolean $disabled
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Returns disabled value.
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param \DateTime $disabledAt
     * @return $this
     */
    public function setDisabledAt(\DateTime $disabledAt)
    {
        $this->disabledAt = $disabledAt;

        return $this;
    }

    /**
     * Returns disabledAt value.
     *
     * @return \DateTime
     */
    public function getDisabledAt()
    {
        return $this->disabledAt;
    }

}