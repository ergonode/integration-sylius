<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Tests\BitBag\SyliusErgonodePlugin\Behat\Page\Admin\Integration\CreatePageInterface;
use Tests\BitBag\SyliusErgonodePlugin\Behat\Page\Admin\Integration\IndexPageInterface;
use Tests\BitBag\SyliusErgonodePlugin\Behat\Page\Admin\Integration\UpdatePageInterface;
use Webmozart\Assert\Assert;

final class ManagingIntegrationsContext implements Context
{
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private IndexPageInterface $indexPage,
        private UpdatePageInterface $updatePage,
        private NotificationCheckerInterface $notificationChecker,
        private CreatePageInterface $createPage,
    ) {
    }

    #[When('I go to the integrations page')]
    public function iGoToTheIntegrationsPage(): void
    {
        $this->indexPage->open();
    }

    #[When('I delete this integration')]
    public function iDeleteThisIntegration(): void
    {
        $this->indexPage->deleteResourceOnPage([
            'name' => $this->sharedStorage->get('integration')->getName(),
        ]);
    }

    #[When('I want to edit this integration')]
    public function iWantToEditThisIntegration(): void
    {
        $this->updatePage->open(['id' => $this->sharedStorage->get('integration')->getId()]);
    }

    #[When('I update it')]
    public function iUpdateIt(): void
    {
        $this->updatePage->saveChanges();
    }

    #[When('I disable it')]
    public function iDisableIt(): void
    {
        $this->updatePage->disable();
    }

    #[Then('I should be notified that the integration has been deleted')]
    public function iShouldBeNotifiedThatTheIntegrationHasBeenDeleted(): void
    {
        $this->notificationChecker->checkNotification(
            'Integration has been successfully deleted.',
            NotificationType::success()
        );
    }

    #[Then('I should see empty list of integrations')]
    public function iShouldSeeEmptyListOfIntegrations(): void
    {
        Assert::true($this->indexPage->isEmpty());
    }


    #[Then('I should be notified that the integration has been successfully updated')]
    public function iShouldBeNotifiedThatTheIntegrationHasBeenSuccessfullyUpdated(): void
    {
        $this->notificationChecker->checkNotification(
            'Integration has been successfully updated.',
            NotificationType::success()
        );
    }

    #[Then('I should see that :integrationName has category tree code :categoryTree specified')]
    public function iShouldSeeThatHasCategoryTreeSpecified(string $integrationName, string $categoryTree): void
    {
        $this->indexPage->isSingleResourceOnPage([
            'name' => $integrationName,
            'category_tree_code' => $categoryTree,
        ]);
    }

    #[Then('this integration should be disabled')]
    public function thisIntegrationShouldBeDisabled(): void
    {
        $this->indexPage->isSingleResourceOnPage([
            'name' => $this->sharedStorage->get('integration')->getName(),
            'enabled' => false,
        ]);
    }

    #[When('I want go to creation page')]
    public function iWantGoToCreationPage(): void
    {
        $this->createPage->open();
    }
    #[When('I change category tree code to :code')]
    public function iChangeCategoryTreeCodeTo(string $code): void
    {
        $this->updatePage->specifyCategoryTreeCode($code);
    }


    #[When('I set category tree code to :code')]
    public function iSetCategoryTreeCodeTo(string $code): void
    {
        $this->createPage->specifyCategoryTreeCode($code);
    }

    #[When('I enable it')]
    public function iEnableIt(): void
    {
        $this->createPage->enable();
    }

    #[Then('I should be notified that the integration has invalid credentials')]
    public function iShouldBeNotifiedThatTheIntegrationHasInvalidCredentials(): void
    {
        Assert::same('An error occurred or provided credentials are invalid.', $this->createPage->getValidationErrors());
    }

    #[When('I set API URL address to :url')]
    public function iSetApiUrlAddressTo(string $url): void
    {
        $this->createPage->specifyApiURL($url);
    }

    #[When('I set API key to :key')]
    public function iSetApiKeyTo(string $key): void
    {
        $this->createPage->specifyApiKey($key);
    }

    #[When('I save it')]
    public function iSaveIt(): void
    {
        $this->createPage->create();
    }
}
