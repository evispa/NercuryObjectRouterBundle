Additions
---------

* Added twig template methods for rendering a route both for a 
  specific object id and a specific object slug.
* Added a form type for object route entity. It just has a field, 
  complete route editing is not yet available for this bundle.
* Added a RoutingEditHelper class which can be used to retrieve 
  routes for editing and update modified routes. It will probably be 
  moved into its own EntityRepository class (when someone will have time for this)
* A correct locale is preserved when redirecting to route.
* Now you can listen for a object_router.get_response event. This
  is now a prefered way to get responses from other bundles.
* Configuration is no longer necessary if the only contains the
  redirect route only, and all the actions are handles over events. 
  In this case it can be safely removed.

Breaking changes
----------------

* Previous doctrine indexes defined for ObjectRoute entity were
  incredibly inefficient on MySQL server. This is now fixed, however
  the changes will require an update to your schema.
