<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Sensors;

abstract class AbstractSensor implements SensorInterface {
  protected string $name = '';
  protected string $description = '';

  public function getName(): string {
    return $this->name;
  }

  public function getDescription(): string {
    return $this->description;
  }
}
