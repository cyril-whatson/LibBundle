<?php

namespace WH\LibBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Content
 *
 * @package WH\LibBundle\Entity
 */
trait Status
{

	/**
	 * Status constructor.
	 */
	public function __construct()
	{

		$this->status = 0;
	}

	/**
	 * @var array
	 */
	static protected $statuses = array(
		0 => 'Brouillon',
		1 => 'Publié',
	);

	/**
	 * @return array
	 */
	static public function getStatuses()
	{

		return self::$statuses;
	}

	/**
	 * @var string
	 *
	 * @ORM\Column(name="status", type="integer")
	 */
	private $status;

	/**
	 * Set status
	 *
	 * @param string $status
	 *
	 * @return $this
	 */
	public function setStatus($status)
	{

		$this->status = $status;

		return $this;
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function getStatus()
	{

		return $this->status;
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function getStatusText()
	{

		$statuses = self::$statuses;
		if (!empty($statuses[$this->status])) {

			return $statuses[$this->status];
		}

		return '';
	}

}