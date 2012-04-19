=============
Custom action
=============

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

Url can also be generated even for the custom action::

    $this->get('object_router')->generateCustomUrl('your_bundle_controller_custom', 
        $objectType, $objectId, array('custom_var' => $custom_var));