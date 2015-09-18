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
use GTheron\RestBundle\Model\LinkableResourceInterface;
use GTheron\RestBundle\Model\ResourceInterface;
use GTheron\RestBundle\Roles;
use GTheron\RestBundle\Security\SecurityResourceManagerInterface;
use GTheron\RestBundle\Service\ResourceManager;
use GTheron\RestBundle\Service\ResourceManagerInterface;
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
abstract class ResourceController extends FOSRestController
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
     * Returns a standard collection view for the controller's Resource
     *
     * @throws AccessDeniedHttpException
     * @return Response
     */
    protected function cgetResourceResponse()
    {
        $rm = $this->getResourceManager();

        $resourceClass = $this->getResourceClass();
        $resource = new $resourceClass();
        $this->checkAuthorization(Roles::VIEW_ALL, $resource);

        $resources = $rm->findAll($resource);

        return $this->handleView($this->view($resources));
    }

    /**
     * Returns a standard view for a resource
     *
     * @param string $uid
     * @return Response
     */
    protected function getResourceResponse($uid)
    {
        $resourceClass = $this->getResourceClass();
        $resource = $this->getAuthorizedResource(Roles::VIEW, $resourceClass, $uid);

        return $this->handleView($this->view($resource));
    }

    /**
     * Returns a standard view after treating a POST request on a resource
     *
     * @param Request $request
     * @return Response
     */
    protected function postResourceResponse(Request $request)
    {
        $resourceClass = $this->getResourceClass();
        $resource = new $resourceClass();
        $this->checkAuthorization(Roles::CREATE, $resource);
        return $this->processResource($resource, $request, true);
    }

    /**
     * Returns a standard view after treating a PUT request on a resource
     *
     * @param Request $request
     * @param $uid
     * @return Response
     */
    protected function putResourceResponse(Request $request, $uid)
    {
        $resourceClass = $this->getResourceClass();
        $resource = $this->getAuthorizedResource(Roles::EDIT, $resourceClass, $uid);
        return $this->processResource($resource, $request, false);
    }

    /**
     * Returns a standard view after treating a DELETE request on a resource
     *
     * @param $uid
     * @return Response
     */
    protected function deleteResourceResponse($uid)
    {
        $resourceClass = $this->getResourceClass();
        $resource = $this->getAuthorizedResource(Roles::DELETE, $resourceClass, $uid);

        $rm = $this->getResourceManager();
        $rm->delete($resource);

        return $this->handleView($this->view(null));
    }

    /**
     * Finds a given Resource, throwing a NotFound exception if was not found
     *
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
     * If they don't, will throw an exception
     *
     * @param string $accessRight Represents the required access right (EDIT, DELETE, OWNER, ...)
     * @param ResourceInterface $resource
     * @throws AccessDeniedHttpException
     * @return bool
     */
    protected function checkAuthorization($accessRight, ResourceInterface $resource)
    {
        if($this->isUsingSecurity()){
            $ac = $this->get('security.authorization_checker');

            //TODO find a way to make this function extensible

            //Managing CREATE case
            if($accessRight == 'CREATE')
            {
                $createRole = $this->getResourceManager()->getRole($resource, Roles::CREATE);
                if(!$ac->isGranted($createRole)) throw new AccessDeniedException();
            }
            else{
                if(!$ac->isGranted($accessRight, $resource))
                {
                    $bypass = false;
                    //Managing general action types
                    if($accessRight == Roles::VIEW)
                    {
                        $viewAllRole = $this->getResourceManager()->getRole($resource, Roles::VIEW_ALL);
                        if($ac->isGranted($viewAllRole)) $bypass = true;
                    }
                    elseif($accessRight == Roles::EDIT)
                    {
                        $viewAllRole = $this->getResourceManager()->getRole($resource, Roles::EDIT_ALL);
                        if($ac->isGranted($viewAllRole)) $bypass = true;
                    } elseif ($accessRight == Roles::DELETE) {
                        $viewAllRole = $this->getResourceManager()->getRole($resource, Roles::DELETE_ALL);
                        if ($ac->isGranted($viewAllRole)) {
                            $bypass = true;
                        }
                    }
                    if(!$bypass) throw new AccessDeniedHttpException();
                }
            }
        }
        //If we're not using the security component, we'll always grant authorization

        return true;
    }

    /**
     * Verifies that the requested object exists and that the current security context has the requested
     * access right on it
     *
     * When security is disabled, checkAuthorization will never fail
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
     * TODO generalize or remove
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
     * TODO generalize or remove
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
            //TODO clarify error formats
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
        //Initialization
        $rm = $this->getResourceManager();
        $parameters = $request->request->all();

        //validation
        $method = $isNew ? ResourceController::HTTP_METHOD_POST : ResourceController::HTTP_METHOD_PATCH;

        $resource = $this->validateResource($resource, $parameters, $method, $formType);

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
     * TODO generalize or remove
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
        //TODO allow bidirectional relations to be established
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
     * TODO find out if this is used
     *
     * @param $role
     * @return string
     * @throws \Exception
     */
    protected function getResourceRole($role)
    {
        $rm = $this->getResourceManager();
        if(!$rm instanceof SecurityResourceManagerInterface)
            throw new \Exception("Can't use roles with non Security ResourceManager");
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
     * @return ResourceManagerInterface
     */
    protected function getResourceManager()
    {
        return $this->get('g_theron_rest.resource_manager');
    }

    /**
     * @return boolean
     */
    protected function isUsingSecurity()
    {
        return $this->getParameter('g_theron_rest.use_security');
    }
}