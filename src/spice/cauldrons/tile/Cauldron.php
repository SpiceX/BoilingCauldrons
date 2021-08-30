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

namespace spice\cauldrons\tile;

use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\tile\Spawnable;
use pocketmine\utils\Color;
use spice\cauldrons\BoilingCauldrons;
use spice\cauldrons\task\CookingTask;


class Cauldron extends Spawnable
{
    public const TAG_POTION_ID = "PotionId";
    public const TAG_SPLASH_POTION = "SplashPotion";
    public const TAG_CUSTOM_COLOR = "CustomColor";

    /** @var int */
    protected int $potionID = -1;
    /** @var bool */
    protected bool $splashPotion = false;
    /** @var Color|null */
    protected ?Color $customColor = null;
    /** @var CookingTask */
    public CookingTask $cookingTask;

    /**
     * @param Level $level
     * @param CompoundTag $nbt
     */
    public function __construct(Level $level, CompoundTag $nbt)
    {
        parent::__construct($level, $nbt);
        BoilingCauldrons::getInstance()->getBlockManager()->addCauldron($this);
        BoilingCauldrons::getInstance()->getScheduler()->scheduleRepeatingTask(
            $this->cookingTask = new CookingTask($this), 20
        );
    }

    /**
     * @return bool
     */
    public function isSplashPotion(): bool
    {
        return $this->splashPotion;
    }

    /**
     * @param bool $splashPotion
     */
    public function setSplashPotion(bool $splashPotion): void
    {
        $this->splashPotion = $splashPotion;
        $this->onChanged();
    }

    /**
     * @return Color|null
     */
    public function getCustomColor(): ?Color
    {
        return $this->customColor;
    }

    /**
     * @param Color $customColor
     */
    public function setCustomColor(Color $customColor): void
    {
        $this->customColor = $customColor;
        $this->onChanged();
    }

    public function resetCustomColor(): void
    {
        $this->customColor = null;
        $this->onChanged();
    }

    public function resetPotion(): void
    {
        $this->setPotionID(-1);
    }

    /**
     * @return bool
     */
    public function hasCustomColor(): bool
    {
        return $this->customColor instanceof Color;
    }

    /**
     * @return bool
     */
    public function hasPotion(): bool
    {
        return $this->getPotionID() != -1;
    }

    /**
     * @return int
     */
    public function getPotionID(): int
    {
        return $this->potionID;
    }

    /**
     * @param int $potionID
     */
    public function setPotionID(int $potionID): void
    {
        $this->potionID = $potionID;
        $this->onChanged();
    }

    /**
     * @param CompoundTag $nbt
     */
    protected function writeSaveData(CompoundTag $nbt): void
    {
        $this->applyBaseNBT($nbt);
    }

    /**
     * @param CompoundTag $nbt
     */
    private function applyBaseNBT(CompoundTag $nbt): void
    {
        $nbt->setShort(self::TAG_POTION_ID, $this->potionID);
        $nbt->setByte(self::TAG_SPLASH_POTION, (int)$this->splashPotion);
        if ($this->customColor instanceof Color) {
            $nbt->setInt(self::TAG_CUSTOM_COLOR, $this->customColor->toARGB());
        } else {
            if ($nbt->hasTag(self::TAG_CUSTOM_COLOR, IntTag::class)) {
                $nbt->removeTag(self::TAG_CUSTOM_COLOR);
            }
        }
    }

    /**
     * @param CompoundTag $nbt
     */
    protected function addAdditionalSpawnData(CompoundTag $nbt): void
    {
        $this->applyBaseNBT($nbt);
    }

    /**
     * @param CompoundTag $nbt
     */
    protected function readSaveData(CompoundTag $nbt): void
    {
        if ($nbt->hasTag(self::TAG_POTION_ID, LongTag::class)) {
            $this->potionID = $nbt->getLong(self::TAG_POTION_ID, $this->potionID);
            $nbt->removeTag(self::TAG_POTION_ID);
        }

        if (!$nbt->hasTag(self::TAG_POTION_ID, ShortTag::class)) {
            $nbt->setShort(self::TAG_POTION_ID, $this->potionID);
        }
        $this->potionID = $nbt->getShort(self::TAG_POTION_ID, $this->potionID);

        if (!$nbt->hasTag(self::TAG_SPLASH_POTION, ByteTag::class)) {
            $nbt->setByte(self::TAG_SPLASH_POTION, (int)$this->splashPotion);
        }
        $this->splashPotion = (bool)$nbt->getByte(self::TAG_SPLASH_POTION, (int)$this->splashPotion);

        if ($nbt->hasTag(self::TAG_CUSTOM_COLOR, IntTag::class)) {
            $this->customColor = Color::fromARGB($nbt->getInt(self::TAG_CUSTOM_COLOR));
        }
    }

    public function __destruct()
    {
        $this->cookingTask->dropItems();
        unset($this->cookingTask);
    }
}