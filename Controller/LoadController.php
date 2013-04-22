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

namespace Nercury\ObjectRouterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoadController extends Controller
{
    /**
     * @return \Nercury\ObjectRouterBundle\RoutingService
     */
    private function getObjectRouter() {
        return $this->get('object_router.routing');
    }
    
    /**
     * @return \Nercury\ObjectRouterBundle\RedirectService
     */
    private function getRedirectService() {
        return $this->get('object_router.redirect');
    }
    
    /**
     * Helper to get action and id for slug string. Throws NotFound exceptions if slug is not found.
     * 
     * @param string $slug 
     * @return array Array of [action, id]
     */
    private function getActionAndId($slug) {
        $locale = $this->get('request')->getLocale();
        $router = $this->getObjectRouter();
        $res = $router->resolveObject($locale, $slug);
        if ($res === false)
            throw new NotFoundHttpException('Unable to locate a route with slug "'.$slug .'" in "'.$locale.'" locale.');
        
        list($id, $type, $visible) = $res;
        
        if (!$visible)
            throw new NotFoundHttpException('Route with slug "'.$slug .'" in "'.$locale.'" locale is not available for viewing.');
        
        $action = $router->getObjectTypeAction($type);
        
        if ($action === false)
            throw new NotFoundHttpException('Route with slug "'.$slug .'" in "'.$locale.'" has type "'.$type.'", but no assigned action to forward to.');
        
        return array($action, $id);
    }
    
    /**
     * Slug length should be longer than 0 symbols
     * Page should be an integer
     * 
     * @Route("/{slug}/page-{page}", name="object_route_with_page", requirements={"slug" = ".+", "page" = "\d+"})
     */
    public function object_with_pageAction($slug, $page)
    {
        list($action, $id) = $this->getActionAndId($slug);
              
        $this->get('logger')->info('Forward to route to "'.$action.'" with id '.$id.', page '.$page.'...');
        
        $router = $this->get('router');
        
        $response = $this->forward($action, array(
            '_locale' => $this->getRequest()->getLocale(),
            'id'  => $id,
            'page' => $page,
        ));
        
        return $response;
    }
    
    /**
     * Slug length should be longer than 0 symbols
     * 
     * @Route("/{slug}", name="object_route", requirements={"slug" = ".+"})
     */
    public function objectAction($slug)
    {        
        list($action, $id) = $this->getActionAndId($slug);
              
        $this->get('logger')->info('Forward to route to "'.$action.'" with id '.$id.'...');
                
        $response = $this->forward($action, array(
            '_locale' => $this->getRequest()->getLocale(),
            'id'  => $id,
        ));

        return $response;
    }
    
    /**
     * Process redirect for specified redirect object
     * 
     * @param integer $id Redirect object ID 
     */
    public function redirectHandlerAction($id) {
        
        $redirectService = $this->getRedirectService();
        $response = $redirectService->getResponseForLink($id);
        
        if ($response === false) {
            throw new NotFoundHttpException('It looks like ObjectRouter redirect points to invalid redirect resource.');
        }
        
        return $response;
        
    }
}
