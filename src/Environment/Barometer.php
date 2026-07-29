<?php

namespace ScrapyardIO\Waveforms\Environment;

use Fabricate\Contracts\Sensors\Enums\PressureUnit;
use Fabricate\Contracts\Sensors\Interfaces\Barometer as BarometerCircuit;
use Fabricate\Contracts\Sensors\SensorException;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;
use Fabricate\Sensors\Sensor;

/**
 * @property-read float $pressure
 */
class Barometer extends Sensor
{
    public function __construct(BarometerCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    /**
     * @throws SensorException
     */
    public function __get(string $name): float
    {
        return match ($name) {
            'pressure' => $this->getPressure(),
            default => throw SensorException::invalidProperty($name, static::class),
        };
    }

    /**
     * @throws SensorException
     */
    public function getPressure(PressureUnit|string $unit = PressureUnit::HECTOPASCAL): float
    {
        if (is_string($unit)) {
            $unit = PressureUnit::tryFrom($unit) ?? PressureUnit::HECTOPASCAL;
        }

        /** @var BarometerCircuit $circuit */
        $circuit = $this->circuit;

        return $circuit->measurePressure($unit);
    }

    /**
     * @throws SensorException
     */
    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof BarometerCircuit) {
            return new static($circuit);
        }

        throw new SensorException("Circuit [{$driver}] is not a Barometric Pressure Sensor.");
    }
}
