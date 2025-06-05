<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Behat\Page\Admin\Integration;

use Sylius\Behat\Page\Admin\Crud\IndexPage as CrudIndexPage;

final class IndexPage extends CrudIndexPage implements IndexPageInterface
{
    public function isEmpty(): bool
    {
        return str_contains($this->getDocument()->find('css', '.empty-title')->getText(), 'No results found');
    }
}
