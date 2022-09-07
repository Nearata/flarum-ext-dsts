<?php

namespace Nearata\Dsts\Post\Event;

use Flarum\Post\CommentPost;
use Flarum\Post\Event\Saving;
use s9e\TextFormatter\Utils;

class ListenSaving
{
    public function handle(Saving $event)
    {
        $formatter = CommentPost::getFormatter();
        $parsed = $formatter->parse($event->post->content);
        $updated = Utils::removeTag($parsed, 'NEARATA-DSTS-ERROR');
        $unparsed = $formatter->unparse($updated);
        $event->post->content = $unparsed;
    }
}
