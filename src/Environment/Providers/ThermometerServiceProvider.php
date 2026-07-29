<?php

namespace ScrapyardIO\Waveforms\Environment\Providers;

use Fabricate\Contracts\Chassis\CircularDependencyException;
use Fabricate\NutsAndBolts\MagicAliases\Sensor;
use Fabricate\NutsAndBolts\ServiceProvider;
use ScrapyardIO\Waveforms\Environment\Thermometer;

class ThermometerServiceProvider extends ServiceProvider
{
    public function register(): void {}

    /**
     * @throws CircularDependencyException
     */
    protected function enabled(): bool
    {
        return config('waveforms.thermometer.enabled', false);
    }

    /**
     * @throws CircularDependencyException
     */
    public function boot(): void {
        if($this->enabled()) {
            Sensor::addSensor('thermometer', Thermometer::class);
        }
    }
}