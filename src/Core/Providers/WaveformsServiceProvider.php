<?php

namespace ScrapyardIO\Waveforms\Core\Providers;

use Fabricate\Core\Machine as ScrapyardIOMachine;
use Fabricate\NutsAndBolts\AggregateServiceProvider;
use Fabricate\Contracts\Chassis\BindingResolutionException;
use ScrapyardIO\Waveforms\Acceleration\AccelerometerServiceProvider;

class WaveformsServiceProvider extends AggregateServiceProvider
{
    protected array $providers = [
        AccelerometerServiceProvider::class,
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
