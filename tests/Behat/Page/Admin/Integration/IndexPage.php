<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Behat\Page\Admin\Integration;

use Sylius\Behat\Page\Admin\Crud\IndexPage as CrudIndexPage;
use Webmozart\Assert\Assert;

final class IndexPage extends CrudIndexPage implements IndexPageInterface
{
    public function isEmpty(): bool
    {
        $element = $this->getDocument()->find('css', '.empty-title');
        Assert::notNull($element);

        return str_contains($element->getText(), 'No results found');
    }
}
