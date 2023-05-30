<?php

namespace Nearata\Dsts;

use Flarum\Extend;
use Flarum\Api\Serializer\BasicPostSerializer;
use Flarum\Api\Serializer\BasicUserSerializer;
use Flarum\Post\Event\Saving;
use Nearata\Dsts\Api\Serializer\BasicPostSerializerAttributes;
use Nearata\Dsts\Api\Serializer\BasicUserSerializerAttributes;
use Nearata\Dsts\Formatter\Configure;
use Nearata\Dsts\Post\Listener\SavingListener;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Formatter)
        ->configure(Configure::class),

    (new Extend\ApiSerializer(BasicPostSerializer::class))
        ->attributes(BasicPostSerializerAttributes::class),

    (new Extend\ApiSerializer(BasicUserSerializer::class))
        ->attributes(BasicUserSerializerAttributes::class),

    (new Extend\Event)
        ->listen(Saving::class, SavingListener::class),

    (new Extend\Settings)
        ->default('nearata-dsts.admin.settings.enabled', true)
        ->default('nearata-dsts.admin.settings.hide_only_first_post', false)
        ->default('nearata-dsts.admin.settings.require_reply', false)
        ->default('nearata-dsts.admin.settings.require_like', false)
        ->default('nearata-dsts.admin.settings.fof_upload.enabled', false)
        ->default('nearata-dsts.admin.settings.fof_upload.require_reply', false)
        ->default('nearata-dsts.admin.settings.fof_upload.require_like', false),

    (new Extend\View)
        ->namespace('nearata-dsts', __DIR__.'/views')
        ->extendNamespace('fof-upload.templates', __DIR__.'/views/fof/upload')
];
