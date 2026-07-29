<?php

namespace ScrapyardIO\Waveforms\Environment;

use Fabricate\Contracts\Sensors\Enums\TemperatureUnit;
use Fabricate\Contracts\Sensors\Interfaces\Thermometer as ThermometerCircuit;
use Fabricate\Contracts\Sensors\SensorException;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;
use Fabricate\Sensors\Sensor;

/**
 * @property-read float $temperature
 */
class Thermometer extends Sensor
{
    public function __construct(ThermometerCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    /**
     * @throws SensorException
     */
    public function __get(string $name): float
    {
        return match ($name) {
            'temperature' => $this->getTemperature(),
            default => throw SensorException::invalidProperty($name, static::class),
        };
    }

    /**
     * @throws SensorException
     */
    public function getTemperature(TemperatureUnit|string $unit = TemperatureUnit::CELSIUS): float
    {
        if (is_string($unit)) {
            $unit = TemperatureUnit::tryFrom($unit) ?? TemperatureUnit::CELSIUS;
        }

        /** @var ThermometerCircuit $circuit */
        $circuit = $this->circuit;

        return $circuit->measureTemp($unit);
    }

    /**
     * @throws SensorException
     */
    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof ThermometerCircuit) {
            return new static($circuit);
        }

        throw new SensorException("Circuit [{$driver}] is not a Thermometer.");
    }
}
