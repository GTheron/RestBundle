<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Security;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Acl\Dbal\AclProvider;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\Entry;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * AuthorizationManager
 *
 * @package GTheron\RestBundle\Service;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class AuthorizationManager
{
    private $em,
        $aclProvider,
        $decisionManager;

    public function __construct(
        EntityManager $em,
        AclProvider $aclProvider,
        AccessDecisionManagerInterface $decisionManager
    )
    {
        $this->em = $em;
        $this->aclProvider = $aclProvider;
        $this->decisionManager = $decisionManager;
    }

    /**
     * @param $entity
     * @param $mask
     * @param SecurityIdentityInterface $securityIdentity
     * @return $entity
     */
    public function grantMask($entity, $mask, SecurityIdentityInterface $securityIdentity)
    {
        $acl = $this->getAcl($entity);
        $this->addMask($securityIdentity, $mask, $acl);

        return $entity;
    }

    /**
     * @param $entity
     * @param $mask
     * @param SecurityIdentityInterface $securityIdentity
     * @return $this
     */
    public function revokeMask($entity, $mask, SecurityIdentityInterface $securityIdentity)
    {
        $acl = $this->getAcl($entity);
        $aces = $acl->getObjectAces();

        foreach($aces as $index => $ace)
        {
            if($securityIdentity->equals($ace->getSecurityIdentity()))
            {
                $this->removeMask($index, $acl, $ace, $mask);
            }
        }

        $this->aclProvider->updateAcl($acl);

        return $this;
    }

    /**
     * @param $role
     * @param $subject
     */
    public function grantRole($role, $subject)
    {
        $subject->addRole($role);
        return $subject;
    }

    /**
     * @param $role
     * @param $subject
     * @return mixed
     */
    public function revokeRole($role, $subject)
    {
        $subject->removeRole($role);
        return $subject;
    }

    /**
     * /!\ Opinionated /!\
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param $object
     * @return array
     */
    public function getAuthorizations(AuthorizationCheckerInterface $authorizationChecker, $object)
    {
        $authorizations = array();
        if($authorizationChecker->isGranted('OWNER', $object)) $authorizations[] = 'owner';
        if($authorizationChecker->isGranted('MASTER', $object)) $authorizations[] = 'master';
        if($authorizationChecker->isGranted('OPERATOR', $object)) $authorizations[] = 'operator';
        if($authorizationChecker->isGranted('VIEW', $object)) $authorizations[] = 'view';
        if($authorizationChecker->isGranted('EDIT', $object)) $authorizations[] = 'edit';
        if($authorizationChecker->isGranted('DELETE', $object)) $authorizations[] = 'delete';
        if($authorizationChecker->isGranted('UNDELETE', $object)) $authorizations[] = 'undelete';
        if($authorizationChecker->isGranted('CREATE', $object)) $authorizations[] = 'create';

        return $authorizations;
    }

    /**
     * Deletes an object's acl (! Should only be done on object deletion!)
     *
     * @param $object
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function deleteAcl($object)
    {
        $acl = $this->getAcl($object); //Simple fix to avoid deleting a non-existing ACL
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $this->aclProvider->deleteAcl($objectIdentity);
    }

    /**
     * Returns an object's Access Control List
     *
     * @param $entity
     * @return \Symfony\Component\Security\Acl\Model\AclInterface
     */
    protected function getAcl($entity){
        $objectIdentity = ObjectIdentity::fromDomainObject($entity);
        try {
            $acl = $this->aclProvider->createAcl($objectIdentity);
        }catch(\Exception $e) {
            $acl = $this->aclProvider->findAcl($objectIdentity);
        }

        return $acl;
    }

    /**
     * @param SecurityIdentityInterface $securityIdentity
     * @param $mask
     * @param AclInterface $acl
     * @return $this
     */
    protected function addMask(SecurityIdentityInterface $securityIdentity, $mask, AclInterface $acl) {
        $acl->insertObjectAce($securityIdentity, $mask);
        $this->aclProvider->updateAcl($acl);

        return $this;
    }

    /**
     * @param $index
     * @param Acl $acl
     * @param Entry $ace
     * @param $mask
     * @return $this
     */
    protected function removeMask($index, Acl $acl, Entry $ace, $mask)
    {
        $acl->updateObjectAce($index, $ace->getMask()&~$mask);

        return $this;
    }

    /**
     * Checks wether a user has access to a given role
     *
     * @param array $roles
     * @param UserInterface $user
     * @return bool
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function isGrantedRoles(array $roles, UserInterface $user)
    {
        $token = new UsernamePasswordToken($user, 'none', 'none', $user->getRoles());
        return $this->decisionManager->decide($token, $roles);
    }
}