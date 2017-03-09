<?php

namespace WH\LibBundle\Twig;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SlugExtension
 *
 * @package WH\LibBundle\Twig
 */
class SlugExtension extends \Twig_Extension
{

    protected $container;

    /**
     * SearchController constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'wh_slug',
                array(
                    $this,
                    'slugFilter',
                )
            ),
        );
    }

    /**
     * @param      $string
     *
     * @return string
     */
    public function slugFilter($string)
    {
        $slug = '';

        if (is_string($string)) {
            $slug = strtolower(str_replace(' ', '-', $string));
        }

        return $slug;
    }

    public function getName()
    {
        return 'lib_slug_extension';
    }
}