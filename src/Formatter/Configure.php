<?php

namespace Nearata\Dsts\Formatter;

use Illuminate\Contracts\View\Factory;
use s9e\TextFormatter\Configurator;

class Configure
{
    public function __invoke(Configurator $configurator)
    {
        $configurator->BBCodes->addCustom(
            '[nearata-dsts login="{SIMPLETEXT1}" like="{SIMPLETEXT2}" reply="{SIMPLETEXT3}" bbcode_error="{SIMPLETEXT4?}"]{ANYTHING}[/nearata-dsts]',
            resolve(Factory::class)->make('nearata-dsts::bbcode')
        );
    }
}
