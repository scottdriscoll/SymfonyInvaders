<?php

namespace App\Tui;

use App\Event\GameOverEvent;
use App\Event\HeartbeatEvent;
use App\Event\PlayerFireEvent;
use App\Event\PlayerMoveLeftEvent;
use App\Event\PlayerMoveRightEvent;
use App\Game\AlienManager;
use App\Game\Board;
use App\Game\GameClock;
use App\Tui\Widget\InvadersDashboardWidget;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Tui\Event\InputEvent;
use Symfony\Component\Tui\Input\Key;
use Symfony\Component\Tui\Input\KeyParser;
use Symfony\Component\Tui\Tui;

final readonly class InvadersTuiRunner
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private Board $board,
        private AlienManager $alienManager,
        private GameViewStateFactory $stateFactory,
        private GameClock $gameClock,
    ) {
    }

    public function run(): void
    {
        $this->board->setMessage('Arrow keys to move, space to shoot.');
        $this->board->initialize();
        $this->alienManager->initialize();

        $tui = new Tui();
        $dashboard = new InvadersDashboardWidget();
        $dashboard->setState($this->stateFactory->create());
        $tui->add($dashboard);

        $gameOver = false;
        $keyParser = new KeyParser();

        $gameOverListener = function (GameOverEvent $event) use (&$gameOver): void {
            $gameOver = true;
        };
        $this->eventDispatcher->addListener(GameOverEvent::class, $gameOverListener, -255);

        $tui->addListener(function (InputEvent $event) use ($keyParser, $tui, $dashboard, &$gameOver): void {
            $key = $keyParser->parse($event->getData())['key'] ?? null;
            $gameEvent = match ($key) {
                Key::LEFT => new PlayerMoveLeftEvent(),
                Key::RIGHT => new PlayerMoveRightEvent(),
                Key::SPACE => new PlayerFireEvent(),
                Key::ESCAPE, 'q', Key::ctrl('c') => new GameOverEvent(),
                default => null,
            };

            if (null === $gameEvent) {
                return;
            }

            $this->eventDispatcher->dispatch($gameEvent);

            if ($gameEvent instanceof GameOverEvent) {
                $gameOver = true;
                $tui->stop();
            }

            $event->stopPropagation();
            if ($dashboard->setState($this->stateFactory->create($gameOver))) {
                $tui->requestRender();
            }
        });

        $tui->onTick(function () use ($tui, $dashboard, &$gameOver): bool {
            $this->eventDispatcher->dispatch(new HeartbeatEvent($this->gameClock->now()));

            if ($dashboard->setState($this->stateFactory->create($gameOver))) {
                $tui->requestRender();
            }

            if ($gameOver) {
                $tui->stop();
            }

            return !$gameOver;
        });

        try {
            $tui->run();
        } finally {
            $this->eventDispatcher->removeListener(GameOverEvent::class, $gameOverListener);
        }
    }
}
