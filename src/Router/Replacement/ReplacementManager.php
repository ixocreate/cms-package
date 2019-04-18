<?php
declare(strict_types=1);
namespace Ixocreate\Package\Cms\Router\Replacement;

use Ixocreate\ServiceManager\SubManager\SubManager;

final class ReplacementManager extends SubManager
{
    private $replacements = null;

    /**
     * @return ReplacementInterface[]
     */
    public function replacementServices(): array
    {
        if ($this->replacements === null) {
            $this->replacements = [];
            foreach ($this->getServices() as $service) {
                $this->replacements[] = $this->get($service);
            }
        }

        return $this->replacements;

    }
}
