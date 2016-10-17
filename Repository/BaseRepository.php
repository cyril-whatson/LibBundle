<?php

namespace WH\LibBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class BaseRepository
 *
 * @package WH\LibBundle\Repository
 */
class BaseRepository extends EntityRepository implements RepositoryInterface
{

	use RepositoryFunctions;

}
