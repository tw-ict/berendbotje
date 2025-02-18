<?php

namespace App\Discord\Fun\Commands;

use App\Discord\Core\Builders\EmbedBuilder;
use App\Discord\Core\SlashIndexCommand;
use App\Domain\Fun\Helpers\Helper;
use App\Domain\Fun\Models\Bump;
use App\Domain\Permission\Enums\Permission;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Exception;

class BumpStatistics extends SlashIndexCommand
{
    public function permission(): Permission
    {
        return Permission::NONE;
    }

    public function trigger(): string
    {
        return 'bumpstats';
    }

    public function __construct()
    {
        $this->description = __('bot.slash.bumpstats');

        $this->slashCommandOptions = [
            [
                'name' => 'date-range',
                'description' => __('bot.date-range'),
                'type' => Option::STRING,
                'required' => true,
                'choices' => [
                    ['name' => __('bot.monthly'), 'value' => 'monthly'],
                    ['name' => __('bot.all-time'), 'value' => 'all-time'],
                ]
            ],
        ];

        parent::__construct();
    }


    /**
     * @return Embed
     * @throws Exception
     */
    public function getEmbed(): Embed
    {
        $description = "";
        $builder = EmbedBuilder::create($this, __('bot.bump.title'));

        if (strtolower($this->getOption('date-range')) === 'all-time') {
            $this->total = Bump::byGuild($this->guildId)->groupBy('user_id')->get()->count();
            foreach (Bump::byGuild($this->guildId)->groupBy('user_id')->orderBy('total', 'desc')->skip($this->getOffset($this->getLastUser()))->limit($this->perPage)->selectRaw('*, sum(count) as total')->get() as $index => $bumper) {
                $description .= Helper::indexPrefix($index, $this->getOffset($this->getLastUser()));
                $description .= "**{$bumper->user->tag()}** •  {$bumper->total}\n";
            }
            $builder->setDescription(__('bot.bump.description', ['bumpers' => $description]));
        } else {
            $this->total = Bump::byGuild($this->guildId)->whereMonth('created_at', date('m'))->groupBy('user_id')->get()->count();
            foreach (Bump::byGuild($this->guildId)
                         ->whereMonth('created_at', date('m'))
                         ->groupBy('user_id')
                         ->orderBy('total', 'desc')
                         ->skip($this->getOffset($this->getLastUser()))
                         ->limit($this->perPage)
                         ->selectRaw('*, sum(count) as total')
                         ->get() as $index => $bumper) {

                $description .= Helper::indexPrefix($index, $this->getOffset($this->getLastUser()));
                $description .= "**{$bumper->user->tag()}** •  {$bumper->total}\n";
            }
            $builder->setDescription(__('bot.bump.description-month', ['bumpers' => $description]));
        }

        return $builder->getEmbed();
    }

    /**
     * @param Interaction $interaction
     * @return array
     */
    public function autoComplete(Interaction $interaction): array
    {
        return [];
    }
}
