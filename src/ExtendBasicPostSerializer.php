<?php

namespace Nearata\Dsts;

use Flarum\Api\Serializer\BasicPostSerializer;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExtendBasicPostSerializer
{
    protected $translator;
    protected $settings;

    public function __construct(TranslatorInterface $translator, SettingsRepositoryInterface $settings)
    {
        $this->translator = $translator;
        $this->settings = $settings;
    }

    public function __invoke(BasicPostSerializer $serializer, Post $post, array $attributes)
    {
        $discussion = $post->discussion;
        $firstPostId = $discussion->first_post_id;
        $postId = $post->id;

        if ($postId !== $firstPostId) {
            return $attributes;
        }

        $actor = $serializer->getActor();

        if ($actor->isGuest()) {
            return [
                'content' => $this->getPlain('login'),
                'contentHtml' => $this->getHtml('login')
            ];
        }

        $actorId = $actor->id;

        if ($actor->id === $post->user_id) {
            return $attributes;
        }

        if ($this->requires('like') && $actor->cannot('nearata-dsts.bypass-like')) {
            $restricted = collect();

            try {
                $restricted = collect($discussion->tags)
                    ->filter(function ($value, $key) use ($actor) {
                        $id = $value->id;
                        return $value->is_restricted && $actor->cannot("tag$id.nearata-dsts.bypass-like");
                    });
            } catch (\Throwable $th) {}

            try {
                $liked = $post->likes()
                    ->where('user_id', $actorId)
                    ->exists();

                if ($restricted->count() && !$liked) {
                    return [
                        'content' => $this->getPlain('like'),
                        'contentHtml' => $this->getHtml('like')
                    ];
                } else if (!$liked) {
                    return [
                        'content' => $this->getPlain('like'),
                        'contentHtml' => $this->getHtml('like')
                    ];
                }
            } catch (\Throwable $th) {}
        }

        if ($this->requires('reply') && $actor->cannot('nearata-dsts.bypass-reply')) {
            $replied = $discussion->posts()
                ->where('user_id', $actorId)
                ->where('hidden_at', null)
                ->exists();
            $restricted = collect();

            try {
                $restricted = collect($discussion->tags)
                    ->filter(function ($value, $key) use ($actor) {
                        $id = $value->id;
                        return $value->is_restricted && $actor->cannot("tag$id.nearata-dsts.bypass-reply");
                    });
            } catch (\Throwable $th) {}

            if ($restricted->count() && !$replied) {
                return [
                    'content' => $this->getPlain('reply'),
                    'contentHtml' => $this->getHtml('reply')
                ];
            } else if (!$replied) {
                return [
                    'content' => $this->getPlain('reply'),
                    'contentHtml' => $this->getHtml('reply')
                ];
            }
        }

        return $attributes;
    }

    private function getPlain(string $key): string
    {
        return $this->translator->trans("nearata-dsts.forum.$key");
    }

    private function getHtml(string $key): string
    {
        return '<p class="Nearata-dsts">'. $this->getPlain($key) .'</p>';
    }

    private function requires(string $key): bool
    {
        return $this->settings
            ->get("nearata-dsts.admin.settings.require_$key");
    }
}
