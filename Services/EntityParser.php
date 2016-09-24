<?php

namespace WH\LibBundle\Services;

/**
 * Class EntityParser
 *
 * @package WH\LibBundle\Services
 */
class EntityParser
{

    /**
     * @param $entityClassName
     *
     * @return array
     */
    public function getEntityFields($entityClassName)
    {

        $class = new \ReflectionClass($entityClassName);

        $fields = array();

        $properties = $class->getProperties();
        foreach ($properties as $property) {

            if ($property->isStatic()) {
                continue;
            }

            $docComment = $property->getDocComment();
            $docComment = str_replace("\n", '', $docComment);
            $docComment = str_replace("\t", '', $docComment);

            $type = preg_replace('#.*type="([a-z]{1,})".*#', '$1', $docComment);

            $fields[$property->getName()] = array(
                'type' => $type,
            );
        }

        return $fields;
    }

}
