<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Controller;

use GTheron\RestBundle\Annotation\SubResourceControllerAnnotation;
use GTheron\RestBundle\Model\ResourceInterface;
use GTheron\RestBundle\Roles;
use GTheron\RestBundle\Service\SubResourceManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * SubResourceController
 *
 * @package GTheron\RestBundle\Controller;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
abstract class SubResourceController extends ResourceController
{
    /**
     * @returns SubResourceManagerInterface
     */
    abstract protected function getSubResourceManager();

    /**
     * Returns a standard collection view for the sub entities of a selected parent
     *
     * @param string $slug
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedHttpException
     */
    protected function getSubResourceCollectionView($slug)
    {
        $this->checkParent($slug);

        $resourceClass = $this->getResourceClass();
        $resource = new $resourceClass();
        $this->checkAuthorization(Roles::VIEW_ALL, $resource);

        $srm = $this->getSubResourceManager();
        $resourceClass = $this->getResourceClass();
        $resources = $srm->findAllByParent(new $resourceClass(), $this->getParent($slug));

        return $this->handleView($this->view($resources));
    }

    /**
     * Returns a standard get view for a specific sub resource of a specific parent
     *
     * @param string $slug
     * @param string $uid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getSubResourceView($slug, $uid)
    {
        $this->checkParent($slug);
        $resource = $this->getAuthorizedResource(Roles::VIEW, $this->getResourceClass(), $uid);

        return $this->handleView($this->view($resource));
    }

    /**
     * Retuns a standard post view for a new sub resource under a given parent
     *
     * @param Request $request
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function postSubResourceView(Request $request, $slug)
    {
        $parent = $this->checkParent($slug);

        $resourceClass = $this->getResourceClass();
        $resource = new $resourceClass();
        $this->checkAuthorization(Roles::CREATE, $resource);

        //resource should implement SubResourceInterface
        $resource->setParent($parent);

        return $this->processResource($resource, $request, true);
    }

    /**
     * Returns a standard put view on a given sub resource under a given parent
     *
     * @param Request $request
     * @param $slug
     * @param $uid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function putSubResourceView(Request $request, $slug, $uid)
    {
        $this->checkParent($slug);
        return $this->putResourceView($request, $uid);
    }

    protected function deleteSubResourceView($slug, $uid)
    {
        $this->checkParent($slug);
        return $this->deleteResourceView($uid);
    }

    /**
     * Returns the parent resource from its slug (uid)
     *
     * @param $slug
     * @return ResourceInterface
     */
    protected function getParent($slug)
    {
        $parentClass = $this->getParentClass();
        $parent = $this->getResourceManager()->getRepository(new $parentClass())->findOneByUid($slug);
        if(get_class($parent) != $parentClass)
            throw new NotFoundHttpException();
        return $parent;
    }

    /**
     * Verifies that the parent resource is "alive" and that the security context is authorized to
     * operate on/view it
     *
     * @param $slug
     * @return ResourceInterface
     */
    protected function checkParent($slug)
    {
        //TODO add more checks on context authorizations on the parent
        return $this->getParent($slug);
    }

    /**
     * Returns the full parent resource class for a given sub resource controller
     *
     * @return string
     */
    protected function getParentClass()
    {
        return $this->readControllerAnnotations()->getParentClass();
    }

    /**
     * Reads a sub resource controller's annotations
     *
     * @return SubResourceControllerAnnotation
     * @throws \Exception
     */
    protected function readControllerAnnotations()
    {
        return $this->readControllerAnnotationsFromClass(get_class(new SubResourceControllerAnnotation()));
        //In PHP 5.5:
        //return $this->readControllerAnnotationsFromClass(SubResourceControllerAnnotation::class);
    }
}