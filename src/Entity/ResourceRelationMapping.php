<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Entity;

use BitBag\SyliusErgonodePlugin\Enum\RelationType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'bitbag_ergonode_resource_relation_mapping')]
class ResourceRelationMapping implements ResourceRelationMappingInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private ?RelationType $relationType = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $ergonodeValue = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $syliusValue = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $importOnce = false;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
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
}
