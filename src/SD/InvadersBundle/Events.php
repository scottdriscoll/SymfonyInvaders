<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
final class Events
{
    const HEARTBEAT = 'sd.heartbeat';
    const PLAYER_MOVE_LEFT = 'sd.player.left';
    const PLAYER_MOVE_RIGHT = 'sd.player.right';
    const PLAYER_MOVED = 'sd.player.moved';
    const PLAYER_FIRE = 'sd.player.fire';
    const PLAYER_INITIALIZED = 'sd.player.initialized';
}
