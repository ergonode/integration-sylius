<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Behat\Page\Admin\Integration;

use Behat\Mink\Exception\ElementNotFoundException;
use Sylius\Behat\Behaviour\SpecifiesItsField;
use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

final class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    use SpecifiesItsField;

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

    public function getValidationErrors(): string
    {
        $validationMessage = $this->getDocument()->find('css', 'form .alert.alert-danger');
        if (null === $validationMessage) {
            throw new ElementNotFoundException($this->getSession(), 'Validation message', 'css', 'form .alert.alert-danger');
        }

        return $validationMessage->getText();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'name' => '[data-test-name]',
            'categoryTreeCode' => '[data-test-category-tree-code]',
            'enabled' => '[data-test-enabled]',
            'apiUrl' => '[data-test-api-url]',
            'apiKey' => '[data-test-api-key]',
        ]);
    }
}
