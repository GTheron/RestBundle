<?php

/**
 * This file contains the ResourceManager class
*/

namespace GTheron\RestBundle\Security;

use Doctrine\ORM\EntityManager;
use GTheron\RestBundle\Model\ResourceInterface;
use JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\CssSelector\Parser\Reader;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ResourceManager
 *
 * @package GTheron\RestBundle\Security;
 * @author Gabriel Théron
*/
class ResourceManager extends \GTheron\RestBundle\Service\ResourceManager
{
    private $em;
    private $dispatcher;
    private $formFactory;
    private $reader;
    private $authorizationManager;

    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $dispatcher,
        FormFactoryInterface $formFactory,
        Reader $reader,
        AuthorizationManager $authorizationManager
    )
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->formFactory = $formFactory;
        $this->reader = $reader;
        $this->authorizationManager = $authorizationManager;
    }

    /**
     * @param ResourceInterface $resource
     * @param bool $andFlush
     * @param UserInterface $creator
     * @return ResourceInterface
     */
    public function create(ResourceInterface $resource, $andFlush = true, UserInterface $creator = null)
    {
        $resource = parent::create($resource, $andFlush);

        if(!is_null($creator))
            $this->authorizationManager
                ->grantMask($resource, MaskBuilder::MASK_OWNER, UserSecurityIdentity::fromAccount($creator));

        return $resource;
    }

    /**
     * Deletes an resource permanently
     *
     * @param ResourceInterface $resource
     * @param bool $andFlush
     * @return ResourceInterface
     */
    public function delete(ResourceInterface $resource, $andFlush = true)
    {
        $this->authorizationManager->deleteAcl($resource);

        return parent::delete($resource, $andFlush);
    }

}