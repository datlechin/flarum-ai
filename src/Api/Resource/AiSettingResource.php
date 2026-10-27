<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Api\Resource;

use Datlechin\Ai\Ai;
use Datlechin\Ai\Driver\DriverInterface;
use Datlechin\Ai\Driver\Field;
use Datlechin\Ai\Exception\ProviderException;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractResource;
use Flarum\Api\Resource\Contracts\Findable;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Cache\Repository;
use stdClass;
use Tobyz\JsonApiServer\Context;

class AiSettingResource extends AbstractResource implements Findable
{
    public function __construct(
        protected Ai $ai,
        protected SettingsRepositoryInterface $settings,
        protected Repository $cache,
    ) {
    }

    public function type(): string
    {
        return 'datlechin-ai-settings';
    }

    public function getId(object $model, Context $context): string
    {
        return '1';
    }

    public function id(Context $context): ?string
    {
        return '1';
    }

    public function find(string $id, Context $context): ?object
    {
        return new stdClass();
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Show::make()
                ->route('GET', '/')
                ->admin(),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Arr::make('drivers')
                ->get(fn () => array_map(fn (DriverInterface $driver) => [
                    'label' => $driver->label(),
                    'fields' => array_map(fn (Field $field) => $field->toArray(), $driver->fields()),
                ], $this->ai->drivers())),
            Schema\Arr::make('errors')
                ->get(fn () => $this->ai->errors()->toArray()),
            Schema\Arr::make('models')
                ->get(fn () => $this->models()),
        ];
    }

    protected function models(): array
    {
        $driver = $this->ai->driver();
        $fields = array_map(fn (Field $field) => $field->setting, $driver?->fields() ?? []);

        if (! $driver || $this->ai->errors()->hasAny($fields)) {
            return ['ids' => [], 'error' => null];
        }

        try {
            $models = $this->cache->remember(
                'datlechin-ai.models.'.sha1(serialize([$this->ai->driverName(), array_map($this->settings->get(...), $fields)])),
                3600,
                fn () => $driver->models($this->settings)
            );

            return ['ids' => $models, 'error' => null];
        } catch (ProviderException $e) {
            return ['ids' => [], 'error' => $e->getMessage()];
        }
    }
}
