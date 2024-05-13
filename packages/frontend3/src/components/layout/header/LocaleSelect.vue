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
		i18n.t('components.common.locale.messages.set', { locale: i18n.t(`components.common.locale.languages.${locale}`) }),
	);
}

</script>

<style lang='scss' scoped>
img {
	border: 1px solid black;
}
</style>
