<?php

namespace Paw\SimpleSymfonyInertiaBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class InertiaExtension extends AbstractExtension
{
    final public function getFunctions(): array
    {
        return [
            new TwigFunction('renderInertiaPage', [$this, 'renderInertiaPage']),
        ];
    }

    final public function renderInertiaPage(object|array|string $page): Markup
    {
        return new Markup('<div id="app" data-page="'.htmlspecialchars(json_encode($page)).'"></div>', 'UTF-8');
    }
}
