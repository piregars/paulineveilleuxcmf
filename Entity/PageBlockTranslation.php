<?php

namespace Msi\Bundle\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cmf_page_block_translation")
 * @ORM\Entity
 */
class PageBlockTranslation extends BlockTranslation
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="PageBlock", inversedBy="translations")
     */
    protected $object;

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }
}
