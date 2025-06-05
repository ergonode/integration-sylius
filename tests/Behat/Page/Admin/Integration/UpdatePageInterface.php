<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusErgonodePlugin\Behat\Page\Admin\Integration;

interface UpdatePageInterface
{
    public function nameIt(string $name): void;

    public function enable(): void;

    public function disable(): void;

    public function specifyApiURL(string $url): void;

    public function specifyApiKey(string $key): void;

    public function specifyCategoryTreeCode(string $code): void;

    public function saveChanges(): void;
}
