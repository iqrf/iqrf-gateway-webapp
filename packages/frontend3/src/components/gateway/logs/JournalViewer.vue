<template>
	<v-skeleton-loader
		:loading='componentState === ComponentState.Loading'
		type='text@3, paragraph@2, text@5'
	>
		<v-responsive>
			<v-alert
				v-if='oldestRecords'
				color='info'
			>
				{{ $t('components.gateway.logs.journal.oldestRecords') }}
			</v-alert>
			<pre
				ref='journal'
				v-scroll.self='scrollUpdate'
				class='log'
			>{{ log }}</pre>
		</v-responsive>
	</v-skeleton-loader>
</template>

<script lang='ts' setup>
import { type JournalService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type JournalRecords } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { nextTick, type Ref, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: JournalService = useApiClient().getConfigServices().getJournalService();
let allowUpdate = false;
let lastCursor: string | null = null;
const log: Ref<string | null> = ref(null);
const journal: Ref<Element | null> = ref(null);
let lastScrollHeight = 0;
let lastScrollPos = 0;
let oldestRecords = false;

const stop = watch(journal, (newVal: Element|null, oldVal: Element|null) => {
	if (oldVal === null && newVal !== null) {
		getJournalRecords(300);
		stop();
	}
});

function getJournalRecords(count: number, cursor: string|null = null): void {
	allowUpdate = false;
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getRecords(count, cursor)
		.then(async (response: JournalRecords) => {
			if (response.records.length === 0) {
				componentState.value = ComponentState.Ready;
				oldestRecords = true;
				allowUpdate = false;
				return;
			}
			componentState.value = ComponentState.Ready;
			log.value = `${response.records.join('\n')}\n${log.value}`;
			if (lastCursor === null) {
				scrollToEnd();
			} else {
				scrollToDisplay();
			}
			lastCursor = response.startCursor;
			allowUpdate = true;
		})
		.catch(() => {
			toast.error('TODO JOURNAL FAILED ERROR HANDLING');
			if (cursor !== null) {
				scrollToPrevious();
			}
			componentState.value = ComponentState.Ready;
			allowUpdate = true;
		});
}

function scrollUpdate(): void {
	if (!allowUpdate) {
		return;
	}
	const el = (journal.value!);
	if (el.scrollTop === 0) {
		lastScrollHeight = el.scrollHeight;
		lastScrollPos = el.scrollTop;
		getJournalRecords(300, lastCursor);
	}
}

function scrollToPrevious(): void {
	nextTick(() => {
		const el = (journal.value!);
		el.scrollTop = lastScrollPos + 1;
	});
}


function scrollToDisplay(): void {
	nextTick(() => {
		const el = (journal.value!);
		const diff = el.scrollHeight - lastScrollHeight;
		el.scrollTop += diff;
	});
}

/**
 * Moves scrollbar to the end
 */
function scrollToEnd(): void {
	nextTick(() => {
		const el = (journal.value!);
		el.scrollTop = el.scrollHeight;
	});
}

</script>
