<?php
/**
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Vincent Petry <pvince81@owncloud.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\ShareImporter\Hooks;

use OC\User\NoUserException;
use OCP\Files\External\IStoragesBackendService;
use OCP\Files\External\Service\IGlobalStoragesService;
use OCP\Files\External\Service\IStoragesService;
use OCP\Files\External\Service\IUserStoragesService;

class Importer {
	/**
	 * @var IGlobalStoragesService
	 */
	private $globalService;

	/**
	 * @var IUserStoragesService
	 */
	private $userService;

	/**
	 * @var IUserManager
	 */
	private $userManager;

	/** @var IStoragesBackendService */
	private $backendService;

	public function __construct(IGlobalStoragesService $globalService,
						 IUserStoragesService $userService,
						 IStoragesBackendService $backendService
	) {
		parent::__construct();
		$this->userService = $userService;
		$this->backendService = $backendService;
	}

	protected function addShares($user, $data) {

		$storageService = $this->userService;

			$mounts = \array_map(function ($entry) use ($storageService) {
				return $this->parseData($entry, $storageService);
			}, $data);
		

			foreach ($mounts as $mount) {
				$mount->setApplicableGroups([]);
				$mount->setApplicableUsers([$user]);
			}

		$existingMounts = $storageService->getAllStorages();
                foreach ($existingMounts as $existingMount) {
                        if ( $existingMount->getApplicableUsers() == [$user])      {
                            $this->globalService->removeStorage($mountId);
                          }
                }

		foreach ($mounts as $mount) {
				$storageService->addStorage($mount);
		}
		return 0;
	}

	private function parseData(array $data, IStoragesService $storageService) {
		// FIXME: use service to create config
		$mount = $storageService->createConfig();
		$mount->setId($data['mount_id']);
		$mount->setMountPoint($data['mount_point']);
		$mount->setBackend($this->getBackendByClass($data['storage']));
		$authBackend = $this->backendService->getAuthMechanism($data['authentication_type']);
		$mount->setAuthMechanism($authBackend);
		$mount->setBackendOptions($data['configuration']);
		$mount->setMountOptions($data['options']);
		$mount->setApplicableUsers(isset($data['applicable_users']) ? $data['applicable_users'] : []);
		$mount->setApplicableGroups(isset($data['applicable_groups']) ? $data['applicable_groups'] : []);
		return $mount;
	}

	private function getBackendByClass($className) {
		$backends = $this->backendService->getBackends();
		foreach ($backends as $backend) {
			if ($backend->getStorageClass() === $className) {
				return $backend;
			}
		}
	}

}
