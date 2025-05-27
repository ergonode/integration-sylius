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

    #[ORM\ManyToOne(targetEntity: Integration::class)]
    #[ORM\JoinColumn(name: 'integration_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?IntegrationInterface $integration = null;

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
}
