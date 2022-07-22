<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		<header class='d-flex'>
			<h1 class='mr-auto'>
				{{ $t('maintenance.pixla.title') }}
			</h1>
			<v-btn
				color='primary'
				href='https://www.pixla.online/'
				target='_blank'
			>
				{{ $t('maintenance.pixla.dashboard') }}
			</v-btn>
		</header>
		<v-card class='mb-5'>
			<v-card-text>
				<ServiceControl service-name='gwman-client' />
			</v-card-text>
		</v-card>
		<PixlaForm />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {NavigationGuardNext, Route} from 'vue-router';
import PixlaForm from '@/components/Maintenance/PixlaForm.vue';
import ServiceControl from '@/components/Maintenance/ServiceControl.vue';

@Component({
	components: {
		PixlaForm,
		ServiceControl,
	},
	beforeRouteEnter(_to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('pixla')) {
				vm.$toast.error(vm.$t('service.gwman-client.messages.disabled').toString());
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.pixla.title',
	},
})

/**
 * Pixla maintenance service manager card
 */
export default class PixlaControl extends Vue {}
</script>
