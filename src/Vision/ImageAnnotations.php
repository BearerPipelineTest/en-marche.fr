<?php

namespace App\Vision;

use Symfony\Component\Serializer\Annotation\Groups;

class ImageAnnotations
{
    private const IDENTITY_DOCUMENT_LABEL = 'Identity document';
    private const NATIONAL_IDENTITY_CARD_LABEL = 'National identity card';
    private const FRENCH_IDENTITY_CARD_LABEL = 'carte d identité française';

    /**
     * @Groups({"ocr"})
     */
    private $labels;

    /**
     * @Groups({"ocr"})
     */
    private $webEntities;

    /**
     * @Groups({"ocr"})
     */
    private $text;

    public function __construct(array $labels, array $webEntities, string $text)
    {
        $this->labels = $labels;
        $this->webEntities = $webEntities;
        $this->text = $text;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function setLabels(array $labels): void
    {
        $this->labels = $labels;
    }

    public function getWebEntities(): array
    {
        return $this->webEntities;
    }

    public function setWebEntities(array $webEntities): void
    {
        $this->webEntities = $webEntities;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function isIdentityDocument(): bool
    {
        return \in_array(self::IDENTITY_DOCUMENT_LABEL, $this->webEntities, true)
            && \in_array(self::NATIONAL_IDENTITY_CARD_LABEL, $this->webEntities, true)
        ;
    }

    public function isFrenchNationalIdentityCard(): bool
    {
        return $this->isIdentityDocument() && \in_array(self::FRENCH_IDENTITY_CARD_LABEL, $this->labels, true);
    }

    public function getFirstNames(): array
    {
        preg_match('/\\nPrénom( )?\(s\):( )?(?<first_names>.+)\\n/', $this->text, $matches);

        return array_map(function (string $firstName) {
            return trim(mb_strtoupper($firstName));
        }, explode(',', $matches['first_names'] ?? null));
    }

    public function getLastName(): ?string
    {
        preg_match('/\\nNom( )?:( )?(?<last_name>.+)\\n/', $this->text, $matches);

        return $matches['last_name'] ?? null;
    }

    public function getBirthDate(): ?string
    {
        preg_match('/\\n(?<birth_date>.{2}\..{2}\..{4})\\n/', $this->text, $matches);

        return $matches['birth_date'] ?? null;
    }
}
