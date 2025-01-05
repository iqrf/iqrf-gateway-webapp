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
	<CCard>
		<CCardHeader color='danger' class='text-white'>
			{{ $t('install.error.missingDependency.title') }}
		</CCardHeader>
		<CCardBody>
			{{ $t('install.error.missingDependency.description', {dependencies: packages}) }}
		</CCardBody>
		<CCardFooter>
			<h4>{{ $t('install.error.missingDependency.details') }}</h4>
			<CDataTable
				:items='dependencies'
				:fields='fields'
				:striped='true'
			/>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardFooter, CCardHeader, CDataTable} from '@coreui/vue/src';
import {InstallationCheckDependency} from '@/services/InstallationService';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardFooter,
		CCardHeader,
		CDataTable,
	},
	metaInfo: {
		title: 'install.error.missingDependency.title'
	},
})

/**
 * Missing dependency notification
 */
export default class MissingDependency extends Vue {

	@Prop({type: String, default: ''}) private json!: string;

	/**
	 * @property {InstallationCheckDependency[]} dependencies Missing dependencies
	 */
	private dependencies: InstallationCheckDependency[] = [];

	private fields = [
		{key: 'command', label: this.$t('install.error.missingDependency.command')},
		{key: 'package', label: this.$t('install.error.missingDependency.package')},
	];

	/**
	 * Vue component lifecycle hook - mounted
	 */
	protected mounted(): void {
		this.dependencies = this.json === '' ? [] : JSON.parse(this.json);
	}

	/**
	 * Returns dependency packages
   */
	get packages(): string {
		const packages = this.dependencies.map((dependency: InstallationCheckDependency): string => dependency.package);
		return packages.filter((value: string, index: number, array: string[]) => array.indexOf(value) === index).join(', ');
	}

}
</script>
