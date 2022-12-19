<?php declare(strict_types=1);

namespace Shopware\Core\Framework\App\Command;

use Shopware\Core\Framework\App\AppStateService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * @internal only for use by the app-system, will be considered internal from v6.4.0 onward
 *
 * @package core
 */
#[AsCommand(
    name: 'app:deactivate',
    description: 'Deactivates an app',
)]
class DeactivateAppCommand extends AbstractAppActivationCommand
{
    private const ACTION = 'deactivate';

    /**
     * @var AppStateService
     */
    private $appStateService;

    public function __construct(EntityRepository $appRepo, AppStateService $appStateService)
    {
        parent::__construct($appRepo, self::ACTION);
        $this->appStateService = $appStateService;
    }

    public function runAction(string $appId, Context $context): void
    {
        $this->appStateService->deactivateApp($appId, $context);
    }
}
