<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Entity;

use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use Sylius\Component\Resource\Model\ResourceInterface;

interface ResourceRelationMappingInterface extends ResourceInterface
{
    public function getId(): ?string;

    public function setId(?string $id): void;

    public function getRelationType(): ?RelationType;

    public function setRelationType(?RelationType $relationType): void;

    public function getErgonodeValue(): ?string;

    public function setErgonodeValue(?string $ergonodeValue): void;

    public function getSyliusValue(): ?string;

    public function setSyliusValue(?string $syliusValue): void;

    public function isImportOnce(): bool;

    public function setImportOnce(bool $importOnce): void;
}
