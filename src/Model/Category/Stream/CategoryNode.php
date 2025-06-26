<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Model\Category\Stream;

class CategoryNode
{
    private string $code;

    /** @var array<LocalizedText> */
    private array $name;

    private AttributeList $attributeList;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return array<LocalizedText>
     */
    public function getName(): array
    {
        return $this->name;
    }

    /**
     * @param array<LocalizedText> $name
     */
    public function setName(array $name): void
    {
        $this->name = $name;
    }

    public function getAttributeList(): AttributeList
    {
        return $this->attributeList;
    }

    public function setAttributeList(AttributeList $attributeList): void
    {
        $this->attributeList = $attributeList;
    }

    public function getAttributeListNodes(): array
    {
        return $this->attributeList->getEdges();
    }
}
