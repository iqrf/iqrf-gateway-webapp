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
	<div>
		<h1>{{ $t('maintenance.mender.service.pageTitle') }}</h1>
		<v-card class='mb-5'>
			<v-card-text>
				<ServiceControl :service-name='serviceName' />
			</v-card-text>
		</v-card>
		<MenderServiceForm />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import MenderServiceForm from '@/components/Maintenance/MenderServiceForm.vue';
import ServiceControl from '@/components/Maintenance/ServiceControl.vue';

import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		MenderServiceForm,
		ServiceControl,
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('mender')) {
				vm.$toast.error(vm.$t('service.mender-client.messages.disabled').toString());
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.mender.service.pageTitle',
	},
})

/**
 * Mender control component
 */
export default class MenderControl extends Vue {
	/**
	 * @constant {string} serviceName Mender service name
	 */
	private serviceName = 'mender-client';
}
</script>
