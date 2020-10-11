<template>
	<CCard class='border-0'>
		<CCardHeader>
			{{ $t('iqrfnet.networkManager.restore.title') }}
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='restoreDevice'>
					<div class='form-group'>
						<CInputFile
							ref='backupFile'
							:label='$t("iqrfnet.networkManager.restore.form.backupFile")'
							@input='isEmpty'
							@click='isEmpty'
						/>
						<p v-if='fileEmpty && !fileUntouched' style='color:red'>
							{{ $t('iqrfnet.networkManager.restore.form.messages.backupFile') }}
						</p>
					</div>			
					<CButton
						type='submit'
						color='primary'
						:disabled='invalid'
					>
						{{ $t('forms.restore') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardHeader, CCardBody, CForm, CInput, CInputFile} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputFile,
		ValidationObserver,
		ValidationProvider
	}
})

export default class Restore extends Vue {
	private fileUntouched = true;
	private fileEmpty = true;

	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
	}

	public restoreDevice() {
		//
	}

	private getFiles() {
		const input = ((this.$refs.backupFile as CInputFile).$el.children[1] as HTMLInputElement);
		return input.files;
	}

	private isEmpty(): void {
		if (this.fileUntouched) {
			this.fileUntouched = false;
		}
		const files = this.getFiles();
		this.fileEmpty = files === null || files.length === 0;
	}
}
</script>
