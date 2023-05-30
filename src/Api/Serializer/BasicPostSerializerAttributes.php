<?php

namespace Nearata\Dsts\Api\Serializer;

use Flarum\Api\Serializer\BasicPostSerializer;
use Flarum\Extension\ExtensionManager;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Support\Arr;
use s9e\TextFormatter\Utils;
use Symfony\Contracts\Translation\TranslatorInterface;

class BasicPostSerializerAttributes
{
    /**
     * @var string
     */
    private $loremIpsum = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
        sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
        quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var ExtensionManager
     */
    protected $extensions;

    public function __construct(TranslatorInterface $translator, SettingsRepositoryInterface $settings, ExtensionManager $extensions)
    {
        $this->translator = $translator;
        $this->settings = $settings;
        $this->extensions = $extensions;
    }

    public function __invoke(BasicPostSerializer $serializer, Post $post, array $attributes): array
    {
        if (! ($post instanceof CommentPost)) {
            return $attributes;
        }

        $actor = $serializer->getActor();

        if ($actor->id === $post->user_id) {
            return $attributes;
        }

        if ($actor->isAdmin()) {
            return $attributes;
        }

        $attributes['nearata-dsts.bbcode.isHidden'] = $this->handleBbcode($post, $actor);

        if ($attributes['nearata-dsts.bbcode.isHidden']) {
            return ['contentHtml' => $post->formatContent($serializer->getRequest())];
        }

        if ($this->settings->get('nearata-dsts.admin.settings.enabled')) {
            $attributes['nearataDstsError'] = $this->handleGlobal($post, $actor);
        }

        if ($this->settings->get('nearata-dsts.admin.settings.fof_upload.enabled')) {
            $attributes['nearataDstsErrorFofUpload'] = $this->handleFofUpload($post, $actor);
        }

        if (! is_null(Arr::get($attributes, 'nearataDstsError'))) {
            $post->content = $this->loremIpsum;
            $attributes['contentHtml'] = $post->formatContent($serializer->getRequest());
        }

        if ($post->content !== $this->loremIpsum && ! is_null(Arr::get($attributes, 'nearataDstsErrorFofUpload'))) {
            $attributes['contentHtml'] = $post->formatContent($serializer->getRequest());
        }

        return $attributes;
    }

    private function handleBbcode(CommentPost $post, User $actor): bool
    {
        $hasBBCode = false;

        $post->parsed_content = Utils::replaceAttributes($post->parsed_content, 'NEARATA-DSTS', function (array $attributes) use (&$hasBBCode, $post, $actor) {
            $hasBBCode = true;

            if ($attributes['login'] == 'true' && ! $this->hasLoggedIn($post, $actor)) {
                $error = $this->translator->trans('nearata-dsts.forum.post.bbcode.login_text');
            } elseif ($attributes['reply'] == 'true' && ! $this->hasReplied($post, $actor)) {
                $error = $this->translator->trans('nearata-dsts.forum.post.bbcode.reply_text');
            } elseif ($attributes['like'] == 'true' && ! $this->hasLiked($post, $actor)) {
                $error = $this->translator->trans('nearata-dsts.forum.post.bbcode.like_text');
            } else {
                $error = '';
            }

            $attributes['bbcode_error'] = $error;

            return $attributes;
        });

        return $hasBBCode;
    }

    private function handleGlobal(CommentPost $post, User $actor): ?string
    {
        if ($this->settings->get('nearata-dsts.admin.settings.hide_only_first_post')) {
            if ($post->id !== $post->discussion->first_post_id) {
                return null;
            }
        }

        if (! $this->hasLoggedIn($post, $actor)) {
            return $this->translator->trans('nearata-dsts.forum.post.login_tooltip');
        }

        if ($this->settings->get('nearata-dsts.admin.settings.require_reply')) {
            if (! $this->hasReplied($post, $actor)) {
                return $this->translator->trans('nearata-dsts.forum.post.reply_tooltip');
            }
        }

        if ($this->settings->get('nearata-dsts.admin.settings.require_like')) {
            if (! $this->hasLiked($post, $actor)) {
                return $this->translator->trans('nearata-dsts.forum.post.like_tooltip');
            }
        }

        return null;
    }

    private function handleFofUpload(CommentPost $post, User $actor): ?string
    {
        if (! $this->extensions->isEnabled('fof-upload')) {
            return null;
        }

        if (! $this->hasLoggedIn($post, $actor)) {
            $this->hideFofUpload($post);

            return $this->translator->trans('nearata-dsts.forum.post.fof_upload.login_text');
        }

        if ($this->settings->get('nearata-dsts.admin.settings.fof_upload.require_reply')) {
            if (! $this->hasReplied($post, $actor)) {
                $this->hideFofUpload($post);

                return $this->translator->trans('nearata-dsts.forum.post.fof_upload.reply_text');
            }
        }

        if ($this->settings->get('nearata-dsts.admin.settings.fof_upload.require_like')) {
            if (! $this->hasLiked($post, $actor)) {
                $this->hideFofUpload($post);

                return $this->translator->trans('nearata-dsts.forum.post.fof_upload.like_text');
            }
        }

        return null;
    }

    private function hideFofUpload(CommentPost $post): void
    {
        $post->parsed_content = Utils::replaceAttributes($post->parsed_content, 'UPL-FILE', function (array $attributes) {
            $attributes['uuid'] = '';

            return $attributes;
        });
    }

    private function hasLoggedIn(CommentPost $post, User $actor): bool
    {
        return $actor->can('nearata-dsts.bypass-login', $post->discussion) || ! $actor->isGuest();
    }

    private function hasReplied(CommentPost $post, User $actor): bool
    {
        if ($actor->can('nearata-dsts.bypass-reply', $post->discussion)) {
            return true;
        }

        return $post->discussion->comments()
            ->where('user_id', $actor->id)
            ->exists();
    }

    private function hasLiked(CommentPost $post, User $actor): bool
    {
        if (! $this->extensions->isEnabled('flarum-likes')) {
            return true;
        }

        if ($actor->can('nearata-dsts.bypass-like', $post->discussion)) {
            return true;
        }

        return $post->likes()
            ->where('user_id', $actor->id)
            ->exists();
    }
}
