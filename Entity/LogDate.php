<?php

namespace WH\LibBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class LogDate
 *
 * @package WH\LibBundle\Entity
 */
trait LogDate
{

	/**
	 * @var \DateTime
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="created", type="datetime")
	 */
	protected $created;

	/**
	 * @var \DateTime
	 *
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(name="updated", type="datetime")
	 */
	protected $updated;

	/**
	 * Set created
	 *
	 * @param \DateTime $created
	 *
	 * @return $this
	 */
	public function setCreated($created)
	{

		$this->created = $created;

		return $this;
	}

	/**
	 * Get created
	 *
	 * @return \DateTime
	 */
	public function getCreated()
	{

		return $this->created;
	}

	/**
	 * Set updated
	 *
	 * @param \DateTime $updated
	 *
	 * @return $this
	 */
	public function setUpdated($updated)
	{

		$this->updated = $updated;

		return $this;
	}

	/**
	 * Get updated
	 *
	 * @return \DateTime
	 */
	public function getUpdated()
	{

		return $this->updated;
	}

}