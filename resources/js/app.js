import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

window.logoSpotlight = (count) => ({
    active: -1,
    count: count,
    timer: null,
    start() {
        this.next();
        this.timer = setInterval(() => this.next(), 2500);
    },
    next() {
        let next;
        do {
            next = Math.floor(Math.random() * this.count);
        } while (next === this.active && this.count > 1);
        this.active = next;
    },
    destroy() {
        if (this.timer) clearInterval(this.timer);
    },
});

window.Alpine = Alpine;
Alpine.start();