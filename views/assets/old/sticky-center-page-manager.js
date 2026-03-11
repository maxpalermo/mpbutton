class StickyCenterPageManager {
    constructor() {
        this.elements = [];
        this.margin = 10;
    }

    addElement(element, options = {}) {
        // Stile base
        Object.assign(element.style, {
            position: "fixed",
            top: "50%",
            left: "50%",
            transform: "translate(-50%, -50%)",
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
        let currentOffset = 0;

        // Riordina elementi dal centro verso l'esterno (verticalmente)
        this.elements.forEach((element, index) => {
            const offset = currentOffset * (index % 2 === 0 ? 1 : -1);
            element.style.top = `calc(50% + ${offset}px)`;
            const rect = element.getBoundingClientRect();
            if (index % 2 === 1) {
                currentOffset += rect.height + this.margin;
            }
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
