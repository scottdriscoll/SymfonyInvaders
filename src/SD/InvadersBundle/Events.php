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
    const PLAYER_FIRE = 'sd.player.fire';
    const PLAYER_PROJECTILES_UPDATED = 'sd.player.projectiles.updated';
    const PLAYER_HIT = 'sd.player.hit';
    const BOARD_REDRAW = 'sd.board.redraw';
    const ALIEN_PROJECTILE_END = 'sd.alien.projectile.end';
    const ALIEN_HIT = 'sd.alien.hit';
    const ALIEN_DEAD = 'sd.alien.dead';
    const ALIEN_REACHED_END = 'sd.alien.reached_end';
    const GAME_OVER = 'sd.game.over';
    const BOSS_HIT = 'sd.boss.hit';
    const BOSS_DEAD = 'sd.boss.dead';
    const POWERUP_REACHED_END = 'sd.powerup.reached_end';
    const POWERUP_ACTIVATED = 'sd.powerup.activated';
}
