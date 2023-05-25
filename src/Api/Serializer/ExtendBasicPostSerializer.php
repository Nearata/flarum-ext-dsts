<?php

namespace Nearata\Dsts\Api\Serializer;

use Flarum\Api\Serializer\BasicPostSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
        if (! ($post instanceof CommentPost)) {
            return $attributes;
        }

        $attributes['nearataDsts'] = false;

        $actor = $serializer->getActor();

        if ($actor->isAdmin()) {
            return $attributes;
        }

        if ($actor->id === $post->user_id) {
            return $attributes;
        }

        $discussion = $post->discussion;

        $fofUpload = $this->settings->get('nearata-dsts.admin.settings.hide_only_files');
        $fofUploadLike = $this->settings->get('nearata-dsts.admin.settings.fof_upload.require_like');
        $fofUploadReply = $this->settings->get('nearata-dsts.admin.settings.fof_upload.require_reply');

        if (Str::contains($post->content, '[nearata-dsts') || ($fofUpload && Str::contains($post->content, '[upl-image-preview'))) {
            $post->content = preg_replace_callback('/\[nearata-dsts .*?\].*?\[\/nearata-dsts\]|\[upl-image-preview .*?\]/s', function ($m) use ($actor, $post, $discussion, $fofUploadLike, $fofUploadReply) {
                if (Str::contains($m[0], '[upl-image-preview')) {
                    if ($this->cannotBypassLogin($actor, $discussion)) {
                        return $this->getError('fof_upload.login');
                    }

                    if ($fofUploadLike && $this->notLiked($actor, $post)) {
                        return $this->getError('fof_upload.like');
                    }

                    if ($fofUploadReply && $this->notReplied($actor, $discussion)) {
                        return $this->getError('fof_upload.reply');
                    }

                    return $m[0];
                }

                if (Str::contains($m[0], 'login="true"') && $this->cannotBypassLogin($actor, $discussion)) {
                    return $this->getError('login');
                }

                if (Str::contains($m[0], 'like="true"') && $this->notLiked($actor, $post)) {
                    return $this->getError('like');
                }

                if (Str::contains($m[0], 'reply="true"') && $this->notReplied($actor, $discussion)) {
                    return $this->getError('reply');
                }

                return $m[0];
            }, $post->content);

            return $this->getResponse($post, $attributes, $serializer);
        }

        $onlyFirstPost = $this->settings->get('nearata-dsts.admin.settings.hide_only_first_post');

        if ($onlyFirstPost && $discussion->first_post_id !== $post->id) {
            return $attributes;
        }

        if ($this->cannotBypassLogin($actor, $discussion)) {
            $post->content = $this->getError('login');
        } elseif ($this->requires('like') && $this->notLiked($actor, $post)) {
            $post->content = $this->getError('like');
        } elseif ($this->requires('reply') && $this->notReplied($actor, $discussion)) {
            $post->content = $this->getError('reply');
        } else {
            return $attributes;
        }

        return $this->getResponse($post, $attributes, $serializer);
    }

    private function getError(string $key): string
    {
        $text = $this->translator->trans("nearata-dsts.forum.$key");

        return "[nearata-dsts-error] $text [/nearata-dsts-error]";
    }

    private function requires(string $key): bool
    {
        return $this->settings
            ->get("nearata-dsts.admin.settings.require_$key");
    }

    private function cannotBypassLogin(User $actor, Discussion $discussion): bool
    {
        $can = false;

        try {
            $can = ! collect($discussion->tags)
                ->filter(function ($value, $key) use ($actor) {
                    return $value->is_restricted && $actor->can('nearata-dsts.bypass-login', $value);
                })
                ->isEmpty();
        } catch (\Throwable $th) {
        }

        if ($can) {
            return false;
        }

        return $actor->isGuest();
    }

    private function notLiked(User $actor, Post $post): bool
    {
        $liked = false;

        try {
            $liked = $post->likes()
                ->where('user_id', $actor->id)
                ->exists();
        } catch (\Throwable $th) {
        }

        $cannot = false;

        try {
            $cannot = ! collect($post->discussion->tags)
                ->filter(function ($value, $key) use ($actor) {
                    return $value->is_restricted && $actor->cannot('nearata-dsts.bypass-like', $value);
                })
                ->isEmpty();
        } catch (\Throwable $th) {
        }

        if ($cannot) {
            $liked = false;
        }

        return ! $liked;
    }

    private function notReplied(User $actor, Discussion $discussion): bool
    {
        $replied = $discussion->posts()
            ->where('user_id', $actor->id)
            ->where('hidden_at', null)
            ->exists();

        $cannot = false;

        try {
            $cannot = ! collect($discussion->tags)
                ->filter(function ($value, $key) use ($actor) {
                    return $value->is_restricted && $actor->cannot('nearata-dsts.bypass-reply', $value);
                })
                ->isEmpty();
        } catch (\Throwable $th) {
        }

        if ($cannot) {
            $replied = false;
        }

        return ! $replied;
    }

    private function getResponse($post, $attributes, $serializer): array
    {
        $response = [
            'content' => $post->content,
            'contentHtml' => $post->formatContent($serializer->getRequest()),
            'nearataDsts' => true,
        ];

        if (! Arr::has($attributes, 'content')) {
            unset($response['content']);
        }

        if (! Arr::has($attributes, 'contentHtml')) {
            unset($response['contentHtml']);
        }

        return $response;
    }
}
