<?php

namespace Msi\Bundle\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="cmf_page_block")
 * @ORM\Entity
 */
class PageBlock extends Block
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Page", inversedBy="blocks", cascade={"persist"})
     * @ORM\JoinTable(name="cmf_page_blocks_pages")
     */
    protected $pages;

    /**
     * @ORM\OneToMany(targetEntity="PageBlockTranslation", mappedBy="object", cascade={"persist", "remove"})
     */
    protected $translations;

    public function __construct()
    {
        parent::__construct();
        $this->pages = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
