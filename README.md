<h1 align="center">BoilingCauldrons</h1>
<p align="center">Makes water in cauldrons boil when there's fire under it and allows players to cook food in the boil</p>

<br>

<p align="center">
    <a href="https://poggit.pmmp.io/p/BoilingCauldrons">
        <img src="https://poggit.pmmp.io/shield.state/BoilingCauldrons" alt="Plugin version">
    </a>
    <a href="https://github.com/pmmp/PocketMine-MP">
        <img src="https://poggit.pmmp.io/shield.api/BoilingCauldrons" alt="API version">
    </a>
    <a href="https://poggit.pmmp.io/p/BoilingCauldrons">
        <img src="https://poggit.pmmp.io/shield.dl/BoilingCauldrons" alt="Downloads on Poggit">
    </a>
    <a href="https://github.com/SpiceX/BoilingCauldrons/blob/master/LICENSE">
        <img src="https://img.shields.io/badge/license-Apache%20License%202.0-yellowgreen.svg" alt="license">
    </a>
    <a href="https://gitter.im/SpiceX/BoilingCauldrons">
        <img src="https://img.shields.io/gitter/room/SpiceX/BoilingCauldrons.svg" alt="Gitter">
    </a>
    <a href="https://twitter.com/SpiceX">
        <img src="https://img.shields.io/twitter/url?label=SpiceX%20on%20Twitter&style=social&url=https%3A%2F%2Ftwitter.com%2Fsurvanetwork" alt="Twitter">
    </a>
</p>

##

[• Description](#-description)  
[• Planned Features](#-planned-features)  
[• Configurations](#-configurations)
[• Contribution](#-contribution)  

## 📙 Description

This can be aesthetic or functional or both, as it displays a particle effect above a cauldron and allows the player to cook food in the cauldron when fire or lava is placed under it. The max stack size and time for each item is configurable!!!

Each feature can be individually enabled or disabled in the configuration file.

Particle Effect:
The particles are right above the water regardless of the water level of the cauldron. The normal function of the cauldron is not changed. This is just a small visual effect to make a server seem more alive. I saw a server place a cauldron above fire as if to heat or boil the water, but the water was just sitting there, so I decided to make the setup a little more interesting.

Cooking Food:
To cook food, throw the raw food into the cauldron (the max stack size can be set in the config - defaults to 1). The cooked food will come out after the amount of time set in the config (defaults to 8 seconds) multiplied by the stack size and the water level will go down by one level. There must be some water in the cauldron in order for the food to be accepted into the cauldron.

## 🎁 Planned Features
- **FOOD EFFECTS** Give effects to the player when he eats food cooked in potions from the cauldron.
- **ADVANCED FEATURES** Customizable Particles or blocks
- **FOODS** What foods can be cooked and the corresponding outcomes.

## 🖱 Configurations
There are different types of configurations in the plugin:

- enabled-worlds: In which worlds does the plugin take effect.
- cook-time: How many seconds does a cauldron cook a piece of food. Cooking can be disabled.
- max-stack: How many items does a cauldron can cook at once.
- damages: Control how much damage player take from being inside a boiling cauldron. Damages can be disabled.


## 🙋‍ Contribution
Feel free to contribute if you have ideas or found an issue.

You can:
- [open an issue](https://github.com/SpiceX/BoilingCauldrons/issues) (problems, bugs or feature requests)
- [create a pull request](https://github.com/SpiceX/BoilingCauldrons/pulls) (code contributions like fixed bugs or added features)

Please read our **[Contribution Guidelines](CONTRIBUTING.md)** before creating an issue or submitting a pull request.

Many thanks for their support to all contributors!

