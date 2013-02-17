<?php

namespace Msi\Bundle\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Msi\Bundle\CmfBundle\Entity\Translatable;
use Knp\Menu\NodeInterface;

/**
 * @ORM\Table(name="cmf_menu")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @Gedmo\Tree(type="nested")
 * @ORM\HasLifecycleCallbacks
 */
class Menu extends Translatable implements NodeInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer")
     */
    protected $menu;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="children")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent", cascade={"remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", name="updated_at")
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="MenuTranslation", mappedBy="object", cascade={"persist", "remove"})
     */
    protected $translations;

    /**
     * @ORM\ManyToOne(targetEntity="Msi\Bundle\CmfBundle\Entity\Page")
     */
    protected $page;

    protected $options = array();

    /**
     * @ORM\Column(type="boolean")
     */
    protected $targetBlank;

    /**
     * @ORM\ManyToMany(targetEntity="Msi\Bundle\UserBundle\Entity\Group")
     * @ORM\JoinTable(name="cmf_menus_groups",
     *      joinColumns={@ORM\JoinColumn(name="menu_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->translations = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->targetBlank = false;
        $this->groups = new ArrayCollection();
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    public function getTargetBlank()
    {
        return $this->targetBlank;
    }

    public function setTargetBlank($targetBlank)
    {
        $this->targetBlank = $targetBlank;

        return $this;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getOptions()
    {
        $this->options['extras']['groups'] = $this->groups;
        $this->options['extras']['published'] = $this->getTranslation()->getPublished();

        if ($this->page) {
            if (!$this->page->getRoute()) {
                $this->options['route'] = 'msi_page_show';
                if (!$this->page->getHome()) {
                    $this->options['routeParameters'] = array('slug' => $this->page->getTranslation()->getSlug());
                }
            } else {
                $this->options['route'] = $this->page->getRoute();
            }
        } else if (preg_match('#^@#', $this->getTranslation()->getRoute())) {
            $this->options['route'] = substr($this->getTranslation()->getRoute(), 1);
        } else {
            $this->options['uri'] = $this->getTranslation()->getRoute();
        }

        if ($this->targetBlank) {
            $this->options['linkAttributes'] = array('target' => '_blank');
        }

        return $this->options;
    }

    public function setOption($k ,$v)
    {
        $this->options[$k] = $v;
    }

    public function addChild($child)
    {
        $this->children[] = $child;

        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getName()
    {
        return $this->getTranslation()->getName();
    }

    public function getLvl()
    {
        return $this->lvl;
    }

    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    public function getLft()
    {
        return $this->lft;
    }

    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    public function getRgt()
    {
        return $this->rgt;
    }

    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getMenu()
    {
        return $this->menu;
    }

    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getToTree()
    {
        $prefix = '';
        for ($i=0; $i < $this->lvl; $i++) {
            $prefix .= '- ';
        }

        if ($this->lvl === 0) {
            $name = $prefix.'Root';
        } else {
            $name = $prefix.$this->getTranslation()->getName();
        }

        return $name;
    }

    public function __toString()
    {
        return (string) $this->getTranslation()->getName();
    }
}
