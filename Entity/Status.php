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

    static $STATUS_DRAFT = 0;
    static $STATUS_PUBLISHED = 1;

    /**
     * @var array
     */
    static protected $statuses = [
        0 => 'Draft',
        1 => 'Published',
    ];

    /**
     * @return array
     */
    static public function getStatuses()
    {
        return self::$statuses;
    }

    /**
     * Status constructor.
     */
    public function __construct()
    {
        $this->status = self::$STATUS_DRAFT;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="integer")
     */
    protected $status;

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

}