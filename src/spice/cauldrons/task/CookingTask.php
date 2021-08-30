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

use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use spice\cauldrons\BoilingCauldrons;
use spice\cauldrons\tile\Cauldron as CauldronTile;

class CookingTask extends Task
{

    /** @var int[] $queue */
    private array $queue = [];
    /** @var ItemEntity[] $items */
    private array $items = [];
    /** @var int */
    private int $cookingTime;
    /** @var CauldronTile */
    private CauldronTile $cauldron;

    /**
     * @param CauldronTile $cauldron
     */
    public function __construct(CauldronTile $cauldron)
    {
        $this->cookingTime = BoilingCauldrons::getInstance()->getCookingTime();
        $this->cauldron = $cauldron;
    }

    /**
     * @param ItemEntity $item
     */
    public function addToQueue(ItemEntity $item): void
    {
        $this->queue[$item->getId()] = 0;
        $this->items[$item->getId()] = $item;
        if (!$item->isClosed()) {
            $item->flagForDespawn();
        }
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void
    {
        if ($this->cauldron->isClosed()) {
            $this->getHandler()->cancel();
            return;
        }
        foreach ($this->queue as $id => $tick) {
            $item = $this->items[$id];

            if ($tick === $this->cookingTime) {
                $this->cook($item->getItem());
                unset($this->queue[$id]);
                unset($this->items[$id]);
                continue;
            }

            $this->queue[$id]++;
        }
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function dropItems(): void
    {
        foreach ($this->items as $item) {
            $this->cauldron->level->dropItem($this->cauldron->asVector3()->add(0.5, 1, 0.5), $item->getItem());
        }
    }

    /**
     * @param Item $item
     */
    private function cook(Item $item): void
    {
        $this->cauldron->getLevel()->dropItem($this->cauldron->asVector3()->add(0.5, 1, 0.5), BoilingCauldrons::getCookedItem($item));
    }
}