<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Menu;

use Knp\Menu\Util\MenuManipulator;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function __construct(
        private readonly int $position,
    ) {
    }

    public function addErgonodeSection(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $ergonodeSection = $menu
            ->addChild('ergonode')
            ->setLabel('bitbag_ergonode.ui.ergonode')
            ->setLabelAttribute('icon', 'tabler:cube')
        ;
        (new MenuManipulator())->moveToPosition($ergonodeSection, $this->position);

        $ergonodeSection->addChild('integration', ['route' => 'bitbag_ergonode_admin_integration_index'])
                ->setLabel('bitbag_ergonode.ui.integrations')
        ;

        $ergonodeSection->addChild('resource_relation_mapping', ['route' => 'bitbag_ergonode_admin_resource_relation_mapping_index'])
            ->setLabel('bitbag_ergonode.ui.resource_relation_mappings')
        ;
    }
}
