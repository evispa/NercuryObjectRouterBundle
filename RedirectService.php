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
    
    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager() {
        return $this->doctrine->getManager();
    }
    
    /**
     * Add additional redirect to some object. 
     * Object should already have an assigned default route, otherwise ObjectHasNoRouteException will be thrown.
     * Route slug should be unique in locale, otherwise RouteAlreadyExistsException will be thrown.
     * 
     * @param string $objectType
     * @param integer $objectId
     * @param string $locale
     * @param string $slug
     * @param string $type
     * @return integer Returns link id of the created redirect.
     * 
     * @throws Exception\RouteAlreadyExistsException
     * @throws Exception\ObjectHasNoRouteException 
     */
    public function addRedirectToObject($objectType, $objectId, $locale, $slug, $type = 301) {
        
        $existingRoute = $this->objectRouter->resolveObject($locale, $slug);
        if ($existingRoute !== false)
            throw new Exception\RouteAlreadyExistsException('Can not create redirect route "'. $slug . '" to object "'. $objectType . '" with id "'.$objectId.'", because such route already exists in "'.$locale.'" locale.');   
        
        $om = $this->getManager();
        
        $objectRouteResults = $om->getRepository("ObjectRouterBundle:ObjectRoute")->getObjectRoutes($objectType, $objectId, $locale);
        if (count($objectRouteResults) == 0) {
            throw new Exception\ObjectHasNoRouteException('Can not create redirect route "'. $slug . '" to object "'. $objectType . '" with id "'.$objectId.'", because object has no defined route in "'.$locale.'" locale.');
        }
        
        $route = $objectRouteResults[0];
        
        $redirect = new Entity\ObjectRouteRedirect();
        $redirect->setObjectRoute($route);
        $redirect->setType($type);
        
        $om->persist($redirect);
        
        $om->flush();
        
        $this->objectRouter->setSlug('redirect', $redirect->getId(), $locale, $slug, true);
        
        return $redirect->getId();
    }
    
    
    public function updateRedirect($linkId, $link, $type) {
        
    }
    
    public function deleteRedirect($linkId) {
        
    }
    
    /**
     * Get link locale and slug identified by link id
     * 
     * @param integer $linkId
     * @return mixed Boolean FALSE if route slug was not found or array(lng, slug, type) 
     */
    public function getLinkLocaleSlugAndType($linkId) {
        $om = $this->getManager();
        
        $results = $om->getRepository("ObjectRouterBundle:ObjectRouteRedirect")->getLinkLocaleSlugAndType($linkId);
        
        if (count($results) == 0)
            return false;
        
        return array($results[0]['lng'], $results[0]['slug'], $results[0]['type']);
    }
    
    public function getResponseForLink($linkId) {
        return $this->getCustomResponseForLink($linkId, $this->configuration['default_route'], array());
    }
    
    public function getCustomResponseForLink($linkId, $route, $parameters) {
        $localeAndSlug = $this->getLinkLocaleSlugAndType($linkId);
        if ($localeAndSlug === false) {
            return false;
        } else {
            list($lang, $slug, $type) = $localeAndSlug;
            
            $url = $this->objectRouter->generateCustomUrlForSlug($route, $lang, $slug, $parameters);
            return new \Symfony\Component\HttpFoundation\RedirectResponse($url, $type);
        }
    }
    
}