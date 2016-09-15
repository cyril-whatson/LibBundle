<?php

namespace WH\LibBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Tree
 *
 * @package WH\LibBundle\Entity
 */
trait Tree
{

	/**
	 * @Gedmo\TreeLeft
	 * @ORM\Column(type="integer")
	 */
	private $lft;

	/**
	 * @Gedmo\TreeLevel
	 * @ORM\Column(type="integer")
	 */
	private $lvl;

	/**
	 * @Gedmo\TreeRight
	 * @ORM\Column(type="integer")
	 */
	private $rgt;

	/**
	 * Set lft
	 *
	 * @param integer $lft
	 *
	 * @return $this
	 */
	public function setLft($lft)
	{

		$this->lft = $lft;

		return $this;
	}

	/**
	 * Get lft
	 *
	 * @return integer
	 */
	public function getLft()
	{

		return $this->lft;
	}

	/**
	 * Set lvl
	 *
	 * @param integer $lvl
	 *
	 * @return $this
	 */
	public function setLvl($lvl)
	{

		$this->lvl = $lvl;

		return $this;
	}

	/**
	 * Get lvl
	 *
	 * @return integer
	 */
	public function getLvl()
	{

		return $this->lvl;
	}

	/**
	 * Set rgt
	 *
	 * @param integer $rgt
	 *
	 * @return $this
	 */
	public function setRgt($rgt)
	{

		$this->rgt = $rgt;

		return $this;
	}

	/**
	 * Get rgt
	 *
	 * @return integer
	 */
	public function getRgt()
	{

		return $this->rgt;
	}

}