<?php
namespace Nercury\ObjectRouterBundle\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * @author Tadcka
 */
class ObjectRouteRedirectRepository extends EntityRepository {
    
    /**
     * Get redirect links to an object.
     * 
     * @param string $objectType Object type
     * @param integer $objectId Object Id
     * @param integer $type Redirect type (301, 302, ...). If false, returns all redirects.
     * @param string $locale Object locale. If false, returns links to object in all locales.
     * @return array Returns array of 
     *      "slug" - redirect route slug, 
     *      "linkId" - redirect item id,
     *      "locale" - redirect route locale
     *      "visible" - true if redirect is visible
     *      "redirectType" - redirect type (301, 302, ...)
     *      "objectLng" - target object language for this specific route
     */
    public function getRedirectsToObject($objectType, $objectId, $type = false, $locale = false) {
                
        $em = $this->_em;
        
        $qb = $em->createQueryBuilder();
        $qb->from('ObjectRouterBundle:ObjectRouteRedirect', 'rr')
                ->innerJoin('rr.objectRoute', 'r')
                ->andWhere('r.object_type = ?1')
                ->andWhere('r.object_id = ?2')
                ->select('rr.id as linkId, r.lng as objectLng, rr.type as redirectType')
                ->setParameter(1, $objectType)
                ->setParameter(2, $objectId);

        if ($locale !== false) {
            $qb->andWhere('r.lng = :lng')->setParameter('lng', $locale);
        }
        
        if ($type !== false) {
            $qb->andWhere('rr.type = :type')->setParameter('type', $type);
        }
        
        $link_ids = array();
        $link_data = array();
        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $link_ids[] = $row['linkId'];
            $link_data[$row['linkId']] = $row;
        }
        
        if (empty($link_ids))
            return array();
        
        $qb = $em->createQueryBuilder();
        $qb->from('ObjectRouterBundle:ObjectRoute', 'r')
                ->andWhere('r.object_type = :ot')
                ->andWhere('r.object_id IN (:ids)')
                ->select('r.slug, r.object_id as linkId, r.lng as locale, r.visible')
                ->setParameter('ot', 'redirect')
                ->setParameter('ids', $link_ids);
               
        $results = $qb->getQuery()->execute();
        
        foreach ($results as &$row) {
            $row['redirectType'] = $link_data[$row['linkId']]['redirectType'];
            $row['objectLng'] = $link_data[$row['linkId']]['objectLng'];
        }
        
        return $results;
        
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
        
        return $results;
    }
}
