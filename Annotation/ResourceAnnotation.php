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
    public $formTypeClass,
        $rolesClass,
        $eventsClass,
        $shortName,
        $repositoryClass;

    /**
     * @return mixed
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
    }

    /**
     * @param mixed $formTypeClass
     */
    public function setFormTypeClass($formTypeClass)
    {
        $this->formTypeClass = $formTypeClass;
    }

    /**
     * @return mixed
     */
    public function getRolesClass()
    {
        return $this->rolesClass;
    }

    /**
     * @param mixed $rolesClass
     */
    public function setRolesClass($rolesClass)
    {
        $this->rolesClass = $rolesClass;
    }

    /**
     * @return mixed
     */
    public function getEventsClass()
    {
        return $this->eventsClass;
    }

    /**
     * @param mixed $eventsClass
     */
    public function setEventsClass($eventsClass)
    {
        $this->eventsClass = $eventsClass;
    }

    /**
     * Used for prefixing Roles and Events
     *
     * @return mixed
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param mixed $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }

    /**
     * @return mixed
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * @param mixed $repositoryClass
     */
    public function setRepositoryClass($repositoryClass)
    {
        $this->repositoryClass = $repositoryClass;
    }
}