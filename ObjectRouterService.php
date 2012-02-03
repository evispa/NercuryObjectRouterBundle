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

/**
 * Used to manage object routes.
 *
 * @author nercury
 */
class ObjectRouterService {

    /**
     * @var \Symfony\Bridge\Monolog\Logger 
     */
    private $logger;

    /**
     *
     * @var \Symfony\Bundle\DoctrineBundle\Registry 
     */
    private $doctrine;

    private $configuration;
    
    public function __construct($configuration, Logger $logger, Registry $doctrine) {
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->doctrine = $doctrine;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager() {
        return $this->doctrine->getEntityManager();
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
     * @param int $objectId
     * @param string $objectType
     * @param string $language
     * @param boolean $only_visible 
     * @return string
     */
    public function getGetSlugCacheId($objectId, $objectType, $language, $only_visible) {
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
     * @param int $objectId
     * @param string $objectType
     * @param string $language
     * @param boolean $only_visible 
     */
    public function clearGetSlugCache($objectId, $objectType, $language, $only_visible) {
        $em = $this->doctrine->getEntityManager();
        $cache_impl = $em->getConfiguration()->getResultCacheImpl();
        if ($cache_impl)
            $cache_impl->delete($this->getGetSlugCacheId($objectId, $objectType, $language, $only_visible));
    }

    /**
     * Get object id and type based on language and slug
     * 
     * @param string $language
     * @param string $slug 
     * @return array Pair of objectId and objectType: array(id, type) or FALSE on failure
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
     * Set slug for specified object, type and language
     * 
     * @param integer $objectId Id of the object
     * @param string $objectType Object type string
     * @param string $language Language for slug
     * @param string $slug Object slug
     */
    public function setSlug($objectId, $objectType, $language, $slug) {
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
            $route->setVisible(0);
            $em->persist($route);
        } else {
            $route = $res[0];
        }
        
        $route->setSlug($slug);
        
        $em->flush();
        
        $this->clearGetSlugCache($objectId, $objectType, $language, true);
        $this->clearGetSlugCache($objectId, $objectType, $language, false);
        $this->clearResolveObjectCache($language, $slug);
    }

    /**
     * Get slug for specified object, type and language
     * 
     * @param integer $objectId Id of the object
     * @param string $objectType Object type string
     * @param string $language Language for slug
     * @param boolean $only_visible Return FALSE if route is not visible
     * @return string Object slug (returns FALSE if object slug was not found)
     */
    public function getSlug($objectId, $objectType, $language, $only_visible = true) {
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

        $q->useResultCache(true, 300, $this->getGetSlugCacheId($objectId, $objectType, $language, $only_visible));
        $res = $q->getArrayResult();

        if (empty($res))
            return FALSE;
        
        return $res[0]['slug'];
    }

    /**
     * Delete all slugs for specified object
     * 
     * @param integer $objectId Id of the object
     * @param string $objectType Object type string
     * @return boolean TRUE if something was deleted, otherwise FALSE
     */
    public function deleteSlugs($objectId, $objectType) {
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
            $this->clearGetSlugCache($objectId, $objectType, $route->getLng(), true);
            $this->clearGetSlugCache($objectId, $objectType, $route->getLng(), false);
        }
        
        $em->flush();
    }

    /**
     * Delete slug in single language for specified object
     * 
     * @param integer $objectId Id of the object
     * @param string $objectType Object type string
     * @param string $language Language for slug
     */
    public function deleteSlug($objectId, $objectType, $language) {
        $this->logger->info('Delete slug for object id '.$objectId.' of type '.$objectType.' in '.$language.' language...');
        
        $slug = $this->getSlug($objectId, $objectType, $language, false);
        
        $em = $this->getEntityManager();
        $q = $em->createQuery('DELETE from ObjectRouterBundle:ObjectRoute r WHERE r.object_id = ?1 AND r.object_type = ?2 AND r.lng = ?3');
        $q->setParameter(1, $objectId);
        $q->setParameter(2, $objectType);
        $q->setParameter(3, $language);
        $q->execute();
        
        $this->clearResolveObjectCache($language, $slug);
        $this->clearGetSlugCache($objectId, $objectType, $language, true);
        $this->clearGetSlugCache($objectId, $objectType, $language, false);
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
    
}