<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Console;

use Doctrine\ORM\EntityManagerInterface;
use Ixocreate\Application\Console\CommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class GenerateCache extends Command implements CommandInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * GenerateRoutingConsole constructor.
     */
    public function __construct(EntityManagerInterface $master)
    {
        parent::__construct(self::getCommandName());
        $this->entityManager = $master;
    }

    public static function getCommandName()
    {
        return 'cms:generate-cache';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = "UPDATE cms_sitemap as s INNER JOIN
(SELECT n.id,
         n.nestedLeft,
         COUNT(*)-1 AS level,
         ROUND ((n.nestedRight - n.nestedLeft - 1) / 2) AS offspring
    FROM cms_sitemap AS n,
         cms_sitemap AS p
   WHERE (n.nestedLeft BETWEEN p.nestedLeft AND p.nestedRight)
GROUP BY n.id, n.nestedLeft
ORDER BY n.nestedLeft) as sub ON (s.id = sub.id)
SET s.level=sub.level";
        $this->entityManager->getConnection()->exec($sql);

        $baseCommand = PHP_BINARY . ' ' . \getcwd() . '/' . \basename($_SERVER['SCRIPT_FILENAME']);

        $process = new Process($baseCommand . ' cms:generate-structure-cache');
        $process->setTimeout(null);
        $exitCode = $process->run();
        if ($exitCode !== 0) {
            $output->writeln("<error>Error will executing cms:generate-structure-cache</error>");
            $output->writeln($process->getErrorOutput());
        }

        $process = new Process($baseCommand . ' cms:generate-router-matcher-cache');
        $process->setTimeout(null);
        $exitCode = $process->run();
        if ($exitCode !== 0) {
            $output->writeln("<error>Error will executing cms:generate-router-matcher-cache</error>");
            $output->writeln($process->getErrorOutput());
        }

        $process = new Process($baseCommand . ' cms:generate-router-generator-cache');
        $process->setTimeout(null);
        $exitCode = $process->run();
        if ($exitCode !== 0) {
            $output->writeln("<error>Error will executing cms:generate-router-generator-cache</error>");
            $output->writeln($process->getErrorOutput());
        }

        return 0;
    }
}
