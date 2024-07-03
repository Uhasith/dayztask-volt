<div x-data="{
    id: $wire.entangle('taskId'),
    time: $wire.entangle('trackedTime'),
    running: $wire.entangle('timerRunning'),
    timer: null,
    startTimer() {
        let startTime = new Date().getTime();
        let initialTime = this.convertToMilliseconds(this.time);
        this.timer = setInterval(() => {
            let now = new Date().getTime();
            let elapsed = now - startTime + initialTime;
            let hours = Math.floor(elapsed / (1000 * 60 * 60));
            let minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((elapsed % (1000 * 60)) / 1000);
            this.time = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }, 1000);
    },
    stopTimer() {
        clearInterval(this.timer);
        this.timer = null;
    },
    convertToMilliseconds(time) {
        let parts = time.split(':');
        return (+parts[0] * 3600 + +parts[1] * 60 + +parts[2]) * 1000;
    }
}" x-init="if (running) { startTimer(); }">
    <x-wui-badge flat amber x-text="time"
        x-on:start-tracking.window="if ($event.detail[0].id == id) { startTimer(); }"
        x-on:end-tracking.window="if ($event.detail[0].id == id) { stopTimer(); }" />
</div>
