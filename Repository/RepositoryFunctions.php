<?php

namespace WH\LibBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use WH\LibBundle\Utils\Inflector;

/**
 * Class RepositoryFunctions
 *
 * @package WH\LibBundle\Repository
 */
trait RepositoryFunctions
{
    public $entityName = '';

    public $conditions = [];
    public $groups = [];
    public $joins = [];
    public $orders = [];

    public $baseJoins = [];
    public $baseOrders = [];

    protected $queryConditions = [];
    protected $queryGroups = [];
    protected $queryJoins = [];
    protected $queryOrders = [];

    /**
     * @return string
     */
    protected function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return mixed
     */
    protected function getBaseQuery()
    {
        $qb = $this
            ->createQueryBuilder($this->getEntityName());

        $qb = $this->processJoins($qb, $this->baseJoins);
        $qb = $this->processOrders($qb, $this->baseOrders);

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param              $joins
     *
     * @return QueryBuilder
     */
    protected function processJoins(QueryBuilder $qb, $joins)
    {
        foreach ($joins as $joinSlug) {
            // On ne fait pas le join 2 fois
            if (in_array($joinSlug, $this->queryJoins)) {
                continue;
            }

            // On stocke tous les joins de la requête
            $this->queryJoins[] = $joinSlug;

            // On récupère les propriétés du join
            $joinProperties = $this->joins[$joinSlug];

            // Type de join
            $joinType = 'left';
            if (isset($joinProperties['type'])) {
                $joinType = $joinProperties['type'];

                if (!in_array($joinType, ['left', 'inner'])) {
                    throw new \UnexpectedValueException(sprintf('Join type "%s" is invalid', $joinType));
                }
            }

            // Entité à laquelle lier
            $joinEntity = $this->entityName;
            if (isset($joinProperties['joinEntity'])) {
                $joinEntity = $joinProperties['joinEntity'];
            }

            // Entité à lier
            $toJoinEntity = $joinSlug;
            if (isset($joinProperties['toJoinEntity'])) {
                $toJoinEntity = $joinProperties['toJoinEntity'];
            }

            $qb->addSelect($joinSlug);
            $qb->{$joinType . 'Join'}($joinEntity . '.' . $toJoinEntity, $joinSlug);
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param              $limit
     *
     * @return QueryBuilder
     */
    protected function processLimit(QueryBuilder $qb, $limit)
    {
        if (!is_int($limit)) {
            throw new InvalidTypeException('Integer expected, "' . gettype($limit) . '" received');
        }

        $qb->setMaxResults($limit);

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param              $orders
     * @param bool         $resetOrders
     *
     * @return QueryBuilder
     */
    protected function processOrders(QueryBuilder $qb, $orders, $resetOrders = true)
    {
        if ($resetOrders) {
            $this->queryOrders = [];
        }

        if (!is_array($orders)) {
            throw new InvalidTypeException('Array expected, "' . gettype($orders) . '" received');
        }

        foreach ($orders as $orderKey => $orderValue) {
            if (in_array($orderKey, $this->queryOrders)) {
                continue;
            }

            if (isset($this->orders[$orderKey])) {
                // Si c'est une clé spécifique, on envoie le QueryBuilder à la fonction dédiée
                $qb = $this->{$this->orders[$orderKey]}($qb, $orderValue);
            } else {
                // Sinon c'est un cas classique

                // S'il le tableau $queryOrders est vide c'est qu'aucun order n'a encore été défini
                if (empty($this->queryOrders)) {
                    $qb->orderBy($orderKey, $orderValue);
                } else {
                    $qb->addOrderBy($orderKey, $orderValue);
                }
            }

            $this->queryOrders[] = $orderKey;
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param              $groups
     * @param bool         $resetGroups
     *
     * @return QueryBuilder
     */
    protected function processGroups(QueryBuilder $qb, $groups, $resetGroups = true)
    {
        if ($resetGroups) {
            $this->queryGroups = [];
        }

        if (!is_array($groups)) {
            throw new InvalidTypeException('Array expected, "' . gettype($groups) . '" received');
        }

        foreach ($groups as $groupValue) {
            // S'il le tableau $queryGroups est vide c'est qu'aucun group n'a encore été défini
            if (empty($this->queryGroups)) {
                $qb->groupBy($groupValue);
            } else {
                $qb->addGroupBy($groupValue);
            }

            $this->queryGroups[] = $groupValue;
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param              $parameter
     * @param              $value
     * @param              $where
     *
     * @return QueryBuilder
     */
    protected function processCondition(QueryBuilder $qb, $where, $parameter = null, $value = null)
    {
        if ($parameter) {
            $qb->setParameter($parameter, $value);
        }

        if (empty($this->queryConditions)) {
            $qb->where($where);
        } else {
            $qb->andWhere($where);
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param              $condition
     * @param              $value
     *
     * @return QueryBuilder
     */
    protected function processConditionNotEqual(QueryBuilder $qb, $condition, $value)
    {
        $condition = preg_replace('#(.*) !=#', '$1', $condition);
        $parameter = Inflector::transformConditionInConditionParameter($condition);

        if ($value === null) {
            $parameter = null;

            $where = $condition . ' IS NOT NULL';
        } else {
            if (is_array($value)) {
                $where = $condition . ' NOT IN (:' . $parameter . ')';
            } else {
                $where = $condition . ' != :' . $parameter;
            }
        }

        return $this->processCondition($qb, $where, $parameter, $value);
    }

    /**
     * @param QueryBuilder $qb
     * @param              $condition
     * @param              $value
     *
     * @return QueryBuilder
     */
    protected function processConditionLike(QueryBuilder $qb, $condition, $value)
    {
        $condition = preg_replace('#(.*) LIKE#', '$1', $condition);
        $parameter = Inflector::transformConditionInConditionParameter($condition . 'LIKE');

        $where = $condition . ' LIKE :' . $parameter;

        return $this->processCondition($qb, $where, $parameter, $value);
    }

    /**
     * @param QueryBuilder $qb
     * @param              $condition
     * @param              $value
     *
     * @return QueryBuilder
     */
    protected function processConditionSuperiorOrEqual(QueryBuilder $qb, $condition, $value)
    {
        $condition = preg_replace('#(.*) >=#', '$1', $condition);
        $parameter = Inflector::transformConditionInConditionParameter($condition . 'SuperiorOrEqual');

        $where = $condition . ' >= :' . $parameter;

        return $this->processCondition($qb, $where, $parameter, $value);
    }

    /**
     * @param QueryBuilder $qb
     * @param              $condition
     * @param              $value
     *
     * @return QueryBuilder
     */
    protected function processConditionSuperior(QueryBuilder $qb, $condition, $value)
    {
        $condition = preg_replace('#(.*) >#', '$1', $condition);
        $parameter = Inflector::transformConditionInConditionParameter($condition . 'Superior');

        $where = $condition . ' > :' . $parameter;

        return $this->processCondition($qb, $where, $parameter, $value);
    }

    /**
     * @param QueryBuilder $qb
     * @param              $condition
     * @param              $value
     *
     * @return QueryBuilder
     */
    protected function processConditionInferiorOrEqual(QueryBuilder $qb, $condition, $value)
    {
        $condition = preg_replace('#(.*) <=#', '$1', $condition);
        $parameter = Inflector::transformConditionInConditionParameter($condition . 'InferiorOrEqual');

        $where = $condition . ' <= :' . $parameter;

        return $this->processCondition($qb, $where, $parameter, $value);
    }

    /**
     * @param QueryBuilder $qb
     * @param              $condition
     * @param              $value
     *
     * @return QueryBuilder
     */
    protected function processConditionInferior(QueryBuilder $qb, $condition, $value)
    {
        $condition = preg_replace('#(.*) <#', '$1', $condition);
        $parameter = Inflector::transformConditionInConditionParameter($condition . 'Inferior');

        $where = $condition . ' < :' . $parameter;

        return $this->processCondition($qb, $where, $parameter, $value);
    }

    /**
     * @param QueryBuilder $qb
     * @param              $condition
     *
     * @return QueryBuilder
     */
    protected function processConditionNull(QueryBuilder $qb, $condition)
    {
        $where = $condition . ' IS NULL';

        return $this->processCondition($qb, $where);
    }

    /**
     * @param QueryBuilder $qb
     * @param              $condition
     * @param              $value
     *
     * @return QueryBuilder
     */
    protected function processConditionEqual(QueryBuilder $qb, $condition, $value)
    {
        $condition = preg_replace('#(.*) <#', '$1', $condition);
        $parameter = Inflector::transformConditionInConditionParameter($condition);

        if (is_array($value)) {
            $where = $condition . ' IN (:' . $parameter . ')';
        } else {
            $where = $condition . ' = :' . $parameter;
        }

        return $this->processCondition($qb, $where, $parameter, $value);
    }

    /**
     * @param QueryBuilder $qb
     * @param              $conditions
     * @param bool         $resetConditions
     *
     * @return QueryBuilder
     */
    protected function processConditions(QueryBuilder $qb, $conditions, $resetConditions = true)
    {
        if ($resetConditions) {
            $this->queryConditions = [];
        }

        if (!is_array($conditions)) {
            throw new InvalidTypeException('Array expected, "' . gettype($conditions) . '" received');
        }

        foreach ($conditions as $conditionKey => $conditionValue) {
            if (in_array($conditionKey, $this->queryConditions)) {
                continue;
            }

            if (isset($this->conditions[$conditionKey])) {
                // Si c'est une clé spécifique, on envoie le QueryBuilder à la fonction dédiée
                $qb = $this->{$this->conditions[$conditionKey]}($qb, $conditionValue);
            } else {
                // Sinon c'est un cas classique

                // Exemple de $condition "entity.field !="
                if (preg_match('#.* !=#', $conditionKey)) {
                    $qb = $this->processConditionNotEqual($qb, $conditionKey, $conditionValue);
                    // Exemple de $condition "entity.field LIKE"
                } elseif (preg_match('#.* LIKE#', $conditionKey)) {
                    $qb = $this->processConditionLike($qb, $conditionKey, $conditionValue);
                    // Exemple de $condition "entity.field >="
                } elseif (preg_match('#.* >=#', $conditionKey)) {
                    $qb = $this->processConditionSuperiorOrEqual($qb, $conditionKey, $conditionValue);
                    // Exemple de $condition "entity.field >"
                } elseif (preg_match('#.* >#', $conditionKey)) {
                    $qb = $this->processConditionSuperior($qb, $conditionKey, $conditionValue);
                    // Exemple de $condition "entity.field <="
                } elseif (preg_match('#.* <=#', $conditionKey)) {
                    $qb = $this->processConditionInferiorOrEqual($qb, $conditionKey, $conditionValue);
                    // Exemple de $condition "entity.field <"
                } elseif (preg_match('#.* <#', $conditionKey)) {
                    $qb = $this->processConditionInferior($qb, $conditionKey, $conditionValue);
                    // Exemple de $condition "entity.field NULL"
                } elseif ($conditionValue === null) {
                    $qb = $this->processConditionNull($qb, $conditionKey);
                    // Exemple de $condition "entity.field"
                } else {
                    $qb = $this->processConditionEqual($qb, $conditionKey, $conditionValue);
                }
            }

            $this->queryConditions[] = $conditionKey;
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return null
     */
    protected function getFirstResult(QueryBuilder $qb)
    {
        $qb->setMaxResults(1);

        $query = $this->getQuery($qb);

        $results = $query->getResult();

        if ($results) {
            return $results[0];
        }

        return null;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return array
     */
    protected function getAllResult(QueryBuilder $qb)
    {
        $query = $this->getQuery($qb);

        return $query->getResult();
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return mixed
     */
    protected function getOneResult(QueryBuilder $qb)
    {
        $query = $this->getQuery($qb);

        return $query->getOneOrNullResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param              $options
     *
     * @return array
     */
    protected function getPaginateResult(QueryBuilder $qb, $options)
    {
        $qb->getQuery();

        if (!empty($options['page'])) {
            $qb->setFirstResult(($options['page'] - 1) * $options['limit']);
        }

        if (!empty($options['limit'])) {
            $qb->setMaxResults($options['limit']);
        }

        $query = $this->getQuery($qb);

        $paginator = new Paginator($query, true);

        return [
            'entities' => $paginator->getIterator(),
            'count'    => $paginator->count(),
        ];
    }

    /**
     * @param string $type
     * @param array  $options
     *
     * @return array|bool|\Doctrine\ORM\QueryBuilder|Paginator|mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get($type = 'all', $options = [])
    {
        $this->queryJoins = [];

        $qb = $this->getBaseQuery();

        foreach ($options as $key => $option) {
            switch ($key) {
                case 'group':
                    $qb = $this->processGroups($qb, $option);
                    break;

                case 'joins':
                    $qb = $this->processJoins($qb, $option);
                    break;

                case 'limit':
                    $qb = $this->processLimit($qb, $option);
                    break;

                case 'order':
                    $qb = $this->processOrders($qb, $option);
                    break;

                case 'conditions':
                    $qb = $this->processConditions($qb, $option);
                    break;
            }
        }

        switch ($type) {
            case 'query':
                return $qb;
                break;

            case 'first':
                return $this->getFirstResult($qb);
                break;

            case 'all':
                return $this->getAllResult($qb);
                break;

            case 'one':
                return $this->getOneResult($qb);
                break;

            case 'paginate':
                $options = (!empty($options['paginate'])) ? $options['paginate'] : [];

                return $this->getPaginateResult($qb, $options);
                break;
        }

        return false;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return Query
     */
    private function getQuery(QueryBuilder $qb)
    {
        $query = $qb->getQuery();

        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        return $query;
    }

}
