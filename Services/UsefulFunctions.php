<?php

namespace WH\LibBundle\Services;

/**
 * Class UsefulFunctions
 *
 * @package WH\LibBundle\Services
 */
class UsefulFunctions
{

    /**
     * Retourne de façon récursive la valeur d'un champ d'une entité
     *
     * Exemple :
     * $field = 'metas.title'
     * Retounera (si possible) la valeur du champ 'title' du champ 'metas' de $entity
     *
     * @param $entity
     * @param $field
     *
     * @return null
     */
    public function getRecursiveValueOfEntity($entity, $field)
    {
        $value = null;

        $fields = explode('.', $field);

        // Champ lié à une entité liée (sur un ou plusieurs niveaux)
        if (sizeof($fields) > 1) {
            $value = $entity;
            foreach ($fields as $field) {
                if ($value) {
                    if ($value->{'get' . ucfirst($field)}()) {
                        $value = $value->{'get' . ucfirst($field)}();
                    } else {
                        $value = null;
                    }
                }
            }
            // Champ lié directement à l'entité
        } else {
            $value = $entity->{'get' . ucfirst($fields[0])}();
        }

        return $value;
    }

}
