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

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\Transparent;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\Potion;
use pocketmine\item\TieredTool;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\utils\Color;
use spice\cauldrons\BoilingCauldrons;
use spice\cauldrons\tile\Cauldron as CauldronTile;
use spice\cauldrons\tile\Tile;

class Cauldron extends Transparent
{

    protected $id = self::CAULDRON_BLOCK;
    protected $itemId = ItemIds::CAULDRON;

    /**
     * Cauldron constructor.
     * @param int $meta
     */
    public function __construct($meta = 0)
    {
        parent::__construct(BlockIds::CAULDRON_BLOCK, $meta, "Cauldron");
        $this->meta = $meta;
    }

    public function canBeActivated(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return "Cauldron";
    }

    public function getHardness(): float
    {
        return 2;
    }

    public function getToolType(): int
    {
        return BlockToolType::TYPE_PICKAXE;
    }

    public function getToolHarvestLevel(): int
    {
        return TieredTool::TIER_WOODEN;
    }

    /**
     * @param Item $item
     * @param Block $blockReplace
     * @param Block $blockClicked
     * @param int $face
     * @param Vector3 $clickVector
     * @param Player|null $player
     * @return bool
     */
    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool
    {
        Tile::createTile(Tile::CAULDRON, $this->getLevel(), CauldronTile::createNBT($this, $face, $item, $player));
        return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    /**
     * @param Item $item
     * @param Player|null $player
     * @return bool
     */
    public function onBreak(Item $item, Player $player = null): bool
    {
        $tile = $this->level->getTile($this->asVector3());
        if ($tile instanceof CauldronTile) {
            BoilingCauldrons::getInstance()->getBlockManager()->removeCauldron($tile);
        }
        return parent::onBreak($item, $player);
    }

    public function tick(): void
    {
        $down = $this->getSide(Vector3::SIDE_DOWN);
        if ($down->getId() === BlockIds::MAGMA || $down->getId() === BlockIds::FIRE) {
            if (BoilingCauldrons::getInstance()->canBeUsedInWorld($this->level)) {
                if ($this->getDamage() === 0) {
                    return;
                }
                for ($i = 0; $i < 3; $i++) {
                    $this->level->addParticle(new GenericParticle($this->asVector3()->add(lcg_value(), 0.5, lcg_value()), Particle::TYPE_SPLASH));
                    $this->level->addParticle(new GenericParticle($this->asVector3()->add(lcg_value(), 0.7, lcg_value()), Particle::TYPE_CANDLE_FLAME));
                }
                if (mt_rand(0, 5) === 3) {
                    $this->level->addParticle(new GenericParticle($this->asVector3()->add(0, 0.5), Particle::TYPE_CAMPFIRE_SMOKE));
                }
                $tile = $this->level->getTile($this->asVector3());
                foreach ($this->level->getNearbyEntities($this->getBoundingBox()->expandedCopy(0, 1, 0)) as $entity) {
                    if ($entity instanceof ItemEntity) {
                        $item = $entity->getItem();
                        if (
                            BoilingCauldrons::canBeCooked($item) and BoilingCauldrons::getInstance()->isCookingEnabled() and
                            $tile->cookingTask->getItemCount() < BoilingCauldrons::getInstance()->getMaxStack()
                        ) {
                            if ($tile instanceof CauldronTile) {
                                $tile->cookingTask->addToQueue($entity);
                            }
                        }
                    }
                    if ($entity instanceof Player) {
                        if (BoilingCauldrons::getInstance()->canDamagePlayers()) {
                            $ev = new EntityDamageByBlockEvent(
                                $this, $entity,
                                EntityDamageEvent::CAUSE_FIRE, BoilingCauldrons::getInstance()->getPlayerDamage()
                            );
                            $entity->attack($ev);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Item $item
     * @param Player|null $player
     * @return bool
     */
    public function onActivate(Item $item, Player $player = null): bool
    {
        $tile = $this->getLevel()->getTile($this);
        if (!($tile instanceof CauldronTile)) {
            return false;
        }
        switch ($item->getId()) {
            case ItemIds::BUCKET:
                if ($item->getDamage() == 0) {
                    if (!$this->isFull() or $tile->hasCustomColor() or $tile->hasPotion()) {
                        break;
                    }
                    $bucket = clone $item;
                    $bucket->setDamage(8);
                    if ($player->isSurvival()) {
                        $player->getInventory()->setItemInHand($bucket);
                    }
                    $this->meta = 0;
                    $tile->resetCustomColor();
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_WATER);
                } elseif ($item->getDamage() == 8) {
                    if ($this->isFull() and !$tile->hasCustomColor() and !$tile->hasPotion()) {
                        break;
                    }
                    $bucket = clone $item;
                    $bucket->setDamage(0);
                    if ($player->isSurvival()) {
                        $player->getInventory()->setItemInHand($bucket);
                    }
                    if ($tile->hasPotion()) {
                        $tile->resetPotion();
                        $tile->setSplashPotion(false);
                        $tile->resetCustomColor();
                        $this->meta = 0;
                        $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_EXPLODE);
                    } else {
                        $this->meta = 6;
                        $tile->resetCustomColor();
                        $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_FILL_WATER);
                    }
                }
                break;
            case ItemIds::POTION:
            case ItemIds::SPLASH_POTION:
                if (!$this->isEmpty() && (
                        ($tile->getPotionId() != $item->getDamage() && $item->getDamage() != 0) || ($item->getId() == ItemIds::POTION && $tile->isSplashPotion()) || ($item->getId() == ItemIds::SPLASH_POTION && !$tile->isSplashPotion()) && $item->getDamage() != 0 || ($item->getDamage() == 0 && $tile->hasPotion()))) {
                    $this->meta = 0;
                    $tile->resetPotion();
                    $tile->setSplashPotion(false);
                    $tile->resetCustomColor();
                    if ($player->isSurvival()) {
                        $player->getInventory()->setItemInHand(Item::get(ItemIds::GLASS_BOTTLE));
                    }
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_EXPLODE);
                } elseif ($item->getDamage() == 0) {
                    $this->meta += 2;
                    if ($this->meta > 6) {
                        $this->meta = 6;
                    }
                    if ($player->isSurvival()) {
                        $player->getInventory()->setItemInHand(Item::get(ItemIds::GLASS_BOTTLE));
                    }
                    $tile->resetPotion();
                    $tile->setSplashPotion(false);
                    $tile->resetCustomColor();
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_FILL_WATER);
                } elseif (!$this->isFull()) {
                    $this->meta += 2;
                    if ($this->meta > 6) {
                        $this->meta = 6;
                    }
                    $tile->setPotionId($item->getDamage());
                    $tile->setSplashPotion($item->getId() == ItemIds::SPLASH_POTION);
                    $col = new Color(0, 0, 0, 0);
                    foreach (Potion::getPotionEffectsById($item->getDamage()) as $effect) {
                        $col = Color::mix($effect->getColor(), $col);
                    }
                    $tile->setCustomColor($col);
                    if ($player->isSurvival()) {
                        $player->getInventory()->setItemInHand(Item::get(ItemIds::GLASS_BOTTLE));
                    }
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_POTION);
                }
                break;
            case ItemIds::GLASS_BOTTLE:
                if ($this->meta < 2) {
                    break;
                }
                $this->meta -= 2;
                if ($tile->hasPotion()) {
                    if ($tile->isSplashPotion()) {
                        $result = Item::get(ItemIds::SPLASH_POTION, $tile->getPotionId());
                    } else {
                        $result = Item::get(ItemIds::POTION, $tile->getPotionId());
                    }
                    if ($this->isEmpty()) {
                        $tile->resetPotion();
                        $tile->setSplashPotion(false);
                        $tile->resetCustomColor();
                    }
                    $item->pop();
                    if (($inv = $player->getInventory())->canAddItem($result)) {
                        $inv->addItem($result);
                    } else {
                        $this->getLevel()->dropItem($player, $result);
                    }
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_POTION);
                } else {
                    if ($player->isSurvival()) {
                        $result = Item::get(ItemIds::POTION);
                        $item->pop();
                        if (($inv = $player->getInventory())->canAddItem($result)) {
                            $inv->addItem($result);
                        } else {
                            $this->getLevel()->dropItem($player, $result);
                        }
                    }
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_WATER);
                }
                break;
        }
        $this->meta += 3;
        $this->getLevel()->setBlock($this, $this, true);
        $this->meta -= 3;
        $this->getLevel()->setBlock($this, $this, true);
        return true;
    }

    public function isFull(): bool
    {
        return $this->meta >= 6;
    }

    public function isEmpty(): bool
    {
        return $this->meta == 0;
    }
}