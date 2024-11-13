/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

local options = import 'packages/options.jsonnet';
local variants = import 'packages/variants.jsonnet';

// Export environment variable for given stability
local stabilityEnvironmentVars(stability) = (
if stability == 'devel' then
	'export STABILITY=devel'
else
	'[[ "$CI_COMMIT_TAG" =~ ^.*-(alpha|beta|rc)[0-9]*$ ]] && export STABILITY="testing" || export STABILITY="stable"'
);

/**
 * Docker image
 */
local image = {
	image: options.baseImage,
	tags: ['linux', 'amd64'],
};

/**
 * Build package job
 *
 * @param variant Variant
 * @param stability Stability
 */
local buildPackageJob(variant, stability) = image + {
	stage: 'build',
	before_script: [
		'git checkout -B "$CI_COMMIT_REF_NAME" "$CI_COMMIT_SHA"'
	],
	script: [
	] + (
		if variant.patches != [] then
			['mkdir -p debian/patches']
		else []
	) + std.flattenArrays([
		([
			'cp debian/' + patch + '/patches/* debian/patches',
			'ls --format=single-column debian/' + patch + '/patches >> debian/patches/series',
		])
        for patch in variant.patches
    ]) + (
		if stability == 'devel' then
			[
				'gbp dch -a -S --snapshot-number="$CI_PIPELINE_ID" --ignore-branch',
				'debuild -e CI_PIPELINE_ID -b -us -uc -tc',
			]
		else
			[
				'debuild -b -us -uc -tc',
			]
	) + [
		'rm -rf packageDeploy && mkdir packageDeploy',
		'mv ../*.buildinfo ../*.changes ../*.deb packageDeploy',
		'mv ../*.ddeb packageDeploy 2>/dev/null || :',
		'sed -i "s/.ddeb/.deb/g" packageDeploy/*.changes',
		'for file in $(ls packageDeploy/*.ddeb) ; do mv "${file}" "${file%.ddeb}.deb"; done',
	],
	artifacts: {
		paths: [
			'packageDeploy',
		],
		expire_in: '2 weeks',
	},
} + (
	if stability == 'devel' then {
		except: ['tags'],
	} else {
		only: ['tags'],
	}
);

/**
 * Deploy package job
 *
 * @param variant Variant
 * @param stability Stability
 */
local deployPackageJob(variant, stability) = image + {
	stage: 'deploy',
	before_script: [
		'mkdir -p ~/.ssh',
		'chmod 700 ~/.ssh',
		'eval $(ssh-agent -s)',
		'echo "$SSH_PRIVATE_KEY" | tr -d \'\r\' | ssh-add - > /dev/null',
		'echo "$SSH_KNOWN_HOSTS" > ~/.ssh/known_hosts',
		'chmod 644 ~/.ssh/known_hosts',
	],
	needs: [
		{
			job: 'build-package_' + stability + '/' + variant.name,
			artifacts: true,
		},
	],
	environment: {
		name: 'package_' + stability + '/' + variant.name,
		url: options.deployBaseUrl + variant.name + '/' + stability,
	},
	variables: {
		VARIANT: variant.name,
	},
} + (
if stability == 'devel' then {
	except: ['tags'],
	only: {
		refs: [options.mainBranch],
	},
	script: [
		stabilityEnvironmentVars(stability),
		'ssh ' + options.deployServer + ' "rm -f ' + options.deployDir + '*"',
		'rsync -hrvz --delete -e ssh packageDeploy/* ' + options.deployServer + ':' + options.deployDir + '',
	],
} else {
	only: ['tags'],
	script: [
		stabilityEnvironmentVars(stability),
		'ssh ' + options.deployServer + ' "mkdir -p ' + options.deployDir + 'old && if [ -e ' + options.deployDir + '*.* ]; then mv ' + options.deployDir + '*.* ' + options.deployDir + 'old; fi"',
		'rsync -hrvz --delete -e ssh packageDeploy/* ' + options.deployServer + ':' + options.deployDir + '',
	],
}
);

{
	stages: [
		'build',
		'deploy',
	],
} + {
	['build-package_' + stability + '/' + variant.name]: buildPackageJob(variant, stability)
		for variant in variants
		for stability in ['devel', 'release']
} + {
	['build-package_' + stability + '/' + variant.name + '-v3']: buildPackageJob(variant, stability)
		for variant in std.map(function(item) {
			name: item.name,
			patches: item.patches + ['frontend-v3'],
		}, variants)
		for stability in ['devel', 'release']
} + {
	['deploy-package_' + stability + '/' + variant.name]: deployPackageJob(variant, stability)
		for variant in variants
		for stability in ['devel', 'release']
}
