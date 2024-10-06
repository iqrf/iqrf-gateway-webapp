<template>
	<Head>
		<title>{{ $t('pages.install.errors.title') }}</title>
	</Head>
	<Card>
		<template #title>
			{{ $t('pages.install.errors.title') }}
		</template>
		{{ $t('pages.install.errors.text') }}
		<v-stepper-vertical v-if='checks !== null'>
			<MissingDependencies
				v-if='items.includes(InstallationError.MissingDependencies)'
				v-model='checks.dependencies'
				:index='getIndex(InstallationError.MissingDependencies)'
			/>
			<MissingPhpExtensions
				v-if='items.includes(InstallationError.MissingPhpExtensions) && checks.phpModules.missing !== undefined'
				v-model='checks.phpModules.missing'
				:index='getIndex(InstallationError.MissingPhpExtensions)'
			/>
			<MissingMigrations
				v-if='items.includes(InstallationError.MissingMigrations)'
				:index='getIndex(InstallationError.MissingMigrations)'
			/>
			<MisconfiguredSudo
				v-if='items.includes(InstallationError.MisconfiguredSudo) && checks.sudo !== undefined'
				v-model='checks.sudo'
				:index='getIndex(InstallationError.MisconfiguredSudo)'
			/>
		</v-stepper-vertical>
		<template #actions>
			<CardActionBtn
				:action='Action.Reload'
				:loading='componentState === ComponentState.Loading'
				@click='reload'
			/>
		</template>
	</Card>
</template>

<route>
{
	"name": "InstallationErrors",
	"meta": {
		"layout": "Install",
		"requiresAuth": false,
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import { storeToRefs } from 'pinia';
import { computed, ComputedRef, Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';

import MisconfiguredSudo from '@/components/install/errors/MisconfiguredSudo.vue';
import MissingDependencies from '@/components/install/errors/MissingDependencies.vue';
import MissingMigrations from '@/components/install/errors/MissingMigrations.vue';
import MissingPhpExtensions from '@/components/install/errors/MissingPhpExtensions.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import { useInstallStore } from '@/store/install';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';
import { InstallationError } from '@/types/install';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const installStore = useInstallStore();
const router = useRouter();
const {
	getChecks: checks,
	getErrors: errors,
	hasErrors,
	isRunning: isInstallationRunning,
} = storeToRefs(installStore);

const items: ComputedRef<InstallationError[]> = computed((): InstallationError[] => {
	const items: InstallationError[] = [];
	if (errors.value === null) {
		return items;
	}
	for (const [error, value] of Object.entries(errors.value)) {
		if (value) {
			items.push(error as InstallationError);
		}
	}
	return items;
});

/**
 * Returns the index of the given error in the list of errors.
 * @param {InstallationError} error Error to get the index for
 * @return {number} Index of the error
 */
function getIndex(error: InstallationError): number {
	return items.value.indexOf(error) + 1;
}

/**
 * Reloads the installation checks.
 */
async function reload(): Promise<void> {
	try {
		componentState.value = ComponentState.Loading;
		await installStore.check();
		if (!hasErrors.value) {
			toast.success(i18n.t('pages.install.errors.messages.reload.successNoErrors'));
			await router.push(isInstallationRunning.value ? { name: 'InstallationWizard' } : '/');
		} else {
			toast.success(i18n.t('pages.install.errors.messages.reload.success'));
		}
		componentState.value = ComponentState.Created;
	} catch {
		toast.error(i18n.t('pages.install.errors.messages.reload.failed'));
	}
}
</script>
