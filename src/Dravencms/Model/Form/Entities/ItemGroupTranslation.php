<?php declare(strict_types = 1);
namespace Dravencms\Model\Form\Entities;

use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Nette;

/**
 * Class ItemGroupTranslation
 * @package App\Model\Form\Entities
 * @ORM\Entity
 * @ORM\Table(name="formItemGroupTranslation", uniqueConstraints={@UniqueConstraint(name="item_group_idlocale_id", columns={"item_group_id", "locale_id"})})
 */
class ItemGroupTranslation
{
    use Nette\SmartObject;
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
    public function __construct(ItemGroup $itemGroup, Locale $locale, string $name)
    {
        $this->name = $name;
        $this->itemGroup = $itemGroup;
        $this->locale = $locale;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param ItemGroup $itemGroup
     */
    public function setItemGroup(ItemGroup $itemGroup): void
    {
        $this->itemGroup = $itemGroup;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ItemGroup
     */
    public function getItemGroup(): ItemGroup
    {
        return $this->itemGroup;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }
}

