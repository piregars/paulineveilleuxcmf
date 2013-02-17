<?php

namespace Msi\Bundle\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="uniq_object_id_locale", columns={"object_id", "locale"})})
 * @ORM\MappedSuperclass
 */
class BlockTranslation
{
    /**
     * @ORM\Column()
     */
    protected $locale;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $published;

    /**
     * @ORM\Column(type="array")
     */
    protected $settings;

    public function __construct()
    {
        $this->published = false;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getPublished()
    {
        return $this->published;
    }

    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    public function getSetting($key)
    {
        return array_key_exists($key, $this->settings) ? $this->settings[$key] : null;
    }

    public function setSetting($key, $value)
    {
        $this->settings[$key] = $value;

        return $this;
    }
}
