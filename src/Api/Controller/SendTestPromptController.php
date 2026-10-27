<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Api\Controller;

use Datlechin\Ai\Ai;
use Datlechin\Ai\Exception\NotConfiguredException;
use Datlechin\Ai\Exception\ProviderException;
use Datlechin\Ai\Prompt;
use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Flarum\Locale\TranslatorInterface;
use Flarum\Settings\SettingsRepositoryInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SendTestPromptController implements RequestHandlerInterface
{
    public function __construct(
        protected Ai $ai,
        protected SettingsRepositoryInterface $settings,
        protected TranslatorInterface $translator,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        RequestUtil::getActor($request)->assertAdmin();

        $prompt = Prompt::make(
            'You are checking that a forum is connected to you. Reply with one short, friendly sentence.',
            'Say hello to the admins of '.$this->settings->get('forum_title').'.',
            maxOutputTokens: 200,
        );

        $start = hrtime(true);

        try {
            $response = $this->ai->generate($prompt);
        } catch (NotConfiguredException) {
            throw new ValidationException(['datlechin-ai.driver' => $this->translator->trans('datlechin-ai.api.not_configured_message')]);
        } catch (ProviderException $e) {
            throw new ValidationException(['datlechin-ai.driver' => $this->translator->trans('datlechin-ai.api.provider_error_message', ['message' => $e->getMessage()])]);
        }

        return new JsonResponse([
            'text' => $response->text,
            'model' => $response->model,
            'inputTokens' => $response->usage->inputTokens,
            'outputTokens' => $response->usage->outputTokens,
            'seconds' => round((hrtime(true) - $start) / 1e9, 1),
        ]);
    }
}
