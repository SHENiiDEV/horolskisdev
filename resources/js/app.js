import './bootstrap';

document.addEventListener('alpine:init', () => {
    Alpine.store('timer', {
        seconds: 0,
        running: false,
        interval: null,
        description: '',
        startTime: null,

        init(initialSeconds = 0, initialRunning = false, description = '', startTime = null) {
            this.seconds = parseInt(initialSeconds);
            this.running = !!initialRunning;
            this.description = description;
            this.startTime = startTime; // timestamp string

            if (this.running) {
                this.startInterval();
            }
        },

        startInterval() {
            if (this.interval) clearInterval(this.interval);
            this.interval = setInterval(() => {
                this.seconds++;
            }, 1000);
        },

        start(description) {
            this.running = true;
            this.description = description;
            this.seconds = 0;
            this.startInterval();
        },

        stop() {
            this.running = false;
            if (this.interval) clearInterval(this.interval);
            this.seconds = 0;
            this.description = '';
        },
        
        get formattedTime() {
            const h = Math.floor(this.seconds / 3600);
            const m = Math.floor((this.seconds % 3600) / 60);
            const s = this.seconds % 60;
            return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }
    });
});
