<?php
namespace Dravencms\Model\Form\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Dravencms\Model\Locale\Entities\Locale;
use Nette;

/**
 * Class ItemOption
 * @package App\Model\Form\Entities
 * @ORM\Entity
 * @ORM\Table(name="formItemOptionTranslation", uniqueConstraints={@UniqueConstraint(name="item_option_idlocale_id", columns={"item_option_id", "locale_id", "name"})})
 */
class ItemOptionTranslation
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $name;

    /**
     * @var ItemOption
     * @ORM\ManyToOne(targetEntity="ItemOption", inversedBy="translations")
     * @ORM\JoinColumn(name="item_option_id", referencedColumnName="id")
     */
    private $itemOption;

    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * ItemOptionTranslation constructor.
     * @param ItemOption $itemOption
     * @param Locale $locale
     * @param $name
     */
    public function __construct(ItemOption $itemOption, Locale $locale, $name)
    {
        $this->name = $name;
        $this->itemOption = $itemOption;
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
     * @param ItemOption $itemOption
     */
    public function setItemOption($itemOption)
    {
        $this->itemOption = $itemOption;
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
     * @return ItemOption
     */
    public function getItemOption()
    {
        return $this->itemOption;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
}

