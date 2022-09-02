<?php

namespace Nearata\Dsts;

use s9e\TextFormatter\Configurator;

class ExtendFormatter
{
    public function __invoke(Configurator $configurator)
    {
        $configurator->BBCodes->addCustom(
            '[nearata-dsts login="{TEXT}" like="{TEXT1}" reply="{TEXT2}"]{ANYTHING}[/nearata-dsts]',
            '<div class="nearata-dsts">{ANYTHING}</div>'
        );
    }
}
