<?php

namespace ScrapyardIO\Waveforms\Input;

use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\HumanInput\GameController as GameControllerCircuit;
use Fabricate\Contracts\Actuation\HumanInput\GameControllerAxis;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class GameController extends ButtonPad
{
    public function __construct(GameControllerCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    public function connected(): bool
    {
        return $this->controller()->connected();
    }

    public function axis(GameControllerAxis $axis): float
    {
        return $this->controller()->axis($axis);
    }

    /**
     * @return array<string, float>
     */
    public function axes(): array
    {
        return $this->controller()->axes();
    }

    public function supportsRumble(): bool
    {
        return method_exists($this->controller(), 'rumble');
    }

    public function rumble(int $instance_id, int $low, int $high, int $duration_ms): bool
    {
        $controller = $this->controller();

        if (! method_exists($controller, 'rumble')) {
            throw new ActuatorException('The wrapped game controller does not support rumble.');
        }

        return $controller->rumble($instance_id, $low, $high, $duration_ms);
    }

    public function supportsTriggerRumble(): bool
    {
        return method_exists($this->controller(), 'triggerRumble');
    }

    public function triggerRumble(int $instance_id, int $left, int $right, int $duration_ms): bool
    {
        $controller = $this->controller();

        if (! method_exists($controller, 'triggerRumble')) {
            throw new ActuatorException('The wrapped game controller does not support trigger rumble.');
        }

        return $controller->triggerRumble($instance_id, $left, $right, $duration_ms);
    }

    /**
     * @return array{connected: bool, buttons: list<string>, axes: array<string, float>}
     */
    public function snapshot(): array
    {
        return [
            'connected' => $this->connected(),
            'buttons' => $this->downLabels(),
            'axes' => $this->axes(),
        ];
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof GameControllerCircuit) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not a GameController input.");
    }

    private function controller(): GameControllerCircuit
    {
        /** @var GameControllerCircuit */
        return $this->circuit;
    }
}
