<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Behat\Page\Admin\Integration;

use Sylius\Behat\Page\Admin\Crud\UpdatePage as CrudUpdatePage;

final class UpdatePage extends CrudUpdatePage implements UpdatePageInterface
{
    public function nameIt(string $name): void
    {
        $this->getElement('name')->setValue($name);
    }

    public function enable(): void
    {
        $this->getElement('enabled')->setValue(true);
    }

    public function disable(): void
    {
        $this->getElement('enabled')->setValue(false);
    }

    public function specifyApiURL(string $url): void
    {
        $this->getElement('apiUrl')->setValue($url);
    }

    public function specifyApiKey(string $key): void
    {
        $this->getElement('apiKey')->setValue($key);
    }

    public function specifyCategoryTreeCode(string $code): void
    {
        $this->getElement('categoryTreeCode')->setValue($code);
    }

    public function saveChanges(): void
    {
        $this->getElement('submit')->click();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'name' => '[data-test-name]',
            'categoryTreeCode' => '[data-test-category-tree-code]',
            'enabled' => '[data-test-enabled]',
            'apiUrl' => '[data-test-api-url]',
            'apiKey' => '[data-test-api-key]',
            'submit' => '[data-test-update-changes-button]',
        ]);
    }
}
