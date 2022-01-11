<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Sensors;

interface SensorInterface {
  /**
   * Return the sensor name.
   * 
   * @return string
   */
  public function getName(): string;
  /**
   * Return the sensor description.
   * 
   * @return string
   */
  public function getDescription(): string;
}
