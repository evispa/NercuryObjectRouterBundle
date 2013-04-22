<?php

namespace Nercury\ObjectRouterBundle\Twig;

class ObjectRouteExtensions extends \Twig_Extension {

    /**
     *
     * @var \JMS\DebuggingBundle\DependencyInjection\TraceableContainer
     */
    private $container;

    public function setContainer($container) {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return array(
            'object_slug_url' => new \Twig_Function_Method($this, 'getObjectSlugUrl'),
            'object_url' => new \Twig_Function_Method($this, 'getObjectUrl'),
        );
    }

    /**
     * @return \Nercury\ObjectRouterBundle\RoutingService
     */
    private function getRouting() {
        return $this->container->get('object_router.routing');
    }

    public function getObjectSlugUrl($locale, $slug, $params = array()) {
        $routing = $this->getRouting();
        return $routing->generateCustomUrlForSlug($routing->getDefaultRoute(), $locale, $slug, $params);
    }

    public function getObjectUrl($locale, $objectType, $objectId, $params = array()) {
        $routing = $this->getRouting();
        $slug = $routing->getSlug($objectType, $objectId, $locale);
        return $routing->generateCustomUrlForSlug($routing->getDefaultRoute(), $locale, $slug, $params);
    }

    public function getName() {
        return 'object_router_extensions';
    }

}