<?php
namespace Nercury\ObjectRouterBundle\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * @author Tadcka
 */
class ObjectRouteRepository extends EntityRepository {
    
    /**
     * Get object router
     * @param string $objectType
     * @param integer $objectId
     * @param string $locale
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getObjectRoutes($objectType, $objectId, $locale){
        $q = $this->_em->createQuery('SELECT r FROM ObjectRouterBundle:ObjectRoute r WHERE r.object_type = ?1 AND r.object_id = ?2 AND r.lng = ?3');
        $q->setParameter(1, $objectType);
        $q->setParameter(2, $objectId);
        $q->setParameter(3, $locale);
        
        $objectRouteResults = $q->execute();
        
        return $objectRouteResults;
    }
    
    public function getObjectRoute($objectType, $objectId, $language){
        $q = $this->_em->createQueryBuilder()
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
        
        return $res;
    }
    
    public function updateObjectRoute($value, $objectType, $objectId, $languages){
        $qb = $this->_em->createQueryBuilder()
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
    }
    
    public function getObjectRoutesByObjectIdAndType($objectId, $objectType){
        $qb = $this->_em->createQueryBuilder()
                ->from('ObjectRouterBundle:ObjectRoute', 'r')
                ->andWhere('r.object_id = ?1')
                ->andWhere('r.object_type = ?2')
                ->select('r')
                ->setParameter(1, $objectId)
                ->setParameter(2, $objectType);
        
        $q = $qb->getQuery();
        $results = $q->getResult();
        
        return $results;
    }
    
    public function deleteSlug($objectType, $objectId, $language) {
        $q = $this->_em->createQuery('DELETE from ObjectRouterBundle:ObjectRoute r WHERE r.object_id = ?1 AND r.object_type = ?2 AND r.lng = ?3');
        $q->setParameter(1, $objectId);
        $q->setParameter(2, $objectType);
        $q->setParameter(3, $language);
        $q->execute();
    }
    
    public function getSlug($language, $objectId, $objectType, $only_visible, $cacheId){
        $qb = $this->_em->createQueryBuilder()
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

        $q->useResultCache(true, 300, $cacheId);
        $res = $q->getArrayResult();

        if (empty($res))
            return FALSE;
        
        return $res[0]['slug'];
    }
    
    public function getRoutingItems($objectType, $objectId) {
        return $this->_em
            ->createQuery('SELECT r FROM ObjectRouterBundle:ObjectRoute r WHERE r.object_type = :object_type AND r.object_id = :object_id')
            ->setParameter('object_type', $objectType)
            ->setParameter('object_id', $objectId)
            ->getResult();
    }
    
    public function resolveObject($language, $slug, $cacheId) {
        $q = $this->_em->createQueryBuilder()
                ->from('ObjectRouterBundle:ObjectRoute', 'r')
                ->andWhere('r.lng = ?1')
                ->andWhere('r.slug = ?2')
                ->select('r.object_type, r.object_id, r.visible')
                ->setParameter(1, $language)
                ->setParameter(2, $slug)
                ->setMaxResults(1)
                ->getQuery();

        $q->useResultCache(true, 300, $cacheId);
        $res = $q->getArrayResult();
        
        return $res;
    }
    
    public function getRoutesForObject($objectId, $type) {
        $q = $this->_em->createQueryBuilder()
           ->from('ObjectRouterBundle:ObjectRoute', 'r')
           ->andWhere('r.object_id = ?1')
           ->andWhere('r.object_type = ?2')
           ->select('r')
           ->setParameter(1, $objectId)
           ->setParameter(2, $type)
           ->getQuery();

       $res = $q->getResult();

       return $res;
    }
}
