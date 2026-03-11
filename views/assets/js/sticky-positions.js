class StickyPositions {
    static get POSITIONS() {
        if (typeof StickyManager === "undefined" || !StickyManager?.POSITIONS) {
            throw new Error("StickyPositions requires StickyManager to be loaded before sticky-positions.js");
        }
        return StickyManager.POSITIONS;
    }

    constructor(options = {}) {
        this.options = options || {};
        this.managers = new Map();

        const positions = Object.values(StickyPositions.POSITIONS);
        positions.forEach((pos) => {
            this.managers.set(pos, new StickyManager(pos));
        });
    }

    getManager(position) {
        if (!this.managers.has(position)) {
            this.managers.set(position, new StickyManager(position));
        }
        return this.managers.get(position);
    }

    addElement(position, element, options = {}) {
        const mgr = this.getManager(position);
        return mgr.addElement(element, options);
    }

    removeElement(position, element) {
        const mgr = this.getManager(position);
        return mgr.removeElement(element);
    }

    clear(position = null) {
        if (position) {
            const mgr = this.getManager(position);
            const items = Array.from(mgr.elements);
            items.forEach((el) => mgr.removeElement(el));
            return;
        }

        for (const mgr of this.managers.values()) {
            const items = Array.from(mgr.elements);
            items.forEach((el) => mgr.removeElement(el));
        }
    }
}

if (typeof window !== "undefined") {
    window.StickyPositions = StickyPositions;
}
