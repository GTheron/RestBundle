<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Tests;

use GTheron\RestBundle\Annotation\ResourceAnnotation;
use GTheron\RestBundle\Model\Resource;

/**
 * TestResource
 *
 * @package GTheron\RestBundle\Tests;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
 *
 * @ORM\Table(name="g_theron_rest_test_resource")
 * @ORM\Entity(repositoryClass="GTheron\RestBundle\Tests\TestResourceRepository")
 *
 * @ResourceAnnotation(
 *   formTypeClass="GTheron\RestBundle\Tests\TestResourceFormType",
 *   shortName="TestResource",
 *   eventPrefix="g_theron.rest_test",
 *   rolePrefix="GTHERON_REST_TEST"
 * )
*/
class TestResource extends Resource
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}