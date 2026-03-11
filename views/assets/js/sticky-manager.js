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

class StickyManager {
    static POSITIONS = {
        BOTTOM: "bottom",
        TOP: "top",
        LEFT: "left",
        RIGHT: "right",
        CENTER: "center",
        AFTER_CART: "after_cart",
        DESCRIPTION: "description",
    };

    constructor(position) {
        if (!Object.values(StickyManager.POSITIONS).includes(position)) {
            throw new Error(`Invalid position: ${position}. Must be one of: ${Object.values(StickyManager.POSITIONS).join(", ")}`);
        }

        this.position = position;
        this.elements = [];
        this.margin = 10;
    }

    addElement(element, options = {}) {
        // Calcola offset iniziale (es. per whatsapp widget in bottom)
        let initialOffset = this.getInitialOffset();

        let baseStyle = {};
        if (this.position === StickyManager.POSITIONS.DESCRIPTION || this.position === StickyManager.POSITIONS.AFTER_CART) {
            baseStyle = {
                position: "relative",
                boxShadow: "none",
                display: "block",
                background: options.background || "transparent",
                padding: options.padding || "0",
                border: options.border || "none",
                borderRadius: options.borderRadius || "none",
                width: options.width || "auto",
            };
        } else {
            baseStyle = {
                position: "fixed",
                boxShadow: "none",
                display: "none",
                background: options.background || "transparent",
                padding: options.padding || "0",
                border: options.border || "none",
                borderRadius: options.borderRadius || "none",
                zIndex: 1000 + this.elements.length,
                width: options.width || "auto",
            };
        }

        // Aggiungi stili specifici per posizione
        const positionStyle = this.getPositionStyle(initialOffset);
        Object.assign(element.style, baseStyle, positionStyle);

        const delay = element.dataset.delay || 0;
        const expire = element.dataset.expire || 0;

        // Gestione delay
        if (delay > 0) {
            setTimeout(() => {
                fadeIn(element);
            }, delay);
        } else {
            fadeIn(element);
        }

        // Gestione expire
        if (expire > 0) {
            setTimeout(() => {
                fadeOut(element);
            }, expire);
        }

        // Se non c'è expire, chiudi al click
        if (!(expire > 0)) {
            element.addEventListener(
                "click",
                () => {
                    fadeOut(element);
                    setTimeout(() => {
                        if (element && element.parentNode) {
                            element.parentNode.removeChild(element);
                        }
                        this.removeElement(element);
                    }, 550);
                },
                { once: true },
            );
        }

        document.body.appendChild(element);
        if (this.position === StickyManager.POSITIONS.DESCRIPTION) {
            const short_description_content = document.querySelector(".product-information");
            if (short_description_content) {
                short_description_content.insertAdjacentElement("afterbegin", element);
            }
        }

        this.elements.push(element);
        this.repositionAll(initialOffset);

        return element;
    }

    getInitialOffset() {
        // Solo per bottom, controlla se c'è un widget whatsapp
        if (this.position === StickyManager.POSITIONS.BOTTOM) {
            const whatsappEl = document.querySelector(".whatsapp.whatsapp_3.whatsapp-bottomWidth.bottom-right");
            if (whatsappEl) {
                return whatsappEl.offsetHeight;
            }
        }
        return 0;
    }

    getPositionStyle(initialOffset) {
        switch (this.position) {
            case StickyManager.POSITIONS.BOTTOM:
                return {
                    bottom: `${initialOffset}px`,
                    left: "50%",
                    transform: "translateX(-50%)",
                };

            case StickyManager.POSITIONS.TOP:
                return {
                    top: "0",
                    left: "50%",
                    transform: "translateX(-50%)",
                };

            case StickyManager.POSITIONS.LEFT:
                return {
                    left: "0",
                    top: "50%",
                    transform: "translateY(-50%)",
                };

            case StickyManager.POSITIONS.RIGHT:
                return {
                    right: "0",
                    top: "50%",
                    transform: "translateY(-50%)",
                };

            case StickyManager.POSITIONS.CENTER:
                return {
                    top: "50%",
                    left: "50%",
                    transform: "translate(-50%, -50%)",
                };
            case StickyManager.POSITIONS.DESCRIPTION:
            case StickyManager.POSITIONS.AFTER_CART:
            default:
                return {};
        }
    }

    repositionAll(initialOffset = 0) {
        let currentOffset = initialOffset;

        this.elements.forEach((element, index) => {
            const rect = element.getBoundingClientRect();

            switch (this.position) {
                case StickyManager.POSITIONS.BOTTOM:
                    element.style.bottom = `${currentOffset}px`;
                    currentOffset += rect.height + this.margin;
                    break;

                case StickyManager.POSITIONS.TOP:
                    element.style.top = `${currentOffset}px`;
                    currentOffset += rect.height + this.margin;
                    break;

                case StickyManager.POSITIONS.LEFT:
                    element.style.left = `${currentOffset}px`;
                    currentOffset += rect.width + this.margin;
                    break;

                case StickyManager.POSITIONS.RIGHT:
                    element.style.right = `${currentOffset}px`;
                    currentOffset += rect.width + this.margin;
                    break;

                case StickyManager.POSITIONS.CENTER:
                    // Per il centro, distribuisce verticalmente
                    const offset = currentOffset * (index % 2 === 0 ? 1 : -1);
                    element.style.top = `calc(50% + ${offset}px)`;
                    if (index % 2 === 1) {
                        currentOffset += rect.height + this.margin;
                    }
                    break;
            }
        });
    }

    removeElement(element) {
        const index = this.elements.indexOf(element);
        if (index > -1) {
            this.elements.splice(index, 1);
            element.remove();
            this.repositionAll(this.getInitialOffset());
        }
    }
}
