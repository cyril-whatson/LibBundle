<?php

namespace WH\LibBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Class BaseTreeRepository
 *
 * @package WH\LibBundle\Repository
 */
class BaseTreeRepository extends NestedTreeRepository implements RepositoryInterface
{

	use RepositoryFunctions;

}
