<?php

namespace Nercury\ObjectRouterBundle\Twig;

use Nercury\ObjectRouterBundle\RoutingService;

class ObjectRouteExtensions extends \Twig_Extension
{

    /**
     * @var RoutingService
     */
    private $routing;

    public function __construct($routing)
    {
        $this->routing = $routing;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'object_slug_url' => new \Twig_Function_Method($this, 'getObjectSlugUrl'),
            'object_url' => new \Twig_Function_Method($this, 'getObjectUrl'),
        );
    }

    /**
     * Get absolute URL string with given slug and parameters in desired locale.
     *
     * @param string $locale
     * @param string $slug
     * @param array $params
     *
     * @return string|null
     */
    public function getObjectSlugUrl($locale, $slug, $params = array())
    {
        try {
            $url = $this->routing->generateCustomUrlForSlug($this->routing->getDefaultRoute(), $locale, $slug, $params);
        } catch (\Exception $e) {
            $url = null;
        }

        return $url;
    }

    /**
     * Get URL string for $objectId of $objectType.
     * If URL not found then NULL is returned.
     *
     * @param string $locale
     * @param string $objectType
     * @param int $objectId
     * @param array $params
     *
     * @return string|null
     */
    public function getObjectUrl($locale, $objectType, $objectId, $params = array())
    {
        $slug = $this->routing->getSlug($objectType, $objectId, $locale);

        if (false === $slug) {
            return null;
        }

        try {
            $url = $this->routing->generateCustomUrlForSlug($this->routing->getDefaultRoute(), $locale, $slug, $params);
        } catch (\Exception $e) {
            $url = null;
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'object_router_extensions';
    }
}
