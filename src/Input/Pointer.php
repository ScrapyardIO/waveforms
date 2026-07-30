<?php

namespace ScrapyardIO\Waveforms\Input;

use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\HumanInput\CoordinateSpace;
use Fabricate\Contracts\Actuation\HumanInput\Pointer as PointerCircuit;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class Pointer extends ButtonPad
{
    public function __construct(PointerCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    public function coordinateSpace(): CoordinateSpace
    {
        return $this->pointer()->coordinateSpace();
    }

    public function x(): float
    {
        return $this->pointer()->x();
    }

    public function y(): float
    {
        return $this->pointer()->y();
    }

    public function deltaX(): float
    {
        return $this->pointer()->deltaX();
    }

    public function deltaY(): float
    {
        return $this->pointer()->deltaY();
    }

    public function wheelX(): float
    {
        return $this->pointer()->wheelX();
    }

    public function wheelY(): float
    {
        return $this->pointer()->wheelY();
    }

    /**
     * @return array{x: float, y: float, space: CoordinateSpace}
     */
    public function position(): array
    {
        return [
            'x' => $this->x(),
            'y' => $this->y(),
            'space' => $this->coordinateSpace(),
        ];
    }

    /**
     * @return array{x: float, y: float}
     */
    public function delta(): array
    {
        return ['x' => $this->deltaX(), 'y' => $this->deltaY()];
    }

    /**
     * @return array{x: float, y: float}
     */
    public function wheel(): array
    {
        return ['x' => $this->wheelX(), 'y' => $this->wheelY()];
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof PointerCircuit) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not a Pointer input.");
    }

    private function pointer(): PointerCircuit
    {
        /** @var PointerCircuit */
        return $this->circuit;
    }
}
