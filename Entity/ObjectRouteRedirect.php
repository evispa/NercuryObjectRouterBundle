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

namespace Nercury\ObjectRouterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nercury\ObjectRouterBundle\Entity\ObjectRoute
 * 
 * Object router object which handles redirects from old links.
 *
 * @ORM\Table(
 *     name="object_route_redirect", 
 *     indexes = {
 *         @ORM\Index(name="object_route_id", columns={"object_route_id"}),
 *     }
 * )
 * @ORM\Entity
 */
class ObjectRouteRedirect
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer $object_route_id
     *
     * @ORM\Column(name="object_route_id", type="integer")
     */
    private $object_route_id;

    /**
     * 301 - Moved Permanently -- used when this url should no longer be used
     * 303 - See Other -- url is valid, but redirect for other reasons
     * 
     * @var integer $type
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set object_route_id
     *
     * @param integer $objectRouteId
     * @return ObjectRouteRedirect
     */
    public function setObjectRouteId($objectRouteId)
    {
        $this->object_route_id = $objectRouteId;
        return $this;
    }

    /**
     * Get object_route_id
     *
     * @return integer 
     */
    public function getObjectRouteId()
    {
        return $this->object_route_id;
    }

    /**
     * 301 - Moved Permanently -- used when this url should no longer be used
     * 303 - See Other -- url is valid, but redirect for other reasons
     *
     * @param integer $type
     * @return ObjectRouteRedirect
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * 301 - Moved Permanently -- used when this url should no longer be used
     * 303 - See Other -- url is valid, but redirect for other reasons
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }
}