<?php declare(strict_types = 1);
namespace Dravencms\Model\Form\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class Item
 * @package App\Model\Form\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="formItem", uniqueConstraints={@UniqueConstraint(name="name_unique", columns={"name", "item_group_id"})})
 */
class Item
{
    use Nette\SmartObject;
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
     * @var integer
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var ArrayCollection|ItemTranslantion[]
     * @ORM\OneToMany(targetEntity="ItemTranslantion", mappedBy="item",cascade={"persist", "remove"})
     */
    private $translations;

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
     * @ORM\OrderBy({"position" = "ASC"})
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
     * @param $type
     * @param null $minValue
     * @param null $maxValue
     */
    public function __construct(ItemGroup $itemGroup, string $name, string $type, int $minValue = null, int $maxValue = null)
    {
        $this->itemGroup = $itemGroup;
        $this->name = $name;
        $this->type = $type;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;

        $this->itemOptions = new ArrayCollection();
        $this->saveValues = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        if (!array_key_exists($type, self::$typeList))
        {
            throw new \InvalidArgumentException('$type have wrong value');
        }
        $this->type = $type;
    }

    /**
     * @param int $minValue
     */
    public function setMinValue(int $minValue = null): void
    {
        $this->minValue = $minValue;
    }

    /**
     * @param int $maxValue
     */
    public function setMaxValue(int $maxValue = null): void
    {
        $this->maxValue = $maxValue;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return null|int
     */
    public function getMinValue(): ?int
    {
        return $this->minValue;
    }

    /**
     * @return null|int
     */
    public function getMaxValue(): ?int
    {
        return $this->maxValue;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return ItemGroup
     */
    public function getItemGroup(): ItemGroup
    {
        return $this->itemGroup;
    }
    
    /**
     * @return ItemOption[]|ArrayCollection
     */
    public function getItemOptions()
    {
        return $this->itemOptions;
    }

    /**
     * @return ArrayCollection|ItemTranslantion[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}

