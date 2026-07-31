<?php

namespace ScrapyardIO\Waveforms\Light;

use Fabricate\Actuation\Actuator;
use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\Interfaces\LED as LEDCircuit;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class LED extends Actuator
{
    public function __construct(LEDCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    public function on(): void
    {
        $this->led()->on();
    }

    public function off(): void
    {
        $this->led()->off();
    }

    public function toggle(): void
    {
        $this->led()->toggle();
    }

    public function isOn(): bool
    {
        return $this->led()->isOn();
    }

    public function brightness(?int $percent = null): int
    {
        return $this->led()->brightness($percent);
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof LEDCircuit) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not an LED.");
    }

    protected function led(): LEDCircuit
    {
        /** @var LEDCircuit */
        return $this->circuit;
    }
}
