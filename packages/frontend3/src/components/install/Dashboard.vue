<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<v-card-text>{{ $t('install.invitation') }}</v-card-text>
		<v-divider />
		<v-card-actions>
			<v-btn
				color='primary'
				variant='flat'
				to='/install/user/'
			>
				{{ $t('install.createUser.title') }}
			</v-btn>
			<v-btn
				color='primary'
				variant='flat'
				to='/install/gateway/'
			>
				{{ $t('install.gwInfo.button') }}
			</v-btn>
			<v-btn
				color='primary'
				variant='flat'
			>
				{{ $t('install.restore.button') }}
			</v-btn>
		</v-card-actions>
	</v-card>
</template>

<script lang='ts' setup>
</script>
