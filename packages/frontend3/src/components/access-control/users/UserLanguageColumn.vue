<template>
	<img height='16' :src='"data:image/svg+xml;charset=utf-8;base64," + languageFlag' />
</template>

<script lang='ts' setup>
import { UserLanguage } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Locale, useLocaleStore } from '@/store/locale';
import { computed } from 'vue';

interface Props {
	language: UserLanguage
}

const props = defineProps<Props>();

const localeStore = useLocaleStore();
const availableLocales = localeStore.getAvailableLocales;

const languageFlag = computed(() => {
	const idx = availableLocales.findIndex((item: Locale): boolean => (item.code === props.language));
	if (idx === -1) {
		return '🏴‍☠️';
	}
	return availableLocales[idx].flag;
});
</script>

<style lang='scss' scoped>
span {
	font-size: large;
}
</style>
