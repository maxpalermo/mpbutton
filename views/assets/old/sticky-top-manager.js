class StickyTopManager {
    constructor() {
        this.elements = [];
        this.margin = 10;
    }

    addElement(element, options = {}) {
        // Stile base
        Object.assign(element.style, {
            position: "fixed",
            top: "0",
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
        this.repositionAll();

        return element;
    }

    repositionAll() {
        let currentTop = 0;

        // Riordina elementi dall'alto verso il basso
        this.elements.forEach((element) => {
            element.style.top = `${currentTop}px`;
            const rect = element.getBoundingClientRect();
            currentTop += rect.height + this.margin;
        });
    }

    removeElement(element) {
        const index = this.elements.indexOf(element);
        if (index > -1) {
            this.elements.splice(index, 1);
            element.remove();
            this.repositionAll();
        }
    }
}
