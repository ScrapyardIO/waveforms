<?php

namespace ScrapyardIO\Waveforms\Light;

use Fabricate\Actuation\Actuator;
use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\Interfaces\LEDShape;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class NeoPixel extends Actuator
{
    public function __construct(LEDShape $circuit)
    {
        parent::__construct($circuit);
    }

    public function pixelCount(): int
    {
        return $this->shape()->pixelCount();
    }

    public function setPixelColor(
        int $pixel,
        int $color_or_red,
        ?int $green = null,
        ?int $blue = null,
        ?int $white = null,
    ): static {
        $this->shape()->setPixelColor($pixel, $color_or_red, $green, $blue, $white);

        return $this;
    }

    public function getPixelColor(int $pixel): int
    {
        return $this->shape()->getPixelColor($pixel);
    }

    public function fill(
        int $color_or_red,
        ?int $green = null,
        ?int $blue = null,
        ?int $white = null,
    ): static {
        $this->shape()->fill($color_or_red, $green, $blue, $white);

        return $this;
    }

    public function clear(): static
    {
        $this->shape()->clear();

        return $this;
    }

    public function brightness(float $brightness): static
    {
        $this->shape()->setBrightness($brightness);

        return $this;
    }

    public function show(): static
    {
        $this->shape()->show();

        return $this;
    }

    public function chase(
        int $color,
        int $cycles = 1,
        int $delay_us = 50_000,
    ): static {
        for ($cycle = 0; $cycle < $cycles; $cycle++) {
            for ($pixel = 0; $pixel < $this->pixelCount(); $pixel++) {
                $this->shape()
                    ->clear()
                    ->setPixelColor($pixel, $color)
                    ->show();

                if ($delay_us > 0) {
                    usleep($delay_us);
                }
            }
        }

        return $this;
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof LEDShape) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not an LEDShape.");
    }

    public function shape(): LEDShape
    {
        /** @var LEDShape */
        return $this->circuit;
    }
}
