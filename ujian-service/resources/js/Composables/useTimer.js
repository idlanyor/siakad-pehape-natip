import { ref, computed, onUnmounted } from 'vue';

export function useTimer(initialSeconds = 0) {
    const remainingSeconds = ref(initialSeconds);
    const isRunning = ref(false);
    const isPaused = ref(false);
    let intervalId = null;

    const hours = computed(() => Math.floor(remainingSeconds.value / 3600));
    const minutes = computed(() => Math.floor((remainingSeconds.value % 3600) / 60));
    const seconds = computed(() => remainingSeconds.value % 60);

    const formattedTime = computed(() => {
        const h = String(hours.value).padStart(2, '0');
        const m = String(minutes.value).padStart(2, '0');
        const s = String(seconds.value).padStart(2, '0');
        return `${h}:${m}:${s}`;
    });

    const percentage = computed(() => {
        if (initialSeconds === 0) return 0;
        return ((initialSeconds - remainingSeconds.value) / initialSeconds) * 100;
    });

    const isExpired = computed(() => remainingSeconds.value <= 0);

    const isWarning = computed(() => {
        // Warning when less than 5 minutes remaining
        return remainingSeconds.value > 0 && remainingSeconds.value <= 300;
    });

    const isDanger = computed(() => {
        // Danger when less than 1 minute remaining
        return remainingSeconds.value > 0 && remainingSeconds.value <= 60;
    });

    const start = () => {
        if (isRunning.value || remainingSeconds.value <= 0) return;

        isRunning.value = true;
        isPaused.value = false;

        intervalId = setInterval(() => {
            if (remainingSeconds.value > 0) {
                remainingSeconds.value--;
            } else {
                stop();
            }
        }, 1000);
    };

    const pause = () => {
        if (!isRunning.value) return;

        isPaused.value = true;
        isRunning.value = false;

        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    };

    const resume = () => {
        if (isRunning.value || remainingSeconds.value <= 0) return;

        isPaused.value = false;
        start();
    };

    const stop = () => {
        isRunning.value = false;
        isPaused.value = false;

        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    };

    const reset = (seconds = initialSeconds) => {
        stop();
        remainingSeconds.value = seconds;
    };

    const addTime = (seconds) => {
        remainingSeconds.value += seconds;
    };

    // Cleanup on unmount
    onUnmounted(() => {
        stop();
    });

    return {
        remainingSeconds,
        hours,
        minutes,
        seconds,
        formattedTime,
        percentage,
        isRunning,
        isPaused,
        isExpired,
        isWarning,
        isDanger,
        start,
        pause,
        resume,
        stop,
        reset,
        addTime
    };
}
