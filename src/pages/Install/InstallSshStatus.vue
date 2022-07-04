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
	<v-card>
		<v-card-title>{{ $t('install.ssh.title') }}</v-card-title>
		<v-card-text>
			<CElementCover
				v-if='running'
				:opacity='0.75'
				style='z-index: 10000;'
			>
				<CSpinner color='primary' />
			</CElementCover>
			<form @submit.prevent='setService'>
				<div class='form-group'>
					{{ $t('install.ssh.messages.note') }}
				</div>
				<v-radio-group
					v-model='status'
					:label='$t("install.ssh.state")'
				>
					<v-radio v-for='option in options' :key='option.value' :value='option.value' :label='option.text'/>
				</v-radio-group>
				<p>
					<em>{{ $t('install.ssh.messages.reminder') }}</em>
				</p>
				<v-btn
					color='primary'
					type='submit'
				>
					{{ $t('forms.save') }}
				</v-btn> <v-btn
					color='secondary'
					@click='nextStep'
				>
					{{ $t('forms.skip') }}
				</v-btn>
			</form>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CElementCover, CSpinner} from '@coreui/vue/src';

import ServiceService from '@/services/ServiceService';

import {extendedErrorToast} from '@/helpers/errorToast';
import {SSHStatus} from '@/enums/Install/ssh';

import {AxiosError} from 'axios';
import {IOption} from '@/interfaces/coreui';

@Component({
	components: {
		CElementCover,
		CSpinner,
	},
	metaInfo: {
		title: 'install.ssh.title'
	}
})

export default class InstallSshStatus extends Vue {

	/**
	 * @var {SSHStatus} status SSH service status select
	 */
	private status: SSHStatus = SSHStatus.ENABLE;

	/**
	 * @var {bool} running Indicates whether axios requests are in progress
	 */
	private running = false;

	/**
	 * @constant {Array<IOption>} options SSH service status options
	 */
	private options: Array<IOption> = [
		{
			value: SSHStatus.ENABLE,
			text: this.$t('install.ssh.states.enable').toString(),
		},
		{
			value: SSHStatus.START,
			text: this.$t('install.ssh.states.start').toString(),
		},
		{
			value: SSHStatus.DISABLE,
			text: this.$t('install.ssh.states.disable').toString(),
		}
	];

	/**
	 * Advances the install wizard step
	 */
	private setService(): void {
		this.running = true;
		if (this.status === SSHStatus.ENABLE) {
			ServiceService.enable('ssh')
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else if (this.status === SSHStatus.START) {
			ServiceService.start('ssh')
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			ServiceService.disable('ssh')
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		}
	}

	/**
	 * Handles service status change success
	 */
	private handleSuccess(): void {
		this.running = false;
		this.nextStep();
	}

	/**
	 * Handles service status change failure
	 */
	private handleFailure(error: AxiosError): void {
		extendedErrorToast(error, 'install.ssh.messages.serviceError');
		this.running = false;
	}

	/**
	 * Finishes the install wizard
	 */
	private nextStep(): void {
		this.$router.push('/');
		this.$toast.success(
			this.$t('install.messages.finished').toString()
		);
	}
}
</script>
