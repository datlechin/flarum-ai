<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai;

use Datlechin\Ai\Driver\DriverInterface;
use Datlechin\Ai\Driver\Field;
use Datlechin\Ai\Event\Generated;
use Datlechin\Ai\Exception\NotConfiguredException;
use Datlechin\Ai\Exception\ProviderException;
use Flarum\Locale\TranslatorInterface;
use Flarum\Settings\SettingsRepositoryInterface;
use Generator;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\MessageBag;
use Psr\Log\LoggerInterface;

class Ai
{
    public function __construct(
        protected Container $container,
        protected SettingsRepositoryInterface $settings,
        protected Factory $validator,
        protected TranslatorInterface $translator,
        protected Dispatcher $events,
        protected LoggerInterface $logger,
    ) {
    }

    public function drivers(): array
    {
        return array_map(
            fn (string $driver) => $this->container->make($driver),
            $this->container->make('datlechin-ai.drivers')
        );
    }

    public function driverName(): string
    {
        return (string) $this->settings->get('datlechin-ai.driver');
    }

    public function driver(): ?DriverInterface
    {
        return $this->drivers()[$this->driverName()] ?? null;
    }

    public function model(): string
    {
        return trim((string) $this->settings->get($this->modelSetting()));
    }

    public function modelSetting(?string $driver = null): string
    {
        return 'datlechin-ai.'.($driver ?? $this->driverName()).'.model';
    }

    public function errors(): MessageBag
    {
        $driver = $this->driver();

        if (! $driver) {
            return new MessageBag(['datlechin-ai.driver' => $this->translator->trans('datlechin-ai.api.no_driver_message')]);
        }

        $fields = [...$driver->fields(), new Field($this->modelSetting(), 'text', 'datlechin-ai.admin.fields.model_label', rules: ['required', 'string'])];

        // Setting keys contain dots, which the validator would read as nested arrays.
        $validation = $this->validator->make(
            array_map(fn (Field $field) => $this->settings->get($field->setting), $fields),
            array_map(fn (Field $field) => $field->rules, $fields),
            [],
            array_map(fn (Field $field) => $this->translator->trans($field->label), $fields)
        );

        $errors = new MessageBag();

        foreach ($validation->errors()->messages() as $index => $messages) {
            $errors->merge([$fields[$index]->setting => $messages]);
        }

        return $errors;
    }

    public function isConfigured(): bool
    {
        return $this->errors()->isEmpty();
    }

    public function generate(Prompt $prompt): Response
    {
        $driver = $this->configuredDriver();

        try {
            $response = $driver->generate($this->settings, $this->model(), $prompt);
        } catch (ProviderException $e) {
            $this->report($e);

            throw $e;
        }

        $this->events->dispatch(new Generated($this->driverName(), $prompt, $response));

        return $response;
    }

    // Yields the text as it arrives and returns the Response, so read it with getReturn() once the loop ends.
    public function stream(Prompt $prompt): Generator
    {
        return $this->streamFrom($this->configuredDriver(), $prompt);
    }

    protected function streamFrom(DriverInterface $driver, Prompt $prompt): Generator
    {
        try {
            $response = yield from $driver->stream($this->settings, $this->model(), $prompt);
        } catch (ProviderException $e) {
            $this->report($e);

            throw $e;
        }

        $this->events->dispatch(new Generated($this->driverName(), $prompt, $response));

        return $response;
    }

    protected function report(ProviderException $e): void
    {
        $this->logger->error("[datlechin/flarum-ai] {$this->driverName()} ({$this->model()}): {$e->getMessage()}");
    }

    protected function configuredDriver(): DriverInterface
    {
        $driver = $this->driver();

        if (! $driver || ! $this->isConfigured()) {
            throw new NotConfiguredException('No AI provider is set up yet.');
        }

        return $driver;
    }
}
