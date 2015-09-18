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

use FOS\RestBundle\Controller\FOSRestController;
use GTheron\RestBundle\Annotation\ResourceControllerAnnotation;
use GTheron\RestBundle\GTheronRestRoles;
use GTheron\RestBundle\Model\LinkableResourceInterface;
use GTheron\RestBundle\Model\ResourceInterface;
use GTheron\RestBundle\Service\ResourceManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * ResourceController
 *
 * @package GTheron\RestBundle\Controller;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
class ResourceController extends FOSRestController
{
    //TODO refactor those constants somewhere better
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_PATCH = 'PATCH';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_LINK = 'LINK';
    const HTTP_METHOD_UNLINK = 'UNLINK';

    /**
     * Returns a standard collection view for a resourceClass
     *
     * @throws AccessDeniedHttpException
     * @return Response
     */
    protected function getResourceCollectionView()
    {
        $rm = $this->getResourceManager();
        $ac = $this->get('security.authorization_checker');

        $resourceClass = $this->getResourceClass();
        if(!$ac->isGranted($rm->getRole(new $resourceClass(), GTheronRestRoles::VIEW_ALL)))
        {
            throw new AccessDeniedHttpException();
        }

        $resources = $rm->findAll(new $resourceClass());

        return $this->handleView($this->view($resources));
    }

    /**
     * Returns a standard view for a resource
     *
     * @param string $uid
     * @return Response
     */
    protected function getResourceView($uid)
    {
        $resourceClass = $this->getResourceClass();
        $resource = $this->getAuthorizedResource('VIEW', $resourceClass, $uid);

        return $this->handleView($this->view($resource));
    }

    /**
     * Returns a standard view after treating a POST request on a resource
     *
     * @param Request $request
     * @return Response
     */
    protected function postResourceView(Request $request)
    {
        $resourceClass = $this->getResourceClass();
        $resource = new $resourceClass();
        $this->checkAuthorization('CREATE', $resource);
        return $this->processResource($resource, $request, true);
    }

    /**
     * Returns a standard view after treating a PUT request on a resource
     *
     * @param Request $request
     * @param $uid
     * @return Response
     */
    protected function putResourceView(Request $request, $uid)
    {
        $resourceClass = $this->getResourceClass();
        $resource = $this->getAuthorizedResource('EDIT', $resourceClass, $uid);
        return $this->processResource($resource, $request, false);
    }

    /**
     * Returns a standard view after treating a DELETE request on a resource
     *
     * @param $uid
     * @return Response
     */
    protected function deleteResourceView($uid)
    {
        $resourceClass = $this->getResourceClass();
        $resource = $this->getAuthorizedResource('DELETE', $resourceClass, $uid);

        $rm = $this->getResourceManager();
        $rm->delete($resource);

        return $this->handleView($this->view(null));
    }

    /**
     * @param string $resourceClass
     * @param string $uid
     * @throws NotFoundHttpException
     * @return ResourceInterface
     */
    protected function findResource($resourceClass, $uid)
    {
        $rm = $this->getResourceManager();
        $resource = $rm->findOne(new $resourceClass(), $uid);

        if(get_class($resource) != $resourceClass) throw new NotFoundHttpException();
        return $resource;
    }

    /**
     * Verifies that the current security context has the right to access a resource
     *
     * @param string $accessRight Represents the required access right (EDIT, DELETE, OWNER, ...)
     * @param ResourceInterface $resource
     * @throws AccessDeniedHttpException
     * @return bool
     */
    protected function checkAuthorization($accessRight, ResourceInterface $resource)
    {
        $ac = $this->get('security.authorization_checker');

        //Managing CREATE case
        if($accessRight == 'CREATE')
        {
            $createRole = $this->getResourceManager()->getRole($resource, GTheronRestRoles::CREATE);
            if(!$ac->isGranted($createRole)) throw new AccessDeniedException();
        }
        else{
            if(!$ac->isGranted($accessRight, $resource))
            {
                $bypass = false;
                //Managing VIEW_ALL case
                if($accessRight == 'VIEW')
                {
                    $viewAllRole = $this->getResourceManager()->getRole($resource, GTheronRestRoles::VIEW_ALL);
                    if($ac->isGranted($viewAllRole)) $bypass = true;
                }
                elseif($accessRight == 'EDIT')
                {
                    $viewAllRole = $this->getResourceManager()->getRole($resource, GTheronRestRoles::EDIT_ALL);
                    if($ac->isGranted($viewAllRole)) $bypass = true;
                } elseif ($accessRight == 'DELETE') {
                    $viewAllRole = $this->getResourceManager()->getRole($resource, GTheronRestRoles::DELETE_ALL);
                    if ($ac->isGranted($viewAllRole)) {
                        $bypass = true;
                    }
                }
                if(!$bypass) throw new AccessDeniedHttpException();
            }
        }
        return true;
    }

    /**
     * Verifies that the requested object exists and that the current security context has the requested
     * access right on it
     *
     * @param string $accessRight
     * @param string $resourceClass
     * @param string $uid
     * @return ResourceInterface
     */
    protected function getAuthorizedResource($accessRight, $resourceClass, $uid)
    {
        $resource = $this->findResource($resourceClass, $uid);
        $this->checkAuthorization($accessRight, $resource);
        return $resource;
    }

    /**
     * Parses a header string and returns the associated resource uid
     * For instance, with the following parameters:
     * - 'users'
     * - new User()
     * - '<http://localhost:80/org/orgdfgldk6546d5g4/users/usrfdg654gdf>; rel="link"'
     * the function will return "usrfdg654gdf"
     *
     * @param string $resourcePath
     * @param string $header
     * @return string
     */
    protected function getUidFromHeader($resourcePath, $header)
    {
        //+1 accounting for the trailing slash
        $uidPos = strpos($header, $resourcePath) + strlen($resourcePath) + 1;
        $uid = substr($header, $uidPos, strpos($header, ">") - $uidPos);
        return $uid;
    }

    /**
     * Returns an authorized resource from a Link header
     *
     * @param $resourcePath
     * @param $header
     * @param $accessRight
     * @param $resourceClass
     * @return ResourceInterface
     */
    protected function getAuthorizedResourceFromHeader($resourcePath, $header, $accessRight, $resourceClass)
    {
        $uid = $this->getUidFromHeader($resourcePath, $header);
        return $this->getAuthorizedResource($accessRight, $resourceClass, $uid);
    }

    /**
     * Validates a resource, throwing an exception if the validation fails
     *
     * @param ResourceInterface $resource
     * @param array $parameters
     * @param $method
     * @param AbstractType $formType
     * @throws HttpException
     * @return ResourceInterface
     */
    protected function validateResource(
        ResourceInterface $resource,
        array $parameters,
        $method,
        AbstractType $formType = null)
    {
        $rm = $this->getResourceManager();
        $validation = $rm->validate($resource, $parameters, $method, $formType);

        $resourceClass = get_class($resource);
        if(!($validation instanceof $resourceClass)){
            $validationObject = array();
            if(is_string($validation))
                $validationObject['error'] = $validation;
            else if($validation instanceof FormInterface){
                $validationObject = $validation->getErrors(true, false);
            }
            else
                $validationObject = $validation;

            $serializer = $this->get('serializer');
            $message = $serializer->serialize($validationObject, 'json');

            throw new HttpException(400, $message);
        }

        return $validation;
    }

    /**
     * Process an operation on a single resource (POST or PATCH)
     *
     * @param ResourceInterface $resource
     * @param Request $request
     * @param bool $isNew
     * @param AbstractType|null $formType
     * @return Response
     */
    protected function processResource(
        ResourceInterface $resource,
        Request $request,
        $isNew,
        AbstractType $formType = null
    )
    {
        //Initializing
        $rm = $this->getResourceManager();
        $parameters = $request->request->all();

        //validation
        $method = $isNew ? ResourceController::HTTP_METHOD_POST : ResourceController::HTTP_METHOD_PATCH;

        $this->beforeValidation($resource, $request, $isNew);
        $resource = $this->validateResource($resource, $parameters, $method, $formType);
        $this->afterValidation($resource, $request, $isNew);

        //Saving the resource
        if($isNew) $rm->create($resource);
        else $rm->update($resource);

        //Making the response
        $statusCode = $isNew ? 201 : 204;
        $response = $isNew ? $resource->getUid() : $resource;

        return $this->handleView($this->view($response, $statusCode));
    }

    /**
     * Sets a link between two resources, one being the $owner and the other a $foreign.
     * This either adds or remove the $foreign from the $owner's corresponding collection,
     * throwing a HttpException accordingly
     *
     * @param LinkableResourceInterface $owner
     * @param ResourceInterface $foreign
     * @param string $method
     * @param null $beforeLink
     */
    protected function linkResources(
        LinkableResourceInterface $owner,
        ResourceInterface $foreign,
        $method,
        $beforeLink = null
    )
    {
        $rm = $this->getResourceManager();

        //Checking whether resources are already linked
        $alreadyLinked = $owner->checkLink($foreign);
        $toBeLinked = $method == ResourceController::HTTP_METHOD_LINK;
        if($toBeLinked == $alreadyLinked){
            $message = $alreadyLinked ? 'Resources already linked' : 'Resources are not linked';
            throw new HttpException(409, $message);
        }
        if(is_callable($beforeLink)) $beforeLink($owner, $foreign, $toBeLinked, $this);

        $owner->setLink($foreign);
        $rm->update($owner);
    }

    /**
     * Returns the full resource class for a given controller
     *
     * @return string
     */
    protected function getResourceClass()
    {
        return $this->readControllerAnnotations()->getResourceClass();
    }

    /**
     * Returns a specific resource role from its class and a ResourceManager::Role
     *
     * @param $role
     * @return string
     */
    protected function getResourceRole($role)
    {
        $rm = $this->getResourceManager();
        $resourceClass = $this->getResourceClass();
        return $rm->getRole(new $resourceClass(), $role);
    }

    /**
     * Reads a controller's annotations
     *
     * @return ResourceControllerAnnotation
     * @throws \Exception
     */
    protected function readControllerAnnotations()
    {
        return $this->readControllerAnnotationsFromClass(get_class(new ResourceControllerAnnotation()));
        //In PHP 5.5:
        //return $this->readControllerAnnotationsFromClass(ResourceControllerAnnotation::class);
    }

    /**
     * Reads a controller's annotations from a fully qualified annotation class
     *
     * @param $annotationClass
     * @return mixed
     * @throws \Exception
     */
    protected function readControllerAnnotationsFromClass($annotationClass)
    {
        $controllerClass = get_class($this);
        $controllerAnnotation = $this->get("annotation_reader")->getClassAnnotation(
            new \ReflectionClass(new $controllerClass),
            $annotationClass
        );
        if(!$controllerAnnotation) {
            throw new \Exception(
                sprintf(
                    'Controller class %s does not have required annotation '.$annotationClass,
                    $controllerClass
                )
            );
        }

        return $controllerAnnotation;
    }

    /**
     * Override to execute operations before resource validation
     *
     * @param ResourceInterface $resource
     * @param Request $request
     * @param bool $isNew
     */
    protected function beforeValidation(ResourceInterface $resource, Request $request, $isNew = false)
    {
        //TODO refactor this callback as an event
    }

    /**
     * Override to execute operations after resource validation
     *
     * @param ResourceInterface $resource
     * @param Request $request
     * @param bool $isNew
     */
    protected function afterValidation(ResourceInterface $resource, Request $request, $isNew = false)
    {
        //TODO refactor this callback as an event
    }

    /**
     * @return ResourceManager
     */
    protected function getResourceManager()
    {
        return $this->get('cm_rest.resource_manager');
    }
}