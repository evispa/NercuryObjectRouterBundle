<?php

namespace Nercury\ObjectRouterBundle;

/**
 * Used to get router entities for editing, and update entities based on updated entities.
 *
 * @author nerijus
 */
class RoutingEditHelper {

    /**
     * @var EntityManager
     */
    private $em;
    private $objectType;
    private $objectId;

    public function __construct($em, $objectType, $objectId) {
        $this->em = $em;
        $this->objectType = $objectType;
        $this->objectId = $objectId;
    }

    public function getRoutingItems() {
        return $this->em
            ->createQuery('SELECT r FROM ObjectRouterBundle:ObjectRoute r WHERE r.object_type = :object_type AND r.object_id = :object_id')
            ->setParameter('object_type', $this->objectType)
            ->setParameter('object_id', $this->objectId)
            ->getResult();
    }

    public function updateRoutingItems(GeneratorService $routerGenerator, &$items) {
        foreach ($items as $k => &$rItem) {
            if ($rItem->getSlug() === null || trim($rItem->getSlug()) === '') {
                unset($items[$k]);
                if ($this->em->contains($rItem)) {
                    $this->em->remove($rItem);
                }
                continue;
            }

            if (!$this->em->contains($rItem)) {
                $rItem->setObjectType($this->objectType);
                $rItem->setObjectId($this->objectId);
                $rItem->setVisible(false);
                $rItem->setSlug($routerGenerator->generateUniqueSlug('product', $this->objectId, $rItem->getLng(), $rItem->getSlug()));
                $this->em->persist($rItem);
            } else {
                $uow = $this->em->getUnitOfWork();
                $uow->computeChangeSets();
                $changeset = $uow->getEntityChangeSet($rItem);
                if (isset($changeset['slug'])) {
                    $rItem->setSlug($routerGenerator->generateUniqueSlug('product', $this->objectId, $rItem->getLng(), $rItem->getSlug()));
                }
            }
        }
    }

}