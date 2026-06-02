<template>
    <div class="exam-timer" :class="timerClasses">
        <div class="timer-icon">
            <i :class="iconClass"></i>
        </div>
        <div class="timer-display">
            <div class="timer-time">{{ formattedTime }}</div>
            <div class="timer-label">{{ label }}</div>
        </div>
        <div v-if="showProgress" class="timer-progress">
            <div class="progress">
                <div 
                    class="progress-bar" 
                    :class="progressBarClass"
                    :style="{ width: `${percentage}%` }"
                    role="progressbar"
                    :aria-valuenow="percentage"
                    aria-valuemin="0"
                    aria-valuemax="100"
                ></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useTimer } from '@/Composables/useTimer';

const props = defineProps({
    duration: {
        type: Number,
        required: true
    },
    autoStart: {
        type: Boolean,
        default: false
    },
    showProgress: {
        type: Boolean,
        default: true
    },
    label: {
        type: String,
        default: 'Waktu Tersisa'
    }
});

const emit = defineEmits(['expired', 'warning', 'danger']);

const timer = useTimer(props.duration);

const timerClasses = computed(() => {
    const classes = ['card'];
    
    if (timer.isDanger.value) {
        classes.push('border-danger');
    } else if (timer.isWarning.value) {
        classes.push('border-warning');
    } else {
        classes.push('border-primary');
    }
    
    return classes.join(' ');
});

const iconClass = computed(() => {
    if (timer.isDanger.value) {
        return 'fas fa-exclamation-triangle text-danger';
    } else if (timer.isWarning.value) {
        return 'fas fa-clock text-warning';
    }
    return 'fas fa-clock text-primary';
});

const progressBarClass = computed(() => {
    if (timer.isDanger.value) {
        return 'bg-danger';
    } else if (timer.isWarning.value) {
        return 'bg-warning';
    }
    return 'bg-primary';
});

const formattedTime = computed(() => timer.formattedTime.value);
const percentage = computed(() => 100 - timer.percentage.value);

// Watch for timer events
let lastWarningEmitted = false;
let lastDangerEmitted = false;

const checkTimerStatus = () => {
    if (timer.isExpired.value) {
        emit('expired');
        timer.stop();
    } else if (timer.isDanger.value && !lastDangerEmitted) {
        emit('danger', timer.remainingSeconds.value);
        lastDangerEmitted = true;
    } else if (timer.isWarning.value && !lastWarningEmitted) {
        emit('warning', timer.remainingSeconds.value);
        lastWarningEmitted = true;
    }
};

// Start timer if autoStart is true
if (props.autoStart) {
    timer.start();
}

// Check timer status every second
setInterval(checkTimerStatus, 1000);

// Expose timer methods
defineExpose({
    start: timer.start,
    pause: timer.pause,
    resume: timer.resume,
    stop: timer.stop,
    reset: timer.reset,
    addTime: timer.addTime,
    remainingSeconds: timer.remainingSeconds,
    isRunning: timer.isRunning,
    isPaused: timer.isPaused,
    isExpired: timer.isExpired
});
</script>

<style scoped>
.exam-timer {
    padding: 1rem;
    margin-bottom: 1rem;
    border-width: 2px;
    border-style: solid;
}

.timer-icon {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.timer-display {
    text-align: center;
}

.timer-time {
    font-size: 2.5rem;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    line-height: 1;
}

.timer-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.timer-progress {
    margin-top: 1rem;
}

.progress {
    height: 8px;
    border-radius: 4px;
}

.progress-bar {
    transition: width 1s linear;
}
</style>
