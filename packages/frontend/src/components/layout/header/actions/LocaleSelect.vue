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
						<ILanguageFlag :language='current' class='me-5' />
					</v-list-item-media>
				</template>
				<template #title>
					{{ translatedOptions[current] }}
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
				:key='locale'
				density='compact'
				@click='setLocale(locale)'
			>
				<template #prepend>
					<ILanguageFlag :language='locale' class='me-2' />
				</template>
				<v-list-item-title>
					{{ translatedOptions[locale] }}
				</v-list-item-title>
			</v-list-item>
		</v-list>
	</v-menu>
	<div v-else />
</template>

<script lang='ts' setup>
import { Language } from '@iqrf/iqrf-ui-common-types';
import { ILanguageFlag } from '@iqrf/iqrf-vue-ui';
import { mdiChevronLeft } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useLocaleStore } from '@/store/locale';

/// Component props
withDefaults(
	defineProps<{
		mobile?: boolean;
	}>(),
	{
		mobile: false,
	},
);
const i18n = useI18n();
const localeStore = useLocaleStore();
const {
	getAvailableLocales: availableLocales,
	getLocale: current,
} = storeToRefs(localeStore);

const translatedOptions: Record<Language, string> = {
	[Language.Czech]: i18n.t('components.common.locale.languages.cs'),
	[Language.English]: i18n.t('components.common.locale.languages.en'),
};

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
		i18n.t('components.common.locale.messages.set', { locale: translatedOptions[locale] }),
	);
}

</script>
