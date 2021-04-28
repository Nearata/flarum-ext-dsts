<?php

namespace Nearata\Dsts;

use Flarum\Api\Serializer\BasicPostSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class CustomBasicPostSerializer
{
    public function __invoke(BasicPostSerializer $serializer, Post $post, array $attributes)
    {
        $discussionId = $post->discussion_id;
        $discussion = Discussion::where('id', $discussionId)->first();
        $firstPostId = $discussion->first_post_id;
        $postId = $post->id;

        if ($postId !== $firstPostId) {
            return $attributes;
        }

        $actor = $serializer->getActor();

        if ($actor->isGuest()) {
            $attributes['content'] = $this->getPlain('login');
            $attributes['contentHtml'] = $this->getHtml('login');
            return $attributes;
        }

        $actorId = $actor->id;

        if ($actor->id === $post->user_id) {
            return $attributes;
        }

        if ($this->requires('like') && !$actor->hasPermission('nearata.dsts.can_bypass_like')) {
            try {
                $liked = $post->likes()
                    ->where('user_id', $actorId)
                    ->exists();

                if (!$liked) {
                    $attributes['content'] = $this->getPlain('like');
                    $attributes['contentHtml'] = $this->getHtml('like');
                    return $attributes;
                }
            } catch (\Throwable $th) {}
        }

        if ($this->requires('reply') && !$actor->hasPermission('nearata.dsts.can_bypass_reply')) {
            $replied = $discussion->posts()
                ->where('user_id', $actorId)
                ->exists();

            if (!$replied) {
                $attributes['content'] = $this->getPlain('reply');
                $attributes['contentHtml'] = $this->getHtml('reply');
                return $attributes;
            }
        }

        return $attributes;
    }

    private function getPlain(string $key): string
    {
        return resolve(TranslatorInterface::class)
            ->get('nearata-dsts.forum.'.$key);
    }

    private function getHtml(string $key): string
    {
        return '<p class="Nearata-dsts">'. $this->getPlain($key) .'</p>';
    }

    private function requires(string $key): bool
    {
        return resolve(SettingsRepositoryInterface::class)
            ->get('nearata-dsts.admin.settings.require_'.$key, false);
    }
}
