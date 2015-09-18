<?php

/**
 * This file contains the SecurityResourceManagerInterface class
*/

namespace GTheron\RestBundle\Security;

use GTheron\RestBundle\Model\ResourceInterface;
use GTheron\RestBundle\Service\ResourceManagerInterface;

/**
 * SecurityResourceManagerInterface
 *
 * @package GTheron\RestBundle\Security;
 * @author Gabriel Théron
*/
interface SecurityResourceManagerInterface extends ResourceManagerInterface
{
    /**
     * Returns the Roles class of the associated bundle
     *
     * @param ResourceInterface $resource
     * @return string
     */
    public function getRoleClass(ResourceInterface $resource);

    /**
     * Gets a role on a resource from its suffix and the resource's class
     *
     * @param ResourceInterface $resource
     * @param string $roleSuffix
     * @return string
     */
    public function getRole(ResourceInterface $resource, $roleSuffix);
}