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

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * ResourceControllerTest
 *
 * @package GTheron\RestBundle\Tests\Controller;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class ResourceControllerTest extends \PHPUnit_Framework_TestCase
{
    private $controller;

    /**
    * Test suite set up
    */
    public function setUp()
    {
    }

    /**
     * @expectedException NotFoundHttpException
     */
    public function test_get_fail()
    {
    }
}