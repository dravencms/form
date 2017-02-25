<?php
namespace Dravencms\Model\Form\Entities;

use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class ItemTranslantion
 * @package App\Model\Form\Entities
 * @ORM\Entity
 * @ORM\Table(name="formItemTranslantion")
 */
class ItemTranslantion extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $defaultValue;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $placeholder;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $required;

    /**
     * @var Item
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="translations")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;

    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * ItemTranslantion constructor.
     * @param Item $item
     * @param Locale $locale
     * @param $title
     * @param null $defaultValue
     * @param null $placeholder
     * @param null $required
     */
    public function __construct(Item $item, Locale $locale, $title, $defaultValue = null, $placeholder = null, $required = null)
    {
        $this->item = $item;
        $this->locale = $locale;
        $this->defaultValue = $defaultValue;
        $this->placeholder = $placeholder;
        $this->title = $title;
        $this->required = $required;
    }

    /**
     * @param string $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param string $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
}

