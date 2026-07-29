<?php

namespace ScrapyardIO\Waveforms\Environment\Providers;

use Fabricate\NutsAndBolts\AggregateServiceProvider;

class EnvironmentalSensorsServiceProvider extends AggregateServiceProvider
{
    protected array $providers = [
        BarometerServiceProvider::class,
        HygrometerServiceProvider::class,
        ThermometerServiceProvider::class,
        LightningSensorServiceProvider::class,
    ];


}