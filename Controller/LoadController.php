<?php

namespace Nercury\ObjectRouterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoadController extends Controller
{
    /**
     * @Route("/{slug}/page-{page}", requirements={"page" = "\d+"})
     */
    public function object_with_pageAction($slug, $page)
    {        
        $locale = $this->get('session')->getLocale();
        
        $objectRouter = $this->get('object_router');
        $res = $objectRouter->resolveObject($locale, $slug);
        if ($res === FALSE)
            throw new NotFoundHttpException('Unable to locate a route with slug "'.$slug .'" in "'.$locale.'" locale.');

        list($id, $type, $visible) = $res;
        
        if (!$visible)
            throw new NotFoundHttpException('Route with slug "'.$slug .'" in "'.$locale.'" locale is not available for viewing.');
        
        $response = $this->forward('CmsBundle:Load:test', array(
            'id'  => $id,
            'page' => $page,
        ));

        // further modify the response or return it directly

        return $response;
    }
    
    /**
     * @Route("/{slug}")
     */
    public function objectAction($slug)
    {        
        $response = $this->forward('CmsBundle:Load:test', array(
            'id'  => $slug,
        ));

        // further modify the response or return it directly

        return $response;
    }
}
