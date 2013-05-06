<?php

namespace Nercury\ObjectRouterBundle;

/**
 * Used to get router entities for editing, and update entities based on updated entities.
 * Note: a proper form type to use this is not included in this bundle (yet).
 *
 * @author nerijus
 */
class RoutingEditHelper {

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;
    private $objectType;
    private $objectId;

    public function __construct($om, $objectType, $objectId) {
        $this->om = $om;
        $this->objectType = $objectType;
        $this->objectId = $objectId;
    }

    public function getRoutingItems() {
        $items = $this->om->getRepository('ObjectRouterBundle:ObjectRoute')->getRoutingItems($this->objectType, $this->objectId);
        
        return $items;
    }

    public function updateRoutingItems(GeneratorService $routerGenerator, &$items) {
        foreach ($items as $k => &$rItem) {
            if ($rItem->getSlug() === null || trim($rItem->getSlug()) === '') {
                unset($items[$k]);
                if ($this->om->contains($rItem)) {
                    $this->om->remove($rItem);
                }
                continue;
            }

            if (!$this->om->contains($rItem)) {
                $rItem->setObjectType($this->objectType);
                $rItem->setObjectId($this->objectId);
                $rItem->setVisible(false);
                $rItem->setSlug($routerGenerator->generateUniqueSlug('product', $this->objectId, $rItem->getLng(), $rItem->getSlug()));
                $this->om->persist($rItem);
            } else {
                $uow = $this->om->getUnitOfWork();
                $uow->computeChangeSets();
                $changeset = $uow->getEntityChangeSet($rItem);
                if (isset($changeset['slug'])) {
                    $rItem->setSlug($routerGenerator->generateUniqueSlug('product', $this->objectId, $rItem->getLng(), $rItem->getSlug()));
                }
            }
        }
    }

}