<?php

namespace ScrapyardIO\Waveforms\Input;

use Fabricate\Actuation\Actuator;
use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\HumanInput\CoordinateSpace;
use Fabricate\Contracts\Actuation\HumanInput\Touch as TouchCircuit;
use Fabricate\Contracts\Actuation\HumanInput\TouchContact;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class Touch extends Actuator
{
    public function __construct(TouchCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    public function poll(): static
    {
        $this->touch()->poll();

        return $this;
    }

    /**
     * @return list<TouchContact>
     */
    public function contacts(CoordinateSpace $space = CoordinateSpace::NORMALIZED): array
    {
        return $this->touch()->contacts($space);
    }

    public function primaryContact(CoordinateSpace $space = CoordinateSpace::NORMALIZED): ?TouchContact
    {
        return $this->touch()->primaryContact($space);
    }

    public function isTouched(): bool
    {
        return ! is_null($this->primaryContact());
    }

    public function contactCount(): int
    {
        return count($this->contacts());
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof TouchCircuit) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not a Touch input.");
    }

    private function touch(): TouchCircuit
    {
        /** @var TouchCircuit */
        return $this->circuit;
    }
}
