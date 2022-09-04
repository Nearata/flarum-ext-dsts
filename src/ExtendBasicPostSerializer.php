<?php

namespace Nearata\Dsts;

use Flarum\Api\Serializer\BasicPostSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Symfony\Contracts\Translation\TranslatorInterface;
use Illuminate\Support\Str;

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
        if (!($post instanceof CommentPost)) {
            return $attributes;
        }

        $actor = $serializer->getActor();

        if ($actor->isAdmin()) {
            return $attributes;
        }

        $discussion = $post->discussion;

        $fofUpload = $this->settings->get('nearata-dsts.admin.settings.hide_only_files');
        $fofUploadLike = $this->settings->get('nearata-dsts.admin.settings.fof_upload.require_like');
        $fofUploadReply = $this->settings->get('nearata-dsts.admin.settings.fof_upload.require_reply');

        if (Str::contains($post->content, '[nearata-dsts') || ($fofUpload && Str::contains($post->content, '[upl-image-preview'))) {
            $search = $fofUpload ? Str::matchAll('/\[upl-image-preview .*?\]/s', $post->content)
                : Str::matchAll('/\[nearata-dsts .*?\].*?\[\/nearata-dsts\]/s', $post->content);
            $replacements = collect();

            $search->each(function ($item) use ($replacements, $post, $actor, $discussion, $fofUploadLike, $fofUploadReply) {
                if (Str::contains($item, '[upl-image-preview')) {
                    if ($this->cannotBypassLogin($actor, $discussion)) {
                        $replacements->push($this->getPlain('fof_upload.login'));
                        return;
                    }

                    if ($fofUploadLike && $this->notLiked($actor, $post)) {
                        $replacements->push($this->getPlain('fof_upload.like'));
                        return;
                    }

                    if ($fofUploadReply && $this->notReplied($actor, $discussion)) {
                        $replacements->push($this->getPlain('fof_upload.reply'));
                        return;
                    }

                    $replacements->push($item);
                    return;
                }

                if (Str::contains($item, 'login="true"')) {
                    if ($this->cannotBypassLogin($actor, $discussion)) {
                        $replacements->push($this->getPlain('login'));
                        return;
                    }
                }

                if (Str::contains($item, 'like="true"') && $this->notLiked($actor, $post)) {
                    $replacements->push($this->getPlain('like'));
                    return;
                }

                if (Str::contains($item, 'reply="true"') && $this->notReplied($actor, $discussion)) {
                    $replacements->push($this->getPlain('reply'));
                    return;
                }

                $replacements->push($item);
            });

            if (!$replacements->isEmpty()) {
                $post->content = Str::replace($search->toArray(), $replacements->toArray(), $post->content);

                return [
                    'content' => $post->content,
                    'contentHtml' => $post->formatContent($serializer->getRequest())
                ];
            }

            return $attributes;
        }

        if ($discussion->first_post_id !== $post->id) {
            return $attributes;
        }

        if ($this->cannotBypassLogin($actor, $discussion)) {
            return [
                'content' => $this->getPlain('login'),
                'contentHtml' => $this->getHtml('login')
            ];
        }

        if ($actor->id === $post->user_id) {
            return $attributes;
        }

        if ($this->requires('like') && $this->notLiked($actor, $post)) {
            return [
                'content' => $this->getPlain('like'),
                'contentHtml' => $this->getHtml('like')
            ];
        }

        if ($this->requires('reply') && $this->notReplied($actor, $discussion)) {
            return [
                'content' => $this->getPlain('reply'),
                'contentHtml' => $this->getHtml('reply')
            ];
        }

        return $attributes;
    }

    private function getPlain(string $key): string
    {
        return $this->translator->trans("nearata-dsts.forum.$key");
    }

    private function getHtml(string $key): string
    {
        return '<p class="nearata-dsts">' . $this->getPlain($key) . '</p>';
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
            $can = !collect($discussion->tags)
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
            $cannot = !collect($post->discussion->tags)
                ->filter(function ($value, $key) use ($actor) {
                    return $value->is_restricted && $actor->cannot('nearata-dsts.bypass-like', $value);
                })
                ->isEmpty();
        } catch (\Throwable $th) {
        }

        if ($cannot) {
            $liked = false;
        }

        return !$liked;
    }

    private function notReplied(User $actor, Discussion $discussion): bool
    {
        $replied = $discussion->posts()
            ->where('user_id', $actor->id)
            ->where('hidden_at', null)
            ->exists();

        $cannot = false;

        try {
            $cannot = !collect($discussion->tags)
                ->filter(function ($value, $key) use ($actor) {
                    return $value->is_restricted && $actor->cannot('nearata-dsts.bypass-reply', $value);
                })
                ->isEmpty();
        } catch (\Throwable $th) {
        }

        if ($cannot) {
            $replied = false;
        }

        return !$replied;
    }
}
