<?php

namespace Paw\SimpleSymfonyInertiaBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

class Inertia implements InertiaInterface
{
    protected array $sharedProps = [];
    protected array $sharedViewData = [];
    protected array $sharedContext = [];
    protected ?string $version = null;

    public function __construct(protected string $rootView, private readonly Environment $engine, private readonly RequestStack $requestStack, private readonly ?SerializerInterface $serializer = null)
    {
    }

    public function setShared(string $key, mixed $value = null): self
    {
        $this->sharedProps[$key] = $value;

        return $this;
    }

    public function getShared(string $key = null): mixed
    {
        if ($key) {
            return $this->sharedProps[$key] ?? null;
        }

        return $this->sharedProps;
    }

    public function setViewData(string $key, mixed $value = null): self
    {
        $this->sharedViewData[$key] = $value;

        return $this;
    }

    public function getViewData(string $key = null): mixed
    {
        if ($key) {
            return $this->sharedViewData[$key] ?? null;
        }

        return $this->sharedViewData;
    }

    public function setContext(string $key, mixed $value = null): self
    {
        $this->sharedContext[$key] = $value;

        return $this;
    }

    public function getContext(string $key = null): mixed
    {
        if ($key) {
            return $this->sharedContext[$key] ?? null;
        }

        return $this->sharedContext;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function setRootView(string $rootView): self
    {
        $this->rootView = $rootView;

        return $this;
    }

    public function getRootView(): string
    {
        return $this->rootView;
    }

    public function render(string $component, array $props = [], array $viewData = [], array $context = [], mixed $url = null): Response
    {
        $context = array_merge($this->sharedContext, $context);
        $viewData = array_merge($this->sharedViewData, $viewData);
        $props = array_merge($this->sharedProps, $props);
        $request = $this->requestStack->getCurrentRequest();
        $url = $url ?? $request->getRequestUri();

        $only = array_filter(explode(',', $request->headers->get('X-Inertia-Partial-Data') ?? ''));
        $props = ($only && $request->headers->get('X-Inertia-Partial-Component') === $component)
            ? array_intersect_key($props, array_flip($only))
            : $props;

        $version = $this->version;
        $page = $this->serialize(compact('component', 'props', 'url', 'version'), $context);

        if ($request->headers->get('X-Inertia')) {
            return new JsonResponse($page, 200, [
                'Vary' => 'Accept',
                'X-Inertia' => true,
            ]);
        }

        $response = new Response();
        $response->setContent($this->engine->render($this->rootView, compact('page', 'viewData')));

        return $response;
    }

    private function serialize(array $page, array $context = []): array
    {
        if (null !== $this->serializer) {
            $json = $this->serializer->serialize($page, 'json', array_merge([
                'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function () {
                    return null;
                },
                AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true,
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            ], $context));
        } else {
            $json = json_encode($page);
        }

        return (array)json_decode($json, false);
    }

    public function redirect(string|RedirectResponse $url): Response
    {
        if ($url instanceof RedirectResponse) {
            $url = $url->getTargetUrl();
        }

        if ($this->requestStack->getCurrentRequest()->headers->has('X-Inertia')) {
            return new Response('', Response::HTTP_CONFLICT, ['X-Inertia-Location' => $url]);
        }

        return new RedirectResponse($url);
    }
}
