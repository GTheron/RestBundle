<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Service;
use Doctrine\ORM\EntityManager;
use GTheron\RestBundle\Controller\ResourceController;
use GTheron\RestBundle\Model\DisableableResourceInterface;
use \Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\Common\Annotations\Reader;
use GTheron\RestBundle\Model\ResourceInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use GTheron\RestBundle\Event\ResourceEvent;

/**
 * ResourceManager
 *
 * @package GTheron\RestBundle\Service;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class ResourceManager
{
    private $em;
    private $dispatcher;
    private $formFactory;
    private $reader;

    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $dispatcher,
        FormFactoryInterface $formFactory,
        Reader $reader
    )
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->formFactory = $formFactory;
        $this->reader = $reader;
    }

    /**
     * @param ResourceInterface $resource
     * @param bool $andFlush
     * @return ResourceInterface
     * @throws \Exception
     */
    public function create(ResourceInterface $resource, $andFlush = true)
    {
        $resource->updateTimeStamps();
        $this->save($resource, $andFlush);

        $event = new ResourceEvent($resource);
        $this->dispatcher->dispatch($this->getEvent($resource, 'CREATED'), $event);

        return $resource;
    }

    /**
     * @param ResourceInterface $resource
     * @param bool $andFlush
     * @return ResourceInterface
     */
    public function update(ResourceInterface $resource, $andFlush = true)
    {
        $resource->updateTimeStamps();
        $this->em->persist($resource);

        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($resource);

        if($andFlush) $this->em->flush();

        if(array_key_exists('disabled', $changeSet)){
            $disable = $changeSet['disabled'][1];
            if ($disable === true) $this->setDisabled($resource, true, $andFlush);
            elseif ($disable === false) $this->setDisabled($resource, false, $andFlush);
        }

        $event = new ResourceEvent($resource);
        $this->dispatcher->dispatch($this->getEvent($resource, 'UPDATED'), $event);

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
        $resource->setDeleted(true);
        $resource->setDeletedAt(new \DateTime());

        $this->save($resource, $andFlush);

        $event = new ResourceEvent($resource);
        $this->dispatcher->dispatch($this->getEvent($resource, 'DELETED'), $event);

        return $resource;
    }

    /**
     * @param ResourceInterface $resource
     * @param bool $andFlush
     * @return ResourceInterface
     */
    public function save(ResourceInterface $resource, $andFlush = true)
    {
        $this->em->persist($resource);
        if($andFlush) $this->em->flush();

        return $resource;
    }

    /**
     * Validates a Restable from its form data
     *
     * @param ResourceInterface $resource
     * @param array $parameters
     * @param $method
     * @param AbstractType $formType
     * @return mixed
     */
    public function validate(
        ResourceInterface $resource,
        array $parameters,
        $method,
        AbstractType $formType = null
    )
    {
        if($formType == null) {
            $formTypeClass = $this->getFormType($resource);
            $formType = new $formTypeClass();
        }

        $form = $this->formFactory->create($formType, $resource, array('method' => $method));

        //Stripping the parameters from all unexpected fields
        $children = $form->all();
        $parameters = array_intersect_key($parameters, $children);

        //TODO refactor constant where it makes sense
        //If the given method is a patch, we won't need all expected fields to be given
        $form->submit($parameters, ResourceController::HTTP_METHOD_PATCH !== $method);
        if ($form->isValid()) {
            return $form->getData();
        }

        return $form;
    }

    /**
     * Disables/enables an resource
     *
     * @param DisableableResourceInterface $resource
     * @param bool $disabled
     * @param bool $andFlush
     * @return DisableableResourceInterface
     * @throws \Exception
     */
    public function setDisabled(DisableableResourceInterface $resource, $disabled = true, $andFlush = true)
    {
        if($disabled) $resource->setDisabledAt(new \DateTime());

        $resource->setDisabled($disabled);
        $this->em->persist($resource);
        if($andFlush) $this->em->flush();

        $event = new ResourceEvent($resource);
        $stateType = $disabled ? 'DISABLED' : 'ENABLED';
        $this->dispatcher->dispatch($this->getEvent($resource, $stateType), $event);

        return $resource;
    }

    /**
     * Uses the findAll method on an resource's repository
     *
     * @param ResourceInterface $resource
     * @return array
     */
    public function findAll(ResourceInterface $resource)
    {
        return $this->getRepository($resource)->findAll();
    }

    /**
     * Fetches one resource from its repository and its uid
     *
     * @param ResourceInterface $resource
     * @param string $uid
     */
    public function findOne(ResourceInterface $resource, $uid)
    {
        return $this->getRepository($resource)->findOneByUid($uid);
    }

    /**
     * Returns the resource's repository
     *
     * @param ResourceInterface $resource
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository(ResourceInterface $resource)
    {
        //throw new \Exception(get_class($resource));
        $annotation = $this->readResourceAnnotations($resource);
        $shortName = $annotation->getShortName();
        $repositoryClass = $annotation->getRepositoryClass();

        //Getting the Repository from the resource's name
        $fullClass = explode('\\', $repositoryClass);
        //Will be something like CommonUserBundle:Organisation
        return $this->em->getRepository($fullClass[0].$fullClass[1].':'.$shortName);
    }

    /**
     * Returns an resource's associated FormType class
     * For instance, for Common\UserBundle\Entity\Organisation, this will return
     * Common\UserBundle\Form\Type\OrganisationFormType
     *
     * @param ResourceInterface $resource
     * @return string
     */
    public function getFormType(ResourceInterface $resource)
    {
        return $this->readResourceAnnotations($resource)->getFormTypeClass();
    }

    /**
     * Returns the short class name of an resource, which is used as a base for all classes name prediction
     * For instance, for Common\UserBundle\Entity\Organisation, this will return Organisation
     *
     * @param ResourceInterface $resource
     * @return string
     */
    public function getResourceShortName(ResourceInterface $resource)
    {
        return $this->readResourceAnnotations($resource)->getShortName();
    }

    /**
     * Returns the Roles class of the associated bundle
     *
     * @param ResourceInterface $resource
     * @return string
     */
    public function getRoleClass(ResourceInterface $resource)
    {
        return $this->readResourceAnnotations($resource)->getRolesClass();
    }

    /**
     * Fires an event on a resource for an event type
     *
     * @param ResourceInterface $resource
     * @param string $type
     * @throws \Exception
     */
    public function fireEvent(ResourceInterface $resource, $type)
    {
        $event = new ResourceEvent($resource);
        $this->dispatcher->dispatch($this->getEvent($resource, $type), $event);
    }

    /**
     * Gets a role on a resource from its suffix and the resource's class
     *
     * @param ResourceInterface $resource
     * @param string $roleSuffix
     * @return string
     */
    public function getRole(ResourceInterface $resource, $roleSuffix)
    {
        $shortName = $this->readResourceAnnotations($resource)->getShortName();
        return constant($this->getRoleClass($resource).'::'.strtoupper($shortName)."_".$roleSuffix);

    }

    /**
     * Refreshes a resource
     *
     * @param ResourceInterface $resource
     */
    public function refreshResource(ResourceInterface $resource)
    {
        $this->em->refresh($resource);
    }

    /**
     * Shorthand for directly getting the em
     *
     * @return EntityManager
     */
    public function getEntityManager(){
        return $this->em;
    }

    /**
     * Returns an event in the Events class determined from the resource's annotations
     *
     * @param ResourceInterface $resource
     * @param string $type
     * @return string
     * @throws \Exception
     */
    protected function getEvent(ResourceInterface $resource, $type)
    {
        $annotation = $this->readResourceAnnotations($resource);
        $eventsClass = $annotation->getEventsClass();
        $shortName = $annotation->getShortName();

        return constant($eventsClass.'::'.strtoupper($shortName)."_".$type);
    }

    /**
     * Reads the custom annotations on a resource
     *
     * @param ResourceInterface $resource
     * @throws \Exception
     * @return object
     */
    protected function readResourceAnnotations(ResourceInterface $resource)
    {
        $restableAnnotation = $this->reader->getClassAnnotation(
            new \ReflectionClass($resource),
            'Common\\RestBundle\\Annotation\\ResourceAnnotation'
        );
        if(!$restableAnnotation) {
            throw new \Exception(
                sprintf('Resource class %s does not have required annotation ResourceAnnotation', get_class($resource))
            );
        }

        $ormAnnotation = $this->reader->getClassAnnotation(
            new \ReflectionClass($resource),
            'Doctrine\\ORM\\Mapping\\Entity'
        );

        $restableAnnotation->setRepositoryClass($ormAnnotation->repositoryClass);

        return $restableAnnotation;
    }
}