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

namespace Nercury\ObjectRouterBundle\Twig;

use Nercury\ObjectRouterBundle\RoutingService;

class ObjectRouteExtensions extends \Twig_Extension
{
    /**
     * @var RoutingService
     */
    private $routing;

    public function __construct($routing)
    {
        $this->routing = $routing;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'object_slug_url' => new \Twig_Function_Method($this, 'getObjectSlugUrl'),
            'object_url' => new \Twig_Function_Method($this, 'getObjectUrl'),
        );
    }

    /**
     * Get absolute URL string with given slug and parameters in desired locale.
     *
     * @param string $locale
     * @param string $slug
     * @param array $params
     *
     * @return string|null
     */
    public function getObjectSlugUrl($locale, $slug, $params = array())
    {
        try {
            $url = $this->routing->generateCustomUrlForSlug($this->routing->getDefaultRoute(), $locale, $slug, $params);
        } catch (\Exception $e) {
            $url = null;
        }

        return $url;
    }

    /**
     * Get URL string for $objectId of $objectType.
     * If URL not found then NULL is returned.
     *
     * @param string $locale
     * @param string $objectType
     * @param int $objectId
     * @param array $params
     *
     * @return string|null
     */
    public function getObjectUrl($locale, $objectType, $objectId, $params = array())
    {
        $slug = $this->routing->getSlug($objectType, $objectId, $locale);

        if (false === $slug) {
            return null;
        }

        try {
            $url = $this->routing->generateCustomUrlForSlug($this->routing->getDefaultRoute(), $locale, $slug, $params);
        } catch (\Exception $e) {
            $url = null;
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'object_router_extensions';
    }
}
