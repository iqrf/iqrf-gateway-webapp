<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<Card>
		<template #title>
			{{ $t('components.maintenance.mender.remount.title') }}
		</template>
		<v-btn
			class='mr-2'
			color='primary'
			:disabled='componentState === ComponentState.Saving'
			@click='onSubmit(MenderMountMode.RO)'
		>
			{{ $t('components.maintenance.mender.remount.readonly') }}
		</v-btn>
		<v-btn
			color='primary'
			:disabled='componentState === ComponentState.Saving'
			@click='onSubmit(MenderMountMode.RW)'
		>
			{{ $t('components.maintenance.mender.remount.readwrite') }}
		</v-btn>
	</Card>
</template>

<script lang='ts' setup>
import { type MenderService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { MenderMountMode, type MenderRemount } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/layout/card/Card.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const service: MenderService = useApiClient().getMenderService();

function onSubmit(mode: MenderMountMode): void {
	const data: MenderRemount = {
		mode: mode,
	};
	componentState.value = ComponentState.Saving;
	service.remount(data)
		.then(() => {
			toast.success(
				i18n.t('components.maintenance.mender.remount.messages.success'),
			);
			componentState.value = ComponentState.Ready;
		})
		.catch(() => {
			componentState.value = ComponentState.Ready;
			toast.error('TODO REMOUNT ERROR HANDLING');
		});
}
</script>
