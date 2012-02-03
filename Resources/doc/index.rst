========
Overview
========

Allows to create and manage friendly routes for objects.

-   It is a map from [locale, slug] to [object type, id] and vice versa.
-   You can get object type and id by providing locale and slug to object_router 
    service and vice versa.
-   Changing of route slugs is done over the object_router service.
-   This bundle has pre-configured controller which uses object_router 
    configuration to load another controller and action based on object type.
    You can use this controller or create your own.
-   Route resolver uses doctrine cache to speed things up.
-   Tested and works with with i18n-routing-bundle by Johannes M. Schmitt.

Installation
------------

Recommended instalation is over Composer.

Configuration and usage
-----------------------

To set up a route to some object (i.e. a product), add this to config.yml file::
    
    object_router:
        controllers:
            product: SomeOtherBundle:SomeOtherController:index

"product" is understood as object type which should be routed to specified action.

To use the default object route controller, add this at the end of routing.yml::

    NercuryObjectRouterBundle:
    resource: "@ObjectRouterBundle/Controller/"
    type:     annotation
    prefix:   /

It defines the last route rule as object route:
    
    /{slug}

It redirects the {page} parameter to the actual controller and action which receives:
    
    'id'  => $resolved_object_id,

To assign a route slug to some object, use::

    $this->get('object_router')->setSlug('product', $id, $locale, 'test-route');

Url should be routed to the product action when accessing the page over::

    /test-route

You can get url of the product by calling::

    $this->get('object_router')->generateUrl('product', $id); // get url in current locale

Localle can be specified as an additional parameter::

    $this->get('object_router')->generateUrl('product', $id, $locale);

Pagination
----------

Additionally, paging route is also defined::

    /{slug}/page-{page}

It redirects the {page} parameter to the actual controller and action which receives::
    
    'id'  => $resolved_object_id,
    'page' => $page,

To get an url with a page, use::

    $this->get('object_router')->generateUrlWithPage('product', $id, $page);

Custom action
-------------

Custom actions can be easily used instead of the provided two. For example, a custom action that receives additional string parameter can be defined like this::

    /**
     * @Route("/{slug}/{custom_var}")
     */
    public function customAction($slug, $custom_var)
    {        
        list($action, $id) = $this->getActionAndId($slug);
              
        $this->get('logger')->info('Forward to route to "'.$action.'" with id '.$id.', custom_var '.$custom_var.'...');
        
        $router = $this->get('router');
        
        $response = $this->forward($action, array(
            'id'  => $id,
            'custom_var' => $custom_var,
        ));
        
        return $response;
    }

    /**
     * Helper to get action and id for slug string. Throws NotFound exceptions if slug is not found.
     * This is copied from Nercury/ObjectRouterBundle/Controllers/LoadController
     *
     * @param string $slug 
     * @return array Array of [action, id]
     */
    private function getActionAndId($slug) {
        $locale = $this->get('session')->getLocale();
        $router = $this->get('object_router');
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

Url can also be generated even for the custom action::

    $this->get('object_router')->generateCustomUrl('your_bundle_controller_custom', 
        $objectType, $objectId, array('custom_var' => $custom_var));