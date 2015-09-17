<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Tests\Service;

/**
 * ResourceManagerTest
 *
 * @package GTheron\RestBundle\Tests\Service;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class ResourceManagerTest extends \PHPUnit_Framework_TestCase
{
    private $em,
        $am,
        $dispatcher,
        $formFactory,
        $reader,
        $resourceManager,
        $resource,
        $resourceAnnotation,
        $disableable,
        $user,
        $uow,
        $repository;

    /**
     * Test suite set up
     */
    public function setUp()
    {
        $this->em = \Mockery::mock('Doctrine\ORM\EntityManager');
        $this->am = \Mockery::mock('Common\RestBundle\Service\AuthorizationManager');
        $this->dispatcher = \Mockery::mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $this->formFactory = \Mockery::mock('Symfony\Component\Form\FormFactory');
        $this->reader = \Mockery::mock('Doctrine\Common\Annotations\AnnotationReader');

        $this->resourceManager = new ResourceManager(
            $this->em,
            $this->am,
            $this->dispatcher,
            $this->formFactory,
            $this->reader
        );

        $this->resource = \Mockery::mock('Common\RestBundle\Model\ResourceInterface');
        $this->resourceAnnotation = \Mockery::mock('Common\RestBundle\Annotation\ResourceAnnotation');
        $this->disableable = \Mockery::mock('Common\RestBundle\Model\DisableableResourceInterface');
        $this->user = \Mockery::mock('Symfony\Component\Security\Core\User\UserInterface');
        $this->uow = \Mockery::mock('Doctrine\ORM\UnitOfWork');
        $this->repository = \Mockery::mock('Doctrine\ORM\Repository');

        //Boilerplate code for getEvent
        $this->reader->shouldReceive('getClassAnnotation')->andReturn($this->resourceAnnotation);
        $this->resourceAnnotation->shouldReceive('setRepositoryClass');
        $this->resourceAnnotation->shouldReceive('getRepositoryClass')
            ->andReturn('Common\RestBundle\Repository\ResourceInterfaceRepository');
        $this->resourceAnnotation->shouldReceive('getEventsClass')
            ->andReturn('Common\RestBundle\Tests\Service\ResourceTestEventsClass');
        $this->resourceAnnotation->shouldReceive('getShortName')->andReturn('ResourceInterface');

        //Other boilerplate code
        $this->user->shouldReceive('getUsername')->andReturn('mockusername');
        $this->em->shouldReceive('getUnitOfWork')->andReturn($this->uow);
        $this->uow->shouldReceive('computeChangeSets');
    }

    /**
     * Verifies that when the create method is called with andFlush to true,
     * it calls the right methods on the resource, the em
     */
    public function test_create_and_flush_success()
    {
        $this->resource->shouldReceive('updateTimestamps')->once();
        $this->em->shouldReceive('persist')->once()
            ->with($this->resource);
        $this->em->shouldReceive('flush')->once();

        $this->am->shouldReceive('grantMask')->never();

        $this->dispatcher->shouldReceive('dispatch')->once()->
        with(ResourceTestEventsClass::RESOURCEINTERFACE_CREATED,
            \Mockery::type('Common\RestBundle\Event\ResourceEvent')
        );

        $result = $this->resourceManager->create($this->resource);

        $this->assertEquals($this->resource, $result);
    }

    /**
     * Tests that the flush method is not called when andFlush is passed at false
     */
    public function test_create_not_and_flush_success()
    {
        $this->resource->shouldReceive('updateTimestamps')->once();
        $this->em->shouldReceive('persist')->once()
            ->with($this->resource);
        $this->em->shouldReceive('flush')->never();

        $this->am->shouldReceive('grantMask')->never();

        $this->dispatcher->shouldReceive('dispatch')->once()->
        with(ResourceTestEventsClass::RESOURCEINTERFACE_CREATED,
            \Mockery::type('Common\RestBundle\Event\ResourceEvent')
        );

        $result = $this->resourceManager->create($this->resource, null, false);

        $this->assertEquals($this->resource, $result);
    }

    /**
     * Tests that the grantMask method is called when a user is passed
     */
    public function test_create_creator_not_null_success()
    {
        $this->resource->shouldReceive('updateTimestamps')->once();
        $this->em->shouldReceive('persist')->once()
            ->with($this->resource);
        $this->em->shouldReceive('flush')->once();

        $this->am->shouldReceive('grantMask')->once()
            ->with($this->resource, MaskBuilder::MASK_OWNER,
                \Mockery::type('Symfony\Component\Security\Acl\Domain\UserSecurityIdentity')
            );

        $this->dispatcher->shouldReceive('dispatch')->once()->
        with(ResourceTestEventsClass::RESOURCEINTERFACE_CREATED,
            \Mockery::type('Common\RestBundle\Event\ResourceEvent')
        );

        $result = $this->resourceManager->create($this->resource, $this->user);

        $this->assertEquals($this->resource, $result);
    }

    /**
     * Tests that flush will be called on
     */
    public function test_update_and_flush_success()
    {
        $this->resource->shouldReceive('updateTimestamps')->once();
        $this->em->shouldReceive('persist')->once()
            ->with($this->resource);
        $this->em->shouldReceive('flush')->once();

        $this->uow->shouldReceive('getEntityChangeSet')->once()
            ->andReturn(array());

        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with(ResourceTestEventsClass::RESOURCEINTERFACE_UPDATED,
                \Mockery::type('Common\RestBundle\Event\ResourceEvent')
            );

        $result = $this->resourceManager->update($this->resource);

        $this->assertEquals($this->resource, $result);
    }

    /**
     * Tests that flush will be called on
     */
    public function test_update_not_and_flush_success()
    {
        $this->resource->shouldReceive('updateTimestamps')->once();
        $this->em->shouldReceive('persist')->once()
            ->with($this->resource);
        $this->em->shouldReceive('flush')->never();

        $this->uow->shouldReceive('getEntityChangeSet')->once()
            ->andReturn(array());

        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with(ResourceTestEventsClass::RESOURCEINTERFACE_UPDATED,
                \Mockery::type('Common\RestBundle\Event\ResourceEvent')
            );

        $result = $this->resourceManager->update($this->resource, false);

        $this->assertEquals($this->resource, $result);
    }

    /**
     * Tests that flush will be called on
     */
    public function test_update_enabled_to_disabled_success()
    {
        $this->disableable->shouldReceive('updateTimestamps')->once();
        $this->disableable->shouldReceive('setDisabled')->once()
            ->with(true);
        $this->disableable->shouldReceive('setDisabledAt')->once()
            ->with(\Mockery::type('\DateTime'));
        $this->em->shouldReceive('persist')->twice()
            ->with($this->disableable);
        $this->em->shouldReceive('flush')->twice();

        $this->uow->shouldReceive('getEntityChangeSet')->once()
            ->andReturn(array('disabled' => array('disabled', true)));

        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with(ResourceTestEventsClass::RESOURCEINTERFACE_UPDATED,
                \Mockery::type('Common\RestBundle\Event\ResourceEvent')
            );
        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with(ResourceTestEventsClass::RESOURCEINTERFACE_DISABLED,
                \Mockery::type('Common\RestBundle\Event\ResourceEvent')
            );

        $result = $this->resourceManager->update($this->disableable);

        $this->assertEquals($this->disableable, $result);
    }

    /**
     * Tests that flush will be called on
     */
    public function test_update_disabled_to_enabled_success()
    {
        $this->disableable->shouldReceive('updateTimestamps')->once();
        $this->disableable->shouldReceive('setDisabled')->once()
            ->with(false);
        $this->disableable->shouldReceive('setDisabledAt')->never();
        $this->em->shouldReceive('persist')->twice()
            ->with($this->disableable);
        $this->em->shouldReceive('flush')->twice();

        $this->uow->shouldReceive('getEntityChangeSet')->once()
            ->andReturn(array('disabled' => array('disabled', false)));

        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with(ResourceTestEventsClass::RESOURCEINTERFACE_UPDATED,
                \Mockery::type('Common\RestBundle\Event\ResourceEvent')
            );
        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with(ResourceTestEventsClass::RESOURCEINTERFACE_ENABLED,
                \Mockery::type('Common\RestBundle\Event\ResourceEvent')
            );

        $result = $this->resourceManager->update($this->disableable);

        $this->assertEquals($this->disableable, $result);
    }

    /**
     * Verifies that the delete method calls all methods on resource and em, and fires a DELETED event
     */
    public function test_delete_success()
    {
        $this->resource->shouldReceive('setDeletedAt')->once()
            ->with(\Mockery::type('\DateTime'));
        $this->resource->shouldReceive('setDeleted')->once()
            ->with(true);
        $this->em->shouldReceive('persist')->once()
            ->with($this->resource);
        $this->em->shouldReceive('flush')->once();

        $this->am->shouldReceive('deleteAcl')->once()
            ->with($this->resource);
        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with(ResourceTestEventsClass::RESOURCEINTERFACE_DELETED,
                \Mockery::type('Common\RestBundle\Event\ResourceEvent')
            );

        $result = $this->resourceManager->delete($this->resource);

        $this->assertEquals($this->resource, $result);
    }

    /**
     * Verifies that a valid entity goes through validation, with a passed formType
     */
    public function test_validate_post_with_form_type_success()
    {
        //TODO test the validate method
        //$parameters = array();

        //$this->resourceManager->validate($this->resource, $parameters, 'POST', $this->formType);
    }

    /**
     * Verifies that the findAll method calls the right repository
     */
    public function test_find_all_success()
    {
        $this->em->shouldReceive('getRepository')->once()
            ->with('CommonRestBundle:ResourceInterface')
            ->andReturn($this->repository);
        $this->repository->shouldReceive('findAll')->once()
            ->andReturn('result');

        $result = $this->resourceManager->findAll($this->resource);
        $this->assertEquals('result', $result);
    }

    /**
     * Verifies that the findOne method calls the right repository with the right argument
     */
    public function test_find_one_success()
    {
        $this->em->shouldReceive('getRepository')->once()
            ->with('CommonRestBundle:ResourceInterface')
            ->andReturn($this->repository);
        $this->repository->shouldReceive('findOneByUid')->once()
            ->with('uid')
            ->andReturn('result');

        $result = $this->resourceManager->findOne($this->resource, 'uid');
        $this->assertEquals('result', $result);
    }

}