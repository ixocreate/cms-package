<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router\Replacement;

use Ixocreate\ServiceManager\SubManager\AbstractSubManager;

final class ReplacementManager extends AbstractSubManager
{
    private $replacements = null;

    /**
     * @return ReplacementInterface[]
     */
    public function replacementServices(): array
    {
        if ($this->replacements === null) {
            $this->replacements = [];
            foreach ($this->services() as $service) {
                $this->replacements[] = $this->get($service);
            }
        }

        return $this->replacements;
    }
}
