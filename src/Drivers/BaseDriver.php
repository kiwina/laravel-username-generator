<?php

namespace TaylorNetwork\UsernameGenerator\Drivers;

use TaylorNetwork\UsernameGenerator\Support\LoadsConfig;

abstract class BaseDriver
{
    use LoadsConfig;

    public $field;

    protected $original;

    public function __construct()
    {
        $this->loadConfig();
    }

    public function generate(string $text): string
    {
        $this->original = $text;

        $text = $this->preHook($text);
        $text = $this->convertCase($text);
        $text = $this->stripUnwantedCharacters($text);
        $text = $this->collapseWhitespace($text);
        $text = $this->addSeparator($text);
        $text = $this->makeUnique($text);
        $text = $this->postHook($text);

        return $text;
    }

    public function preHook(string $text): string
    {
        return $text;
    }

    public function convertCase(string $text): string
    {
        if (strtolower($this->getConfig('case')) === 'lower' || strtolower($this->getConfig('case')) === 'upper') {
            $case = 'strto'.strtolower($this->getConfig('case'));

            return $case($text);
        }

        return $text;
    }

    public function stripUnwantedCharacters(string $text): string
    {
        return preg_replace('/[^'.$this->getConfig('allowed_characters').']/', '', $text);
    }

    public function collapseWhitespace(string $text): string
    {
        return preg_replace('/\s+/', ' ', trim($text));
    }

    public function addSeparator(string $text): string
    {
        return preg_replace('/ /', $this->getConfig('separator'), $text);
    }

    public function makeUnique(string $text): string
    {
        if ($this->getConfig('unique') && $this->model() && method_exists($this->model(), 'findSimilarUsernames')) {
            if (($similar = count($this->model()->findSimilarUsernames($text))) > 0) {
                return $text.$this->getConfig('separator').$similar;
            }
        }

        return $text;
    }

    public function postHook(string $text): string
    {
        return $text;
    }

    public function getOriginal(): string
    {
        return $this->original;
    }
}
