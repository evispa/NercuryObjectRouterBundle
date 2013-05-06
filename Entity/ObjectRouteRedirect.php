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
 *     uniqueConstraints = {
 *         @ORM\UniqueConstraint(name="unique_redirect_type_idx", columns={"object_route_id", "type"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="Nercury\ObjectRouterBundle\Entity\ObjectRouteRedirectRepository")
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
     * 301 - Moved Permanently -- used when this url should no longer be used
     * 302 - Found -- redirect to another url, but still use this url for next requests
     * 303 - See Other -- url is valid, but redirect for other reasons
     * 
     * @var integer $type
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;
    
    /** 
     * @var ObjectRoute
     *
     * @ORM\ManyToOne(targetEntity="ObjectRoute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_route_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $objectRoute;
       
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
     * 301 - Moved Permanently -- used when this url should no longer be used
     * 302 - Found -- redirect to another url, but still use this url for next requests
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
     * 302 - Found -- redirect to another url, but still use this url for next requests
     * 303 - See Other -- url is valid, but redirect for other reasons
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set objectRoute
     *
     * @param Nercury\ObjectRouterBundle\Entity\ObjectRoute $objectRoute
     * @return ObjectRouteRedirect
     */
    public function setObjectRoute(\Nercury\ObjectRouterBundle\Entity\ObjectRoute $objectRoute = null)
    {
        $this->objectRoute = $objectRoute;
        return $this;
    }

    /**
     * Get objectRoute
     *
     * @return Nercury\ObjectRouterBundle\Entity\ObjectRoute 
     */
    public function getObjectRoute()
    {
        return $this->objectRoute;
    }
}