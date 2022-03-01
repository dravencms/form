<?php declare(strict_types = 1);
namespace Dravencms\Model\Form\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class Form
 * @package App\Model\Form\Entities
 * @ORM\Entity
 * @ORM\Table(name="formForm")
 */
class Form
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
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private $hookUrl;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isAntispam;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isSaveToDatabase;

    /**
     * @var ArrayCollection|ItemGroup[]
     * @ORM\OneToMany(targetEntity="ItemGroup", mappedBy="form",cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $itemGroups;

    /**
     * @var ArrayCollection|FormTranslation[]
     * @ORM\OneToMany(targetEntity="FormTranslation", mappedBy="form",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * Form constructor.
     * @param $name
     * @param $email
     * @param $email
     * @param bool $isActive
     * @param bool $isAntispam
     */
    public function __construct(
            string $name, 
            string $email = null, 
            string $hookUrl = null, 
            bool $isSaveToDatabase = true, 
            bool $isActive = true, 
            bool $isAntispam = true
            )
    {
        $this->name = $name;
        $this->email = $email;
        $this->isActive = $isActive;
        $this->isAntispam = $isAntispam;
        $this->hookUrl = $hookUrl;
        $this->isSaveToDatabase = $isSaveToDatabase;

        $this->itemGroups = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }


    /**
     * @param boolean $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email = null): void
    {
        $this->email = $email;
    }

    /**
     * @param boolean $isAntispam
     */
    public function setIsAntispam(bool $isAntispam): void
    {
        $this->isAntispam = $isAntispam;
    }

    /**
     * @param string $hookUrl
     */
    public function setHookUrl(string $hookUrl = null): void
    {
        $this->hookUrl = $hookUrl;
    }

    /**
     * @param boolean $isSaveToDatabase
     */
    public function setIsSaveToDatabase(bool $isSaveToDatabase): void
    {
        $this->isSaveToDatabase = $isSaveToDatabase;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return ItemGroup[]|ArrayCollection
     */
    public function getItemGroups()
    {
        return $this->itemGroups;
    }

    /**
     * @return ArrayCollection|FormTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return boolean
     */
    public function isAntispam(): bool
    {
        return $this->isAntispam;
    }

    /**
     * @return null|string
     */
    public function getHookUrl(): ?string
    {
        return $this->hookUrl;
    }

    /**
     * @return boolean
     */
    public function isSaveToDatabase(): bool
    {
        return $this->isSaveToDatabase;
    }
}

