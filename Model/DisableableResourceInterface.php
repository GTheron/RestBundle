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
 * DisableableResourceInterface
 *
 * @package GTheron\RestBundle\Model;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
interface DisableableResourceInterface extends ResourceInterface
{
    /**
     * @return boolean
     */
    public function isDisabled();

    /**
     * @param $disabled
     */
    public function setDisabled($disabled);

    /**
     * @param \DateTime $disabledAt
     */
    public function setDisabledAt(\DateTime $disabledAt);
}