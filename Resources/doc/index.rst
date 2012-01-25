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

Installation
------------

Recommended instalation is over Composer.
This bundle is not yet finished.

Configuration
-------------

To use the default object route controller, add this at the end of routing.yml::

    NercuryObjectRouterBundle:
    resource: "@ObjectRouterBundle/Controller/"
    type:     annotation
    prefix:   /

It defines the last route rule as object route:
    
    /{slug}

Additionally, paging route is also defined:

    /{slug}/page-{page}

It redirects the {page} parameter to the actual controller and action which receives:
    
    'id'  => $resolved_object_id,
    'page' => $page,

Usage
-----

    TBD