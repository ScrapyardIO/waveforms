<?php

namespace ScrapyardIO\Waveforms\Input;

use Fabricate\Actuation\Actuator;
use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\Interfaces\Button as ButtonCircuit;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class Button extends Actuator
{
    public function __construct(ButtonCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    public function label(): string
    {
        return $this->buttonCircuit()->label();
    }

    public function holdMs(): int
    {
        return $this->buttonCircuit()->holdMs();
    }

    public function setHoldMs(int $hold_ms): static
    {
        $this->buttonCircuit()->setHoldMs($hold_ms);

        return $this;
    }

    public function poll(): static
    {
        $this->buttonCircuit()->poll();

        return $this;
    }

    public function isDown(): bool
    {
        return $this->buttonCircuit()->isDown();
    }

    public function isPressed(): bool
    {
        return $this->buttonCircuit()->isPressed();
    }

    public function wasReleased(): bool
    {
        return $this->buttonCircuit()->wasReleased();
    }

    public function isHolding(): bool
    {
        return $this->buttonCircuit()->isHolding();
    }

    public function heldMs(): int
    {
        return $this->buttonCircuit()->heldMs();
    }

    /**
     * @return list<array{down: bool, pressed: bool, released: bool, holding: bool, at_ns: int}>
     */
    public function history(): array
    {
        return $this->buttonCircuit()->history();
    }

    public function clearHistory(): static
    {
        $this->buttonCircuit()->clearHistory();

        return $this;
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof ButtonCircuit) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not a Button.");
    }

    protected function buttonCircuit(): ButtonCircuit
    {
        /** @var ButtonCircuit */
        return $this->circuit;
    }
}
