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
    const PLAYER_PROJECTILES_UPDATED = 'sd.player.projectiles.updated';
    const BOARD_REDRAW = 'sd.board.redraw';
    const ALIENS_UPDATED = 'sd.aliens.updated';
    const ALIEN_PROJECTILE_END = 'sd.alien.projectile.end';
}
