<?php

namespace ScrapyardIO\Waveforms\Input;

use Fabricate\Actuation\Actuator;
use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\Interfaces\Button as ButtonCircuit;
use Fabricate\Contracts\Actuation\Interfaces\ButtonPad as ButtonPadCircuit;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class ButtonPad extends Actuator
{
    public function __construct(ButtonPadCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    public function poll(): static
    {
        $this->buttonPad()->poll();

        return $this;
    }

    /**
     * @return array<string, ButtonCircuit>
     */
    public function buttons(): array
    {
        return $this->buttonPad()->buttons();
    }

    /**
     * @return list<string>
     */
    public function labels(): array
    {
        return $this->buttonPad()->labels();
    }

    public function button(string $label): ButtonCircuit
    {
        return $this->buttonPad()->button($label);
    }

    public function has(string $label): bool
    {
        return $this->buttonPad()->has($label);
    }

    public function isDown(string $label): bool
    {
        return $this->buttonPad()->isDown($label);
    }

    public function isPressed(string $label): bool
    {
        return $this->buttonPad()->isPressed($label);
    }

    public function wasReleased(string $label): bool
    {
        return $this->buttonPad()->wasReleased($label);
    }

    public function isHolding(string $label): bool
    {
        return $this->buttonPad()->isHolding($label);
    }

    /**
     * @return list<string>
     */
    public function downLabels(): array
    {
        return $this->buttonPad()->downLabels();
    }

    /**
     * @return list<string>
     */
    public function pressedLabels(): array
    {
        return $this->buttonPad()->pressedLabels();
    }

    /**
     * @return list<string>
     */
    public function holdingLabels(): array
    {
        return $this->buttonPad()->holdingLabels();
    }

    public function anyDown(string ...$labels): bool
    {
        return $this->buttonPad()->anyDown(...$labels);
    }

    public function allDown(string ...$labels): bool
    {
        return $this->buttonPad()->allDown(...$labels);
    }

    public function chord(string ...$labels): bool
    {
        return $this->buttonPad()->chord(...$labels);
    }

    public function anyPressed(string ...$labels): bool
    {
        return $this->buttonPad()->anyPressed(...$labels);
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof ButtonPadCircuit) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not a ButtonPad.");
    }

    protected function buttonPad(): ButtonPadCircuit
    {
        /** @var ButtonPadCircuit */
        return $this->circuit;
    }
}
