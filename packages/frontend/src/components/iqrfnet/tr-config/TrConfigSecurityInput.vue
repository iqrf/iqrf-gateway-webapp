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
	<PasswordInput
		v-model='modelValue'
		:label='label'
		:description='$t("components.iqrfnet.tr-config.security.empty")'
		:rules='[
			lengthRule,
			regexRule,
		]'
	>
		<template #prepend-inner>
			<v-icon
				v-tooltip:bottom='formatSwitchMessage'
				:icon='formatSwitchIcon'
				color='primary'
				size='x-large'
				@click='changeAccessPasswordFormat'
			/>
		</template>
		<template #append-inner>
			<v-icon
				v-tooltip:left='formatHint'
				:icon='mdiInformationBox'
				color='info'
				size='x-large'
			/>
		</template>
	</PasswordInput>
</template>

<script lang='ts' setup>
import { mdiAlphabetical, mdiHexadecimal, mdiInformationBox } from '@mdi/js';
import { computed, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import PasswordInput from '@/components/layout/form/PasswordInput.vue';
import ValidationRules from '@/helpers/ValidationRules';

const i18n = useI18n();
const componentProps = defineProps({
	accessPassword: {
		type: Boolean,
		default: true,
		required: false,
	},
});
const modelValue = defineModel<string | undefined>('modelValue', { required: true });
const asciiPattern = /^[\s\w"#$%&()*+,./:;?@[\\\]^`{}~-]{0,16}$/;
const hexPattern = /^[\da-f]{0,32}$/i;
const useHex: Ref<boolean> = ref(false);
const label = computed(() => {
	if (componentProps.accessPassword) {
		return i18n.t('components.iqrfnet.tr-config.security.accessPassword');
	}
	return i18n.t('components.iqrfnet.tr-config.security.userKey');
});
const formatHint = computed(() => {
	if (useHex.value) {
		return i18n.t('components.iqrfnet.tr-config.security.allowedCharactersHex');
	}
	return i18n.t('components.iqrfnet.tr-config.security.allowedCharactersAscii');
});
const formatSwitchIcon = computed(() => {
	if (useHex.value) {
		return mdiAlphabetical;
	}
	return mdiHexadecimal;
});
const formatSwitchMessage = computed(() => {
	if (useHex.value) {
		return i18n.t('components.iqrfnet.tr-config.security.asciiChange');
	}
	return i18n.t('components.iqrfnet.tr-config.security.hexChange');
});
const lengthRule = computed(() => {
	const len = useHex.value ? 32 : 16;
	let message: string;
	if (componentProps.accessPassword) {
		message = i18n.t(useHex.value ? 'components.iqrfnet.tr-config.security.errors.accessPasswordInvalidHexLen' : 'components.iqrfnet.tr-config.security.errors.accessPasswordInvalidAsciiLen');
	} else {
		message = i18n.t(useHex.value ? 'components.iqrfnet.tr-config.security.errors.userKeyInvalidHexLen' : 'components.iqrfnet.tr-config.security.errors.userKeyInvalidAsciiLen');
	}
	return (v: string) => ValidationRules.maxLength(v, len, message);
});
const regexRule = computed(() => {
	const pattern = useHex.value ? hexPattern : asciiPattern;
	let message: string;
	if (componentProps.accessPassword) {
		message = i18n.t(useHex.value ? 'components.iqrfnet.tr-config.security.errors.accessPasswordInvalidHexChar' : 'components.iqrfnet.tr-config.security.errors.accessPasswordInvalidAsciiChar');
	} else {
		message = i18n.t(useHex.value ? 'components.iqrfnet.tr-config.security.errors.userKeyInvalidHexChar' : 'components.iqrfnet.tr-config.security.errors.userKeyInvalidAsciiChar');
	}
	return (v: string) => ValidationRules.regex(v, pattern, `${message } ${ formatHint.value}`);
});

function changeAccessPasswordFormat(): void {
	if (modelValue.value) {
		if (useHex.value) {
			modelValue.value = hexToAscii(modelValue.value);
		} else {
			modelValue.value = asciiToHex(modelValue.value);
		}
	}
	useHex.value = !useHex.value;
}

function hexToAscii(value: string): string {
	const converted = (value.toString().match(/.{1,2}/g) ?? []).map((item: string) => String.fromCharCode(Number.parseInt(item, 16))).join('');
	if (asciiPattern.test(converted)) {
		return converted;
	}
	toast.warn(
		i18n.t('components.iqrfnet.tr-config.security.messages.hexToAsciiConversionInvalid'),
	);
	return '';
}

function asciiToHex(value: string): string {
	if (!asciiPattern.test(value)) {
		toast.warn(
			i18n.t('components.iqrfnet.tr-config.security.messages.asciiToHexConversionInvalid'),
		);
		return '';
	}
	return value.toString().split('').map((c: string) => c.charCodeAt(0).toString(16).padStart(2, '0')).join('');
}
</script>
