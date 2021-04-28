<?php

namespace Nearata\Dsts;

use Flarum\Extend;
use Flarum\Api\Serializer\BasicPostSerializer;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),
    new Extend\Locales(__DIR__ . '/resources/locale'),
    (new Extend\ApiSerializer(BasicPostSerializer::class))
        ->attributes(CustomBasicPostSerializer::class)
];
