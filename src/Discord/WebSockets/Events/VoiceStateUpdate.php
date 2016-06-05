<?php

/*
 * This file is apart of the DiscordPHP project.
 *
 * Copyright (c) 2016 David Cole <david@team-reflex.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace Discord\WebSockets\Events;

use Discord\Parts\WebSockets\VoiceStateUpdate as VoiceStateUpdatePart;
use Discord\WebSockets\Event;
use React\Promise\Deferred;

class VoiceStateUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function handle(Deferred $deferred, $data)
    {
        $state = $this->factory->create(VoiceStateUpdatePart::class, $data, true);

        foreach ($this->discord->guilds as $index => $guild) {
            if ($guild->id == $state->guild_id) {
                foreach ($guild->channels as $cindex => $channel) {
                    if (isset($channel->members[$state->user_id])) {
                        unset($channel->members[$state->user_id]);
                    }

                    if ($channel->id == $state->channel_id) {
                        $channel->members[$state->user_id] = $state;
                    }
                }
            } else {
                foreach ($guild->channels as $cindex => $channel) {
                    if (isset($channel->members[$state->user_id]) && ! $this->discord->bot) {
                        unset($channel->members[$state->user_id]);
                    }
                }
            }
        }

        $deferred->resolve($state);
    }
}