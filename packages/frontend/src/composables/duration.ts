import { onUnmounted, ref, type Ref } from 'vue';

export function useDuration() {
	const running: Ref<boolean> = ref(false);
	const startTime: Ref<number> = ref(0);
	const duration: Ref<number> = ref(0);
	let intervalId: number | null = null;

	function start(): void {
		if (running.value) {
			return;
		}
		running.value = true;
		startTime.value = Date.now();
		intervalId = window.setInterval(() => {
			duration.value = Date.now() - startTime.value;
		}, 200);
	}

	function stop(): void {
		if (!running.value || intervalId === null) {
			return;
		}
		window.clearInterval(intervalId);
		intervalId = null;
		running.value = false;
	}

	function reset(): void {
		stop();
		duration.value = 0;
	}

	onUnmounted(() => {
		stop();
	});

	return {
		running,
		startTime,
		duration,
		start,
		stop,
		reset,
	};
}
