<?php

namespace Nearata\Dsts\Api\Serializer;

use Flarum\Api\Serializer\BasicUserSerializer;
use Flarum\User\User;

class BasicUserSerializerAttributes
{
    public function __invoke(BasicUserSerializer $serializer, User $user, array $attributes): array
    {
        return [
            'canNearataDstsUseBbcode' => $user->can('nearata-dsts.can-use-bbcode'),
        ];
    }
}
