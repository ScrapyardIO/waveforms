<?php

namespace ScrapyardIO\Waveforms\Input;

use Fabricate\Actuation\Actuator;
use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\Interfaces\Potentiometer as PotentiometerCircuit;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class Potentiometer extends Actuator
{
    public function __construct(PotentiometerCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    public function raw(): int
    {
        return $this->potentiometerCircuit()->raw();
    }

    public function position(): float
    {
        return $this->potentiometerCircuit()->position();
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof PotentiometerCircuit) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not a Potentiometer.");
    }

    protected function potentiometerCircuit(): PotentiometerCircuit
    {
        /** @var PotentiometerCircuit */
        return $this->circuit;
    }
}
