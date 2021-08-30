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

namespace spice\cauldrons\task;

use pocketmine\scheduler\Task;
use spice\cauldrons\block\Cauldron as CauldronBlock;
use spice\cauldrons\BoilingCauldrons;

class CauldronUpdaterTask extends Task
{
    /** @var BoilingCauldrons */
    private BoilingCauldrons $plugin;

    /**
     * @param BoilingCauldrons $plugin
     */
    public function __construct(BoilingCauldrons $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void
    {
        foreach ($this->plugin->getBlockManager()->getCauldrons() as $cauldronTile) {
            $block = $cauldronTile->getBlock();
            if ($block instanceof CauldronBlock) {
                $block->tick();
            }
        }
    }
}