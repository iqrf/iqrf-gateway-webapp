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
	<div class='d-inline'>
		{{ $t('gateway.info.usages.used') }}
		{{ usage.usage.replace('%', ' %') }}
		({{ usage.used }} / {{ usage.size }})
		<div class='progress'>
			<div
				:class='className'
				role='progressbar'
				:style='{ width: usage.usage }'
			/>
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

import {IUsageData} from '@/interfaces/Gateway/Information';

/**
 * Resource usage component for gateway information
 */
@Component({})
export default class ResourceUsage extends Vue {
	/**
	 * @property {Record<string, string>} usage Dictionary of gateway device resource usage
	 */
	@Prop({ required: true }) usage!: IUsageData;

	/**
	 * Returns CSS classes for the progress bar
	 */
	get className(): string {
		const usage = Number.parseFloat(this.usage.usage.replace('%', ''));
		let className = 'progress-bar usage-progress-bar';
		if (usage >= 90) {
			className += ' bg-danger';
		} else if (usage >= 80) {
			className += ' bg-warning';
		} else {
			className += ' bg-success';
		}
		return className;
	}
}
</script>

<style lang='scss'>
.table-striped tbody tr:nth-of-type(2n+1) .progress {
	background-color: white;
}
</style>
