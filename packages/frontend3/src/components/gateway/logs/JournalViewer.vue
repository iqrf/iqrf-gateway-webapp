<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

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
import { LogService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { type JournalRecords } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { nextTick, ref, type Ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: LogService = useApiClient().getGatewayServices().getLogService();
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

async function getJournalRecords(count: number, cursor: string|null = null): Promise<void> {
	allowUpdate = false;
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	try {
		const data = await service.getJournalRecords(count, cursor);
		if (data.records.length === 0) {
			componentState.value = ComponentState.Ready;
			oldestRecords = true;
			allowUpdate = false;
			return;
		}
		log.value = `${data.records.join('\n')}\n${log.value}`;
		if (lastCursor === null) {
			scrollToEnd();
		} else {
			scrollToDisplay();
		}
		lastCursor = data.startCursor;
		allowUpdate = true;
	} catch {
		toast.error('TODO JOURNAL FAILED ERROR HANDLING');
		if (cursor !== null) {
			scrollToPrevious();
		}
		allowUpdate = true;
	}
	componentState.value = ComponentState.Ready;
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
