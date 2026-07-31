<?php

use Fabricate\Contracts\Actuation\Interfaces\LED as LEDCircuit;
use Fabricate\Contracts\Actuation\Interfaces\LEDShape;
use ScrapyardIO\Waveforms\Light\LED;
use ScrapyardIO\Waveforms\Light\NeoPixel;

it('delegates single LED control', function () {
    $circuit = new class implements LEDCircuit {
        public bool $on = false;

        public int $brightness = 0;

        public function on(): void
        {
            $this->on = true;
            $this->brightness = 100;
        }

        public function off(): void
        {
            $this->on = false;
            $this->brightness = 0;
        }

        public function toggle(): void
        {
            $this->on ? $this->off() : $this->on();
        }

        public function isOn(): bool
        {
            return $this->on;
        }

        public function brightness(?int $percent = null): int
        {
            if (! is_null($percent)) {
                $this->brightness = $percent;
                $this->on = $percent > 0;
            }

            return $this->brightness;
        }

        public function close(): void {}
    };

    $led = new LED($circuit);
    $led->on();

    expect($led->isOn())->toBeTrue()
        ->and($led->brightness(40))->toBe(40);

    $led->toggle();
    expect($led->isOn())->toBeFalse();
});

it('delegates NeoPixel shape operations', function () {
    $circuit = new class implements LEDShape {
        public array $pixels = [0, 0, 0];

        public float $brightness = 1.0;

        public int $shows = 0;

        public function pixelCount(): int
        {
            return count($this->pixels);
        }

        public function setPixelColor(int $pixel, int $color_or_red, ?int $green = null, ?int $blue = null, ?int $white = null): static
        {
            $this->pixels[$pixel] = $color_or_red;

            return $this;
        }

        public function getPixelColor(int $pixel): int
        {
            return $this->pixels[$pixel];
        }

        public function fill(int $color_or_red, ?int $green = null, ?int $blue = null, ?int $white = null): static
        {
            $this->pixels = array_fill(0, $this->pixelCount(), $color_or_red);

            return $this;
        }

        public function clear(): static
        {
            return $this->fill(0);
        }

        public function setBrightness(float $brightness): static
        {
            $this->brightness = $brightness;

            return $this;
        }

        public function show(): static
        {
            $this->shows++;

            return $this;
        }

        public function close(): void {}
    };

    $pixels = new NeoPixel($circuit);
    $pixels->fill(0xff0000)->setPixelColor(1, 0x00ff00)->brightness(0.5)->show();

    expect($pixels->pixelCount())->toBe(3)
        ->and($pixels->getPixelColor(1))->toBe(0x00ff00)
        ->and($circuit->brightness)->toBe(0.5)
        ->and($circuit->shows)->toBe(1);
});
