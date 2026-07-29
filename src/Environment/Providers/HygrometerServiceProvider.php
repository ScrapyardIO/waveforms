<?php

namespace ScrapyardIO\Waveforms\Environment\Providers;

use Fabricate\Contracts\Chassis\CircularDependencyException;
use Fabricate\NutsAndBolts\MagicAliases\Sensor;
use Fabricate\NutsAndBolts\ServiceProvider;
use ScrapyardIO\Waveforms\Environment\Hygrometer;

class HygrometerServiceProvider extends ServiceProvider
{
    public function register(): void {}

    /**
     * @throws CircularDependencyException
     */
    protected function enabled(): bool
    {
        return config('waveforms.hygrometer.enabled', false);
    }

    /**
     * @throws CircularDependencyException
     */
    public function boot(): void {
        if($this->enabled()) {
            Sensor::addSensor('hygrometer', Hygrometer::class);
        }
    }
}