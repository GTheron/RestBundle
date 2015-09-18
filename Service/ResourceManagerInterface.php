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

use Doctrine\ORM\EntityManager;
use GTheron\RestBundle\Model\DisableableResourceInterface;
use GTheron\RestBundle\Model\ResourceInterface;
use Symfony\Component\Form\AbstractType;

/**
 * ResourceManagerInterface
 *
 * @package GTheron\RestBundle\Service;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
interface ResourceManagerInterface
{
    /**
     * Creates a new resource
     *
     * @param ResourceInterface $resource
     * @param bool $andFlush
     * @return ResourceInterface
     */
    public function create(ResourceInterface $resource, $andFlush = true);

    /**
     * Updates a resource
     *
     * @param ResourceInterface $resource
     * @param bool $andFlush
     * @return ResourceInterface
     */
    public function update(ResourceInterface $resource, $andFlush = true);

    /**
     * Deletes an resource
     *
     * @param ResourceInterface $resource
     * @param bool $andFlush
     * @return ResourceInterface
     */
    public function delete(ResourceInterface $resource, $andFlush = true);

    /**
     * Saves a resource in its current state, without throwing a CREATE or UPDATE event
     *
     * @param ResourceInterface $resource
     * @param bool $andFlush
     * @return ResourceInterface
     */
    public function save(ResourceInterface $resource, $andFlush = true);

    /**
     * Validates a Resource's received fields against a specified form (using the default one if null)
     *
     * @param ResourceInterface $resource
     * @param array $fields
     * @param string $method
     * @param AbstractType $formType
     * @return mixed
     */
    public function validate(
        ResourceInterface $resource,
        array $fields,
        $method,
        AbstractType $formType = null
    );

    /**
     * Disables/enables an resource
     *
     * @param DisableableResourceInterface $resource
     * @param bool $disabled
     * @param bool $andFlush
     * @return DisableableResourceInterface
     * @throws \Exception
     */
    public function setDisabled(DisableableResourceInterface $resource, $disabled = true, $andFlush = true);

    /**
     * Finds all of the given resource
     * TODO add parameters injection to the method
     *
     * @param ResourceInterface $resource
     * @return array
     */
    public function findAll(ResourceInterface $resource);

    /**
     * Fetches one resource from its uid
     *
     * @param ResourceInterface $resource
     * @param string $uid
     */
    public function findOne(ResourceInterface $resource, $uid);

    /**
     * Returns the resource's repository
     *
     * @param ResourceInterface $resource
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository(ResourceInterface $resource);

    /**
     * Returns an resource's associated FormType class
     *
     * @param ResourceInterface $resource
     * @return string
     */
    public function getFormType(ResourceInterface $resource);

    /**
     * Returns the short class name of an resource, which is used as a base for all classes name prediction
     *
     * TODO stop guessing
     *
     * @param ResourceInterface $resource
     * @return string
     */
    public function getResourceShortName(ResourceInterface $resource);

    /**
     * Returns the Roles class of the associated bundle
     *
     * @param ResourceInterface $resource
     * @return string
     */
    public function getRoleClass(ResourceInterface $resource);

    /**
     * Refreshes a resource (EntityManager functionality)
     *
     * @param ResourceInterface $resource
     */
    public function refreshResource(ResourceInterface $resource);

    /**
     * Shorthand for directly getting the em
     *
     * @return EntityManager
     */
    public function getEntityManager();

}