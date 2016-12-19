<?php

namespace WH\LibBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Position
 *
 * @package WH\LibBundle\Entity
 */
trait Position
{

	/**
	 * Status constructor.
	 */
	public function __construct()
	{
		$this->position = 0;
	}

	/**
	 * @var string
	 *
	 * @ORM\Column(name="position", type="integer", nullable=true)
	 */
	protected $position;

	/**
	 * Set position
	 *
	 * @param integer $position
	 *
	 * @return $this
	 */
	public function setPosition($position)
	{
		$this->position = $position;

		return $this;
	}

	/**
	 * Get position
	 *
	 * @return integer
	 */
	public function getPosition()
	{
		return $this->position;
	}
}