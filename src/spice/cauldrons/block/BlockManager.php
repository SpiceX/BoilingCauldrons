<?php
/**
 * Copyright 2021-2022 Spice
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);

namespace spice\cauldrons\block;

use pocketmine\block\BlockFactory;
use spice\cauldrons\BoilingCauldrons;
use spice\cauldrons\task\CauldronUpdaterTask;
use spice\cauldrons\tile\Cauldron as CauldronTile;
use spice\cauldrons\tile\Tile;

class BlockManager
{
    /** @var BoilingCauldrons */
    private BoilingCauldrons $plugin;
    /** @var CauldronTile[] */
    private array $cauldrons = [];

    /**
     * @param BoilingCauldrons $plugin
     */
    public function __construct(BoilingCauldrons $plugin)
    {
        $this->plugin = $plugin;
        $this->init();
    }

    private function init(): void
    {
        BlockFactory::registerBlock(new Cauldron(), true);
        Tile::init();
        if (in_array("default", $this->plugin->settings["enabled-worlds"], true)) {
            $this->plugin->settings["enabled-worlds"][] = $this->plugin->getServer()->getDefaultLevel()->getFolderName();
        }
        $this->plugin->getScheduler()->scheduleRepeatingTask(new CauldronUpdaterTask($this->plugin), 20);
    }

    /**
     * @param CauldronTile $cauldron
     */
    public function addCauldron(CauldronTile $cauldron): void
    {
        $this->cauldrons[$cauldron->getId()] = $cauldron;
    }

    /**
     * @param CauldronTile $cauldron
     */
    public function removeCauldron(CauldronTile $cauldron): void
    {
        $cauldron->cookingTask->dropItems();
        if (isset($this->cauldrons[$cauldron->getId()])) unset($this->cauldrons[$cauldron->getId()]);
    }

    /**
     * @return BoilingCauldrons
     */
    public function getPlugin(): BoilingCauldrons
    {
        return $this->plugin;
    }

    /**
     * @return CauldronTile[]
     */
    public function getCauldrons(): array
    {
        return $this->cauldrons;
    }
}