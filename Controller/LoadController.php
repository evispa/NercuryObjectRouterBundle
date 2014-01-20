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

use Nercury\ObjectRouterBundle\Event\ObjectRouteEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoadController extends Controller
{
    /**
     * @return \Nercury\ObjectRouterBundle\RoutingService
     */
    private function getObjectRouter()
    {
        return $this->get('object_router.routing');
    }

    /**
     * @return \Nercury\ObjectRouterBundle\RedirectService
     */
    private function getRedirectService()
    {
        return $this->get('object_router.redirect');
    }

    /**
     * Helper to get action and id for slug string. Throws NotFound exceptions if slug is not found.
     *
     * @param string $type
     * @param $id
     *
     * @return array Array of [action, id]
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getActionAndId($type, $id)
    {
        $action = $this->getObjectRouter()->getObjectTypeAction($type);

        if (false === $action) {
            throw new NotFoundHttpException('Route with type "' . $type . '" has no assigned action to forward to.');
        }

        return array($action, $id);
    }

    /**
     * Return object type and id, and throw exceptions if object does not exist or is not visible
     *
     * @param Request $request
     * @param string $slug
     *
     * @return array
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getRouterEvent(Request $request, $slug)
    {
        $locale = $request->getLocale();
        $router = $this->getObjectRouter();
        $res = $router->resolveObject($locale, $slug);
        if (false === $res) {
            throw new NotFoundHttpException('Unable to locate a route with slug "' . $slug . '" in "' . $locale . '" locale.');
        }

        list($id, $type, $visible) = $res;

        if (!$visible) {
            throw new NotFoundHttpException('Route with slug "' . $slug . '" in "' . $locale . '" locale is not available for viewing.');
        }

        $event = new ObjectRouteEvent($type, $id);
        $event->setRequest($request);

        return $event;
    }

    /**
     * Slug length should be longer than 0 symbols
     * Page should be an integer
     *
     * @Route("/{slug}/page-{page}", name="object_route_with_page", requirements={"slug" = ".+", "page" = "\d+"})
     */
    public function object_with_pageAction(Request $request, $slug, $page)
    {
        $event = $this->getRouterEvent($request, $slug);
        $event->parameters->set('page', $page);
        $this->get('event_dispatcher')->dispatch('object_router.get_response', $event);

        $response = $event->getResponse();

        if ($response === null) {
            list($action, $id) = $this->getActionAndId($event->getObjectType(), $event->getObjectId());

            $this->get('logger')->info('Forward to route to "' . $action . '" with id ' . $id . ', page ' . $page . '...');

            $response = $this->forward($action, array(
                '_locale' => $request->getLocale(),
                'id' => $id,
                'page' => $page,
            ));
        }

        return $response;
    }

    /**
     * Slug length should be longer than 0 symbols
     *
     * @Route("/{slug}", name="object_route", requirements={"slug" = ".+"})
     */
    public function objectAction(Request $request, $slug)
    {
        $event = $this->getRouterEvent($request, $slug);
        $this->get('event_dispatcher')->dispatch('object_router.get_response', $event);

        $response = $event->getResponse();

        if ($response === null) {
            list($action, $id) = $this->getActionAndId($event->getObjectType(), $event->getObjectId());

            $this->get('logger')->info('Forward to route to "' . $action . '" with id ' . $id . '...');

            $response = $this->forward($action, array(
                '_locale' => $request->getLocale(),
                'id' => $id,
            ));
        }

        return $response;
    }

    /**
     * Process redirect for specified redirect object
     *
     * @param integer $id Redirect object ID
     */
    public function redirectHandlerAction($id)
    {
        $redirectService = $this->getRedirectService();
        $response = $redirectService->getResponseForLink($id);

        if ($response === false) {
            throw new NotFoundHttpException('It looks like ObjectRouter redirect points to invalid redirect resource.');
        }

        return $response;
    }
}
