<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\tasks;

use DaPigGuy\PiggyShopUI\PiggyShopUI;
use Exception;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

class CheckUpdatesTask extends AsyncTask
{
    public function onRun(): void
    {
        $this->setResult([Internet::getURL("https://poggit.pmmp.io/releases.json?name=PiggyShopUI", 10, [], $error), $error]);
    }

    public function onCompletion(Server $server): void
    {
        $plugin = PiggyShopUI::getInstance();
        try {
            if ($plugin->isEnabled()) {
                $results = $this->getResult();

                $error = $results[1];
                if ($error !== null) throw new Exception($error);

                $data = json_decode($results[0], true);
                if (version_compare($plugin->getDescription()->getVersion(), $data[0]["version"]) === -1) {
                    if ($server->getPluginManager()->isCompatibleApi($data[0]["api"][0]["from"])) {
                        PiggyShopUI::getInstance()->getLogger()->info("PiggyShopUI v" . $data[0]["version"] . " is available for download at " . $data[0]["artifact_url"] . "/PiggyShopUI.phar");
                    }
                }
            }
        } catch (Exception $exception) {
            $plugin->getLogger()->warning("Auto-update check failed.");
            $plugin->getLogger()->debug((string)$exception);
        }
    }
}