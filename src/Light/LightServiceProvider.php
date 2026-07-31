<?php

namespace ScrapyardIO\Waveforms\Light;

use Fabricate\NutsAndBolts\MagicAliases\Actuator;
use Fabricate\NutsAndBolts\ServiceProvider;

class LightServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $actuators = [
            'led' => LED::class,
            'neopixel' => NeoPixel::class,
        ];

        foreach ($actuators as $key => $class) {
            if (config("waveforms.{$key}.enabled", false)) {
                Actuator::addActuator($key, $class);
            }
        }
    }
}
