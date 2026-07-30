<?php

namespace ScrapyardIO\Waveforms\Input;

use Fabricate\NutsAndBolts\MagicAliases\Actuator;
use Fabricate\NutsAndBolts\ServiceProvider;

class InputServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $actuators = [
            'button' => Button::class,
            'button-pad' => ButtonPad::class,
            'potentiometer' => Potentiometer::class,
            'touch' => Touch::class,
            'pointer' => Pointer::class,
            'game-controller' => GameController::class,
        ];

        foreach ($actuators as $key => $class) {
            if (config("waveforms.{$key}.enabled", false)) {
                Actuator::addActuator($key, $class);
            }
        }
    }
}
