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

namespace Nercury\ObjectRouterBundle;

use \Symfony\Bridge\Monolog\Logger;
use \Symfony\Bundle\DoctrineBundle\Registry;
use \Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Used to manage redirects to objects.
 *
 * @author nercury
 */
class RedirectService {
    
    /**
     *
     * @var RoutingService 
     */
    private $objectRouter;
    
    /**
     *
     * @var \Symfony\Bundle\DoctrineBundle\Registry 
     */
    protected $doctrine;
    
    protected $configuration;
    
    public function __construct($configuration) {
        $this->configuration = $configuration;
    }
    
    public function setObjectRouter($objectRouter) {
        $this->objectRouter = $objectRouter;
    }
    
    public function setDoctrine($doctrine) {
        $this->doctrine = $doctrine;
    }
    
    public function addObjectLink($objectType, $objectId, $link, $type = 301, $locale = false) {
        
    }
    
    public function getObjectLinks($objectType, $objectId, $type = false, $locale = false) {
        
    }
    
    public function getResponseForRedirectId($id) {
        return $this->getCustomResponseForRedirectId($id, $this->configuration['default_route'], array());
    }
    
    public function getCustomResponseForRedirectId($id, $route, $parameters) {
        
    }
    
}