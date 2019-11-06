<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Console;

use Ixocreate\Application\Console\CommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class GenerateCache extends Command implements CommandInterface
{
    /**
     * GenerateRoutingConsole constructor.
     */
    public function __construct()
    {
        parent::__construct(self::getCommandName());
    }

    public static function getCommandName()
    {
        return 'cms:generate-cache';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseCommand = PHP_BINARY . ' ' . \getcwd() . '/' . \basename($_SERVER['SCRIPT_FILENAME']);

        $process = new Process($baseCommand . ' cms:generate-structure-cache');
        $process->setTimeout(null);
        $process->run();

        $process = new Process($baseCommand . ' cms:generate-router-matcher-cache');
        $process->setTimeout(null);
        $process->run();

        $process = new Process($baseCommand . ' cms:generate-router-generator-cache');
        $process->setTimeout(null);
        $process->run();
    }
}
