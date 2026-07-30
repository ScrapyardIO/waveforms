<?php

namespace ScrapyardIO\Waveforms\Tests\Unit;

use Fabricate\Config\Repository;
use Fabricate\Core\Machine;
use Fabricate\NutsAndBolts\MagicAliases\Actuator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ScrapyardIO\Waveforms\Core\Providers\WaveformsServiceProvider;
use ScrapyardIO\Waveforms\Input\InputServiceProvider;
use ScrapyardIO\Waveforms\Motion\MotionServiceProvider;

class WaveformsProvidersTest extends TestCase
{
    protected function tearDown(): void
    {
        Actuator::clearResolvedInstance();

        parent::tearDown();
    }

    public function testProvidersRegisterOnlyEnabledActuators(): void
    {
        $config = require dirname(__DIR__, 2).'/config/waveforms.php';
        $config['fan']['enabled'] = true;
        $config['button']['enabled'] = true;
        $config['potentiometer']['enabled'] = true;
        $config['pointer']['enabled'] = true;

        $program = new Machine(dirname(__DIR__, 2));
        $program->instance('config', new Repository(['waveforms' => $config]));

        $registry = new class {
            /** @var array<string, class-string> */
            public array $actuators = [];

            public function addActuator(string $name, string $class_name): void
            {
                $this->actuators[$name] = $class_name;
            }
        };
        Actuator::swap($registry);

        (new InputServiceProvider($program))->boot();
        (new MotionServiceProvider($program))->boot();

        self::assertSame([
            'button' => \ScrapyardIO\Waveforms\Input\Button::class,
            'potentiometer' => \ScrapyardIO\Waveforms\Input\Potentiometer::class,
            'pointer' => \ScrapyardIO\Waveforms\Input\Pointer::class,
            'fan' => \ScrapyardIO\Waveforms\Motion\Fan::class,
        ], $registry->actuators);
    }

    public function testAllNewActuatorFeaturesDefaultToDisabled(): void
    {
        $config = require dirname(__DIR__, 2).'/config/waveforms.php';

        foreach ([
            'fan',
            'positional-servo',
            'continuous-servo',
            'button',
            'button-pad',
            'potentiometer',
            'touch',
            'pointer',
            'game-controller',
        ] as $key) {
            self::assertFalse($config[$key]['enabled'], "Expected [{$key}] to default disabled.");
        }
    }

    public function testWaveformsAggregateIncludesInputAndMotionProviders(): void
    {
        $providers = (new ReflectionClass(WaveformsServiceProvider::class))
            ->getDefaultProperties()['providers'];

        self::assertContains(InputServiceProvider::class, $providers);
        self::assertContains(MotionServiceProvider::class, $providers);
    }
}
