<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
		@submit.prevent='onSubmit()'
	>
		<ICard>
			<template #title>
				{{ $t('pages.config.daemon.data-collecting.title') }}
			</template>
			<template #titleActions>
				<IActionBtn
					:action='Action.Reload'
					container-type='card-title'
					:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					:disabled='componentState === ComponentState.Action'
					@click='getConfig()'
				/>
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.config.daemon.data-collecting.messages.get.failed")'
			/>
			<v-skeleton-loader
				v-else
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='text, heading@2, heading'
			>
				<v-responsive>
					<section v-if='config'>
						<v-checkbox
							v-model='config.autoRun'
							:label='$t("components.config.daemon.data-collecting.autoRun")'
							density='compact'
							hide-details
						/>
						<INumberInput
							v-model='config.period'
							:label='$t("components.config.daemon.data-collecting.period")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.data-collecting.validation.period.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.daemon.data-collecting.validation.period.integer")),
								(v: number) => ValidationRules.min(v, 4, $t("components.config.daemon.data-collecting.validation.period.min")),
							]'
							required
							:prepend-inner-icon='mdiTimerOutline'
						/>
						<INumberInput
							v-model='config.retryPeriod'
							:label='$t("components.config.daemon.data-collecting.retryPeriod")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.data-collecting.validation.retryPeriod.required")),
								(v: number) => ValidationRules.integer(v, $t("components.config.daemon.data-collecting.validation.retryPeriod.integer")),
								(v: number) => ValidationRules.min(v, 1, $t("components.config.daemon.data-collecting.validation.retryPeriod.min")),
							]'
							required
							:prepend-inner-icon='mdiTimerRefreshOutline'
						/>
						<v-checkbox
							v-model='config.asyncReports'
							:label='$t("components.config.daemon.data-collecting.asyncReports")'
							density='compact'
							hide-details
						/>
						<ISelectInput
							v-model='config.messagingList'
							:items='messagingOptions'
							:label='$t("components.config.daemon.data-collecting.messagingList")'
							multiple
							:prepend-inner-icon='mdiMulticast'
						/>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value || componentState !== ComponentState.Ready'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { MessagingType, SensorDataMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SensorDataService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { DaemonApiResponse, SensorDataConfig } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { IqrfGatewayDaemonMessagingInstances } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { Action, ComponentState, IActionBtn, ICard, INumberInput, ISelectInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiMulticast, mdiTimerOutline, mdiTimerRefreshOutline } from '@mdi/js';
import { computed, onBeforeUnmount, ref, type Ref, useTemplateRef } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const form: Ref<VForm | null> = useTemplateRef('form');
const config: Ref<SensorDataConfig> = ref({
	autoRun: false,
	period: 10,
	retryPeriod: 1,
	asyncReports: true,
	messagingList: [],
});
const webappSchedulerService: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const messagings: Ref<IqrfGatewayDaemonMessagingInstances | null> = ref(null);
const msgId: Ref<string | null> = ref(null);

const messagingOptions = computed(() => {
	if (messagings.value === null) {
		return [];
	}
	const mqttOptions = messagings.value.mqtt.map((item: string) => ({
		title: `[MQTT] ${item}`,
		value: {
			type: MessagingType.Mqtt,
			instance: item,
		},
	}));
	const wsOptions = messagings.value.ws.map((item: string) => ({
		title: `[WS] ${item}`,
		value: {
			type: MessagingType.Websocket,
			instance: item,
		},
	}));
	return [
		...mqttOptions,
		...wsOptions,
	];
});

const daemonStore = useDaemonStore();
daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			if (rsp.mType === SensorDataMessages.GetConfig) {
				handleGetConfigResponse(rsp);
			} else if (rsp.mType === SensorDataMessages.SetConfig) {
				handleSetConfigResponse(rsp);
			}
		});
	}
});

async function getMessagings(): Promise<void> {
	try {
		messagings.value = await webappSchedulerService.getMessagingInstances();
	} catch {
		toast.error(
			i18n.t('components.config.daemon.data-collecting.messages.fetchInstances.failed'),
		);
	}
}

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	await getMessagings();
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.config.daemon.data-collecting.messages.get.timeout'),
		() => {
			componentState.value = ComponentState.FetchFailed;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SensorDataService.getConfig(opts),
	);
}

function handleGetConfigResponse(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.config.daemon.data-collecting.messages.get.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
		return;
	}
	config.value = rsp.data.rsp as SensorDataConfig;
	componentState.value = ComponentState.Ready;
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.config.daemon.data-collecting.messages.save.timeout'),
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SensorDataService.setConfig(config.value, opts),
	);
}

function handleSetConfigResponse(rsp: DaemonApiResponse): void {
	componentState.value = ComponentState.Ready;
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.config.daemon.data-collecting.messages.save.failed'),
		);
		return;
	}
	toast.success(
		i18n.t('components.config.daemon.data-collecting.messages.save.success'),
	);
}

onMounted(async () => {
	await getConfig();
});

onBeforeUnmount(() => {
	if (msgId.value !== null) {
		daemonStore.removeMessage(msgId.value);
	}
});
</script>
