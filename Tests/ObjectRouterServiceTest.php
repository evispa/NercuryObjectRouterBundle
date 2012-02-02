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

namespace Nercury\ObjectRouterBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Nercury\ObjectRouterBundle\ObjectRouterService;
use Nercury\ObjectRouterBundle\Entity\ObjectRoute;

class ObjectRouterServiceTest extends WebTestCase
{
    /**
     * @return \Symfony\Bundle\DoctrineBundle\Registry 
     */
    private function _getDoctrine($client) {
        return $client->getContainer()->get('doctrine');
    }
    
    private $test_route_slug = 'dsj?ak-fhsadjkfsdk?fjds-ahj-sdjahfprtoA';
    
    /**
     * @return Nercury\ObjectRouterBundle\Entity\ObjectRoute
     */
    private function _getTestRoute($em) {
        $q = $em->createQuery("select r from ObjectRouterBundle:ObjectRoute r where r.slug = ?1 and r.lng = 'lt'");
        $q->setParameter(1, $this->test_route_slug);
        $results = $q->getResult();
        
        if (count($results) == 0) {
            $route = new ObjectRoute();
            $route->setLng('lt');
            $route->setSlug($this->test_route_slug);
            $route->setObjectId(184564);
            $route->setObjectType('very_good_object_type');
            $route->setVisible(1);

            $em->persist($route);
            $em->flush();
        } else {
            $route = $results[0];
        }
        return $route;
    }
    
    public function testResolveObject() {
        $client = $this->createClient();
        $doctrine = $this->_getDoctrine($client);
        $em = $doctrine->getEntityManager();
        
        $route = $this->_getTestRoute($em);
        
        $service = $client->getContainer()->get("object_router");
        $object = $service->resolveObject('lt', $this->test_route_slug);
        
        $this->assertNotEquals($object, false);
        $this->assertEquals($object[0], 184564); // object id
        $this->assertEquals($object[1], 'very_good_object_type'); // object type
        $this->assertEquals($object[2], 1); // visibility
        
        $em->remove($route);
        $em->flush();
    }

    public function testSetSlug() {
        $client = $this->createClient();
        $doctrine = $this->_getDoctrine($client);
        $em = $doctrine->getEntityManager();
        
        $route = $this->_getTestRoute($em);
        $service = $client->getContainer()->get("object_router");
        
        $other_slug = 'other_object_slug_'.mt_rand(0, 500000);
        $service->setSlug(184564, 'very_good_object_type', 'lt', $other_slug);
        
        $object = $service->resolveObject('lt', $other_slug);
        $this->assertNotEquals($object, false);
        $this->assertEquals($object[0], 184564); // object id
        $this->assertEquals($object[1], 'very_good_object_type'); // object type
        
        $em->remove($route);
        $em->flush();
    }

    public function testGetSlug() {
        $client = $this->createClient();
        $doctrine = $this->_getDoctrine($client);
        $em = $doctrine->getEntityManager();
        
        $route = $this->_getTestRoute($em);
        $service = $client->getContainer()->get("object_router");
        $other_slug = 'other_object_slug_'.mt_rand(0, 500000);
        $service->setSlug(184564, 'very_good_object_type', 'lt', $other_slug);
        $slug = $service->getSlug(184564, 'very_good_object_type', 'lt');
        $this->assertEquals($slug, $other_slug);
        $slug = $service->getSlug(8888888, 'very_good_object_type_not_existing', 'asd');
        $this->assertEquals($slug, false);
        
        $em->remove($route);
        $em->flush();
    }

    public function testDeleteSlugs() {
        $client = $this->createClient();
        $doctrine = $this->_getDoctrine($client);
        $em = $doctrine->getEntityManager();
        
        $route = $this->_getTestRoute($em);
        $service = $client->getContainer()->get("object_router");
        $slug = $service->getSlug(184564, 'very_good_object_type', 'lt');
        $this->assertEquals($slug, $this->test_route_slug);
        $service->setSlug(184564, 'very_good_object_type', 'en', 'miau-aaaaaa-miau');
        $slug = $service->getSlug(184564, 'very_good_object_type', 'en');
        $this->assertEquals($slug, false);
        
        $slug = $service->getSlug(184564, 'very_good_object_type', 'en', false);
        $this->assertEquals($slug, 'miau-aaaaaa-miau');
        
        $service->deleteSlugs(184564, 'very_good_object_type');
        
        $slug = $service->getSlug(184564, 'very_good_object_type', 'lt');
        $this->assertEquals($slug, false);
        $object = $service->resolveObject('lt', $this->test_route_slug);
        $this->assertEquals($object, false);
        
        $em->remove($route);
        $em->flush();
    }

    public function testDeleteSlug() {
        $client = $this->createClient();
        $doctrine = $this->_getDoctrine($client);
        $em = $doctrine->getEntityManager();
        
        $route = $this->_getTestRoute($em);
        $service = $client->getContainer()->get("object_router");
        $slug = $service->getSlug(184564, 'very_good_object_type', 'lt');
        $this->assertEquals($slug, $this->test_route_slug);
        
        $service->deleteSlug(184564, 'very_good_object_type', 'lt');
        
        $slug = $service->getSlug(184564, 'very_good_object_type', 'lt');
        $this->assertEquals($slug, false);
        $object = $service->resolveObject('lt', $this->test_route_slug);
        $this->assertEquals($object, false);
        
        $em->remove($route);
        $em->flush();
    }
}