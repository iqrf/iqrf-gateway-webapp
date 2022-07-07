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
	<v-dialog
		v-model='show'
		persistent
		width='50%'
	>
		<v-card>
			<v-card-title>
				{{ $t('config.daemon.interfaces.interfaceMapping.deleteModal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.interfaces.interfaceMapping.deleteModal.prompt', {mapping: name}) }}
			</v-card-text>
			<v-card-actions>
				<v-btn
					color='error'
					@click='deleteMapping'
				>
					{{ $t('forms.delete') }}
				</v-btn>
				<v-spacer />
				<v-btn
					color='secondary'
					@click='deactivateModal'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

@Component({})

/**
 * Mapping delete confirmation modal window component
 */
export default class MappingDeleteConfirmation extends Vue {
	/**
	 * @var {boolean} show Controls whether modal window is rendered
	 */
	private show = false;

	/**
	 * @var {number} idx Mapping index
	 */
	private idx = 0;

	/**
	 * @var {string} name Mapping name
	 */
	private name = '';

	/**
	 * Stores mapping metadata and renders the modal window
	 */
	public activateModal(idx: number, name: string): void {
		this.idx = idx;
		this.name = name;
		this.show = true;
	}

	/**
	 * Emits event to delete mapping
	 */
	private deleteMapping(): void {
		this.deactivateModal();
		this.$emit('delete-mapping', this.idx);
	}

	/**
	 * Clears mapping metadata and closes the modal window
	 */
	private deactivateModal(): void {
		this.show = false;
	}
}
</script>
