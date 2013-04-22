<?php

namespace Nercury\ObjectRouterBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Event object which is passed into listener to respond with a response
 *
 * @author nercury
 */
class ObjectRouteEvent extends Event {

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

    public function __construct($objectType, $objectId, $parameters = array()) {
        $this->objectType = $objectType;
        $this->objectId = $objectId;
        $this->parameters = new ParameterBag($parameters);
    }

    public function getObjectType() {
        return $this->objectType;
    }

    public function getObjectId() {
        return $this->objectId;
    }

    public function setResponse(Response $response) {
        $this->response = $response;
        if ($this->response !== null) {
            $this->stopPropagation();
        }
    }

    public function getResponse() {
        return $this->response;
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest(\Symfony\Component\HttpFoundation\Request $request, $useRequestLocale = true) {
        $this->request = $request;
        if($useRequestLocale) {
            $this->parameters->set('_locale', $request->getLocale());
        }
        return $this;
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function setParameters(ParameterBag $parameters) {
        $this->parameters = $parameters;
        return $this;
    }

}