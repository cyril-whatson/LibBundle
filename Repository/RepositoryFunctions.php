<?php

namespace WH\LibBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Gedmo\Exception\InvalidArgumentException;
use Gedmo\Tool\Wrapper\EntityWrapper;
use WH\LibBundle\Utils\Inflector;

/**
 * Class RepositoryFunctions
 *
 * @package WH\LibBundle\Repository
 */
trait RepositoryFunctions
{

    public $qb;

    public $joins = array();

    /**
     * @return string
     */
    public function getEntityNameQueryBuilder()
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function getBaseQuery()
    {
        $this->qb = $this
            ->createQueryBuilder($this->getEntityNameQueryBuilder());

        $this->addJoins(
            array()
        );

        return $this->qb;
    }

    /**
     * @param $joins
     *
     * @return bool
     */
    public function addJoins($joins)
    {
        foreach ($joins as $joinSlug) {
            $joinProperties = $this->joins[$joinSlug];

            // Type de join
            $joinType = 'left';
            if (isset($joinProperties['type'])) {
                $joinType = $joinProperties['type'];
            }

            // Entité à laquelle lier
            $joinEntity = $this->getEntityNameQueryBuilder();
            if (isset($joinProperties['joinEntity'])) {
                $joinEntity = $joinProperties['joinEntity'];
            }

            // Entité à lier
            $toJoinEntity = $joinSlug;
            if (isset($joinProperties['toJoinEntity'])) {
                $toJoinEntity = $joinProperties['toJoinEntity'];
            }

            $this->qb->addSelect($joinSlug);
            $this->qb->{$joinType . 'Join'}($joinEntity . '.' . $toJoinEntity, $joinSlug);
        }

        return true;
    }

    /**
     * @param $condition
     * @param $value
     *
     * @return bool
     */
    public function handleCondition($condition, $value)
    {
        switch ($condition) {
            default:
                return false;
                break;
        }

        return true;
    }

    /**
     * @param $order
     *
     * @return bool
     */
    public function handleOrder($order)
    {
        switch ($order) {
            default:
                return false;
                break;
        }

        return true;
    }

    /**
     * @param $group
     *
     * @return bool
     */
    public function handleGroup($group)
    {
        switch ($group) {
            default:
                return false;
                break;
        }

        return true;
    }

    /**
     * @param string $type
     * @param array  $options
     *
     * @return array|bool|\Doctrine\ORM\QueryBuilder|Paginator|mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get($type = 'all', $options = array())
    {
        $this->getBaseQuery();

        foreach ($options as $key => $option) {

            switch ($key) {

                case 'limit':
                    $this->qb->setMaxResults($option);
                    break;

                case 'joins':
                    $this->addJoins($option);
                    break;

                case 'order':
                    $firstOrder = true;
                    foreach ($option as $order => $value) {
                        // Autre comportement défini ?
                        if ($this->handleOrder($order)) {
                            continue;
                        }

                        if ($firstOrder) {
                            $firstOrder = false;
                            $this->qb->orderBy($order, $value);
                        } else {
                            $this->qb->addOrderBy($order, $value);
                        }
                    }
                    break;

                case 'conditions':
                    foreach ($option as $condition => $value) {
                        // Autre comportement défini ?
                        if ($this->handleCondition($condition, $value)) {
                            continue;
                        }

                        // Exemple de $condition "entity.field !="
                        if (preg_match('#.* !=#', $condition)) {
                            $condition = preg_replace('#(.*) !=#', '$1', $condition);
                            $conditionParameter = Inflector::transformConditionInConditionParameter($condition);

                            if ($value === null) {
                                $conditionParameter = null;

                                $conditionWhere = $condition . ' IS NOT NULL';
                            } else {
                                if (is_array($value)) {
                                    $conditionWhere = $condition . ' NOT IN (:' . $conditionParameter . ')';
                                } else {
                                    $conditionWhere = $condition . ' != :' . $conditionParameter;
                                }
                            }
                            // Exemple de $condition "entity.field LIKE"
                        } elseif (preg_match('#.* LIKE#', $condition)) {
                            $condition = preg_replace('#(.*) LIKE#', '$1', $condition);
                            $conditionParameter = Inflector::transformConditionInConditionParameter(
                                $condition . 'LIKE'
                            );

                            $conditionWhere = $condition . ' LIKE :' . $conditionParameter;
                            // Exemple de $condition "entity.field >="
                        } elseif (preg_match('#.* >=#', $condition)) {
                            $condition = preg_replace('#(.*) >=#', '$1', $condition);
                            $conditionParameter = Inflector::transformConditionInConditionParameter(
                                $condition . 'SuperiorOrEqual'
                            );

                            $conditionWhere = $condition . ' >= :' . $conditionParameter;
                            // Exemple de $condition "entity.field >"
                        } elseif (preg_match('#.* >#', $condition)) {
                            $condition = preg_replace('#(.*) >#', '$1', $condition);
                            $conditionParameter = Inflector::transformConditionInConditionParameter(
                                $condition . 'Superior'
                            );

                            $conditionWhere = $condition . ' > :' . $conditionParameter;
                            // Exemple de $condition "entity.field <="
                        } elseif (preg_match('#.* <=#', $condition)) {
                            $condition = preg_replace('#(.*) <=#', '$1', $condition);
                            $conditionParameter = Inflector::transformConditionInConditionParameter(
                                $condition . 'InferiorOrEqual'
                            );

                            $conditionWhere = $condition . ' <= :' . $conditionParameter;
                            // Exemple de $condition "entity.field <"
                        } elseif (preg_match('#.* <#', $condition)) {
                            $condition = preg_replace('#(.*) <#', '$1', $condition);
                            $conditionParameter = Inflector::transformConditionInConditionParameter(
                                $condition . 'Inferior'
                            );

                            $conditionWhere = $condition . ' < :' . $conditionParameter;
                            // Exemple de $condition "entity.field NULL"
                        } elseif ($value === null) {
                            $conditionParameter = null;

                            $conditionWhere = $condition . ' IS NULL';
                            // Exemple de $condition "entity.field"
                        } else {
                            $conditionParameter = Inflector::transformConditionInConditionParameter($condition);

                            if (is_array($value)) {
                                $conditionWhere = $condition . ' IN (:' . $conditionParameter . ')';
                            } else {
                                $conditionWhere = $condition . ' = :' . $conditionParameter;
                            }
                        }

                        if ($conditionParameter) {
                            $this->qb->setParameter($conditionParameter, $value);
                        }
                        $this->qb->andWhere($conditionWhere);
                    }
                    break;

                case 'group':
                    foreach ($option as $group) {
                        // Autre comportement défini ?
                        if ($this->handleGroup($group)) {
                            continue;
                        }

                        $this->qb->addGroupBy($group);
                    }
                    break;
            }
        }

        switch ($type) {
            case 'query':
                return $this->qb;
                break;

            case 'first':
                $this->qb->setMaxResults(1);

                $query = $this->qb->getQuery();

                $query->setHint(
                    \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                    'Gedmo\Translatable\Query\TreeWalker\TranslationWalker'
                );

                $results = $query->getResult();

                if ($results) {
                    return $results[0];
                }

                return null;
                break;

            case 'all':
                $query = $this->qb->getQuery();

                $query->setHint(
                    \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                    'Gedmo\Translatable\Query\TreeWalker\TranslationWalker'
                );

                return $query->getResult();
                break;

            case 'one':
                return $this->qb->getQuery()->getOneOrNullResult();
                break;

            case 'paginate':
                $this->qb->getQuery();

                if (!empty($options['paginate']['page'])) {
                    $this->qb->setFirstResult(($options['paginate']['page'] - 1) * $options['paginate']['limit']);
                }

                if (!empty($options['paginate']['limit'])) {
                    $this->qb->setMaxResults($options['paginate']['limit']);
                }

                $paginator = new Paginator($this->qb, true);

                return array(
                    'entities' => $paginator->getIterator(),
                    'count'    => $paginator->count(),
                );
                break;

            case 'select':
                $results = $this->qb->getQuery()->getResult();

                $options = array();
                foreach ($results as $result) {
                    $options[$result->getId()] = $result->getName();
                }

                $options = array_flip($options);

                return $options;
                break;
        }

        return false;
    }

}
