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
	<v-empty-state :image='Logo'>
		<template #media>
			<v-img class='mb-6' height='48pt' />
		</template>
		<template #title>
			<div v-if='componentState === ComponentState.Loading'>
				<v-progress-linear
					class='my-2'
					color='primary'
					height='16'
					indeterminate
				/>
				{{ $t('components.install.checks.loading') }}
			</div>
			<v-alert
				v-else-if='componentState === ComponentState.FetchFailed'
				class='my-2'
				type='error'
				variant='elevated'
			>
				<div>
					{{ $t("components.install.checks.messages.failed") }}
				</div>
				<IActionBtn
					:action='Action.Reload'
					container-type='card'
					class='float-right mt-2'
					@click='checkInstallation()'
				/>
			</v-alert>
		</template>
	</v-empty-state>
</template>

<script lang='ts' setup>
import {
	Action,
	ComponentState,
	IActionBtn,
} from '@iqrf/iqrf-vue-ui';
import { storeToRefs } from 'pinia';
import { onMounted, ref, Ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import Logo from '@/assets/logo-blue.svg?url';
import { useApiClient } from '@/services/ApiClient';
import { useFeatureStore } from '@/store/features';
import { useGatewayStore } from '@/store/gateway';
import { useInstallStore } from '@/store/install';
import { useRepositoryStore } from '@/store/repository';
import { useUserStore } from '@/store/user';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const featureStore = useFeatureStore();
const gatewayStore = useGatewayStore();
const installStore = useInstallStore();
const repositoryStore = useRepositoryStore();
const userStore = useUserStore();
const route = useRoute();
const router = useRouter();

const { isLoggedIn } = storeToRefs(userStore);

/**
 * Check the IQRF Gateway webapp installation
 */
async function checkInstallation(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		await featureStore.fetch();
		await installStore.check();
		const { hasErrors, hasNoUsers } = storeToRefs(installStore);
		const isInstallUrl: boolean = [
			'InstallationErrors',
			'InstallationWizard',
		].includes(route.name as string);
		if (hasErrors.value) {
			await router.push({ name: 'InstallationErrors' });
			installStore.setChecked();
			componentState.value = ComponentState.Ready;
			return;
		}
		if (hasNoUsers.value) {
			userStore.clearUserData();
			installStore.reset();
			if (!isInstallUrl) {
				await router.push('/install');
			}
			installStore.setChecked();
			componentState.value = ComponentState.Ready;
			return;
		}
		if (isLoggedIn.value) {
			await useApiClient().getAccountService().getInfo();
			if (isInstallUrl && !installStore.isRunning) {
				await router.push('/');
			} else if (!isInstallUrl && installStore.isRunning) {
				await router.push('/install');
			}
			await userStore.refreshUserPreferences();
			await repositoryStore.fetch();
			await gatewayStore.fetchInfo();
		}
		installStore.setChecked();
		componentState.value = ComponentState.Ready;
	} catch {
		componentState.value = ComponentState.FetchFailed;
		installStore.setChecked();
	}
}

onMounted(async () => await checkInstallation());
</script>
