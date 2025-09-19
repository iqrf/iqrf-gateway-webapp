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
	<v-menu v-if='availableLocales.length > 1' :location='mobile ? "left" : "bottom left"'>
		<template #activator='{ props }'>
			<v-list-item
				v-if='mobile'
				class='py-0 my-0'
				density='compact'
				v-bind='props'
			>
				<template #prepend>
					<v-list-item-media>
						<ILanguageFlag :language='current' class='me-2' />
					</v-list-item-media>
				</template>
				<template #title>
					{{ $t(`components.common.locale.languages.${current.toString()}`) }}
				</template>
				<template #append>
					<v-icon :icon='mdiChevronLeft' />
				</template>
			</v-list-item>
			<v-btn
				v-else
				variant='elevated'
				v-bind='props'
				color='white'
				size='small'
				:ripple='false'
			>
				<ILanguageFlag :language='current' :height='16' />
			</v-btn>
		</template>
		<v-list
			:class='mobile ? "" : "locale-select-list"'
			density='compact'
		>
			<v-list-item
				v-for='locale in availableLocales'
				:key='locale.code'
				density='compact'
				@click='setLocale(locale.code)'
			>
				<template #prepend>
					<ILanguageFlag :language='locale.code' class='me-2' />
				</template>
				<v-list-item-title>
					{{ $t(`components.common.locale.languages.${locale.code.toString()}`) }}
				</v-list-item-title>
			</v-list-item>
		</v-list>
	</v-menu>
</template>

<script lang='ts' setup>
import {
	ILanguageFlag,
	Language,
} from '@iqrf/iqrf-vue-ui';
import { mdiChevronLeft } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useLocaleStore } from '@/store/locale';

/// Component props
defineProps({
	mobile: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const i18n = useI18n();
const localeStore = useLocaleStore();
const {
	getAvailableLocales: availableLocales,
	getLocale: current,
} = storeToRefs(localeStore);


/**
 * Set the user language
 * @param {Language} locale User language
 */
function setLocale(locale: Language): void {
	if (current.value === locale) {
		return;
	}
	localeStore.setLocale(locale);
	toast.success(
		i18n.t('components.common.locale.messages.set', { locale: i18n.t(`components.common.locale.languages.${locale.toString()}`) }),
	);
}

</script>
