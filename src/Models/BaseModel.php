<?php

namespace App\Models;
use InvalidArgumentException;
use JsonSerializable;

require_once __DIR__ . '/../../vendor/autoload.php';

abstract class BaseModel implements JsonSerializable
{
    protected function requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments = []): void
    {
        foreach ($notNullArguments as $notNullArgument) {
            if (!isset($data[$notNullArgument])) {
                throw new InvalidArgumentException("Missing required field: $notNullArgument");
            }
        }

        foreach ($notEmptyArguments as $notEmptyArgument) {
            if (empty($data[$notEmptyArgument])) {
                throw new InvalidArgumentException("Missing required field: $notEmptyArgument");
            }
        }
    }

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

    abstract public function getId();

}