=============
Quick Summary
=============

Requirements
------------

-   Doctrine2
-   Framework Extra Bundle

What It Does
------------

Allows multiple object types to share the same friendly route space.
For example, menu::

/my-menu-a
/my-menu-b

A product::

/some-product-link

An article::

/my-article

Allows you to define object types and their controllers in a config file::

    object_router:
        controllers:
            menu: CategoryBundle:MainController:showCategory
            product: ProductBundle:MainController:showProduct
            article: CmsBundle:MainController:showArticle

Generate a unique slug for link to an object::

    $generatedSlug = $this->get('object_router.generator')
        ->setUniqueSlug('product', $id, $request->getLocale(), 'My Menu Title');

Generates and sets 'my-menu-title'.
If 'my-menu-title' is already used (by any other object), generates 'my-menu-title-1', '...-2' and so on.

Get a link to an object by it's type and id::

    $link = $this->get('object_router.routing')->generateUrl('product', $id);

==================
More detailed info
==================

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

Recommended instalation is over Composer::

    // composer.json
    {
        // ...
        require: {
            // ...
            "nercury/object-router": "master-dev"
        }
    }

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

Custom actions can be easily used instead of the provided two. More documentation in custom_action.rst.

Redirects
---------

Object router features a way to manage redirects over "redirect" object. This way
default router logic is not polluted with redirect functionality if it is not needed.

To enable redirects, add this object route::

    object_router:
        controllers:
            <...>
            redirect: ObjectRouterBundle:Load:redirectHandler
            <...>

To create a redirect to an object::

    $this->get('object_router.redirect')->addRedirectToObject('product', $id, $locale, $redirectFromSlug);

Additionally redirect type can be specified (Permanent redirect is the default)::

    $this->get('object_router.redirect')->addRedirectToObject('product', $id, $locale, $redirectFromSlug, 301);

Generator
---------

Since object slugs need to be unique, a generator is available to automatically generate and set such slugs.
Generator can use any string as source for slug. Generated slug is returned as string::

    $finalSlug = $this->get('object_router.generator')->setUniqueSlug('product', $id, $locale, 'Not unique text', true);

    // $finalSlug contains 'not-unique-text'