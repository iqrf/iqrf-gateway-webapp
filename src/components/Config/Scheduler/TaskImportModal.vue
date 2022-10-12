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
	<span>
		<CButton
			color='primary'
			size='sm'
			@click='openModal'
		>
			<CIcon :content='cilArrowTop' size='sm' />
			{{ $t('forms.import') }}
		</CButton>
		<CModal
			:show.sync='show'
			color='primary'
			size='lg'
			:close-on-backdrop='false'
			:fade='false'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.scheduler.import.title') }}
				</h5>
			</template>
			<CForm>
				<div class='form-group'>
					<CInputFile
						ref='schedulerInput'
						accept='application/json,.zip'
						:label='$t("config.daemon.scheduler.import.file")'
						@input='isEmpty'
						@click='isEmpty'
					/>
					<p
						v-if='inputEmpty && inputTouched'
						class='text-danger'
					>
						{{ $t('config.daemon.scheduler.import.errors.fileEmpty') }}
					</p>
				</div>
			</CForm>
			<template #footer>
				<CButton
					color='secondary'
					@click='closeModal'
				>
					{{ $t('forms.cancel') }}
				</CButton>
				<CButton
					color='primary'
					:disabled='inputEmpty'
					@click='importScheduler'
				>
					{{ $t('forms.import') }}
				</CButton>
			</template>
		</CModal>
	</span>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CInputFile, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {cilArrowTop} from '@coreui/icons';
import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';

import SchedulerService from '@/services/SchedulerService';
import ServiceService from '@/services/ServiceService';

import {AxiosError} from 'axios';

/**
 * Scheduler task import modal component
 */
@Component({
	components: {
		CButton,
		CInputFile,
		CModal,
	},
	data: () => ({
		cilArrowTop,
	})
})
export default class TaskImportModal extends ModalBase {
	/**
	 * @var {boolean} inputEmpty Indicates whether file input is empty or not
	 */
	private inputEmpty = true;

	/**
	 * @var {boolean} inputTouched Indicates that file input hasn't been touched
	 */
	private inputTouched = false;

	/**
	 * Imports scheduler tasks from zip file
	 */
	private importScheduler(): void {
		const file = this.getFile();
		if (file === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		SchedulerService.importConfig(file)
			.then(() => {
				this.$toast.success(
					this.$t('config.daemon.scheduler.messages.importSuccess').toString()
				);
				this.closeModal();
				ServiceService.restart('iqrf-gateway-daemon')
					.then(() => {
						this.$store.commit('spinner/HIDE');
						this.$toast.info(
							this.$t('service.iqrf-gateway-daemon.messages.restart')
								.toString()
						);
						this.$emit('imported');
					})
					.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.restartFailed'));
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.importFailed'));
	}

	/**
	 * Retrieves file from input if one was selected
	 * @return {File|null}
	 */
	private getFile(): File|null {
		const input = (this.$refs.schedulerImport as CInputFile).$el.children[1] as HTMLInputElement;
		const filelist = (input.files as FileList);
		if (filelist.length === 0) {
			return null;
		}
		return filelist[0];
	}

	/**
	 * Checks if file input is empty
	 */
	private isEmpty(): void {
		if (!this.inputTouched) {
			this.inputTouched = true;
		}
		this.inputEmpty = this.getFile() === null;
	}
}
</script>
