<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Sensors;

use EmbeddedPhp\Core\Gpio\GpioInterface;

/**
 * Ultrasonic ranging sensor.
 */
final class HcSr04 {
  /**
   * TRIGGER reset pusel width (in microseconds)
   */
  private const TRIGGER_RPW = 2;
  /**
   * TRIGGER activation pulse width (in microseconds)
   */
  private const TRIGGER_APW = 10;
  /**
   * ECHO resolution (in microseconds)
   */
  private const ECHO_RESOL = 1;
  /**
   * ECHO measurement timeout (in microseconds)
   */
  private const ECHO_TIMEOUT = 100000;
  /**
   * Sound speed (in meters per second)
   */
  private const SOUND_SPEED = 340;

  private GpioInterface $gpio;
  private int $triggerPin;
  private int $echoPin;

  public function __construct(
    GpioInterface $gpio,
    int $triggerPin,
    int $echoPin
  ) {
    $this->gpio       = $gpio;
    $this->triggerPin = $triggerPin;
    $this->echoPin    = $echoPin;

    $this->gpio->setOutputMode($triggerPin);
    $this->gpio->setInputMode($echoPin);
  }

  public function __destruct() {
    $this->gpio->release($this->triggerPin);
    $this->gpio->release($this->echoPin);
  }

  /**
   * Return the current distance to an object in millimeters.
   *
   * @return int
   */
  public function getDistance(): int {
    // reset trigger
    $this->gpio->setLow($this->triggerPin);
    usleep(self::TRIGGER_RPW);
    // activate trigger
    $this->gpio->setHigh($this->triggerPin);
    usleep(self::TRIGGER_APW);
    // release trigger
    $this->gpio->setLow($this->triggerPin);

    // wait for $echoPin to be activated (LOW -> HIGH)
    $timeout = self::ECHO_TIMEOUT;
    while ($this->gpio->isLow($this->echoPin) && $timeout > 0) {
      $timeout -= self::ECHO_RESOL;
      usleep(self::ECHO_RESOL);
    }

    // $echoPin was not activated within ECHO_TIMEOUT microseconds
    if ($timeout === 0) {
      return 0;
    }

    // start measure
    $resol = 0;
    while ($this->gpio->isHigh($this->echoPin)) {
      $resol += self::ECHO_RESOL;
      usleep(self::ECHO_RESOL);
    }

    return (int)((($resol * self::SOUND_SPEED) / 2) * 10);
  }
}
