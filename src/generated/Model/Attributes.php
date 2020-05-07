<?php

namespace MonsieurBiz\SyliusSearchPlugin\generated\Model;

class Attributes
{
    /**
     * 
     *
     * @var string|null
     */
    protected $code;
    /**
     * 
     *
     * @var string|null
     */
    protected $name;
    /**
     * 
     *
     * @var string[]|null
     */
    protected $value;
    /**
     * 
     *
     * @var string|null
     */
    protected $locale;
    /**
     * 
     *
     * @var int|null
     */
    protected $score;
    /**
     * 
     *
     * @return string|null
     */
    public function getCode() : ?string
    {
        return $this->code;
    }
    /**
     * 
     *
     * @param string|null $code
     *
     * @return self
     */
    public function setCode(?string $code) : self
    {
        $this->code = $code;
        return $this;
    }
    /**
     * 
     *
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }
    /**
     * 
     *
     * @param string|null $name
     *
     * @return self
     */
    public function setName(?string $name) : self
    {
        $this->name = $name;
        return $this;
    }
    /**
     * 
     *
     * @return string[]|null
     */
    public function getValue() : ?array
    {
        return $this->value;
    }
    /**
     * 
     *
     * @param string[]|null $value
     *
     * @return self
     */
    public function setValue(?array $value) : self
    {
        $this->value = $value;
        return $this;
    }
    /**
     * 
     *
     * @return string|null
     */
    public function getLocale() : ?string
    {
        return $this->locale;
    }
    /**
     * 
     *
     * @param string|null $locale
     *
     * @return self
     */
    public function setLocale(?string $locale) : self
    {
        $this->locale = $locale;
        return $this;
    }
    /**
     * 
     *
     * @return int|null
     */
    public function getScore() : ?int
    {
        return $this->score;
    }
    /**
     * 
     *
     * @param int|null $score
     *
     * @return self
     */
    public function setScore(?int $score) : self
    {
        $this->score = $score;
        return $this;
    }
}