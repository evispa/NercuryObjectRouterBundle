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

use \Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Used to manage object routes.
 *
 * @author nercury
 */
class RoutingService {

    /**
     * @var \Symfony\Bridge\Monolog\Logger 
     */
    protected $logger;

    /**
     *
     * @var \Symfony\Bundle\DoctrineBundle\Registry 
     */
    protected $doctrine;

    /**
     *
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router 
     */
    protected $router;
    
    protected $configuration;
    
    /**
     *
     * @var \Symfony\Component\HttpKernel\Kernel 
     */
    protected $kernel;
    
    public function __construct($configuration, $logger, $doctrine, $router) {
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->doctrine = $doctrine;
        $this->router = $router;
    }

    public function setKernel($kernel) {
        $this->kernel = $kernel;
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager() {
        return $this->doctrine->getEntityManager();
    }
    
    /**
     * 
     * @return type 
     */
    private function getCurrentLocale() {
        if(!$this->kernel->getContainer()->isScopeActive('request')) {
            return $this->kernel->getContainer()->getParameter('locale');
        }else{
            return $this->kernel->getContainer()->get('request')->getLocale();
        }
    }
    
    /**
     * Get cache is which is used for resolve object method to cache results.
     * 
     * @param string $language
     * @param string $slug
     * @return string 
     */
    public function getResolveObjectCacheId($language, $slug) {
        return 'rt_' . $slug . $language . '_obj_resolve';
    }
    
    /**
     * Get cache is which is used for resolve object method to cache results.
     * 
     * @param string $objectType
     * @param int $objectId
     * @param string $language
     * @param boolean $only_visible 
     * @return string
     */
    public function getGetSlugCacheId($objectType, $objectId, $language, $only_visible) {
        return 'rt_' . $objectId . $objectType . $language . ($only_visible ? 1 : 0) . '_obj_resolve';
    }

    /**
     * Clear resolve object cache for specific language and slug.
     * 
     * @param string $language
     * @param string $slug 
     */
    public function clearResolveObjectCache($language, $slug) {
        $em = $this->doctrine->getEntityManager();
        $cache_impl = $em->getConfiguration()->getResultCacheImpl();
        if ($cache_impl)
            $cache_impl->delete($this->getResolveObjectCacheId($language, $slug));
    }
    
    /**
     * Clear get slug cache for specific object.
     * 
     * @param string $objectType
     * @param int $objectId
     * @param string $language
     * @param boolean $only_visible 
     */
    public function clearGetSlugCache($objectType, $objectId, $language, $only_visible) {
        $em = $this->doctrine->getEntityManager();
        $cache_impl = $em->getConfiguration()->getResultCacheImpl();
        if ($cache_impl)
            $cache_impl->delete($this->getGetSlugCacheId($objectType, $objectId, $language, $only_visible));
    }

    /**
     * Get object id and type based on language and slug
     * 
     * @param string $language
     * @param string $slug 
     * @return array of objectId and objectType: array(id, type, visible) or FALSE on failure
     */
    public function resolveObject($language, $slug) {
        $this->logger->info('Resolve object slug ' . $slug . ' in ' . $language . ' language...');
        $em = $this->getEntityManager();       
        
        $q = $em->createQueryBuilder()
                ->from('ObjectRouterBundle:ObjectRoute', 'r')
                ->andWhere('r.lng = ?1')
                ->andWhere('r.slug = ?2')
                ->select('r.object_type, r.object_id, r.visible')
                ->setParameter(1, $language)
                ->setParameter(2, $slug)
                ->setMaxResults(1)
                ->getQuery();

        $q->useResultCache(true, 300, $this->getResolveObjectCacheId($language, $slug));
        $res = $q->getArrayResult();

        if (empty($res))
            return FALSE;

        return array($res[0]['object_id'], $res[0]['object_type'], $res[0]['visible']);
    }

    /**
     * Get all object_router entries for given object id.
     *
     * @param int $objectId
     * @param string $type
     * @return array
     */
    public function getRoutesForObject($objectId = null, $type = '') {
        if($objectId) {
            $em = $this->getEntityManager();
            $q = $em->createQueryBuilder()
                ->from('ObjectRouterBundle:ObjectRoute', 'r')
                ->andWhere('r.object_id = ?1')
                ->andWhere('r.object_type = ?2')
                ->select('r')
                ->setParameter(1, $objectId)
                ->setParameter(2, $type)
                ->getQuery();

            $res = $q->getResult();
            if (empty($res)) {
                return array();
            } else {
                return $res;
            }
        } else {
            return array();
        }
    }

    /**
     * Set slug for specified object, type and language
     * 
     * @param string $objectType Object type string
     * @param integer $objectId Id of the object
     * @param string $language Language for slug
     * @param string $slug Object slug
     * @param boolean $defaultVisible Specify if route should be visible if created for the first time
     */
    public function setSlug($objectType, $objectId, $language, $slug, $defaultVisible = false) {
        $this->logger->info('Set slug to ' . $slug . ' for object id '.$objectId.' of type '.$objectType.' in ' . $language . ' language...');
        $em = $this->getEntityManager();
        $q = $em->createQueryBuilder()
                ->from('ObjectRouterBundle:ObjectRoute', 'r')
                ->andWhere('r.lng = ?1')
                ->andWhere('r.object_id = ?2')
                ->andWhere('r.object_type = ?3')
                ->select('r')
                ->setParameter(1, $language)
                ->setParameter(2, $objectId)
                ->setParameter(3, $objectType)
                ->setMaxResults(1)
                ->getQuery();
                
        $res = $q->getResult();
        
        if (empty($res)) {
            $route = new Entity\ObjectRoute();
            $route->setLng($language);
            $route->setObjectId($objectId);
            $route->setObjectType($objectType);
            $route->setVisible($defaultVisible);
            $em->persist($route);
        } else {
            $route = $res[0];
        }
        
        $route->setSlug($slug);
        
        $em->flush();
                
        $this->clearGetSlugCache($objectType, $objectId, $language, true);
        $this->clearGetSlugCache($objectType, $objectId, $language, false);
        $this->clearResolveObjectCache($language, $slug);
    }

    /**
     * Gets languages used for routing. If i18n_routing bundle is loaded, get's languages from it, otherwise
     * returns just a single language from current locale.
     * 
     * @return array Array of language strings 
     */
    public function getRouterLanguages() {
        $container = $this->kernel->getContainer(); // todo: would be good to inject and use container directly
        if ($container->hasParameter('jms_i18n_routing.locales')) {
            return $container->getParameter('jms_i18n_routing.locales');
        }
        return array($container->getParameter('locale'));
    }
    
    /**
     * Set object visibility in specified languages
     * 
     * @param string $objectType
     * @param integer $objectId
     * @param boolean $value Visibility true/false
     * @param array $languages Array of languages, or language string, or false to update all languages
     */
    public function setVisibility($objectType, $objectId, $value, $languages = false) {
        if ($languages !== false) {
            if (!is_array($languages)) {
                $languages = array($languages);
            }
        }
        
        $this->logger->info('Set slug visibility to ' . ($value ? 1 : 0) . ' for object id '.$objectId.' of type '.$objectType.' in ' . ($languages === false ? 'all languages' : implode(', ', $languages).' languages') . '...');
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
                ->update('ObjectRouterBundle:ObjectRoute', 'r')
                ->set('r.visible', '?1')
                ->andWhere('r.object_id = ?2')
                ->andWhere('r.object_type = ?3')
                ->setParameter(1, $value)
                ->setParameter(2, $objectId)
                ->setParameter(3, $objectType);
        
        if ($languages !== false) {
            $qb->andWhere('r.lng in (?4)');
            $qb->setParameter(4, $languages);
        }
        
        $q = $qb->getQuery();
        $q->execute();
        
        if ($languages === false)
            $languages = $this->getRouterLanguages();
        
        foreach ($languages as $language) {
            $slug = $this->getSlug($objectType, $objectId, $language);
            $this->clearGetSlugCache($objectType, $objectId, $language, true);
            $this->clearGetSlugCache($objectType, $objectId, $language, false);
            $this->clearResolveObjectCache($language, $slug);
        }
    }
    
    /**
     * Get slug for specified object, type and language
     * 
     * @param string $objectType Object type string
     * @param integer $objectId Id of the object
     * @param string $language Language for slug
     * @param boolean $only_visible Return FALSE if route is not visible
     * @return string Object slug (returns FALSE if object slug was not found)
     */
    public function getSlug($objectType, $objectId, $language, $only_visible = true) {
        $this->logger->info('Get slug for object id '.$objectId.' of type '.$objectType.' in ' . $language . ' language...');
        $em = $this->getEntityManager();
       
        $qb = $em->createQueryBuilder()
                ->from('ObjectRouterBundle:ObjectRoute', 'r')
                ->andWhere('r.lng = ?1')
                ->andWhere('r.object_id = ?2')
                ->andWhere('r.object_type = ?3')
                ->select('r.slug')
                ->setParameter(1, $language)
                ->setParameter(2, $objectId)
                ->setParameter(3, $objectType)
                ->setMaxResults(1);
        
        if ($only_visible)
            $qb->andWhere('r.visible = 1');
        
        $q = $qb->getQuery();

        $q->useResultCache(true, 300, $this->getGetSlugCacheId($objectType, $objectId, $language, $only_visible));
        $res = $q->getArrayResult();

        if (empty($res))
            return FALSE;
        
        return $res[0]['slug'];
    }
    
    /**
     * Return visibility
     * 
     * @param type $objectType
     * @param type $objectId
     * @param type $language
     * @return type 
     */
    public function getVisibility($objectType, $objectId, $language) {
        return $this->getSlug($objectType, $objectId, $language) !== FALSE;
    }

    /**
     * Delete all slugs for specified object
     * 
     * @param string $objectType Object type string
     * @param integer $objectId Id of the object
     * @return boolean TRUE if something was deleted, otherwise FALSE
     */
    public function deleteSlugs($objectType, $objectId) {
        $this->logger->info('Delete slugs for object id '.$objectId.' of type '.$objectType.' in all languages...');
        $em = $this->getEntityManager();
        
        $qb = $em->createQueryBuilder()
                ->from('ObjectRouterBundle:ObjectRoute', 'r')
                ->andWhere('r.object_id = ?1')
                ->andWhere('r.object_type = ?2')
                ->select('r')
                ->setParameter(1, $objectId)
                ->setParameter(2, $objectType);
        
        $q = $qb->getQuery();
        $results = $q->getResult();
        if (empty($results))
            return FALSE;
        
        foreach ($results as $route) {
            $em->remove($route);
            $this->clearResolveObjectCache($route->getLng(), $route->getSlug());
            $this->clearGetSlugCache($objectType, $objectId, $route->getLng(), true);
            $this->clearGetSlugCache($objectType, $objectId, $route->getLng(), false);
        }
        
        $em->flush();
    }

    /**
     * Delete slug in single language for specified object
     * 
     * @param string $objectType Object type string
     * @param integer $objectId Id of the object
     * @param string $language Language for slug
     */
    public function deleteSlug($objectType, $objectId, $language) {
        $this->logger->info('Delete slug for object id '.$objectId.' of type '.$objectType.' in '.$language.' language...');
        
        $slug = $this->getSlug($objectType, $objectId, $language, false);
        
        $em = $this->getEntityManager();
        $q = $em->createQuery('DELETE from ObjectRouterBundle:ObjectRoute r WHERE r.object_id = ?1 AND r.object_type = ?2 AND r.lng = ?3');
        $q->setParameter(1, $objectId);
        $q->setParameter(2, $objectType);
        $q->setParameter(3, $language);
        $q->execute();
        
        $this->clearResolveObjectCache($language, $slug);
        $this->clearGetSlugCache($objectType, $objectId, $language, true);
        $this->clearGetSlugCache($objectType, $objectId, $language, false);
    }

    /**
     * Return action for specified object type string
     * 
     * @param string $type 
     * @return string Return action if it exists, otherwise FALSE
     */
    public function getObjectTypeAction($type) {
        if (!isset($this->configuration['controllers'][$type]))
            return FALSE;
        return $this->configuration['controllers'][$type];
    }
    
    public function getDefaultRoute() {
        return $this->configuration['default_route'];
    }
    
    public function getDefaultRouteWithPage() {
        return $this->configuration['default_route_with_page'];
    }
    
    /**
     * Generate object url.
     * 
     * @param string $objectType
     * @param integer $objectId
     * @param string $locale
     * @param boolean $absolute
     * @return type
     * @throws RouteNotFoundException 
     */
    public function generateUrl($objectType, $objectId, $locale = false, $absolute = false) {
        if ($locale === false)
            $locale = $this->getCurrentLocale();
        
        return $this->generateCustomUrl($this->getDefaultRoute(), $objectType, $objectId, array(
            '_locale' => $locale,
        ), $absolute);
    }
    
    /**
     * Generate object url with page.
     * 
     * @param string $objectType
     * @param integer $objectId
     * @param integer $page
     * @param string $locale
     * @param boolean $absolute
     * @return string 
     */
    public function generateUrlWithPage($objectType, $objectId, $page, $locale = false, $absolute = false) {  
        if ($locale === false)
            $locale = $this->getCurrentLocale();
        
        return $this->generateCustomUrl($this->getDefaultRouteWithPage(), $objectType, $objectId, array(
            'page' => $page,
            '_locale' => $locale,
        ), $absolute);
    }
    
    /**
     * Generate object url for specified routing action with specified parameters.
     * 
     * @param string $route Route name
     * @param string $objectType
     * @param integer $objectId
     * @param array $parameters Custom route parameters
     * @param boolean $absolute Generate absolute url
     * @return string The generated URL
     * @throws RouteNotFoundException 
     */
    public function generateCustomUrl($route, $objectType, $objectId, $parameters = array(), $absolute = false) {  
        $locale = isset($parameters['_locale']) ? $parameters['_locale'] : $this->request->getLocale();
        
        $slug = $this->getSlug($objectType, $objectId, $locale);
        
        if ($slug === false)
            throw new RouteNotFoundException('Could not find a route for object id '.$objectId.' of type '.$objectType.' in '.$locale.' locale. Maybe route is not visible?');
        
        return $this->generateCustomUrlForSlug($route, $locale, $slug, $parameters, $absolute);
    }
    
    /**
     * Generate url with explicitly specified slug
     * 
     * @param string $route Route name
     * @param string $locale
     * @param string $slug
     * @param array $parameters Custom route parameters
     * @param boolean $absolute Generate absolute url
     * @return type The generated URL
     */
    public function generateCustomUrlForSlug($route, $locale, $slug, $parameters = array(), $absolute = false) {
        $parameters['slug'] = $slug;
        if (!isset($parameters['_locale']))
            $parameters['_locale'] = $locale;
        
        return $this->router->generate($route, $parameters, $absolute);
    }
    
    public function generateDefaultUrlForSlug($locale, $slug, $absolute = false) {
        return $this->generateCustomUrlForSlug($this->getDefaultRoute(), $locale, $slug, array(), $absolute);
    }
    
    public function generateDefaultUrlWithPageForSlug($locale, $slug, $page, $absolute = false) {
        return $this->generateCustomUrlForSlug($this->getDefaultRoute(), $locale, $slug, array('page' => $page), $absolute);
    }
    
}