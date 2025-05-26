<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ToggleableTrait;

#[ORM\Entity]
#[ORM\Table(name: 'bitbag_sylius_ergonode_plugin_configuration')]
class Configuration implements ConfigurationInterface
{
    use ToggleableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    protected ?string $apiKey;

    #[ORM\Column(type: 'string', nullable: false)]
    protected ?string $apiUrl;

    /** @var bool $enabled */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected $enabled = true;

    #[ORM\Column(type: 'string', nullable: false)]
    protected ?string $categoryTreeCode;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
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
}
