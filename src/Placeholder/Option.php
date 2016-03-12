<?php
namespace nochso\WriteMe\Placeholder;

class Option
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var string
     */
    private $description;
    /**
     * @var mixed
     */
    private $default;
    /**
     * @var mixed
     */
    private $value;

    public function __construct($path, $description, $default = null)
    {
        $this->path = $path;
        $this->description = $description;
        $this->default = $default;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
