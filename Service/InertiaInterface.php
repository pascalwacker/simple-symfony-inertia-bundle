<?php

namespace Paw\SimpleSymfonyInertiaBundle\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

interface InertiaInterface
{
    public function setShared(string $key, mixed $value = null): self;
    public function getShared(string $key = null): mixed;

    public function setViewData(string $key, mixed $value = null): self;
    public function getViewData(string $key = null): mixed;

    public function setContext(string $key, mixed $value = null): self;
    public function getContext(string $key = null): mixed;

    public function setVersion(string $version): self;
    public function getVersion(): ?string;

    public function setRootView(string $rootView): self;
    public function getRootView(): string;

    public function render(string $component, array $props = [], array $viewData = [], array $context = [], mixed $url = null): Response;

    public function redirect(string|RedirectResponse $url): Response;
}
