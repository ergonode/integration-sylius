<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface ConfigurationInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function setId(?int $id): void;

    public function getName(): string;

    public function setName(string $name): void;

    public function getApiKey(): ?string;

    public function setApiKey(?string $apiKey): void;

    public function getApiUrl(): ?string;

    public function setApiUrl(?string $apiUrl): void;

    public function getCategoryTreeCode(): ?string;

    public function setCategoryTreeCode(?string $categoryTreeCode): void;
}
