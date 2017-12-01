<?php

namespace Toro\Bundle\WidgetBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractWidget extends \Twig_Extension implements WidgetInterface
{
    // To use it must implement ContainerAwareWidgetInterface
    use ContainerAwareTrait;

    // To use it must implement ChannelAwareWidgetInterface
    use ChannelContextTrait;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var array
     */
    private $defaultOptions = [];

    /**
     * @var bool
     */
    private $rendered = false;

    /**
     * @var bool
     */
    protected $renderedCheck = true;

    /**
     * @param RouterInterface $router
     */
    final public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param array $defaultOptions
     */
    final public function setDefaultOptions(array $defaultOptions = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->defaultOptions = $resolver->resolve($defaultOptions);
    }

    /**
     * {@inheritdoc}
     */
    function build()
    {
        // TODO: Implement build() method.
    }

    /**
     * {@inheritdoc}
     */
    final public function getFunctions()
    {
        $name = $this->getName();

        if (!preg_match('/^(wg_)/', $name)) {
            throw new \RuntimeException(sprintf("Widget name MUST start with `wg_` but got `%s`.", $name));
        }

        return [
            new \Twig_Function($name, [$this, 'render'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function resolverOptions(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->runtimeOptions($resolver);

        return $resolver->resolve(array_replace_recursive($this->defaultOptions, $options));
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function runtimeOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    final public function render(\Twig_Environment $env, array $options = [])
    {
        if ($this->renderedCheck && $this->rendered) {
            // TODO: just warning, widget should be compile on demand (cms-compiler)
            /*throw new \RuntimeException(sprintf(
                "Strictly check multiple rendering the same widget!\n" .
                "Consider to use inline style twig setter: set %s = %s() to reuse this widget or" .
                "to ignore this check set `this->renderedCheck = false` on `getData()` method.",
                $this->getName(), $this->getName()
            ));*/
        }

        // resolver runtime twig options
        $options = $this->resolverOptions($options);
        $data = [];

        if ('away' === $options['visibility']) {
            $data = $this->getData($options);
        }

        $this->rendered = true;

        $template = $options['template'];

        if (empty($options['remote']['url'])) {
            $url = $this->router->generate($options['remote']['route'], $options);
            $options['remote']['url'] = explode('?', $url)[0];
        }

        // todo: session check DoS refresh
        if (false === $options['auto_refresh']) {
            unset($options['auto_refresh']);
            unset($options['auto_refresh_timer']);
        }

        $optionsData = [
            'width',
            'margin',
            'scripts',
            'styles',
            'script_callbacks',
            'css',
            'style',
            'wg_css',
            'wg_style',
        ];

        $scripts = (array) $options['scripts'];
        $styles = (array) $options['styles'];

        return $env->render($template, array_merge($this->getOptionData($options, $optionsData), [
            'data' => $data,
            'options' => $options,
            'scripts' => $scripts,
            'styles' => $styles,
            'name' => $this->getName(),
        ]));
    }

    /**
     * @param array $options
     * @param array $keys
     * @return array
     */
    protected function getOptionData(array &$options, array $keys)
    {
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $options[$key];
            // TODO: this should store in cache inteastof remove them.
            // unset mean that user defined options will not send to server via ajax call like onscreen visibility.
            unset($options[$key]);
        }

        return $data;
    }

    /**
     * Configure widget options, Usually need to override/overload by sub class.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'title' => '',
            'title_classes' => '',
            'template' => null,
            'visibility' => 'away',
            'auto_refresh' => false,
            'auto_refresh_timer' => 10000, // 10 secs
            'remote' => [
                'url' => null,
                'route' => 'toro_widget_render',
                //'route_params' => [],
                'method' => 'GET',
            ],
            // widget js control options
            'control' => [],
            'callback' => [],
            'styles' => null,
            'scripts' => null,
            'script_callbacks' => null,
            'style' => null,
            'css' => null,
            'wg_style' => null,
            'wg_css' => null,
            'width' => 'auto',
            'margin' => 'auto',
            'scroll' => null, // top | current | null
        ]);

        $resolver->setAllowedTypes('title', ['string']);
        $resolver->setAllowedTypes('title_classes', ['string']);
        $resolver->setAllowedTypes('auto_refresh', ['bool', 'string']);
        $resolver->setAllowedTypes('auto_refresh_timer', ['int']);
        $resolver->setAllowedTypes('visibility', ['string']);
        $resolver->setAllowedTypes('template', ['null', 'string']);
        $resolver->setAllowedTypes('remote', ['null', 'array']);
        $resolver->setAllowedTypes('control', ['null', 'array']);
        $resolver->setAllowedTypes('callback', ['null', 'array']);
        $resolver->setAllowedTypes('styles', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('scripts', ['null', 'string', 'array']);
        $resolver->setAllowedTypes('script_callbacks', ['null', 'string']);
        $resolver->setAllowedTypes('style', ['null', 'string']);
        $resolver->setAllowedTypes('css', ['null', 'string']);
        $resolver->setAllowedTypes('wg_style', ['null', 'string']);
        $resolver->setAllowedTypes('wg_css', ['null', 'string']);
        $resolver->setAllowedTypes('width', ['string']);
        $resolver->setAllowedTypes('margin', ['string']);
        $resolver->setAllowedTypes('scroll', ['string', 'null']);

        $resolver->setRequired(['template']);

        $resolver->setAllowedValues('visibility', ['away', 'onscreen']);
        $resolver->setAllowedValues('auto_refresh', [true, false, 'onscreen']);

        $resolver->setNormalizer('auto_refresh_timer', function (Options $options, $value) {
            if ($value < 10000) {
                $value = 10000;
            }

            return $value;
        });
    }

    /**
     * @param string $content
     *
     * @return int
     */
    protected function isHasTwigTag($content)
    {
        return preg_match('/\{\{(.*)\}\}/', $content);
    }

    /**
     * @param array $options by_reference option allow to modify
     *
     * @return mixed
     */
    abstract protected function getData(array &$options = []);

    /**
     * {@inheritdoc}
     */
    abstract static public function getName();
}
