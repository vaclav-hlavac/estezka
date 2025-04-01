<?php

use PHPUnit\Framework\TestCase;
use App\Models\BaseModel;
use InvalidArgumentException;

// Pomocná třída pro testování abstraktního BaseModel
// Pomocná třída pro testování abstraktního BaseModel
class TestModel extends BaseModel
{
    public int $id;
    public string $name;

    public function getId()
    {
        return $this->id;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }

    // Testovací obal pro volání protected metody
    public function testRequiredArgumentsControlWrapper(array $data, array $notNullArguments, array $notEmptyArguments = []): void
    {
        $this->requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments);
    }
}

class BaseModelTest extends TestCase
{
    public function testRequiredArgumentsControlThrowsExceptionForMissingField()
    {
        $model = new TestModel();
        $data = ['name' => 'Test Name']; // Missing 'id'

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required field: id");

        $model->testRequiredArgumentsControlWrapper($data, ['id']);
    }

    public function testRequiredArgumentsControlThrowsExceptionForEmptyField()
    {
        $model = new TestModel();
        $data = ['id' => 1, 'name' => '']; // 'name' je prázdné

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required field: name");

        $model->testRequiredArgumentsControlWrapper($data, ['id'], ['name']);
    }

    public function testSetAttributesSetsPropertiesCorrectly()
    {
        $model = new TestModel();
        $data = ['id' => 1, 'name' => 'Test Name'];

        $model->setAttributes($data);

        $this->assertEquals(1, $model->id);
        $this->assertEquals('Test Name', $model->name);
    }

    public function testSetAttributesThrowsExceptionForInvalidProperty()
    {
        $model = new TestModel();
        $data = ['id' => 1, 'name' => 'Test Name', 'invalidField' => 'some value'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Model TestModel does not have a field: invalidField");

        $model->setAttributes($data);
    }
}