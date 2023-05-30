<?php

namespace Nearata\Dsts\Post\Listener;

use Flarum\Post\CommentPost;
use Flarum\Post\Event\Saving;
use s9e\TextFormatter\Utils;

class SavingListener
{
    public function handle(Saving $event)
    {
        if (! ($event->post instanceof CommentPost)) {
            return;
        }

        if (! isset($event->data['attributes']['content'])) {
            return;
        }

        // @todo: better way?
        $old = $event->post->parsed_content;
        $new = Utils::removeTag($event->post->parsed_content, 'NEARATA-DSTS');

        if (strlen($new) < strlen($old)) {
            $event->post->user->assertCan('nearata-dsts.can-use-bbcode');
        }
    }
}
