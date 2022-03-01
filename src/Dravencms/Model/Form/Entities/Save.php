<?php declare(strict_types = 1);
namespace Dravencms\Model\Form\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class Save
 * @package App\Model\Form\Entities
 * @ORM\Entity
 * @ORM\Table(name="formSave")
 */
class Save
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $ip;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $userAgent;

    /**
     * @var ArrayCollection|SaveValue[]
     * @ORM\OneToMany(targetEntity="SaveValue", mappedBy="save",cascade={"persist"})
     */
    private $saveValues;

    /**
     * Save constructor.
     * @param string $ip
     * @param string $userAgent
     */
    public function __construct(string $ip, string $userAgent)
    {
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->saveValues = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @return SaveValue[]|ArrayCollection
     */
    public function getSaveValues()
    {
        return $this->saveValues;
    }
}

