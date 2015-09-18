<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Tests\Controller;

use GTheron\RestBundle\Annotation\ResourceControllerAnnotation;
use GTheron\RestBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;

/**
 * TestResourceController
 *
 * @package GTheron\RestBundle\Tests\Controller;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
 *
 * @ResourceControllerAnnotation(
 *  resourceClass="GTheron\RestBundle\Tests\TestResource"
 * )
*/
class TestResourceController extends ResourceController
{
    public function getAction(Request $request, $uid)
    {
        parent::getResourceView($uid);
    }

}