class StickyBottomManager {
    constructor() {
        this.elements = [];
        this.margin = 10;
    }

    addElement(element, options = {}) {
        let bottom = 0;
        const whatsappEl = document.querySelector(".whatsapp.whatsapp_3.whatsapp-bottomWidth.bottom-right");
        if (whatsappEl) {
            bottom = whatsappEl.offsetHeight;
        }

        if (options.position === "desc" || options.position === "after-cart") {
            // Stile base
            Object.assign(element.style, {
                position: "relative",
                background: options.background || "transparent",
                padding: options.padding || "0",
                border: options.border || "none",
                borderRadius: options.borderRadius || "none",
                boxShadow: "none",
                zIndex: 1000 + this.elements.length,
                width: options.width || "auto",
                display: "none",
            });
        } else {
            // Stile base
            Object.assign(element.style, {
                position: "fixed",
                bottom: `${bottom}px`,
                left: "50%",
                transform: "translateX(-50%)",
                background: options.background || "transparent",
                padding: options.padding || "0",
                border: options.border || "none",
                borderRadius: options.borderRadius || "none",
                boxShadow: "none",
                zIndex: 1000 + this.elements.length,
                width: options.width || "auto",
                display: "none",
            });
        }

        const delay = element.dataset.delay || 0;
        const expire = element.dataset.expire || 0;

        //se options.delay > 0 allora fa un setTimeout per mostrare l'elemento
        if (delay > 0) {
            setTimeout(() => {
                fadeIn(element);
            }, delay);
        } else {
            fadeIn(element);
        }

        //se options.expire > 0 allora fa un setTimeout per nascondere l'elemento
        if (expire > 0) {
            setTimeout(() => {
                fadeOut(element);
            }, expire);
        }

        document.body.appendChild(element);
        this.elements.push(element);
        this.repositionAll(bottom);

        return element;
    }

    repositionAll(bottom) {
        // Riordina elementi dal basso verso l'alto
        this.elements.forEach((element) => {
            element.style.bottom = `${bottom}px`;
            const rect = element.getBoundingClientRect();
            bottom += rect.height + this.margin;
        });
    }

    removeElement(element, bottom) {
        const index = this.elements.indexOf(element);
        if (index > -1) {
            this.elements.splice(index, 1);
            element.remove();
            this.repositionAll(bottom);
        }
    }
}
