<?php

namespace Greenbean\Concrete5\GreenbeanString\Entity;

/**
* Represents a sandbox pages for the Greenbean String.
*
* @\Doctrine\ORM\Mapping\Entity(
* )
* @\Doctrine\ORM\Mapping\Table(
*     name="SandboxPages",
*     options={"comment": "Sandbox pages for the Greenbean String"}
* )
*/
class SandboxPage implements \JsonSerializable
{
    /**
    * Create a new instance.
    *
    * @param string $name the sandbox page name
    * @param bool $enabled is the sandbox page enabled (published)?
    * @param string $html the sandbox page html content
    *
    * @return static
    */
    public static function create($name, $html = null, $enabled = true)
    {
        $result = new static();
        $result->name = (string) $name;
        $result->html = (string) $html;
        $result->enabled = (bool) $enabled;

        return $result;
    }

    /**
    * Initializes the instance.
    */
    protected function __construct()
    {
    }

    /**
    * The sandbox page identifier.
    *
    * @\Doctrine\ORM\Mapping\Column(type="integer", options={"unsigned": true, "comment": "Sandbox page identifier"})
    * @\Doctrine\ORM\Mapping\Id
    * @\Doctrine\ORM\Mapping\GeneratedValue(strategy="AUTO")
    *
    * @var int|null
    */
    protected $id;

    /**
    * Get the sandbox page identifier.
    *
    * @return int|null
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * The sandbox page name.
    *
    * @\Doctrine\ORM\Mapping\Column(type="string", length=255, nullable=false, options={"comment": "Sandbox page name"})
    *
    * @var string
    */
    protected $name;

    /**
    * Get the sandbox page name.
    *
    * @return string
    */
    public function getName()
    {
        return $this->name;
    }

    /**
    * Set the sandbox page name.
    *
    * @param string $value
    *
    * @return $this
    */
    public function setName($value)
    {
        $this->name = (string) $value;

        return $this;
    }

    /**
    * The sandbox page html content.
    *
    * @\Doctrine\ORM\Mapping\Column(type="text", options={"comment": "Sandbox page HTML content"})
    *
    * @var string
    */
    protected $html;

    /**
    * Get the sandbox page HTML content.
    *
    * @return string
    */
    public function getHtml()
    {
        return $this->html;
    }

    /**
    * Set the sandbox page html content.
    *
    * @param string $value
    *
    * @return $this
    */
    public function setHtml($value)
    {
        $this->html = (string) $value;

        return $this;
    }

    /**
    * Is the sandbox page enabled (published)?
    *
    * @\Doctrine\ORM\Mapping\Column(type="boolean", nullable=false, options={"comment": "Is the sandbox page enabled (published)?"})
    *
    * @var bool
    */
    protected $enabled;

    /**
    * Is the sandbox page enabled (published)?
    *
    * @return bool
    */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
    * Is the sandbox page enabled (published)?
    *
    * @param bool $value
    *
    * @return $this
    */
    public function setIsEnabled($value)
    {
        $this->enabled = (bool) $value;

        return $this;
    }

    public function jsonSerialize() {
        return $this->asArray();
    }

    public function asArray() {
        return ['id'=>$this->id, 'name'=>$this->name, 'html'=>$this->html];
    }
}
