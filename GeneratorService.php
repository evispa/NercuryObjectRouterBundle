<?php

namespace Nercury\ObjectRouterBundle;

use \Symfony\Bridge\Monolog\Logger;
use \Symfony\Bundle\DoctrineBundle\Registry;
use \Symfony\Component\Routing\Exception\RouteNotFoundException;

class GeneratorService {

    /**
     * @var \Symfony\Bridge\Monolog\Logger 
     */
    protected $logger;

    /**
     *
     * @var \Symfony\Bundle\DoctrineBundle\Registry 
     */
    protected $doctrine;

    /**
     *
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router 
     */
    protected $router;

    /**
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    protected $configuration;

    /**
     *
     * @var \Symfony\Component\HttpKernel\Kernel 
     */
    protected $kernel;
    
    /**
     * ANAL FROM OLD VERSION
     * @var array
     */
    private $translit = array(
        ' ' => '-',
        '~' => '', '!' => '', '@' => '', '#' => '', '$' => '', '%' => '', '^' => '', '&' => '', '*' => '', '(' => '', ')' => '', '=' => '', '+' => '',
        '[' => '', ']' => '', '{' => '', '}' => '', ':' => '', ';' => '', "'" => '', '"' => '', '`' => '', "\'" => '', '\"' => '', '?' => '', ',' => '', '.' => '', '<' => '', '>' => '', '´' => '',
        //LT
        'ą' => 'a', 'ž' => 'z', 'į' => 'i', 'ų' => 'u', 'ė' => 'e', 'ū' => 'u', 'č' => 'c', 'š' => 's', 'ę' => 'e',
        'Ą' => 'A', 'Ž' => 'Z', 'Į' => 'I', 'Ų' => 'U', 'Ė' => 'E', 'Ū' => 'U', 'Č' => 'C', 'Š' => 'S', 'Ę' => 'E',
        //RU
        "й" => "j", "Й" => "j", "ц" => "c", "Ц" => "c", "у" => "u", "У" => "u", "к" => "k", "К" => "k", "е" => "e", "Е" => "e", "н" => "n", "Н" => "n",
        "г" => "g", "Г" => "g", "ш" => "sh", "Ш" => "sh", "щ" => "sch", "Щ" => "sch", "з" => "z", "З" => "z", "х" => "h", "Х" => "h", "ъ" => "", "Ъ" => "",
        "ё" => "jo", "Ё" => "jo", "ф" => "f", "Ф" => "f", "ы" => "y", "Ы" => "y", "в" => "v", "В" => "v", "а" => "a", "А" => "a", "п" => "p", "П" => "p",
        "р" => "r", "Р" => "r", "о" => "o", "О" => "o", "л" => "l", "Л" => "l", "д" => "d", "Д" => "d", "ж" => "zh", "Ж" => "zh", "э" => "e", "Э" => "e",
        "я" => "ja", "Я" => "ja", "ч" => "ch", "Ч" => "ch", "с" => "s", "С" => "s", "м" => "m", "М" => "m", "и" => "i", "И" => "i", "т" => "t", "Т" => "t",
        "ь" => "", "Ь" => "", "б" => "b", "Б" => "b", "ю" => "ju", "Ю" => "ju",
        //LV
        "ā" => "a", "č" => "c", "ē" => "e", "ģ" => "g", "ī" => "i", "ķ" => "k", "ļ" => "l", "ņ" => "n", "š" => "s", "ū" => "u", "ž" => "z",
        "Ā" => "A", "Č" => "C", "Ē" => "E", "Ģ" => "G", "Ī" => "I", "Ķ" => "K", "Ļ" => "L", "Ņ" => "N", "Š" => "S", "Ū" => "U", "Ž" => "Z",
        //EE
        "ä" => "a", "ö" => "o", "õ" => "o", "ü" => "u",
        "Ä" => "A", "Ö" => "O", "Õ" => "O", "Ü" => "U",
    );

    public function __construct($configuration, $logger, $doctrine, $router, $request) {
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->doctrine = $doctrine;
        $this->router = $router;
        $this->request = $request;
    }
    
    public function setKernel($kernel) {
        $this->kernel = $kernel;
    }

    public function setObjectRouter($objectRouter) {
        $this->objectRouter = $objectRouter;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager() {
        return $this->doctrine->getEntityManager();
    }
        
    /**
     * Generate ant set
     * @param type $objectType
     * @param type $objectId
     * @param type $locale
     * @param type $slug
     * @param type $defaultVisible
     * @return type 
     */
    public function setUniqueSlug($objectType, $objectId, $locale, $slug, $defaultVisible = false) {
        $slug = $this->generateUniqueSlug($objectType, $objectId, $locale, $slug);

        if ($slug !== false)
            $this->objectRouter->setSlug($objectType, $objectId, $locale, $slug, $defaultVisible);

        return $slug;
    }
    
    /**
     * Check if already exists
     * TODO: Check router translations files
     * 
     * @param type $slug
     * @param type $locale
     * @return boolean 
     */
    public function slugExists($objectType, $objectId, $locale, $slug) {
        if ($locale === false)
            $locale = $this->request->getLocale();
        
        $object = $this->objectRouter->resolveObject($locale, $slug);
        
        //not exists or same object
        if($object == FALSE || ($object[0] == $objectId && $object[1] == $objectType) )
            return FALSE;

        return TRUE;
    }

    /**
     * Replace or removes all non url chars
     * 
     * @param type $string
     * @return type 
     */
    public function stringToSlug($string) {
        $string = strtr($string, $this->translit);
        $replace = '-';

        $trans = array(
            '&\#\d+?;' => '',
            '&\S+?;' => '',
            '\s+' => $replace,
            '[^a-z0-9\-\._/]' => '',
            $replace . '+' => $replace,
            $replace . '$' => $replace,
            '^' . $replace => $replace,
            '\.+$' => ''
        );
        $string = strip_tags($string);

        foreach ($trans as $key => $val) {
            $string = preg_replace("#" . $key . "#i", $val, $string);
        }

        $string = strtolower($string);
        return trim(stripslashes($string));
    }

    /**
     * 
     * @param type $slug
     * @param type $locale
     * @return string|boolean 
     */
    private function generateUniqueSlug($objectType, $objectId, $locale, $slug) {
        $originalSlug = $this->stringToSlug($slug);
        if (!empty($originalSlug)) {
            $slug = $originalSlug;
            $i = 0;

            while ($this->slugExists($objectType, $objectId, $locale, $slug)) {
                $i++;
                $slug = $originalSlug . '-' . $i;
            }

            return $slug;
        } else {
            return false;
        }
    }
    
}