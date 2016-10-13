<?php

namespace Toro\Bundle\WidgetBundle\Twig;

interface WidgetInterface
{
    /**
     * Widget build, css, js, and more ..
     */
    public function build();

    /**
     * @param array $defaultOptions
     */
    public function setDefaultOptions(array $defaultOptions = []);

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions();

    /**
     * @param \Twig_Environment $env
     * @param array $options
     *
     * @return string
     */
    public function render(\Twig_Environment $env, array $options = []);

    /**
     * The widget name
     *
     * @return string
     */
    public function getName();
}
