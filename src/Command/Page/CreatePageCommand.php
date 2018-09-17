<?php
namespace KiwiSuite\Cms\Command\Page;

use KiwiSuite\CommandBus\Command\AbstractCommand;
use KiwiSuite\Contract\Validation\ValidatableInterface;
use KiwiSuite\Contract\Validation\ViolationCollectorInterface;

final class CreatePageCommand extends AbstractCommand implements ValidatableInterface
{
    /**
     * @return bool
     */
    public function execute(): bool
    {
        // TODO: Implement execute() method.
    }

    public static function serviceName(): string
    {
        return 'cms-create-page';
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
        // TODO: Implement validate() method.
    }
}