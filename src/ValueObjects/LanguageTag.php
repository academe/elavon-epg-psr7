<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\ValueObjects;

use Academe\Elavon\Epg\Psr7\Contracts\ValueObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Language Tag value object.
 *
 * Represents a validated IETF BCP 47 language tag.
 * Examples: "en", "en-GB", "zh-Hans-CN"
 * Serializes to a simple string value.
 */
class LanguageTag implements ValueObject
{
    /**
     * @param string $tag The language tag
     */
    public function __construct(
        public readonly string $tag,
    ) {
        $this->validate();
    }

    /**
     * Creates a LanguageTag instance from JSON-compatible data.
     *
     * @param mixed $data String language tag
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('Language tag must be a string');
        }

        return new self(tag: $data);
    }

    /**
     * Converts the LanguageTag to JSON-compatible data.
     *
     * Returns a simple string representation.
     *
     * @return string
     */
    public function toData(): mixed
    {
        return $this->tag;
    }

    /**
     * Validates the language tag format.
     *
     * Validates against IETF BCP 47 format:
     * - Language code (2-3 lowercase letters or 4-8 alphanumeric)
     * - Optional script subtag (4 letters, first uppercase)
     * - Optional region subtag (2 uppercase letters or 3 digits)
     * - Optional variant/extension subtags
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if (empty($this->tag)) {
            throw new InvalidArgumentException('Language tag cannot be empty');
        }

        if (strlen($this->tag) > 255) {
            throw new InvalidArgumentException(
                'Language tag cannot exceed 255 characters'
            );
        }

        // IETF BCP 47 language tag pattern
        // Simplified pattern that covers most common cases:
        // - Primary language subtag: 2-3 or 5-8 alphanumeric characters
        // - Optional script subtag: 4 alphabetic characters
        // - Optional region subtag: 2 alphabetic or 3 numeric characters
        // - Optional variant/extension subtags
        $pattern = '/^[a-zA-Z]{2,3}(?:-[a-zA-Z]{4})?(?:-(?:[a-zA-Z]{2}|[0-9]{3}))?(?:-[a-zA-Z0-9]+)*$/';

        if (!preg_match($pattern, $this->tag)) {
            throw new InvalidArgumentException(
                "Invalid language tag format: '{$this->tag}'. Expected IETF BCP 47 format (e.g., 'en', 'en-GB', 'zh-Hans-CN')."
            );
        }
    }

    /**
     * Returns the language tag as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->tag;
    }

    /**
     * Gets the primary language subtag.
     *
     * @return string The language code (e.g., "en" from "en-GB")
     */
    public function getLanguage(): string
    {
        $parts = explode('-', $this->tag);
        return $parts[0];
    }

    /**
     * Gets the region subtag if present.
     *
     * @return string|null The region code (e.g., "GB" from "en-GB") or null
     */
    public function getRegion(): ?string
    {
        $parts = explode('-', $this->tag);

        // Look for region subtag (2 letters or 3 digits)
        foreach (array_slice($parts, 1) as $part) {
            if (preg_match('/^[A-Z]{2}$/', $part) || preg_match('/^[0-9]{3}$/', $part)) {
                return $part;
            }
        }

        return null;
    }
}
