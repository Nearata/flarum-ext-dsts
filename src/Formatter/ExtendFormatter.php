<?php

namespace Nearata\Dsts\Formatter;

use s9e\TextFormatter\Configurator;

class ExtendFormatter
{
    public function __invoke(Configurator $configurator)
    {
        $configurator->BBCodes->addCustom(
            '[nearata-dsts login="{TEXT}" like="{TEXT1}" reply="{TEXT2}"]{ANYTHING}[/nearata-dsts]',
            '<div class="nearata-dsts">{ANYTHING}</div>'
        );

        $configurator->BBCodes->addCustom(
            '[nearata-dsts-error]{ANYTHING}[/nearata-dsts-error]',
            '<div class="nearata-dsts hidden">{ANYTHING}</div>'
        );
    }
}
