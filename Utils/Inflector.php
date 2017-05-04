<?php

namespace WH\LibBundle\Utils;

/**
 * Class Inflector
 *
 * @package WH\LibBundle\Utils
 */
class Inflector extends \Doctrine\Common\Inflector\Inflector
{

    /**
     * Camelizes a word. This uses the classify() method and turns the first character to lowercase.
     *
     * @param string $word The word to camelize.
     *
     * @return string The camelized word.
     */
    public static function camelizeWithFirstLetterUpper($word)
    {

        $word = lcfirst(self::classify($word));

        return strtoupper(substr($word, 0, 1)) . substr($word, 1);
    }

    /**
     * Transforme une chaine du type "entity.field" en "entityField"
     *
     * @param $condition
     *
     * @return array|string
     */
    public static function transformConditionInConditionParameter($condition)
    {

        $conditionParameter = explode('.', $condition);
        if (sizeof($conditionParameter) == 1) {
            return $conditionParameter[0];
        }

        $conditionParameter[1] = ucfirst($conditionParameter[1]);
        $conditionParameter = implode('', $conditionParameter);

        return $conditionParameter;
    }
}