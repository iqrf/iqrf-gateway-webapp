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
			<v-tooltip location='bottom'>
				<template #activator='{ props }'>
					<v-icon
						v-bind='props'
						:icon='formatSwitchIcon'
						color='primary'
						size='x-large'
						@click='changeAccessPasswordFormat'
					/>
				</template>
				{{ formatSwitchMessage }}
			</v-tooltip>
		</template>
		<template #append-inner>
			<v-tooltip location='left'>
				<template #activator='{ props }'>
					<v-icon
						v-bind='props'
						:icon='mdiInformationBox'
						color='info'
						size='x-large'
					/>
				</template>
				{{ formatHint }}
			</v-tooltip>
		</template>
	</PasswordInput>
</template>

<script lang='ts' setup>
import { mdiAlphabetical, mdiHexadecimal, mdiInformationBox } from '@mdi/js';
import { computed, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import PasswordInput from '@/components/PasswordInput.vue';
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
const asciiPattern = /^[0-9A-Za-z"#$%&()*+,\-./:;?@[\\\]^_`{}~\s]{0,16}$/;
const hexPattern = /^[0-9A-Fa-f]{0,32}$/;
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
	const message = componentProps.accessPassword ?
		(useHex.value ? 'components.iqrfnet.tr-config.security.errors.accessPasswordInvalidHexLen' : 'components.iqrfnet.tr-config.security.errors.accessPasswordInvalidAsciiLen') :
		(useHex.value ? 'components.iqrfnet.tr-config.security.errors.userKeyInvalidHexLen' : 'components.iqrfnet.tr-config.security.errors.userKeyInvalidAsciiLen');
	return (v: string) => ValidationRules.maxLength(v, len, i18n.t(message));
});
const regexRule = computed(() => {
	const pattern = useHex.value ? hexPattern : asciiPattern;
	const message = componentProps.accessPassword ?
		(useHex.value ? 'components.iqrfnet.tr-config.security.errors.accessPasswordInvalidHexChar' : 'components.iqrfnet.tr-config.security.errors.accessPasswordInvalidAsciiChar') :
		(useHex.value ? 'components.iqrfnet.tr-config.security.errors.userKeyInvalidHexChar' : 'components.iqrfnet.tr-config.security.errors.userKeyInvalidAsciiChar');
	return (v: string) => ValidationRules.regex(v, pattern, i18n.t(message) + ' ' + formatHint.value);
});

function changeAccessPasswordFormat(): void {
	if (useHex.value) {
		if (modelValue.value) {
			modelValue.value = hexToAscii(modelValue.value);
		}
	} else {
		if (modelValue.value) {
			modelValue.value = asciiToHex(modelValue.value);
		}
	}
	useHex.value = !useHex.value;
}

function hexToAscii(value: string): string {
	const converted = (value.toString().match(/.{1,2}/g) ?? []).map((item: string) => String.fromCharCode(parseInt(item, 16))).join('');
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
