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
use Sylius\Component\Resource\Model\ResourceInterface;

interface ResourceRelationMappingInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function setId(?int $id): void;

    public function getRelationType(): ?RelationType;

    public function setRelationType(?RelationType $relationType): void;

    public function getErgonodeValue(): ?string;

    public function setErgonodeValue(?string $ergonodeValue): void;

    public function getSyliusValue(): ?string;

    public function setSyliusValue(?string $syliusValue): void;

    public function isImportOnce(): bool;

    public function setImportOnce(bool $importOnce): void;

    public function getIntegration(): ?IntegrationInterface;

    public function setIntegration(?IntegrationInterface $integration): void;
}
