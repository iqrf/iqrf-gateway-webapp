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
	<v-divider class='my-2' />
	<h2>{{ `${$t('components.gateway.logs.services.tabs.systemd-journald')} log` }}</h2>
	<v-divider class='my-2' />
	<v-skeleton-loader
		:loading='componentState === ComponentState.Loading'
		type='text@3, paragraph@2, text@5'
	>
		<v-responsive>
			<v-alert
				v-if='oldestRecords'
				type='info'
				variant='tonal'
				:text='$t("components.gateway.logs.journal.oldestRecords")'
			/>
			<v-alert
				v-if='componentState === ComponentState.Reloading'
				class='mb-2'
				type='info'
				variant='tonal'
				:text='$t("components.gateway.logs.journal.messages.fetch.action")'
			/>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.gateway.logs.journal.messages.fetch.failed")'
			/>
			<pre
				v-if='log !== null'
				ref='journal'
				v-scroll.self='scrollUpdate'
				class='log'
			>{{ log }}</pre>
		</v-responsive>
	</v-skeleton-loader>
</template>

<script lang='ts' setup>
import { LogService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { ComponentState } from '@iqrf/iqrf-vue-ui';
import { nextTick, ref, type Ref, type TemplateRef, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: LogService = useApiClient().getGatewayServices().getLogService();
let allowUpdate = false;
let lastCursor: string | null = null;
const log: Ref<string | null> = ref(null);
const journal: TemplateRef<HTMLPreElement> = useTemplateRef('journal');
let lastScrollHeight = 0;
let lastScrollPos = 0;
let oldestRecords = false;

async function getJournalRecords(count: number, cursor: string|null = null): Promise<void> {
	allowUpdate = false;
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const data = await service.getJournalRecords(count, cursor);
		if (data.records.length === 0) {
			componentState.value = ComponentState.Ready;
			oldestRecords = true;
			allowUpdate = false;
			return;
		}
		componentState.value = ComponentState.Ready;
		if (log.value === null) {
			log.value = data.records.join('\n');
		} else {
			log.value = `${data.records.join('\n')}\n${log.value}`;
		}
		if (lastCursor === null) {
			scrollToEnd();
		} else {
			scrollToDisplay();
		}
		lastCursor = data.startCursor;
		allowUpdate = true;
	} catch {
		toast.error(
			i18n.t('components.gateway.logs.journal.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
		if (cursor !== null) {
			scrollToPrevious();
		}
		allowUpdate = true;
	}
}

function scrollUpdate(): void {
	if (!allowUpdate || !journal.value) {
		return;
	}
	if (journal.value.scrollTop === 0) {
		lastScrollHeight = journal.value.scrollHeight;
		lastScrollPos = journal.value.scrollTop;
		getJournalRecords(300, lastCursor);
	}
}

async function scrollToPrevious(): Promise<void> {
	await nextTick();
	if (journal.value) {
		journal.value.scrollTop = lastScrollPos + 1;
	}
}


async function scrollToDisplay(): Promise<void> {
	await nextTick();
	if (journal.value) {
		journal.value.scrollTop += journal.value.scrollHeight - lastScrollHeight;
	}
}

/**
 * Moves scrollbar to the end
 */
async function scrollToEnd(): Promise<void> {
	await nextTick();
	if (journal.value) {
		journal.value.scrollTop = journal.value.scrollHeight;
	}
}

function fetch(): void {
	getJournalRecords(300);
}

defineExpose({
	fetch,
});
</script>
