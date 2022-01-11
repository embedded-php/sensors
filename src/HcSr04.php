 <?php
declare(strict_types = 1);

namespace EmbeddedPhp\Sensors;

use EmbeddedPhp\Core\Gpio\GpioInterface;

/**
 * Ultrasonic ranging sensor.
 *
 * @link https://www.sparkfun.com/products/15569
 */
final class HcSr04 extends AbstractSensor {
  /**
   * TRIGGER reset pulse duration (in microseconds)
   */
  private const TRIGGER_RPD = 2;
  /**
   * TRIGGER activation pulse duration (in microseconds)
   */
  private const TRIGGER_APD = 10;
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
  private int $triggerReset = self::TRIGGER_RPD;
  private int $triggerApd = self::TRIGGER_APD;
  private int $echoTimeout = self::ECHO_TIMEOUT;

  protected string $name = 'HC-SR04';
  protected string $description = 'Ultrasonic ranging sensor.';

  /**
   * @param GpioInterface $gpio       A GpioInterface compliant instance
   * @param int           $triggerPin The number of the GPIO Pin wired to the Sensor TRIGGER Pin
   * @param int           $echoPin    The number of the GPIO Pin wired to the Sensor ECHO Pin
   *
   * @return void
   */
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

  public function getTriggerResetPulseDuration(): int {
    return $this->triggerReset;
  }

  public function setTriggerResetPulseDuration(int $microseconds): self {
    $this->triggerReset = $microseconds;

    return $this;
  }

  public function getTriggerActivationPulseDuration(): int {
    return $this->triggerActivation;
  }

  public function setTriggerActivationPulseDuration(int $microseconds): self {
    $this->triggerActivation = $microseconds;

    return $this;
  }

  public function getEchoTimeout(): int {
    return $this->echoTimeout;
  }

  public function setEchoTimeout(int $microseconds): self {
    $this->echoTimeout = $microseconds;

    return $this;
  }

  /**
   * Return the current distance to an object in millimeters.
   *
   * @return int
   */
  public function getDistance(): int {
    // reset trigger
    $this->gpio->setLow($this->triggerPin);
    usleep($this->triggerReset);
    // activate trigger
    $this->gpio->setHigh($this->triggerPin);
    usleep($this->triggerActivation);
    // release trigger
    $this->gpio->setLow($this->triggerPin);

    $time = $this->gpio->timeInHigh($this->echoPin, $this->echoTimeout);
    if ($time === 0) {
      // failed to measure time
      return 0;
    }

    /**
     * Convert $time into seconds (1us = 1e-6s = 0.000001s)
     * Convert SOUND_SPEED into mm/s (1m/s = 1000mm/s)
     * (($time * 1e-6) * (SOUND_SPEED * 1000)) / 2
     */
    return (int)(($time * self::SOUND_SPEED) * 5e-4);
  }
}
