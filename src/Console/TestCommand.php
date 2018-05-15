<?php
namespace KiwiSuite\Cms\Console;

use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Contract\Command\CommandInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TestCommand extends Command implements CommandInterface
{
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    public function __construct(SitemapRepository $sitemapRepository)
    {
        parent::__construct(self::getCommandName());

        $this->sitemapRepository = $sitemapRepository;
    }

    public static function getCommandName()
    {
        return 'test:test';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $child = new Sitemap([
            'id' => Uuid::uuid4()->toString(),
            'pageType' => 'test',
        ]);

        $parent = $this->sitemapRepository->find("86142097-bf97-4eb1-bd67-cc5d0dbdd4c2");

        $this->sitemapRepository->insertAsFirstChild($child, $parent);
    }
}
