<?php declare(strict_types = 1);
namespace Dravencms\Model\Form\Entities;

use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class ItemTranslantion
 * @package App\Model\Form\Entities
 * @ORM\Entity
 * @ORM\Table(name="formItemTranslantion")
 */
class ItemTranslantion
{
    use Nette\SmartObject;
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
     * @param string $title
     * @param null $defaultValue
     * @param null $placeholder
     * @param null $required
     */
    public function __construct(Item $item, Locale $locale, string $title, string $defaultValue = null, string $placeholder = null, string $required = null)
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
    public function setDefaultValue(string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param string $placeholder
     */
    public function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param boolean $required
     */
    public function setRequired(string $required = null): void
    {
        $this->required = $required;
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
    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getRequired(): ?string
    {
        return $this->required;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }
}

