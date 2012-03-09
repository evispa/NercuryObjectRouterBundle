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
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager() {
        return $this->doctrine->getEntityManager();
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
        
        $em = $this->getEntityManager();

        $q = $em->createQuery('SELECT r FROM ObjectRouterBundle:ObjectRoute r WHERE r.object_type = ?1 AND r.object_id = ?2 AND r.lng = ?3');
        $q->setParameter(1, $objectType);
        $q->setParameter(2, $objectId);
        $q->setParameter(3, $locale);
        
        $objectRouteResults = $q->execute();
        if (count($objectRouteResults) == 0) {
            throw new Exception\ObjectHasNoRouteException('Can not create redirect route "'. $slug . '" to object "'. $objectType . '" with id "'.$objectId.'", because object has no defined route in "'.$locale.'" locale.');
        }
        
        $route = $objectRouteResults[0];
        
        $redirect = new Entity\ObjectRouteRedirect();
        $redirect->setObjectRoute($route);
        $redirect->setType($type);
        
        $em->persist($redirect);
        
        $em->flush();
        
        $this->objectRouter->setSlug('redirect', $redirect->getId(), $locale, $slug, true);
        
        return $redirect->getId();
    }
    
    public function getRedirectsToObject($objectType, $objectId, $type = false, $locale = false) {
        
        $em = $this->getEntityManager();
        
        $qb = $em->createQueryBuilder();
        $qb->from('ObjectRouterBundle:ObjectRouteRedirect', 'rr')
                ->innerJoin('rr.objectRoute', 'r')
                ->innerJoin('rr.objectRedirectRoute', 'orr')
                ->andWhere('orr.object_type = :redirect_type')
                ->andWhere('r.object_type = ?1')
                ->andWhere('r.object_id = ?2')
                ->select('rr.id as linkId, rr.type, orr.lng as locale, orr.slug, orr.visible')
                ->setParameter('redirect_type', 'redirect')
                ->setParameter(1, $objectType)
                ->setParameter(2, $objectId);
        
        if ($type !== false) {
            $qb->andWhere('rr.type = ?3')->setParameter(3, $type);
        }
        
        if ($locale !== false) {
            $qb->andWhere('r.lng = ?4')->setParameter(4, $locale);
        }
        
        return $qb->getQuery()->execute();
        
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
        $em = $this->getEntityManager();
        
        $q = $em->createQuery('SELECT r.lng, r.slug, rr.type FROM ObjectRouterBundle:ObjectRouteRedirect rr INNER JOIN rr.objectRoute r WHERE rr.id = ?1');
        $q->setParameter(1, $linkId);
        
        $results = $q->execute();
        
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