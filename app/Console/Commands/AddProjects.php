<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AddProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a list of projects to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domains = [
            'goldenerafinds.co.uk' => 'GoldenEraFinds',
            'fitninja.co.uk' => 'FitNinja',
            'startmoto.co.uk' => 'StartMoto',
            'skills-trade.com' => 'SkillsTrade',
            'accessora.co.uk' => 'Accessora',
            'macix.co.uk' => 'Macix',
            'jumlee.co.uk' => 'Jumlee',
            '1business.co.uk' => '1Business',
            'blackcardhouse.com' => 'BlackCardHouse',
            'elysianlane.com' => 'ElysianLane',
            'oldworldcharm.co.uk' => 'OldWorldCharm',
            'aifitworld.co.uk' => 'AiFitWorld',
            'tagwearly.co.uk' => 'Tagwearly',
            'grovegear.co.uk' => 'GroveGear',
            'playbeam.co.uk' => 'PlayBeam',
            'foconta.co.uk' => 'Foconta',
            'toolshaven.co.uk' => 'ToolsHaven',
            'delicanto.co.uk' => 'Delicanto',
            'zeltrionix.com' => 'Zeltrionix',
            'marinebeauty.co.uk' => 'MarineBeauty',
            'officore.co.uk' => 'Officore',
            'mazzurco.co.uk' => 'Mazzurco',
            'electroplace.co.uk' => 'ElectroPlace',
            'stargen.co.uk' => 'Stargen',
            'recipegen.co.uk' => 'RecipeGen',
            'creativesparkstudios.co.uk' => 'CreativeSparkStudios',
            'drezza.co.uk' => 'Drezza',
            'movawe.co.uk' => 'Movawe',
            'shapeai.co.uk' => 'ShapeAi',
            'sivora.co.uk' => 'Sivora',
            'tolvia.co.uk' => 'Tolvia',
            'electreia.co.uk' => 'Electreia',
            'oh-yes-toys.co.uk' => 'OhYesToys',
            'avemex.co.uk' => 'Avemex',
            'urbanbags.co.uk' => 'UrbanBags',
            'toolGarden.co.uk' => 'ToolGarden',
            'antique-aura.co.uk' => 'AntiqueAura',
            'progerito.co.uk' => 'Progerito',
            'takeyourgoods.co.uk' => 'TakeYourGoods',
            'motheroftheyear.co.uk' => 'MotherOfTheYear',
            'noirdrop.co.uk' => 'NoirDrop',
            'voltoria.co.uk' => 'Voltoria',
            'chaletcoaching.co.uk' => 'ChaletCoaching',
            'ariessim.co.uk' => 'AriesSim',
            'octavelogistics.co.uk' => 'OctaveLogistics',
            'dowear.co.uk' => 'DoWear',
            'imgedit.co.uk' => 'ImgEdit',
            'capique.co.uk' => 'Capique',
            'qepacko.co.uk' => 'Qepacko',
            'clevon.co.uk' => 'Clevon',
            'makemy-cv.co.uk' => 'MakeMyCv',
            'velixo.co.uk' => 'Velixo',
            'ravora.co.uk' => 'Ravora',
            'ifileconverter.co.uk' => 'IFileConverter',
            'ventira.co.uk' => 'Ventira',
            'vauldex.co.uk' => 'Vauldex',
            'kidwear.co.uk' => 'KidWear',
            'averis.co.uk' => 'Averis',
            'qellum.co.uk' => 'Qellum',
            'citymotion.co.uk' => 'CityMotion',
            'olynto.co.uk' => 'Olynto',
            'nivro.co.uk' => 'Nivro',
            'coreon.co.uk' => 'Coreon',
            'cybraxo.co.uk' => 'Cybraxo',
            'cheffmate.co.uk' => 'CheffMate',
            'xloffice.co.uk' => 'XlOffice',
        ];

        $clientId = 1;
        $countAdded = 0;
        $countSkipped = 0;

        foreach ($domains as $domain => $name) {
            $domain = trim($domain);
            
            if (Project::where('domain', $domain)->exists()) {
                $this->info("Skipping $domain - already exists.");
                $countSkipped++;
                continue;
            }

            Project::create([
                'client_id' => $clientId,
                'name' => $name,
                'domain' => $domain,
                'status' => 'live',
                'is_monitoring_enabled' => true,
            ]);

            $this->info("Added project: $name ($domain)");
            $countAdded++;
        }

        $this->info("Finished. Added: $countAdded, Skipped: $countSkipped");
    }
}
