<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle;

/**
 * Contains basic role names
 *
 * @package GTheron\RestBundle;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class Roles
{
    const VIEW = 'VIEW';

    const EDIT = 'EDIT';

    const DELETE = 'DELETE';

    /**
     * Represents the suffix of the role of users who can view all resources of a certain type
     * An actual resource's VIEW_ALL role would look like this: ROLE_RESOURCE_VIEW_ALL
     */
    const VIEW_ALL = 'VIEW_ALL';
    /**
     * Represents the suffix of the role of users who can view all resources of a certain type
     * An actual resource's EDIT_ALL role would look like this: ROLE_RESOURCE_EDIT_ALL
     */
    const EDIT_ALL = 'EDIT_ALL';
    /**
     * Represents the suffix of the role of users who can view all resources of a certain type
     * An actual resource's DELETE_ALL role would look like this: ROLE_RESOURCE_DELETE_ALL
     */
    const DELETE_ALL = 'DELETE_ALL';
    /**
     * Represents the suffix of the role of users who can create a resource of a certain type
     * An actual resource's CREATE role would look like this: ROLE_RESOURCE_CREATE
     */
    const CREATE = 'CREATE';

}