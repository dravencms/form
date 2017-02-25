<?php
namespace Dravencms\Model\Form\Entities;

use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Nette;

/**
 * Class ItemGroupTranslation
 * @package App\Model\Form\Entities
 * @ORM\Entity
 * @ORM\Table(name="formItemGroupTranslation", uniqueConstraints={@UniqueConstraint(name="item_group_idlocale_id", columns={"item_group_id", "locale_id"})})
 */
class ItemGroupTranslation extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false,unique=true)
     */
    private $name;

    /**
     * @var ItemGroup
     * @ORM\ManyToOne(targetEntity="ItemGroup", inversedBy="translations")
     * @ORM\JoinColumn(name="item_group_id", referencedColumnName="id")
     */
    private $itemGroup;

    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * ItemGroupTranslation constructor.
     * @param ItemGroup $itemGroup
     * @param Locale $locale
     * @param $name
     */
    public function __construct(ItemGroup $itemGroup, Locale $locale, $name)
    {
        $this->name = $name;
        $this->itemGroup = $itemGroup;
        $this->locale = $locale;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param ItemGroup $itemGroup
     */
    public function setItemGroup($itemGroup)
    {
        $this->itemGroup = $itemGroup;
    }

    /**
     * @param Locale $locale
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
     * @return ItemGroup
     */
    public function getItemGroup()
    {
        return $this->itemGroup;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
}

