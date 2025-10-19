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
	<IModalWindow
		v-model='show'
		persistent
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='Action.Add'
				container-type='card-title'
				:tooltip='$t("components.iqrfnet.network-manager.autonetwork.midlist.actions.add")'
			/>
			<IDataTableAction
				v-else
				v-bind='props'
				:action='action'
				:tooltip='$t("components.iqrfnet.network-manager.autonetwork.midlist.actions.edit")'
			/>
		</template>
		<v-form
			v-slot='{ isValid }'
			ref='form'
		>
			<ICard>
				<template #title>
					{{ titleText }}
				</template>
				<ITextInput
					v-model='record.deviceMID'
					:label='$t("components.iqrfnet.network-manager.autonetwork.midlist.mid")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.autonetwork.midlist.validation.mid.required")),
						(v: string) => ValidationRules.regex(v, /^[0-9a-fA-F]+$/, $t("components.iqrfnet.network-manager.autonetwork.midlist.validation.mid.regex")),
						(v: string) => ValidationRules.len(v, 8, $t("components.iqrfnet.network-manager.autonetwork.midlist.validation.mid.len")),
						(v: string) => duplicateMid(v, $t("components.iqrfnet.network-manager.autonetwork.midlist.validation.mid.duplicate")),
					]'
					required
				/>
				<v-checkbox
					v-model='useAddr'
					:label='$t("components.iqrfnet.network-manager.autonetwork.midlist.useAddr")'
					density='comfortable'
					hide-details
				/>
				<INumberInput
					v-if='useAddr'
					v-model='record.deviceAddr'
					:label='$t("components.iqrfnet.common.address")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.address.required")),
						(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.address.integer")),
						(v: number) => ValidationRules.between(v, 1, 239, $t("components.iqrfnet.common.validation.address.between")),
						(v: number) => duplicateAddr(v, $t("components.iqrfnet.network-manager.autonetwork.midlist.validation.addr.duplicate")),
					]'
					:min='1'
					:max='239'
					required
				/>
				<v-divider class='mb-2' />
				<div>
					<i>{{ $t('components.iqrfnet.network-manager.autonetwork.notes.midRule1') }}</i><br>
					<i>{{ $t('components.iqrfnet.network-manager.autonetwork.notes.midRule2') }}</i><br>
					<i>{{ $t('components.iqrfnet.network-manager.autonetwork.notes.midRule3') }}</i>
				</div>
				<template #actions>
					<IActionBtn
						:action='Action.Save'
						:disabled='!isValid.value'
						@click='save()'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { AutonetworkMidList } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { Action, IActionBtn, ICard, IDataTableAction, IModalWindow, INumberInput, ITextInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { computed, ref, Ref, useTemplateRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { type VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';

interface Props {
	action: Action;
	index?: number;
	midRecord?: AutonetworkMidList;
	existingRecords: AutonetworkMidList[];
}

const componentProps = defineProps<Props>();
const emit = defineEmits<{
	addRecord: [AutonetworkMidList];
	editRecord: [number, AutonetworkMidList];
}>();
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = useTemplateRef('form');
const defaultRecord: Ref<AutonetworkMidList> = ref({
	deviceMID: '',
	deviceAddr: 1,
});
const record: Ref<AutonetworkMidList> = ref(structuredClone(defaultRecord));
const useAddr: Ref<boolean> = ref(false);

const titleText = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.iqrfnet.network-manager.autonetwork.midlist.actions.add');
	}
	return i18n.t('components.iqrfnet.network-manager.autonetwork.midlist.actions.edit');
});

const duplicateMid = (v: string, error: string): true | string => {
	for (const [idx, val] of componentProps.existingRecords.entries()) {
		if (val.deviceMID === v && componentProps.index !== idx) {
			return error;
		}
	}
	return true;
};
const duplicateAddr = (v: number, error: string): true | string => {
	for (const [idx, val] of componentProps.existingRecords.entries()) {
		if (val.deviceAddr === v && componentProps.index !== idx) {
			return error;
		}
	}
	return true;
};

watch(show, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	if (componentProps.action === Action.Edit && componentProps.midRecord) {
		if (componentProps.midRecord.deviceAddr !== undefined) {
			useAddr.value = true;
		}
		record.value = {
			deviceAddr: 1,
			...componentProps.midRecord,
		};
	} else {
		record.value = { ...defaultRecord.value };
	}
});

async function save(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	const params = { ...record.value };
	if (!useAddr.value) {
		delete params.deviceAddr;
	}
	if (componentProps.action === Action.Add) {
		emit('addRecord', params);
	} else {
		emit('editRecord', componentProps.index!, params);
	}
	close();
}

function close(): void {
	show.value = false;
	useAddr.value = false;
}

</script>
