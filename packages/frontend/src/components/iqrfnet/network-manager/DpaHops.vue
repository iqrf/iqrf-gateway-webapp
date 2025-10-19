<template>
	<v-form
		ref='form'
		:disabled='componentState === ComponentState.Action'
	>
		<ICard flat tile>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.dpa-params.dpa-value.title') }}
			</v-card-title>
			<v-alert
				class='mb-4'
				type='info'
				variant='tonal'
				:text='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.notes.hops")'
			/>
			<INumberInput
				v-model='requestHops'
				:label='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.requestHops")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.dpa-params.dpa-hops.validation.requestHops.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.dpa-params.dpa-hops.validation.requestHops.integer")),
					(v: number) => ValidationRules.between(v, requestDrm ? 0 : 1, requestDom ? 255 : 239, $t("components.iqrfnet.network-manager.dpa-params.dpa-hops.validation.requestHops.between")),
				]'
				:min='requestDrm ? 0 : 1'
				:max='requestDom ? 255 : 239'
				:disabled='requestDom || requestDrm'
				required
			/>
			<v-checkbox
				v-model='requestDom'
				:label='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.optimizeDom")'
				:hint='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.notes.dom")'
				persistent-hint
				density='compact'
				@update:model-value='requestDomChange'
			/>
			<v-checkbox
				v-model='requestDrm'
				:label='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.optimizeDrm")'
				:hint='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.notes.drm")'
				persistent-hint
				density='compact'
				@update:model-value='requestDrmChange'
			/>
			<v-divider class='my-2' />
			<INumberInput
				v-model='responseHops'
				:label='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.responseHops")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.dpa-params.dpa-hops.validation.responseHops.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.dpa-params.dpa-hops.validation.responseHops.integer")),
					(v: number) => ValidationRules.between(v, 1, responseDom ? 255 : 239, $t("components.iqrfnet.network-manager.dpa-params.dpa-hops.validation.responseHops.between")),
				]'
				:min='1'
				:max='responseDom ? 255 : 239'
				:disabled='responseDom'
				:required='!responseDom'
			/>
			<v-checkbox
				v-model='responseDom'
				:label='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.optimizeDom")'
				:hint='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.notes.dom")'
				persistent-hint
				density='compact'
				@update:model-value='responseChange'
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Download'
					:loading='componentState === ComponentState.Action && actionType === IqmeshConfigAction.Get'
					:disabled='componentState === ComponentState.Action && actionType !== IqmeshConfigAction.Get'
					:text='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.actions.get")'
					@click='dpaHopsAction(IqmeshConfigAction.Get)'
				/>
				<IActionBtn
					:action='Action.Upload'
					:loading='componentState === ComponentState.Action && actionType === IqmeshConfigAction.Set'
					:disabled='componentState === ComponentState.Action && actionType !== IqmeshConfigAction.Set'
					:text='$t("components.iqrfnet.network-manager.dpa-params.dpa-hops.actions.set")'
					@click='dpaHopsAction(IqmeshConfigAction.Set)'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { IqmeshConfigAction } from '@iqrf/iqrf-gateway-daemon-utils/enums/iqmesh';
import { DpaParametersService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { IqmeshDpaHopsParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, INumberInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { onBeforeUnmount, ref, Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const form: Ref<VForm|null> = useTemplateRef('form');
const requestHops: Ref<number> = ref(239);
const responseHops: Ref<number> = ref(255);
const requestDom: Ref<boolean> = ref(false);
const requestDrm: Ref<boolean> = ref(false);
const responseDom: Ref<boolean> = ref(true);
const actionType: Ref<IqmeshConfigAction> = ref(IqmeshConfigAction.Get);
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === IqmeshServiceMessages.DpaHops) {
				handleDpaHopsAction(rsp);
			}
		});
	}
});

function requestDomChange(val: boolean | null): void {
	if (val === null) {
		return;
	}
	if (val) {
		requestDrm.value = false;
		requestHops.value = 255;
	} else {
		requestHops.value = 1;
	}
}

function requestDrmChange(val: boolean | null): void {
	if (val === null) {
		return;
	}
	if (val) {
		requestDom.value = false;
		requestHops.value = 0;
	} else {
		requestHops.value = 1;
	}
}

function responseChange(val: boolean | null): void {
	if (val === null) {
		return;
	}
	if (val) {
		responseHops.value = 255;
	} else {
		responseHops.value = 1;
	}
}

async function dpaHopsAction(action: IqmeshConfigAction): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	actionType.value = action;
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		5_000,
		i18n.t(
			actionType.value === IqmeshConfigAction.Get ?
				'components.iqrfnet.network-manager.dpa-params.dpa-hops.messages.get.timeout' :
				'components.iqrfnet.network-manager.dpa-params.dpa-hops.messages.set.timeout',
		),
		() => {
			componentState.value = ComponentState.Idle;
		},
	);
	const params: IqmeshDpaHopsParams = {
		action: actionType.value,
	};
	if (actionType.value === IqmeshConfigAction.Set) {
		params.requestHops = requestHops.value;
		params.responseHops = responseHops.value;
	}
	msgId.value = await daemonStore.sendMessage(
		DpaParametersService.dpaHops(
			{ repeat: 1, returnVerbose: true },
			params,
			opts,
		),
	);
}

function handleDpaHopsAction(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		toast.success(
			i18n.t(
				actionType.value === IqmeshConfigAction.Get ?
					'components.iqrfnet.network-manager.dpa-params.dpa-hops.messages.get.success' :
					'components.iqrfnet.network-manager.dpa-params.dpa-hops.messages.set.success',
			),
		);
		if (actionType.value === IqmeshConfigAction.Get) {
			const rqHops = rsp.data.rsp.requestHops;
			const rspHops = rsp.data.rsp.responseHops;
			if (rqHops === 255) {
				requestDom.value = true;
				requestDrm.value = false;
			} else if (rqHops === 0) {
				requestDom.value = false;
				requestDrm.value = true;
			} else {
				requestDom.value = requestDrm.value = false;
			}
			responseDom.value = rspHops === 255;
			requestHops.value = rqHops;
			responseHops.value = rspHops;

		}
	} else {
		toast.error(
			i18n.t(
				actionType.value === IqmeshConfigAction.Get ?
					'components.iqrfnet.network-manager.dpa-params.dpa-hops.messages.get.failed' :
					'components.iqrfnet.network-manager.dpa-params.dpa-hops.messages.set.failed',
			),
		);
	}
	componentState.value = ComponentState.Idle;
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
