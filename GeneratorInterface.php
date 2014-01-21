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
 * @author Donatas Jasinevičius <donatas@evispa.lt>
 *
 * @since 1/2/14 4:32 PM
 */
interface GeneratorInterface
{
    /**
     * Generate and set.
     *
     * @param string $objectType
     * @param int $objectId
     * @param string $locale
     * @param string $slug
     * @param boolean $defaultVisible
     *
     * @return string
     */
    public function setUniqueSlug($objectType, $objectId, $locale, $slug, $defaultVisible = false);

    /**
     * Generate and set if not exists.
     *
     * @param string $objectType
     * @param int $objectId
     * @param string $locale
     * @param string $string
     * @param boolean $defaultVisible
     * @return string
     */
    public function setUniqueSlugIfNotExists($objectType, $objectId, $locale, $string, $defaultVisible = false);

    /**
     * Check if already exists.
     *
     * @param $objectType
     * @param $objectId
     * @param $locale
     * @param $slug
     *
     * @return mixed
     */
    public function slugExists($objectType, $objectId, $locale, $slug);

    /**
     * Replace or removes all non url chars.
     *
     * @param $string
     *
     * @return mixed
     */
    public function stringToSlug($string);

    /**
     * Generate unique slug.
     *
     * @param $objectType
     * @param $objectId
     * @param $locale
     * @param $slug
     * @return string|boolean
     */
    public function generateUniqueSlug($objectType, $objectId, $locale, $slug);
}
