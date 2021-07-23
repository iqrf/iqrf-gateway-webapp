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
	<CCard>
		<CCardHeader>{{ $t('install.ssh.status.title') }}</CCardHeader>
		<CCardBody class='card-margin-bottom'>
			<CForm>
				<CSelect
					:value.sync='status'
					:options='options'
					:label='$t("install.ssh.status.state")'
				/>
				<p>
					<i>{{ $t('install.ssh.status.messages.note') }}</i>
				</p>
				<CButton
					color='primary'
					@click='submitStep(true)'
				>
					{{ $t('forms.save') }}
				</CButton> <CButton
					color='secondary'
					@click='submitStep(false)'
				>
					{{ $t('forms.skip') }}
				</CButton>
			</CForm>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CSelect} from '@coreui/vue/src';

import ServiceService from '../../services/ServiceService';

import {extendedErrorToast} from '../../helpers/errorToast';
import {SSHStatus} from '../../enums/Install/ssh';

import {AxiosError} from 'axios';
import {IOption} from '../../interfaces/coreui';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CSelect,
	},
	metaInfo: {
		title: 'install.ssh.status.title'
	}
})

export default class InstallSshStatus extends Vue {

	/**
	 * @var {SSHStatus} status SSH service status select
	 */
	private status: SSHStatus = SSHStatus.ENABLE

	/**
	 * @constant {Array<IOption>} options SSH service status options
	 */
	private options: Array<IOption> = [
		{
			value: SSHStatus.ENABLE,
			label: this.$t('install.ssh.status.states.enable').toString(),
		},
		{
			value: SSHStatus.START,
			label: this.$t('install.ssh.status.states.start').toString(),
		},
		{
			value: SSHStatus.DISABLE,
			label: this.$t('install.ssh.status.states.disable').toString(),
		}
	]

	/**
	 * Advances the install wizard step
	 * @param {boolean} setStatus Change ssh status?
	 */
	private submitStep(setStatus: boolean): void {
		if (setStatus) {
			if (this.status === SSHStatus.ENABLE) {
				ServiceService.enable('ssh')
					.then(() => this.$emit('next-step'))
					.catch((error: AxiosError) => extendedErrorToast(error, 'install.ssh.status.messages.serviceError'));
			}
		} else {
			this.$emit('next-step');
		}
	}
}
</script>
