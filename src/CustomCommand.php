<?php

namespace Nearata\Dsts;

use Flarum\Console\AbstractCommand;
use Flarum\Extension\ExtensionManager;

class CustomCommand extends AbstractCommand
{
    protected $extensions;

    public function __construct(ExtensionManager $extensions)
    {
        parent::__construct();
        $this->extensions = $extensions;
    }

    protected function configure()
    {
        $this->setName('customcommand');
    }

    protected function fire()
    {
        $this->info('Custom Command.');
        //exec("composer --version", $output);
        //$this->info( implode( PHP_EOL, $output ) );
        //$this->info($this->extensions->getExtensions());
    }
}
