<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Annotation;

/**
 * ResourceAnnotation
 *
 * @package GTheron\RestBundle\Annotation;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
 *
 * @Annotation
 * @Target("CLASS")
*/
class ResourceAnnotation
{
    public $formTypeClass;
    //TODO remove shortName
    public $shortName;
    public $repositoryClass;
    public $eventPrefix;
    public $rolePrefix;

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
    }

    /**
     * @param string $formTypeClass
     */
    public function setFormTypeClass($formTypeClass)
    {
        $this->formTypeClass = $formTypeClass;
    }

    /**
     * Used for prefixing Roles and Events
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }

    /**
     * @return string
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * @param string $repositoryClass
     */
    public function setRepositoryClass($repositoryClass)
    {
        $this->repositoryClass = $repositoryClass;
    }

    /**
     * @return string
     */
    public function getEventPrefix()
    {
        return $this->eventPrefix;
    }

    /**
     * @param string $evenPrefix
     */
    public function setEventPrefix($evenPrefix)
    {
        $this->eventPrefix = $evenPrefix;
    }

    /**
     * @return string
     */
    public function getRolePrefix()
    {
        return $this->rolePrefix;
    }

    /**
     * @param string $rolePrefix
     */
    public function setRolePrefix($rolePrefix)
    {
        $this->rolePrefix = $rolePrefix;
    }
}