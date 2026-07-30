<?php

namespace ScrapyardIO\Waveforms\Tests\Unit;

use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\HumanInput\CoordinateSpace;
use Fabricate\Contracts\Actuation\HumanInput\GameController as GameControllerCircuit;
use Fabricate\Contracts\Actuation\HumanInput\GameControllerAxis;
use Fabricate\Contracts\Actuation\HumanInput\Pointer as PointerCircuit;
use Fabricate\Contracts\Actuation\HumanInput\Touch as TouchCircuit;
use Fabricate\Contracts\Actuation\HumanInput\TouchContact;
use Fabricate\Contracts\Actuation\HumanInput\TouchPhase;
use Fabricate\Contracts\Actuation\Interfaces\Button as ButtonCircuit;
use Fabricate\Contracts\Actuation\Interfaces\ButtonPad as ButtonPadCircuit;
use Fabricate\Contracts\Actuation\Interfaces\ContinuousServo as ContinuousServoCircuit;
use Fabricate\Contracts\Actuation\Interfaces\Fan as FanCircuit;
use Fabricate\Contracts\Actuation\Interfaces\PositionalServo as PositionalServoCircuit;
use Fabricate\Contracts\Actuation\Interfaces\Potentiometer as PotentiometerCircuit;
use Fabricate\Contracts\Circuits\IntegratedCircuit;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ScrapyardIO\Waveforms\Input\Button;
use ScrapyardIO\Waveforms\Input\ButtonPad;
use ScrapyardIO\Waveforms\Input\GameController;
use ScrapyardIO\Waveforms\Input\Pointer;
use ScrapyardIO\Waveforms\Input\Potentiometer;
use ScrapyardIO\Waveforms\Input\Touch;
use ScrapyardIO\Waveforms\Motion\ContinuousServo;
use ScrapyardIO\Waveforms\Motion\Fan;
use ScrapyardIO\Waveforms\Motion\PositionalServo;

interface RumblingGameControllerCircuit extends GameControllerCircuit
{
    public function rumble(int $instance_id, int $low, int $high, int $duration_ms): bool;

    public function triggerRumble(int $instance_id, int $left, int $right, int $duration_ms): bool;
}

class WaveformsWrappersTest extends TestCase
{
    protected function tearDown(): void
    {
        Circuit::clearResolvedInstance();

        parent::tearDown();
    }

    public function testFanDelegatesControlAndOptionalTachometer(): void
    {
        $circuit = new class implements FanCircuit {
            public bool $running = false;

            public int $speed = 0;

            public int $frequency = 0;

            public function on(): void
            {
                $this->running = true;
            }

            public function off(): void
            {
                $this->running = false;
            }

            public function speed(?int $percent = null): int
            {
                if (! is_null($percent)) {
                    $this->speed = $percent;
                }

                return $this->speed;
            }

            public function frequency(?int $hz = null): int
            {
                if (! is_null($hz)) {
                    $this->frequency = $hz;
                }

                return $this->frequency;
            }

            public function rpm(int $sample_ms = 500, int $pulses_per_revolution = 2): float
            {
                return $sample_ms === 250 && $pulses_per_revolution === 2 ? 1800.0 : 0.0;
            }

            public function close(): void {}
        };

        $fan = new Fan($circuit);
        $fan->on();
        self::assertTrue($circuit->running);
        $fan->off();
        self::assertFalse($circuit->running);

        self::assertSame(75, $fan->speed(75));
        self::assertSame(25_000, $fan->frequency(25_000));
        self::assertTrue($fan->hasTachometer());
        self::assertSame(1800.0, $fan->rpm(250));
    }

    public function testFanRejectsRpmWhenNoTachometerCapabilityExists(): void
    {
        $fan = new Fan($this->createMock(FanCircuit::class));

        self::assertFalse($fan->hasTachometer());

        $this->expectException(ActuatorException::class);

        $fan->rpm();
    }

    public function testPositionalServoDelegatesCalibrationMotionAndSweep(): void
    {
        $circuit = $this->createMock(PositionalServoCircuit::class);
        $circuit->expects($this->once())->method('calibrate')->with(500, 2500, 1500)->willReturnSelf();
        $circuit->expects($this->once())->method('to')->with(90, 250, 5);
        $circuit->expects($this->once())->method('sweep')->with(10, 170, [20, 160], 800, 5);
        $circuit->expects($this->once())->method('getPosition')->willReturn(90);

        $servo = new PositionalServo($circuit);

        self::assertSame($servo, $servo->calibrate(500, 2500, 1500));
        $servo->to(90, 250, 5);
        $servo->sweep(10, 170, [20, 160], 800, 5);
        self::assertSame(90, $servo->getPosition());
    }

    public function testContinuousServoDelegatesDirectionAndDeadband(): void
    {
        $circuit = $this->createMock(ContinuousServoCircuit::class);
        $circuit->expects($this->once())->method('clockwise')->with(65);
        $circuit->expects($this->once())->method('deadband')->with(88, 92)->willReturnSelf();
        $circuit->expects($this->once())->method('stop');

        $servo = new ContinuousServo($circuit);
        $servo->clockwise(65);
        self::assertSame($servo, $servo->deadband(88, 92));
        $servo->stop();
    }

    public function testButtonDelegatesPollingEdgesAndHoldState(): void
    {
        $circuit = $this->createMock(ButtonCircuit::class);
        $history = [[
            'down' => true,
            'pressed' => true,
            'released' => false,
            'holding' => true,
            'at_ns' => 42,
        ]];

        $circuit->expects($this->once())->method('poll')->willReturnSelf();
        $circuit->method('label')->willReturn('A');
        $circuit->method('isDown')->willReturn(true);
        $circuit->method('isPressed')->willReturn(true);
        $circuit->method('wasReleased')->willReturn(false);
        $circuit->method('isHolding')->willReturn(true);
        $circuit->method('heldMs')->willReturn(600);
        $circuit->method('history')->willReturn($history);

        $button = new Button($circuit);

        self::assertSame($button, $button->poll());
        self::assertSame('A', $button->label());
        self::assertTrue($button->isDown());
        self::assertTrue($button->isPressed());
        self::assertFalse($button->wasReleased());
        self::assertTrue($button->isHolding());
        self::assertSame(600, $button->heldMs());
        self::assertSame($history, $button->history());
    }

    public function testButtonPadDelegatesPollingAndNamedState(): void
    {
        $circuit = $this->createMock(ButtonPadCircuit::class);
        $circuit->expects($this->once())->method('poll')->willReturnSelf();
        $circuit->method('labels')->willReturn(['A', 'B']);
        $circuit->method('pressedLabels')->willReturn(['A']);
        $circuit->method('chord')->with('A', 'B')->willReturn(true);

        $pad = new ButtonPad($circuit);

        self::assertSame($pad, $pad->poll());
        self::assertSame(['A', 'B'], $pad->labels());
        self::assertSame(['A'], $pad->pressedLabels());
        self::assertTrue($pad->chord('A', 'B'));
    }

    public function testPotentiometerDelegatesRawAndNormalizedPosition(): void
    {
        $circuit = $this->createMock(PotentiometerCircuit::class);
        $circuit->expects($this->once())->method('raw')->willReturn(2048);
        $circuit->expects($this->once())->method('position')->willReturn(0.5);

        $potentiometer = new Potentiometer($circuit);

        self::assertSame(2048, $potentiometer->raw());
        self::assertSame(0.5, $potentiometer->position());
    }

    public function testTouchAndPointerExposeSnapshotHelpers(): void
    {
        $contact = new TouchContact(
            id: 7,
            x: 0.25,
            y: 0.75,
            phase: TouchPhase::MOVED,
        );
        $touch_circuit = $this->createMock(TouchCircuit::class);
        $touch_circuit->method('contacts')->willReturn([$contact]);
        $touch_circuit->method('primaryContact')->willReturn($contact);

        $touch = new Touch($touch_circuit);

        self::assertTrue($touch->isTouched());
        self::assertSame(1, $touch->contactCount());
        self::assertSame($contact, $touch->primaryContact());

        $pointer_circuit = $this->createMock(PointerCircuit::class);
        $pointer_circuit->method('coordinateSpace')->willReturn(CoordinateSpace::PIXELS);
        $pointer_circuit->method('x')->willReturn(320.0);
        $pointer_circuit->method('y')->willReturn(240.0);
        $pointer_circuit->method('deltaX')->willReturn(4.0);
        $pointer_circuit->method('deltaY')->willReturn(-2.0);
        $pointer_circuit->method('wheelX')->willReturn(0.0);
        $pointer_circuit->method('wheelY')->willReturn(1.0);

        $pointer = new Pointer($pointer_circuit);

        self::assertSame(
            ['x' => 320.0, 'y' => 240.0, 'space' => CoordinateSpace::PIXELS],
            $pointer->position(),
        );
        self::assertSame(['x' => 4.0, 'y' => -2.0], $pointer->delta());
        self::assertSame(['x' => 0.0, 'y' => 1.0], $pointer->wheel());
    }

    public function testGameControllerAddsAxesBeyondButtonPad(): void
    {
        $circuit = $this->createMock(RumblingGameControllerCircuit::class);
        $circuit->method('connected')->willReturn(true);
        $circuit->method('axis')->with(GameControllerAxis::LEFT_X)->willReturn(0.5);
        $circuit->method('axes')->willReturn(['left_x' => 0.5]);
        $circuit->method('downLabels')->willReturn(['south']);
        $circuit->expects($this->once())->method('rumble')->with(2, 100, 200, 500)->willReturn(true);

        $controller = new GameController($circuit);

        self::assertSame(0.5, $controller->axis(GameControllerAxis::LEFT_X));
        self::assertTrue($controller->supportsRumble());
        self::assertTrue($controller->rumble(2, 100, 200, 500));
        self::assertSame([
            'connected' => true,
            'buttons' => ['south'],
            'axes' => ['left_x' => 0.5],
        ], $controller->snapshot());
    }

    public function testGameControllerReportsUnsupportedRumble(): void
    {
        $controller = new GameController($this->createMock(GameControllerCircuit::class));

        self::assertFalse($controller->supportsRumble());
        self::assertFalse($controller->supportsTriggerRumble());

        $this->expectException(ActuatorException::class);

        $controller->rumble(0, 100, 100, 250);
    }

    #[DataProvider('validWrapperContracts')]
    public function testCircuitFactoryResolvesTheExactContract(string $wrapper, string $contract): void
    {
        $circuit = $this->createMock($contract);
        Circuit::swap(new class($circuit) {
            public function __construct(private IntegratedCircuit $circuit) {}

            public function driver(string $driver): IntegratedCircuit
            {
                return $this->circuit;
            }
        });

        self::assertInstanceOf($wrapper, $wrapper::circuit('valid-driver'));
    }

    #[DataProvider('wrapperClasses')]
    public function testCircuitFactoryRejectsTheWrongContract(string $wrapper): void
    {
        Circuit::swap(new class {
            public function driver(string $driver): IntegratedCircuit
            {
                return new class implements IntegratedCircuit {
                    public function close(): void {}
                };
            }
        });

        $this->expectException(ActuatorException::class);

        $wrapper::circuit('wrong-driver');
    }

    /**
     * @return iterable<string, array{class-string}>
     */
    public static function wrapperClasses(): iterable
    {
        yield 'fan' => [Fan::class];
        yield 'positional servo' => [PositionalServo::class];
        yield 'continuous servo' => [ContinuousServo::class];
        yield 'button' => [Button::class];
        yield 'button pad' => [ButtonPad::class];
        yield 'potentiometer' => [Potentiometer::class];
        yield 'touch' => [Touch::class];
        yield 'pointer' => [Pointer::class];
        yield 'game controller' => [GameController::class];
    }

    /**
     * @return iterable<string, array{class-string, class-string<IntegratedCircuit>}>
     */
    public static function validWrapperContracts(): iterable
    {
        yield 'fan' => [Fan::class, FanCircuit::class];
        yield 'positional servo' => [PositionalServo::class, PositionalServoCircuit::class];
        yield 'continuous servo' => [ContinuousServo::class, ContinuousServoCircuit::class];
        yield 'button' => [Button::class, ButtonCircuit::class];
        yield 'button pad' => [ButtonPad::class, ButtonPadCircuit::class];
        yield 'potentiometer' => [Potentiometer::class, PotentiometerCircuit::class];
        yield 'touch' => [Touch::class, TouchCircuit::class];
        yield 'pointer' => [Pointer::class, PointerCircuit::class];
        yield 'game controller' => [GameController::class, GameControllerCircuit::class];
    }
}
