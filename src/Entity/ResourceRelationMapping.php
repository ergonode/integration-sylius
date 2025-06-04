<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Entity;

use BitBag\SyliusErgonodePlugin\Enum\RelationType;

class ResourceRelationMapping implements ResourceRelationMappingInterface
{
    private ?int $id = null;

    private ?RelationType $relationType = null;

    private ?string $ergonodeValue = null;

    private ?string $syliusValue = null;

    private bool $importOnce = false;

    private ?IntegrationInterface $integration = null;

    private ?string $channelCode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getRelationType(): ?RelationType
    {
        return $this->relationType;
    }

    public function setRelationType(?RelationType $relationType): void
    {
        $this->relationType = $relationType;
    }

    public function getErgonodeValue(): ?string
    {
        return $this->ergonodeValue;
    }

    public function setErgonodeValue(?string $ergonodeValue): void
    {
        $this->ergonodeValue = $ergonodeValue;
    }

    public function getSyliusValue(): ?string
    {
        return $this->syliusValue;
    }

    public function setSyliusValue(?string $syliusValue): void
    {
        $this->syliusValue = $syliusValue;
    }

    public function isImportOnce(): bool
    {
        return $this->importOnce;
    }

    public function setImportOnce(bool $importOnce): void
    {
        $this->importOnce = $importOnce;
    }

    public function getIntegration(): ?IntegrationInterface
    {
        return $this->integration;
    }

    public function setIntegration(?IntegrationInterface $integration): void
    {
        $this->integration = $integration;
    }

    public function getChannelCode(): ?string
    {
        return $this->channelCode;
    }

    public function setChannelCode(?string $channelCode): void
    {
        $this->channelCode = $channelCode;
    }
}
