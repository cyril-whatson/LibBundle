<?php

namespace WH\LibBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Content
 *
 * @package WH\LibBundle\Entity
 */
trait Content
{

	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=255, nullable=true)
	 */
	protected $title;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	protected $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="resume", type="text", nullable=true)
	 */
	protected $resume;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="body", type="text", nullable=true)
	 */
	protected $body;

	/**
	 * @var string
	 *
	 * @Gedmo\Slug(fields={"name"})
	 * @ORM\Column(name="slug", type="string", length=255, unique=true)
	 */
	protected $slug;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setName($name)
	{

		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName()
	{

		return $this->name;
	}

	/**
	 * Set title
	 *
	 * @param string $title
	 *
	 * @return $this
	 */
	public function setTitle($title)
	{

		$this->title = $title;

		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle()
	{

		return $this->title;
	}

	/**
	 * Set resume
	 *
	 * @param string $resume
	 *
	 * @return $this
	 */
	public function setResume($resume)
	{

		$this->resume = $resume;

		return $this;
	}

	/**
	 * Get resume
	 *
	 * @return string
	 */
	public function getResume()
	{

		return $this->resume;
	}

	/**
	 * Set body
	 *
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setBody($body)
	{

		$this->body = $body;

		return $this;
	}

	/**
	 * Get body
	 *
	 * @return string
	 */
	public function getBody()
	{

		return $this->body;
	}

	/**
	 * Set slug
	 *
	 * @param string $slug
	 *
	 * @return $this
	 */
	public function setSlug($slug)
	{

		$this->slug = $slug;

		return $this;
	}

	/**
	 * Get slug
	 *
	 * @return string
	 */
	public function getSlug()
	{

		return $this->slug;
	}

}