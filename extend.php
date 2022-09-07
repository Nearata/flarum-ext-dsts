<?php

namespace Nearata\Dsts;

use Flarum\Extend;
use Flarum\Api\Serializer\BasicPostSerializer;
use Flarum\Post\Event\Saving;
use Nearata\Dsts\Api\Serializer\ExtendBasicPostSerializer;
use Nearata\Dsts\Formatter\ExtendFormatter;
use Nearata\Dsts\Post\Event\ListenSaving;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\ApiSerializer(BasicPostSerializer::class))
        ->attributes(ExtendBasicPostSerializer::class),

    (new Extend\Formatter)
        ->configure(ExtendFormatter::class),

    (new Extend\Event)
        ->listen(Saving::class, ListenSaving::class)
];
