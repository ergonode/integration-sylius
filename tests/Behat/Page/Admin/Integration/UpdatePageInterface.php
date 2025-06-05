<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Behat\Page\Admin\Integration;

use Sylius\Behat\Page\Admin\Crud\UpdatePageInterface as UpdatePageInterfaceBase;

interface UpdatePageInterface extends UpdatePageInterfaceBase
{
    public function nameIt(string $name): void;

    public function enable(): void;

    public function disable(): void;

    public function specifyApiURL(string $url): void;

    public function specifyApiKey(string $key): void;

    public function specifyCategoryTreeCode(string $code): void;

    public function saveChanges(): void;
}
