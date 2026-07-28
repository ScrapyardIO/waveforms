<?php

namespace ScrapyardIO\Waveforms\Acceleration;

use Fabricate\NutsAndBolts\ServiceProvider;
use Fabricate\NutsAndBolts\MagicAliases\Sensor;
use Fabricate\Contracts\Chassis\CircularDependencyException;

class AccelerometerServiceProvider extends ServiceProvider
{
    public function register(): void {}

    /**
     * @throws CircularDependencyException
     */
    protected function enabled(): bool
    {
        return config('waveforms.accelerometer.enabled', false);
    }

    /**
     * @throws CircularDependencyException
     */
    public function boot(): void {
        if($this->enabled()) {
            Sensor::addSensor('accelerometer', Accelerometer::class);
        }
    }
}