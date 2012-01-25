<?php

namespace Nercury\ObjectRouterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Index;

/**
 * Nercury\ObjectRouterBundle\Entity\ObjectRoute
 *
 * @ORM\Table(
 *  name="object_route", 
 *  uniqueConstraints = {
 *      @UniqueConstraint(name="slug_lang_idx", columns={"slug", "lng"})
 *  },
 *  indexes = {
 *      @Index(name="object_idx", columns={"object_id", "object_type"}),
 *      @Index(name="lng_idx", columns={"lng"})
 *  }
 * )
 * @ORM\Entity
 */
class ObjectRoute
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $lng
     *
     * @ORM\Column(name="lng", type="string", length=5)
     */
    private $lng;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var string $object_type
     *
     * @ORM\Column(name="object_type", type="string", length=32)
     */
    private $object_type;

    /**
     * @var integer $object_id
     *
     * @ORM\Column(name="object_id", type="integer")
     */
    private $object_id;

    /**
     * @var boolean $visible
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lng
     *
     * @param string $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * Get lng
     *
     * @return string 
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set object_type
     *
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        $this->object_type = $objectType;
    }

    /**
     * Get object_type
     *
     * @return string 
     */
    public function getObjectType()
    {
        return $this->object_type;
    }

    /**
     * Set object_id
     *
     * @param integer $objectId
     */
    public function setObjectId($objectId)
    {
        $this->object_id = $objectId;
    }

    /**
     * Get object_id
     *
     * @return integer 
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }
}