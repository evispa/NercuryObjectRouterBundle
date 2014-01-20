<?php

/*
 * Copyright 2012 Nerijus Arlauskas <nercury@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Nercury\ObjectRouterBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Event object which is passed into listener to respond with a response
 *
 * @author nercury
 */
class ObjectRouteEvent extends Event
{
    private $objectType;
    private $objectId;

    /**
     *
     * @var Response
     */
    private $response = null;

    /**
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request = null;

    /**
     * @var ParameterBag
     */
    public $parameters;

    /**
     * Constructor.
     *
     * @param mixed $objectType
     * @param mixed $objectId
     * @param array $parameters
     */
    public function __construct($objectType, $objectId, $parameters = array())
    {
        $this->objectType = $objectType;
        $this->objectId = $objectId;
        $this->parameters = new ParameterBag($parameters);
    }

    /**
     * Get objectType.
     *
     * @return mixed
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * objectId
     *
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set response
     *
     * @param Response $response
     *
     * @return ObjectRouteEvent
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        if ($this->response !== null) {
            $this->stopPropagation();
        }

        return $this;
    }

    /**
     * Get response.
     *
     * @return null|Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get request.
     *
     * @return null|Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set request.
     *
     * @param Request $request
     * @param bool $useRequestLocale
     *
     * @return ObjectRouteEvent
     */
    public function setRequest(Request $request, $useRequestLocale = true)
    {
        $this->request = $request;
        if ($useRequestLocale) {
            $this->parameters->set('_locale', $request->getLocale());
        }

        return $this;
    }

    /**
     * Get parameters.
     *
     * @return ParameterBag
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set parameters.
     *
     * @param ParameterBag $parameters
     *
     * @return ObjectRouteEvent
     */
    public function setParameters(ParameterBag $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
