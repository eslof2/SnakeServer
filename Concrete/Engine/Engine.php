<?php declare(strict_types=1);

namespace Concrete\Engine;
use Model\Game\IGame;
use Model\GameState;
use Swoole\Atomic;

class Engine {
    private float $timeExpected;

    public function __construct(IGame $game, Atomic $atomicState) {
        // For a reliable UX we want a reliable tick rate but we don't know how long the game logic itself will take;
        // so just doing gameStuff() and then sleep(1) will "drift" forward off the desired schedule.
        // So we keep track of the time between calls and make up for the difference:

        $time = new Time(); //tracks the difference in time between its calls (deltaNs)
        $game->start($time);

        $this->timeExpected = ($game->getTickRateNs() / $game->getTimeScale()); // what the game logic asks for
        while ($atomicState->get() != GameState::SHUTDOWN->value) {
            $game->process(); //do gameStuff()

            //sleep for as long as we're expected to in order to meet what's asked
            usleep((int)($this->timeExpected / 1000));
            $time->update(); //tracks the difference in time between its calls (deltaNs)

            //the remainder ends up being how long it took for $game->process(); to execute
            $remainder = $time->deltaNs - $this->timeExpected; // ex process()+sleep(1) took 1.1s = 0.1s to compensate for

            $targetSleepTime = $game->getTickRateNs() / $game->getTimeScale(); // what the game logic asks for
            $remainder = $remainder <= $targetSleepTime ?: 0; //gone to sleep maybe todo: look into it
            $nextExpected = $targetSleepTime - $remainder; // minus compensation

            $this->timeExpected = $nextExpected;
        }
    }
}
