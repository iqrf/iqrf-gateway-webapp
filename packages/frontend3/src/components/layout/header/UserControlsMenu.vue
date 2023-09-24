<template>
	<v-menu>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				:icon='mdiAccountDetails'
			/>
		</template>
		<v-list density='compact'>
			<v-menu location='left'>
				<template #activator='{ props }'>
					<v-list-item
						class='py-0 my-0'
						v-bind='props'
						density='compact'
					>
						<img height='16' :src='"data:image/svg+xml;charset=utf-8;base64," + localeStore.getLocaleFlag' />
					</v-list-item>
				</template>
				<v-list density='compact'>
					<v-list-item
						v-for='locale in localeStore.getAvailableLocales'
						:key='locale.code'
						density='compact'
						@click='setLocale(locale.code)'
					>
						<img height='16' :src='"data:image/svg+xml;charset=utf-8;base64," + locale.flag' />
					</v-list-item>
				</v-list>
			</v-menu>
			<v-list-item
				@click='signOut'
			>
				<v-icon :icon='mdiLogout' />
			</v-list-item>
		</v-list>
	</v-menu>
</template>

<script lang='ts' setup>
import { useLocaleStore } from '@/store/locale';
import { useUserStore } from '@/store/user';
import { mdiAccountDetails, mdiLogout } from '@mdi/js';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

const i18n = useI18n();
const userStore = useUserStore();
const localeStore = useLocaleStore();

function signOut(): void {
	userStore.signOut();
	toast.success(
		i18n.t('core.sign.out.message').toString()
	);
}

function setLocale(locale: string): void {
	if (localeStore.getLocale === locale) {
		return;
	}
	localeStore.setLocale(locale);
	toast.success(
		i18n.t('core.locale.messages.set', {locale: i18n.t(`core.locale.languages.${locale}`)})
	);
}

</script>

<style lang='scss' scoped>
img {
	border: 1px solid black;
}

</style>
