<?php

namespace WH\LibBundle\Repository;

/**
 * Interface RepositoryInterface
 *
 * @package WH\LibBundle\Repository
 */
interface RepositoryInterface
{

	/**
	 * @return mixed
	 */
	public function getEntityNameQueryBuilder();

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getBaseQuery();

	/**
	 * @param string $type
	 * @param array  $options
	 *
	 * @return mixed
	 */
	public function get($type = 'all', $options = array());

	/**
	 * @param $condition
	 *
	 * @return bool
	 */
	public function handleCondition($condition);

	/**
	 * @param $order
	 *
	 * @return bool
	 */
	public function handleOrder($order);

	/**
	 * @param $group
	 *
	 * @return bool
	 */
	public function handleGroup($group);

}
