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
 * AuthorizationManagerTest
 *
 * @package GTheron\RestBundle\Tests\Service;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class AuthorizationManagerTest extends \PHPUnit_Framework_TestCase
{
    private $em,
        $aclProvider,
        $authorizationManager,
        $object,
        $securityIdentity,
        $acl,
        $ace,
        $user,
        $securityContext;

    /**
     * Test suite set up
     */
    public function setUp()
    {
        $this->em = \Mockery::mock('Doctrine\ORM\EntityManager');
        $this->aclProvider = \Mockery::mock('Symfony\Component\Security\Acl\Dbal\AclProvider');
        $this->authorizationManager = new AuthorizationManager($this->em, $this->aclProvider);

        $this->object = \Mockery::mock('Common\UserBundle\Entity\User');
        $this->securityIdentity = \Mockery::mock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $this->acl = \Mockery::mock('Symfony\Component\Security\Acl\Domain\Acl');
        $this->ace = \Mockery::mock('Symfony\Component\Security\Acl\Domain\Entry');

        $this->user = \Mockery::mock('Common\UserBundle\Entity\User');
        $this->securityContext = \Mockery::mock('Symfony\Component\Security\Core\SecurityContextInterface');
    }

    /**
     * Verifies that the addMask method is called in grantMask
     */
    public function test_grant_mask_calls_add_mask()
    {
        $this->object->shouldReceive('getId')->once()->andReturn('wut');
        $this->acl->shouldReceive('insertObjectAce')->once()->andReturn('');
        $this->aclProvider->shouldReceive('createAcl')->once()->andReturn($this->acl);
        $this->aclProvider->shouldReceive('updateAcl')->once()->andReturn($this->acl);

        $this->authorizationManager->grantMask($this->object, 'MASK', $this->securityIdentity);
    }

    /**
     * Verifies that the removeMask method is called in revokeMask
     */
    public function test_revoke_mask_calls_remove_mask_success()
    {
        $this->object->shouldReceive('getId')->twice()->andReturn('wut');

        $this->acl->shouldReceive('insertObjectAce')->once()->andReturn($this->ace);
        $this->acl->shouldReceive('updateObjectAce')->once()->andReturn($this->ace);
        $this->acl->shouldReceive('getObjectAces')->once()->andReturn(array(0 => $this->ace));

        $this->ace->shouldReceive('getSecurityIdentity')->once()->andReturn($this->securityIdentity);
        $this->ace->shouldReceive('getMask')->once()->andReturn('MASK');

        $this->securityIdentity->shouldReceive('equals')->once()->andReturn(true);

        $this->aclProvider->shouldReceive('createAcl')->twice()->andReturn($this->acl);
        $this->aclProvider->shouldReceive('updateAcl')->twice()->andReturn($this->acl);

        $this->authorizationManager->grantMask($this->object, 'MASK', $this->securityIdentity);
        $this->authorizationManager->revokeMask($this->object, 'MASK', $this->securityIdentity);
    }

    /**
     * Tests that with incorrect data, removeMask is not called by revokeMask
     */
    public function test_revoke_mask_calls_remove_mask_failure()
    {
        $this->object->shouldReceive('getId')->twice()->andReturn('wut');

        $this->acl->shouldReceive('insertObjectAce')->once()->andReturn($this->ace);
        $this->acl->shouldReceive('updateObjectAce')->never();
        $this->acl->shouldReceive('getObjectAces')->once()->andReturn(array());

        $this->ace->shouldReceive('getSecurityIdentity')->never();
        $this->ace->shouldReceive('getMask')->never();

        $this->securityIdentity->shouldReceive('equals')->never();

        $this->aclProvider->shouldReceive('createAcl')->twice()->andReturn($this->acl);
        $this->aclProvider->shouldReceive('updateAcl')->twice()->andReturn($this->acl);

        $this->authorizationManager->grantMask($this->object, 'MASK', $this->securityIdentity);
        $this->authorizationManager->revokeMask($this->object, 'MASK', $this->securityIdentity);
    }

    /**
     * Verifies that grantRole calls addRole on user
     */
    public function test_grant_role()
    {
        $this->user->shouldReceive('addRole')->once()->with('ROLE');

        $this->authorizationManager->grantRole('ROLE', $this->user);
    }

    /**
     * Verifies that revokeRole calls removeRole on user
     */
    public function test_revoke_role()
    {
        $this->user->shouldReceive('removeRole')->once()->with('ROLE');

        $this->authorizationManager->revokeRole('ROLE', $this->user);
    }

    /**
     * Verifies that the getAuthorizations method contains 8 items and calls isGranted 8 times
     */
    public function test_get_authorizations()
    {
        $this->securityContext->shouldReceive('isGranted')->times(8)->andReturn(true);

        $authorizations = $this->authorizationManager->getAuthorizations($this->securityContext, $this->object);
        $this->assertEquals(8, count($authorizations));
    }

    /**
     * Verifies that the deleteAcl method is called on aclProvider by deleteAcl
     */
    public function test_delete_acl()
    {
        $this->object->shouldReceive('getId')->twice()->andReturn('wut');

        $this->aclProvider->shouldReceive('createAcl')->once()->andReturn($this->acl);
        $this->aclProvider->shouldReceive('deleteAcl')->once()->andReturn('');

        $this->authorizationManager->deleteAcl($this->object);
    }
}