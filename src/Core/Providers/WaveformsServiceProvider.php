<?php

namespace ScrapyardIO\Waveforms\Core\Providers;

use Fabricate\Core\Machine as ScrapyardIOMachine;
use Fabricate\NutsAndBolts\AggregateServiceProvider;
use Fabricate\Contracts\Chassis\BindingResolutionException;
use ScrapyardIO\Waveforms\Acceleration\AccelerometerServiceProvider;
use ScrapyardIO\Waveforms\Distance\DistanceSensorServiceProvider;
use ScrapyardIO\Waveforms\Environment\Providers\EnvironmentalSensorsServiceProvider;
use ScrapyardIO\Waveforms\Input\InputServiceProvider;
use ScrapyardIO\Waveforms\Light\LightServiceProvider;
use ScrapyardIO\Waveforms\Motion\MotionServiceProvider;

class WaveformsServiceProvider extends AggregateServiceProvider
{
    protected array $providers = [
        AccelerometerServiceProvider::class,
        DistanceSensorServiceProvider::class,
        EnvironmentalSensorsServiceProvider::class,
        InputServiceProvider::class,
        LightServiceProvider::class,
        MotionServiceProvider::class,
    ];

    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->publishConfig();

        parent::register();
    }

    /**
     * @throws BindingResolutionException
     */
    protected function publishConfig(): void
    {
        $source = realpath($raw = __DIR__.'/../../../config/waveforms.php') ?: $raw;

        if ($this->program instanceof ScrapyardIOMachine && $this->program->runningInConsole()) {
            $this->publishes([$source => $this->program->configPath('waveforms.php')]);
        }

        $this->mergeConfigFrom($source, 'waveforms');
    }
}
