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
				<CardActionBtn
					:action='Action.Reload'
					class='float-right mt-2'
					@click='checkInstallation'
				/>
			</v-alert>
		</template>
	</v-empty-state>
</template>

<script setup lang='ts'>
import { storeToRefs } from 'pinia';
import { onMounted, ref, Ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import Logo from '@/assets/logo-blue.svg?url';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import { useFeatureStore } from '@/store/features';
import { useGatewayStore } from '@/store/gateway';
import { useInstallStore } from '@/store/install';
import { useRepositoryStore } from '@/store/repository';
import { useUserStore } from '@/store/user';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

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
		const {
			hasErrors,
			hasNoUsers,
		} = storeToRefs(installStore);
		const isInstallUrl: boolean = route.path.startsWith('/install/');
		const errorUrl: boolean = route.path.startsWith('/install/error');
		installStore.populateSteps();
		if (hasErrors.value) {
			installStore.setChecked();
			await router.push({ name: 'InstallationErrors' });
		} else if (hasNoUsers.value && !isInstallUrl) {
			installStore.setChecked();
			await router.push('/install/');
		} else if (!hasNoUsers.value && (isInstallUrl || errorUrl)) {
			installStore.setChecked();
			await router.push('/sign/in');
		}
		if (isLoggedIn.value) {
			await repositoryStore.fetch();
			await gatewayStore.fetchInfo();
		}
		installStore.setChecked();
		componentState.value = ComponentState.Error;
	} catch {
		componentState.value = ComponentState.FetchFailed;
	}
}

onMounted(async () => await checkInstallation());
</script>
