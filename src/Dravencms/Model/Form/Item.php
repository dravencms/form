<?php
namespace App\Model\Form\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Translatable\Translatable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class Item
 * @package App\Model\Form\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="formItem", uniqueConstraints={@UniqueConstraint(name="name_unique", columns={"name", "item_group_id"})})
 */
class Item extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_NUMBER = 'number';
    const TYPE_EMAIL = 'email';
    const TYPE_DATE = 'date';
    const TYPE_TEL = 'tel';
    const TYPE_URL = 'url';
    const TYPE_FILE = 'file';
    const TYPE_SELECT = 'select';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_RADIOLIST = 'radiolist';
    const TYPE_CHECKBOX ='checkbox';
    const TYPE_CHECKBOXLIST = 'checkboxlist';

    /** @var array */
    public static $typeList = [
        self::TYPE_TEXT => 'Text input',
        self::TYPE_TEXTAREA => 'Text area',
        self::TYPE_NUMBER => 'Number input',
        self::TYPE_EMAIL => 'Email input',
        self::TYPE_DATE => 'Date input',
        self::TYPE_URL => 'Url input',
        self::TYPE_TEL => 'Telephone input',
        self::TYPE_SELECT => 'Select list',
        self::TYPE_MULTISELECT => 'Multi select list',
        self::TYPE_RADIOLIST => 'Radio list',
        self::TYPE_CHECKBOX => 'Checkbox',
        self::TYPE_CHECKBOXLIST => 'Checkbox list'
    ];

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string",length=255)
     */
    private $type;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $defaultValue;

    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     */
    private $minValue;

    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     */
    private $maxValue;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $placeholder;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $title;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $required;

    /**
     * @var integer
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    private $locale;

    /**
     * @var ItemGroup
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="ItemGroup", inversedBy="items")
     * @ORM\JoinColumn(name="item_group_id", referencedColumnName="id")
     */
    private $itemGroup;

    /**
     * @var ArrayCollection|ItemOption[]
     * @ORM\OneToMany(targetEntity="ItemOption", mappedBy="item",cascade={"persist"})
     */
    private $itemOptions;

    /**
     * @var ArrayCollection|SaveValue[]
     * @ORM\OneToMany(targetEntity="SaveValue", mappedBy="item",cascade={"persist"})
     */
    private $saveValues;

    /**
     * Item constructor.
     * @param ItemGroup $itemGroup
     * @param $name
     * @param $title
     * @param $type
     * @param null $defaultValue
     * @param null $minValue
     * @param null $maxValue
     * @param null $placeholder
     * @param null $required
     */
    public function __construct(ItemGroup $itemGroup, $name, $title, $type, $defaultValue = null, $minValue = null, $maxValue = null, $placeholder = null, $required = null)
    {
        $this->itemGroup = $itemGroup;
        $this->name = $name;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
        $this->placeholder = $placeholder;
        $this->title = $title;
        $this->required = $required;

        $this->itemOptions = new ArrayCollection();
        $this->saveValues = new ArrayCollection();
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        if (!array_key_exists($type, self::$typeList))
        {
            throw new \InvalidArgumentException('$type have wrong value');
        }
        $this->type = $type;
    }

    /**
     * @param string $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param int $minValue
     */
    public function setMinValue($minValue)
    {
        $this->minValue = $minValue;
    }

    /**
     * @param int $maxValue
     */
    public function setMaxValue($maxValue)
    {
        $this->maxValue = $maxValue;
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
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return int
     */
    public function getMinValue()
    {
        return $this->minValue;
    }

    /**
     * @return int
     */
    public function getMaxValue()
    {
        return $this->maxValue;
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
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return ItemOption[]|ArrayCollection
     */
    public function getItemOptions()
    {
        return $this->itemOptions;
    }
}

