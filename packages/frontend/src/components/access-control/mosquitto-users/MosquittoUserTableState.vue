<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
	<span :class='statusTextColor'>
		{{ statusText }}
		<v-tooltip
			v-if='showTooltip'
			activator='parent'
			location='bottom'
		>
			{{ formatTime(blockedAt) }}
		</v-tooltip>
	</span>
</template>

<script lang='ts' setup>
import { MosquittoUserState } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { DateTime } from 'luxon';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { useLocaleStore } from '@/store/locale';

const componentProps = defineProps<{
	state: MosquittoUserState;
	blockedAt: DateTime|null;
}>();
const i18n = useI18n();
const localeStore = useLocaleStore();

const showTooltip = computed<boolean>(() => {
	return componentProps.state === MosquittoUserState.Blocked && componentProps.blockedAt !== null;
});

const statusText = computed<string>(() => {
	if (componentProps.state === MosquittoUserState.Active) {
		return i18n.t('components.accessControl.mosquittoUsers.states.active');
	}
	return i18n.t('components.accessControl.mosquittoUsers.states.blocked');
});

const statusTextColor = computed<string>(() => {
	if (componentProps.state === MosquittoUserState.Active) {
		return 'text-success';
	}
	return 'text-red';
});

function formatTime(time: DateTime | null): string|null {
	if (time === null) {
		return null;
	}
	return time.setLocale(localeStore.getLocale).toLocaleString(DateTime.DATETIME_SHORT_WITH_SECONDS);
}
</script>
