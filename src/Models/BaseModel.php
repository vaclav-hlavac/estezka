<?php

namespace App\Models;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use JsonSerializable;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Abstract base class for all models.
 *
 * Provides utility methods for:
 * - Argument validation (`requiredArgumentsControl`)
 * - Bulk attribute setting with validation (`setAttributes`)
 * - Value formatting for database storage
 * - Date/time conversion
 * - Enforces implementation of `getId()` and `toDatabase()` in derived models.
 *
 * Implements JsonSerializable to allow JSON encoding of model instances.
 */
abstract class BaseModel implements JsonSerializable
{
    /**
     * Validates that required fields are present and optionally not empty.
     *
     * @param array $data The data to validate (usually constructor input).
     * @param array $notNullArguments Keys that must exist in $data.
     * @param array $notEmptyArguments Keys that must not be empty in $data.
     * @return void
     * @throws InvalidArgumentException If a required field is missing or empty.
     */
    protected function requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments = []): void
    {
        foreach ($notNullArguments as $notNullArgument) {
            if (!isset($data[$notNullArgument])) {
                throw new InvalidArgumentException("Missing required field: $notNullArgument", code: 400);
            }
        }

        foreach ($notEmptyArguments as $notEmptyArgument) {
            if (empty($data[$notEmptyArgument])) {
                throw new InvalidArgumentException("Missing required field: $notEmptyArgument", code: 400);
            }
        }
    }

    /**
     * Sets model properties from an associative array, validating property existence.
     *
     * @param array $data Associative array where keys match property names.
     * @return void
     * @throws InvalidArgumentException If a key does not correspond to an existing property.
     */
    public function setAttributes(array $data): void {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            } else {
                $className = static::class;
                throw new InvalidArgumentException("Model {$className} does not have a field: $key", 400);
            }
        }
    }

    /**
     * Converts a value to a database-friendly format.
     *
     * Converts:
     * - DateTimeInterface → MySQL datetime string
     * - boolean → 1 or 0
     *
     * @param mixed $value The value to format.
     * @return mixed Formatted value for database storage.
     */
    protected function formatForDatabase($value)
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return $value;
    }

    /**
     * Converts string or DateTime value to DateTime instance, or null otherwise.
     *
     * @param mixed $value The input value (string, DateTime or other).
     * @return DateTime|null Parsed DateTime instance or null.
     */
    protected function convertToDateTimeIfNeeded($value): ?\DateTime
    {
        if ($value instanceof DateTime) {
            return $value;
        }

        if (is_string($value)) {
            return new DateTime($value);
        }

        return null;
    }

    /**
     * Returns the model's primary key value.
     *
     * @return mixed The ID value of the model.
     */
    abstract public function getId();

    /**
     * Returns the model's data formatted for database insertion/update.
     *
     * @return array Associative array of data to be persisted.
     */
    abstract public function toDatabase();

}