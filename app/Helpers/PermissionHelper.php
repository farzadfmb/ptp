<?php

class PermissionHelper
{
	private const DEFAULT_FALLBACK_PERMISSIONS = ['dashboard_overview_view'];

	/** @var array<string, array<int, string>> */
	private static array $organizationRolePermissionCache = [];

	/** @var array<int, array<int, string>> */
	private static array $organizationAllPermissionCache = [];

	/** @var array<string, array<int, array<string, string>>>|null */
	private static ?array $organizationPermissionCatalog = null;

	/**
	 * Returns the grouped organization permission catalog.
	 */
	public static function getOrganizationPermissionCatalog(): array
	{
		if (self::$organizationPermissionCatalog !== null) {
			return self::$organizationPermissionCatalog;
		}

		self::$organizationPermissionCatalog = [
			'داشبورد' => [
				['key' => 'dashboard_overview_view', 'label' => 'مشاهده داشبورد'],
			],
			'مدیریت سازمان' => [
				['key' => 'org_report_settings_manage', 'label' => 'تنظیمات گزارشات'],
				['key' => 'org_posts_manage', 'label' => 'پست‌های سازمانی'],
				['key' => 'org_service_locations_manage', 'label' => 'محل‌های خدمت'],
			],
			'مدیریت دسترسی‌ها' => [
				['key' => 'users_manage_roles', 'label' => 'مدیریت نقش‌ها'],
				['key' => 'role_access_matrix_manage', 'label' => 'ماتریس نقش دسترسی'],
				['key' => 'executive_units_manage', 'label' => 'دستگاه‌های اجرایی'],
				['key' => 'users_manage_users', 'label' => 'کاربران سازمان'],
				['key' => 'users_manage_user_roles', 'label' => 'ماتریس نقش کاربران'],
			],
			'مدیریت گروه‌های ارزیابی' => [
				['key' => 'evaluation_calendar_manage', 'label' => 'تقویم ارزیابی'],
				['key' => 'evaluation_calendar_matrix_manage', 'label' => 'ماتریس تقویم ارزشیابی'],
			],
			'مدیریت ابزارها' => [
				['key' => 'tools_manage', 'label' => 'ابزارهای ارزیابی'],
				['key' => 'tools_mbti_settings_manage', 'label' => 'تنظیمات آزمون MBTI'],
				['key' => 'tools_disc_settings_manage', 'label' => 'تنظیمات آزمون DISC'],
				['key' => 'tools_neo_settings_manage', 'label' => 'تنظیمات آزمون NEO'],
				['key' => 'tools_view', 'label' => 'مشاهده ابزارهای ارزیابی'],
			],
			'مدیریت شایستگی' => [
				['key' => 'competency_dimensions_manage', 'label' => 'ابعاد شایستگی'],
				['key' => 'competencies_manage', 'label' => 'شایستگی‌ها'],
				['key' => 'competency_model_manage', 'label' => 'مدل شایستگی'],
				['key' => 'competency_features_manage', 'label' => 'ویژگی‌های شایستگی'],
				['key' => 'competency_model_matrix_manage', 'label' => 'ماتریس مدل شایستگی'],
				['key' => 'tool_competency_matrix_manage', 'label' => 'ماتریس شایستگی ابزار'],
				['key' => 'competencies_view', 'label' => 'مشاهده شایستگی‌ها'],
			],
			'مدیریت دوره‌های آموزشی' => [
				['key' => 'courses_manage', 'label' => 'برنامه‌های توسعه فردی'],
				['key' => 'courses_view', 'label' => 'مشاهده دوره‌های آموزشی'],
			],
			'ثبت نتایج' => [
				['key' => 'results_exam_questionwise', 'label' => 'ثبت نتایج آزمون به تفکیک سؤال'],
				['key' => 'results_exam_register', 'label' => 'ثبت نتایج آزمون'],
				['key' => 'results_tool_score_manage', 'label' => 'ثبت امتیاز ابزار'],
				['key' => 'results_assessment_register', 'label' => 'ارزیابی‌های فعال'],
				['key' => 'results_washup_register', 'label' => 'Wash-Up'],
				['key' => 'results_excel_report', 'label' => 'گزارش اکسل'],
				['key' => 'results_resume_selected', 'label' => 'رزومه‌های منتخب'],
				['key' => 'results_washup_final', 'label' => 'ثبت نهایی Wash-Up'],
			],
			'گزارشات' => [
				['key' => 'reports_self_view', 'label' => 'مشاهده نتایج خود ارزیابی کاربران'],
				['key' => 'reports_final_view', 'label' => 'گزارش نهایی ارزیابی'],
				['key' => 'reports_dev_program_view', 'label' => 'گزارش برنامه‌های توسعه فردی'],
				['key' => 'reports_settings_manage', 'label' => 'تنظیمات گزارش ارزیابی'],
				['key' => 'reports_dashboard_view', 'label' => 'داشبورد نتایج'],
			],
		];

		return self::$organizationPermissionCatalog;
	}

	/**
	 * Returns the flattened organization permission definitions with group metadata.
	 */
	public static function getOrganizationPermissionDefinitions(): array
	{
		$definitions = [];

		foreach (self::getOrganizationPermissionCatalog() as $group => $permissions) {
			foreach ($permissions as $definition) {
				$definitions[] = [
					'group' => $group,
					'key' => (string)($definition['key'] ?? ''),
					'label' => (string)($definition['label'] ?? ''),
				];
			}
		}

		return $definitions;
	}

	/**
	 * Fetches allowed permission keys for a specific organization user.
	 */
	public static function fetchPermissionsForOrganizationUser(int $organizationId, ?int $roleId, bool $isSystemAdmin = false): array
	{
		if ($isSystemAdmin) {
			return self::getAllPermissionsForOrganization($organizationId);
		}

		if ($roleId === null || $roleId <= 0) {
			return self::getFallbackPermissions();
		}

		$cacheKey = $organizationId . ':' . $roleId;

		if (!isset(self::$organizationRolePermissionCache[$cacheKey])) {
			$granted = [];

			if ($organizationId > 0) {
				try {
					$rows = DatabaseHelper::fetchAll(
						'SELECT permission_key FROM organization_role_permissions WHERE organization_id = :organization_id AND organization_role_id = :role_id AND is_allowed = 1',
						[
							'organization_id' => $organizationId,
							'role_id' => $roleId,
						]
					);

					foreach ($rows as $row) {
						$permissionKey = trim((string)($row['permission_key'] ?? ''));
						if ($permissionKey !== '') {
							$granted[] = $permissionKey;
						}
					}
				} catch (Exception $exception) {
					// Silent failure; fall back to defaults below.
				}
			}

			if (empty($granted)) {
				$granted = self::getFallbackPermissions();
			} else {
				$granted = array_merge($granted, self::getFallbackPermissions());
			}

			self::$organizationRolePermissionCache[$cacheKey] = array_values(array_unique($granted));
		}

		return self::$organizationRolePermissionCache[$cacheKey];
	}

	/**
	 * Returns the union of known and persisted permissions for an organization.
	 */
	public static function getAllPermissionsForOrganization(int $organizationId): array
	{
		if ($organizationId <= 0) {
			return self::getKnownOrganizationPermissionKeysWithFallback();
		}

		if (!isset(self::$organizationAllPermissionCache[$organizationId])) {
			$keys = [];

			try {
				$rows = DatabaseHelper::fetchAll(
					'SELECT DISTINCT permission_key FROM organization_role_permissions WHERE organization_id = :organization_id AND is_allowed = 1',
					['organization_id' => $organizationId]
				);

				foreach ($rows as $row) {
					$permissionKey = trim((string)($row['permission_key'] ?? ''));
					if ($permissionKey !== '') {
						$keys[] = $permissionKey;
					}
				}
			} catch (Exception $exception) {
				// Ignore; fallback will be applied.
			}

			$keys = array_merge($keys, self::getKnownOrganizationPermissionKeysWithFallback());

			self::$organizationAllPermissionCache[$organizationId] = array_values(array_unique($keys));
		}

		return self::$organizationAllPermissionCache[$organizationId];
	}

	/**
	 * Normalizes the input permission list into a unique array of strings.
	 */
	public static function normalizePermissions($permissions): array
	{
		if (!is_array($permissions)) {
			$permissions = [$permissions];
		}

		$normalized = [];

		foreach ($permissions as $permission) {
			if ($permission === null) {
				continue;
			}

			$permissionKey = trim((string)$permission);

			if ($permissionKey === '') {
				continue;
			}

			$normalized[] = $permissionKey;
		}

		return array_values(array_unique($normalized));
	}

	/**
	 * Filters the menu tree based on granted permissions.
	 */
	public static function filterMenuByPermissions(array $menu, array $grantedPermissions): array
	{
		if (empty($menu)) {
			return [];
		}

		$grantedMap = array_fill_keys(self::normalizePermissions($grantedPermissions), true);

		$filteredMenu = [];

		foreach ($menu as $item) {
			if (!is_array($item)) {
				continue;
			}

			$filteredItem = self::filterMenuItem($item, $grantedMap);

			if ($filteredItem !== null) {
				$filteredMenu[] = $filteredItem;
			}
		}

		return $filteredMenu;
	}

	private static function filterMenuItem(array $item, array $grantedMap): ?array
	{
		$children = $item['children'] ?? [];
		$filteredChildren = [];

		if (is_array($children)) {
			foreach ($children as $child) {
				if (!is_array($child)) {
					continue;
				}

				$filteredChild = self::filterMenuItem($child, $grantedMap);

				if ($filteredChild !== null) {
					$filteredChildren[] = $filteredChild;
				}
			}
		}

		$requiredPermissions = array_merge(
			isset($item['permission']) ? [$item['permission']] : [],
			isset($item['permissions']) && is_array($item['permissions']) ? $item['permissions'] : []
		);

		$requiredPermissions = self::normalizePermissions($requiredPermissions);

		$hasPermission = empty($requiredPermissions);

		if (!$hasPermission) {
			foreach ($requiredPermissions as $permissionKey) {
				if (isset($grantedMap[$permissionKey])) {
					$hasPermission = true;
					break;
				}
			}
		}

		if (!$hasPermission && empty($filteredChildren)) {
			return null;
		}

		if (!empty($filteredChildren)) {
			$item['children'] = $filteredChildren;
		} else {
			unset($item['children']);
		}

		return $item;
	}

	private static function getFallbackPermissions(): array
	{
		return self::DEFAULT_FALLBACK_PERMISSIONS;
	}

	private static function getKnownOrganizationPermissionKeys(): array
	{
		static $keys = null;

		if ($keys !== null) {
			return $keys;
		}

		$keys = [];

		foreach (self::getOrganizationPermissionCatalog() as $permissions) {
			foreach ($permissions as $definition) {
				$permissionKey = trim((string)($definition['key'] ?? ''));

				if ($permissionKey !== '') {
					$keys[] = $permissionKey;
				}
			}
		}

		$keys = array_values(array_unique($keys));

		return $keys;
	}

	private static function getKnownOrganizationPermissionKeysWithFallback(): array
	{
		return array_values(array_unique(array_merge(self::getKnownOrganizationPermissionKeys(), self::getFallbackPermissions())));
	}
}

