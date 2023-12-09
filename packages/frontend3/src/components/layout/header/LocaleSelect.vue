<template>
	<v-menu location='bottom'>
		<template #activator='{ props }'>
			<v-btn
				class='mr-2'
				variant='elevated'
				v-bind='props'
				color='white'
				size='small'
				:ripple='false'
				width='55'
				height='36'
			>
				<img height='16' :src='"data:image/svg+xml;charset=utf-8;base64," + localeStore.getLocaleFlag'>
			</v-btn>
		</template>
		<v-list
			class='pt-0 pb-0'
			density='compact'
		>
			<v-list-item
				v-for='locale in localeStore.getAvailableLocales'
				:key='locale.code'
				density='compact'
				@click='setLocale(locale.code)'
			>
				<img height='16' :src='"data:image/svg+xml;charset=utf-8;base64," + locale.flag'>
			</v-list-item>
		</v-list>
	</v-menu>
</template>

<script lang='ts' setup>
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useLocaleStore } from '@/store/locale';

const i18n = useI18n();
const localeStore = useLocaleStore();

function setLocale(locale: string): void {
	if (localeStore.getLocale === locale) {
		return;
	}
	localeStore.setLocale(locale);
	toast.success(
		i18n.t('status.locale.messages.set', {locale: i18n.t(`status.locale.languages.${locale}`)}),
	);
}

</script>

<style lang='scss' scoped>
img {
	border: 1px solid black;
}
</style>
