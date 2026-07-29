<?php

namespace ScrapyardIO\Waveforms\Environment;

use Fabricate\Contracts\Sensors\Enums\HumidityUnit;
use Fabricate\Contracts\Sensors\Interfaces\Hygrometer as HygrometerCircuit;
use Fabricate\Contracts\Sensors\SensorException;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;
use Fabricate\Sensors\Sensor;

/**
 * @property-read float $humidity
 * @property-read float $relative_humidity
 */
class Hygrometer extends Sensor
{
    public function __construct(HygrometerCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    /**
     * @throws SensorException
     */
    public function __get(string $name): float
    {
        return match ($name) {
            'humidity', 'relative_humidity' => $this->getHumidity(),
            default => throw SensorException::invalidProperty($name, static::class),
        };
    }

    /**
     * @throws SensorException
     */
    public function getHumidity(HumidityUnit|string $unit = HumidityUnit::PERCENT): float
    {
        if (is_string($unit)) {
            $unit = HumidityUnit::tryFrom($unit) ?? HumidityUnit::PERCENT;
        }

        /** @var HygrometerCircuit $circuit */
        $circuit = $this->circuit;

        return $circuit->measureHumidity($unit);
    }

    /**
     * @throws SensorException
     */
    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof HygrometerCircuit) {
            return new static($circuit);
        }

        throw new SensorException("Circuit [{$driver}] is not a Hygrometer/Relative Humidity Sensor.");
    }
}
