<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Entity;

use Sylius\Component\Resource\Model\ToggleableTrait;

class Integration implements IntegrationInterface
{
    use ToggleableTrait;

    protected ?int $id = null;

    protected ?string $code = null;

    protected ?string $name = null;

    protected ?string $apiKey = null;

    protected ?string $apiUrl = null;

    protected ?string $mainLanguageCode = null;

    protected ?string $categoryTreeCode = null;

    protected ?string $categoryStreamCursorEnd = null;

    protected ?string $categoryDeletedStreamCursorEnd = null;

    protected ?string $attributeStreamCursorEnd = null;

    protected ?string $attributeDeletedStreamCursorEnd = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getApiUrl(): ?string
    {
        return $this->apiUrl;
    }

    public function setApiUrl(?string $apiUrl): void
    {
        $this->apiUrl = $apiUrl;
    }

    public function getCategoryTreeCode(): ?string
    {
        return $this->categoryTreeCode;
    }

    public function setCategoryTreeCode(?string $categoryTreeCode): void
    {
        $this->categoryTreeCode = $categoryTreeCode;
    }

    public function getCategoryStreamCursorEnd(): ?string
    {
        return $this->categoryStreamCursorEnd;
    }

    public function setCategoryStreamCursorEnd(?string $categoryStreamCursorEnd): void
    {
        $this->categoryStreamCursorEnd = $categoryStreamCursorEnd;
    }

    public function getCategoryDeletedStreamCursorEnd(): ?string
    {
        return $this->categoryDeletedStreamCursorEnd;
    }

    public function setCategoryDeletedStreamCursorEnd(?string $categoryDeletedStreamCursorEnd): void
    {
        $this->categoryDeletedStreamCursorEnd = $categoryDeletedStreamCursorEnd;
    }

    public function getMainLanguageCode(): ?string
    {
        return $this->mainLanguageCode;
    }

    public function setMainLanguageCode(?string $mainLanguageCode): void
    {
        $this->mainLanguageCode = $mainLanguageCode;
    }

    public function getAttributeStreamCursorEnd(): ?string
    {
        return $this->attributeStreamCursorEnd;
    }

    public function setAttributeStreamCursorEnd(?string $attributeStreamCursorEnd): void
    {
        $this->attributeStreamCursorEnd = $attributeStreamCursorEnd;
    }

    public function getAttributeDeletedStreamCursorEnd(): ?string
    {
        return $this->attributeDeletedStreamCursorEnd;
    }

    public function setAttributeDeletedStreamCursorEnd(?string $attributeDeletedStreamCursorEnd): void
    {
        $this->attributeDeletedStreamCursorEnd = $attributeDeletedStreamCursorEnd;
    }
}
