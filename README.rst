========
Overview
========

Allows to create and manage friendly routes for objects.
Provides a way to manage redirects to routes.
Features unique slug generator.

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

For upgrading to 1.2, please read upgrade notes.

To set up a route to some object (i.e. a product), set up an event listener::
    
     <service id="my_product.object_route_listener" class="%my_product.object_route_listener.class%">
         <tag name="kernel.event_listener" event="object_router.get_response" method="onRouteResponseGetEvent" />
         <!-- setters -->
     </service>
     
Depending on your requirements, you can return response immediatelly (for example,
a redirect response, not found response, etc.) or you can forward the route
to your controller. An example for a forwarding service would look like this::

   use Nercury\ObjectRouterBundle\Event\ObjectRouteEvent;
   
   /**
    * Returns a response based on object route slug
    */
   class ObjectRouteListener {
   
       /**
        * @var \Symfony\Bundle\FrameworkBundle\HttpKernel
        */
       private $kernel;
   
       public function setKernel($kernel) {
           $this->kernel = $kernel;
       }
   
       public function onRouteResponseGetEvent(ObjectRouteEvent $event) {
           // if the object type is "product", handle it the specified way
           if ($event->getObjectType() == 'product') {
               $options = $event->parameters->all();
               $options['productId'] = $event->getObjectId();
               $event->setResponse($this->kernel->forward('MyProductBundle:Product:view', $options));
           }
       }
   
   }

The string "product" is understood as object type which should be routed to specified action.

To use the default object route controller to send previously mentioned event, 
add this at the end of routing.yml::

    NercuryObjectRouterBundle:
    resource: "@ObjectRouterBundle/Controller/"
    type:     annotation
    prefix:   /

It defines the last route rule as object route:
    
    /{slug}

It redirects the {page} parameter to the actual controller and action which receives:
    
    'id'  => $resolved_object_id,

To assign a route slug to some object, use::

    $this->get('object_router.routing')->setSlug('product', $id, $locale, 'test-route');

Url should be routed to the product action when accessing the page over::

    /test-route

You can get url of the product by calling::

    $this->get('object_router.routing')->generateUrl('product', $id); // get url in current locale

Localle can be specified as an additional parameter::

    $this->get('object_router.routing')->generateUrl('product', $id, $locale);

Pagination
----------

Additionally, paging route is also defined::

    /{slug}/page-{page}

It redirects the {page} parameter to the actual controller and action which receives::
    
    'id'  => $resolved_object_id,
    'page' => $page,

To get an url with a page, use::

    $this->get('object_router.routing')->generateUrlWithPage('product', $id, $page);

Custom action
-------------

Custom actions can be easily used instead of the provided two. More documentation in doc/custom_action.rst.

Redirects
---------

To create a redirect to an object::

    $this->get('object_router.redirect')->addRedirectToObject('product', $id, $locale, $redirectFromSlug);

Additionally redirect type can be specified (Permanent redirect is the default)::

    $this->get('object_router.redirect')->addRedirectToObject('product', $id, $locale, $redirectFromSlug, 301);

To disable redirects, set this configuration::

    object_router:
        controllers: []

Generator
---------

Since object slugs need to be unique, a generator is available to automatically generate and set such slugs.
Generator can use any string as source for slug. Generated slug is returned as string::

    $finalSlug = $this->get('object_router.generator')->setUniqueSlug('product', $id, $locale, 'Not unique text', true);

    // $finalSlug contains 'not-unique-text'