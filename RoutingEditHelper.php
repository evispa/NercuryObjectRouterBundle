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
 * Used to get router entities for editing, and update entities based on updated entities.
 * Note: a proper form type to use this is not included in this bundle (yet).
 *
 * @author nerijus
 */
class RoutingEditHelper
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;
    private $objectType;
    private $objectId;

    public function __construct($om, $objectType, $objectId)
    {
        $this->om = $om;
        $this->objectType = $objectType;
        $this->objectId = $objectId;
    }

    public function getRoutingItems()
    {
        $items = $this->om->getRepository('ObjectRouterBundle:ObjectRoute')->getRoutingItems($this->objectType, $this->objectId);

        return $items;
    }

    public function updateRoutingItems(GeneratorService $routerGenerator, &$items)
    {
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
