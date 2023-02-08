<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		<CElementCover
			v-if='running'
			:opacity='0.75'
			style='z-index: 10000;'
		>
			<CSpinner color='primary' />
		</CElementCover>
		<CCardTitle>{{ $t('maintenance.backup.restoreTitle') }}</CCardTitle>
		<CForm>
			<CInputFile
				ref='backupArchive'
				accept='.zip'
				:label='$t("maintenance.backup.form.archive")'
				@click='fileInputEmpty'
				@input='fileInputEmpty'
			/>
			<CButton
				color='primary'
				:disabled='inputEmpty'
				@click.prevent='restore'
			>
				{{ $t('maintenance.backup.form.restore') }}
			</CButton>
		</CForm>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CForm, CInputFile} from '@coreui/vue/src';

import {extendedErrorToast} from '@/helpers/errorToast';

import BackupService from '@/services/BackupService';

import {AxiosError, AxiosResponse} from 'axios';

@Component({
	components: {
		CButton,
		CForm,
		CInputFile,
	},
})

/**
 * Gateway restore component
 */
export default class GatewayRestore extends Vue {

	/**
	 * @var {boolean} inputEmpty Indicates whether backup archive file input is empty
	 */
	private inputEmpty = true;

	/**
	 * @var {boolean} running Indicates whether restore operation is running
	 */
	private running = false;

	/**
	 * Performs gateway restore
	 */
	private restore(): void {
		const files = this.getFiles();
		if (files === null || files.length === 0) {
			return;
		}
		this.showBlockingElement();
		BackupService.restore(files[0])
			.then((response: AxiosResponse) => {
				const time = new Date(response.data.timestamp * 1000).toLocaleTimeString();
				this.hideBlockingElement();
				this.$toast.success(
					this.$t('maintenance.backup.messages.restoreSuccess', {time: time}).toString()
				);
				if (this.$route.path.includes('/install/restore')) {
					this.$router.push('/sign/in');
				}
			})
			.catch((error: AxiosError) => {
				this.hideBlockingElement();
				extendedErrorToast(error, 'maintenance.backup.messages.restoreFailed');
			});
	}

	/**
	 * Extracts uploaded files from form configuration file input
	 * @returns {FileList} List of uploaded files
	 */
	private getFiles(): FileList {
		const input = ((this.$refs.backupArchive as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Checks if form configuration file input is empty
	 */
	private fileInputEmpty(): void {
		const files = this.getFiles();
		this.inputEmpty = files.length === 0;
	}

	/**
	 * Shows interface blocking element depending on the location
	 */
	private showBlockingElement(): void {
		if (this.$route.path.includes('/install/restore')) {
			this.running = true;
		} else {
			this.$store.commit('spinner/SHOW');
			this.$store.commit('spinner/UPDATE_TEXT', this.$t('maintenance.backup.messages.restore').toString());
		}
	}

	/**
	 * Hides interface blocking element depending on the location
	 */
	private hideBlockingElement(): void {
		if (this.$route.path.includes('/install/restore')) {
			this.running = false;
		} else {
			this.$store.commit('spinner/HIDE');
		}
	}

}
</script>
