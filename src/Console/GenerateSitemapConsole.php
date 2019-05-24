<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Console;

use Ixocreate\Application\Console\CommandInterface;
use Ixocreate\Cms\Command\Seo\GenerateSitemapCommand;
use Ixocreate\CommandBus\CommandBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSitemapConsole extends Command implements CommandInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * TestConsole constructor.
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        parent::__construct(self::getCommandName());

        $this->commandBus = $commandBus;
    }

    public static function getCommandName()
    {
        return 'cms:generate-sitemap';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandBus->command(GenerateSitemapCommand::class, []);
    }
}
