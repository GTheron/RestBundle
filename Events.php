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
 * This file contains constants that represent different event suffixes
 * This suffixes will be used to compose an event's label
 *
 * @package GTheron\RestBundle;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class Events
{
    const CREATED = "created";

    const UPDATED = "updated";

    const DELETED = "deleted";

    const SAVED = "saved";

    const DISABLED = "disabled";

    const BEFORE_VALIDATION = "before_validation";

    const VALIDATION_SUCCESS = "validation_success";

    const VALIDATION_FAILED = "validation_failed";
}