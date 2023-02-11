<?php

namespace App\Discord\Moderation\Timeout;

use App\Discord\Core\Bot;
use App\Models\DiscordUser;
use App\Models\Timeout;
use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\Guild\AuditLog\AuditLog;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;

/**
 * The event triggers on ANY use update, we have to manually check if the communication_disabled_until is set to a date
 * in the future. if it is not set, the user never had a timeout before. If it is set to a past date the user has been
 * given a timeout before. We still need to manually read the audit log to figure out the reason for the timeout and who
 * gave the timeout.
 */
class DetectTimeouts
{
    public function __construct()
    {
        Bot::getDiscord()->on(Event::GUILD_MEMBER_UPDATE, function (Member $member, Discord $discord) {
            if ($member->communication_disabled_until == NULL || $member->communication_disabled_until <= Carbon::now()) {
                return;
            }

            $discord->guilds->fetch($member->guild_id)->done(function (Guild $guild) use ($member) {
                $guild->getAuditLog(['limit' => 1])->done(function (AuditLog $auditLog) use ($member, $guild) {
                    foreach ($auditLog->audit_log_entries as $entry) {
                        $endTime = $member->communication_disabled_until;
                        $startTime = Carbon::now();
                        if ($endTime) {
                            $user = DiscordUser::get($entry->user->id);
                            $diff = $endTime->diffInSeconds($startTime);
                            $timeout = Timeout::byGuild($guild->id)->where(['discord_id' => $member->id])->get()->last();
                            $timeoutData = [
                                'discord_id' => $member->id,
                                'discord_username' => $member->username,
                                'length' => $diff ?? 0,
                                'reason' => $entry->reason ?? "Empty",
                                'giver_id' => $user->id,
                                'guild_id' => \App\Models\Guild::get($guild->id)->id,
                            ];

                            if (!$timeout) {
                                \App\Models\Timeout::create($timeoutData);
                            } else {
                                $createdAt = Carbon::create($timeout->created_at);
                                $timeoutEnd = $createdAt->addSeconds($timeout->length);
                                if ($timeoutEnd->isBefore(Carbon::now())) {
                                    \App\Models\Timeout::create($timeoutData);
                                }
                            }
                        }
                    }
                });
            });
        });
    }
}
