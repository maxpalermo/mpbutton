/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

// Fade In
function fadeIn(element) {
    element.style.display = "block";
    element.style.opacity = "0";
    element.classList.remove("fade-out");
    
    // Forza il reflow per permettere al browser di applicare display: block
    element.offsetHeight;
    
    // Poi applica l'animazione
    requestAnimationFrame(() => {
        element.classList.add("fade-in");
    });
}

// Fade Out
function fadeOut(element) {
    element.classList.remove("fade-in");
    element.classList.add("fade-out");
    
    // Nasconde l'elemento dopo l'animazione
    setTimeout(() => {
        if (element.classList.contains("fade-out")) {
            element.style.display = "none";
        }
    }, 500); // Deve corrispondere alla durata dell'animazione CSS
}
