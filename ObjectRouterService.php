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

    public function __construct(Logger $logger, Registry $doctrine) {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
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
     * Get object id and type based on language and slug
     * 
     * @param string $language
     * @param string $slug 
     * @return array Pair of objectId and objectType: array(id, type) or FALSE on failure
     */
    public function resolveObject($language, $slug) {
        $this->logger->info('resolve object slug ' . $slug . ' in ' . $language . ' language...');
        $em = $this->doctrine->getEntityManager();

        //$this->clearResolveObjectCache($language, $slug);
        
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
        
    }

    /**
     * Get slug for specified object, type and language
     * 
     * @param integer $objectId Id of the object
     * @param string $objectType Object type string
     * @param string $language Language for slug
     * @return string Object slug
     */
    public function getSlug($objectId, $objectType, $language) {
        
    }

    /**
     * Delete all slugs for specified object
     * 
     * @param integer $objectId Id of the object
     * @param string $objectType Object type string
     * @return boolean TRUE if something was deleted, otherwise FALSE
     */
    public function deleteSlugs($objectId, $objectType) {
        
    }

    /**
     * Delete slug in single language for specified object
     * 
     * @param integer $objectId Id of the object
     * @param string $objectType Object type string
     * @param string $language Language for slug
     * @return boolean TRUE if something was deleted, otherwise FALSE
     */
    public function deleteSlug($objectId, $objectType, $language) {
        
    }

}