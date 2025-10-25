<?php

require_once __DIR__ . '/../Helpers/autoload.php';

class HomeController {
    private static bool $examStorageTablesEnsured = false;
    private static bool $courseTablesEnsured = false;

    public function index(): void
    {
        AuthHelper::startSession();

        AuthHelper::requireAuth(UtilityHelper::baseUrl('user/login'));

        $title = 'پرتال کاربران';
        $user = AuthHelper::getUser();
        $additional_css = [];
        $additional_js = [];
        $inline_styles = '';
        $inline_scripts = '';

        include __DIR__ . '/../Views/home/dashboard/index.php';
    }

    public function profile(): void
    {
        AuthHelper::startSession();

        AuthHelper::requireAuth(UtilityHelper::baseUrl('user/login'));

        $title = 'پروفایل کاربری';
        $user = AuthHelper::getUser();
        $successMessage = flash('success');
        $errorMessage = flash('error');
        $validationErrors = $_SESSION['validation_errors'] ?? [];
        $oldInput = $_SESSION['old_input'] ?? [];
        unset($_SESSION['validation_errors']);
        $additional_css = [];
        $additional_js = [];
        $inline_styles = '';
        $inline_scripts = '';

        // Fetch fresh organization user record for full details (province, city, etc.)
        $organizationId = (int)($user['organization_id'] ?? 0);
        $organizationUserId = (int)($user['organization_user_id'] ?? 0);
        $orgUserRecord = null;
        if ($organizationId > 0 && $organizationUserId > 0) {
            try {
                $orgUserRecord = DatabaseHelper::fetchOne(
                    'SELECT * FROM organization_users WHERE id = :id AND organization_id = :organization_id LIMIT 1',
                    [
                        'id' => $organizationUserId,
                        'organization_id' => $organizationId,
                    ]
                );
            } catch (Exception $exception) {
                $orgUserRecord = null;
            }
        }

        include __DIR__ . '/../Views/home/profile/index.php';
    }

    public function updateProfile(): void
    {
        AuthHelper::startSession();

        AuthHelper::requireAuth(UtilityHelper::baseUrl('user/login'));

        $redirectUrl = UtilityHelper::baseUrl('profile');

        $token = (string)($_POST['_token'] ?? '');
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $currentUser = AuthHelper::getUser();
        $organizationId = (int)($currentUser['organization_id'] ?? 0);
        $organizationUserId = (int)($currentUser['organization_user_id'] ?? 0);

        if ($organizationId <= 0 || $organizationUserId <= 0) {
            ResponseHelper::flashError('حساب کاربری معتبر یافت نشد.');
            UtilityHelper::redirect($redirectUrl);
        }

        $input = [
            // Blocked fields: first_name, last_name, username, mobile
            'email' => trim((string)($_POST['email'] ?? '')),
            'national_code' => trim((string)($_POST['national_code'] ?? '')),
            'personnel_code' => trim((string)($_POST['personnel_code'] ?? '')),
            'service_location' => trim((string)($_POST['service_location'] ?? '')),
            'organization_post' => trim((string)($_POST['organization_post'] ?? '')),
            'province' => trim((string)($_POST['province'] ?? '')),
            'city' => trim((string)($_POST['city'] ?? '')),
        ];

        $_SESSION['old_input'] = $input;

        $errors = [];

        if ($input['email'] !== '') {
            $normalizedEmail = function_exists('mb_strtolower') ? mb_strtolower($input['email'], 'UTF-8') : strtolower($input['email']);
            if (!filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'ایمیل معتبر وارد کنید.';
            } else {
                // Ensure uniqueness within organization_users if needed
                try {
                    $exists = DatabaseHelper::fetchOne(
                        'SELECT id FROM organization_users WHERE organization_id = :organization_id AND email = :email AND id != :id LIMIT 1',
                        [
                            'organization_id' => $organizationId,
                            'email' => $normalizedEmail,
                            'id' => $organizationUserId,
                        ]
                    );
                    if ($exists) {
                        $errors['email'] = 'این ایمیل قبلاً استفاده شده است.';
                    }
                } catch (Exception $exception) {
                    // ignore lookup failure, proceed without uniqueness enforcement
                }
                $input['email'] = $normalizedEmail;
            }
        } else {
            $input['email'] = null; // allow clearing email
        }

        if ($input['national_code'] !== '') {
            $englishNational = UtilityHelper::persianToEnglish($input['national_code']);
            $digits = preg_replace('/\D+/', '', $englishNational);
            if (strlen((string)$digits) !== 10) {
                $errors['national_code'] = 'کد ملی باید ۱۰ رقم باشد.';
            }
            $input['national_code'] = $digits;
        } else {
            $input['national_code'] = null;
        }

        if ($input['personnel_code'] === '') {
            $input['personnel_code'] = null;
        }
        if ($input['service_location'] === '') {
            $input['service_location'] = null;
        }
        if ($input['organization_post'] === '') {
            $input['organization_post'] = null;
        }
        if ($input['province'] === '') {
            $input['province'] = null;
        }
        if ($input['city'] === '') {
            $input['city'] = null;
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            ResponseHelper::flashError('لطفاً خطاهای فرم را بررسی کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        // Ensure table exists (OrganizationController builds ensure fn, but we avoid calling it here). We'll attempt update and rely on DB schema.
        $updateData = [
            'email' => $input['email'],
            'national_code' => $input['national_code'],
            'personnel_code' => $input['personnel_code'],
            'service_location' => $input['service_location'],
            'organization_post' => $input['organization_post'],
            'province' => $input['province'],
            'city' => $input['city'],
        ];

        try {
            DatabaseHelper::update(
                'organization_users',
                $updateData,
                'id = :id AND organization_id = :organization_id',
                [
                    'id' => $organizationUserId,
                    'organization_id' => $organizationId,
                ]
            );
        } catch (Exception $exception) {
            ResponseHelper::flashError('در ذخیره اطلاعات خطایی رخ داد: ' . $exception->getMessage());
            UtilityHelper::redirect($redirectUrl);
        }

        // Refresh session user fields directly
        try {
            $fresh = DatabaseHelper::fetchOne(
                'SELECT email, national_code, personnel_code, service_location, organization_post, province, city FROM organization_users WHERE id = :id AND organization_id = :organization_id LIMIT 1',
                [
                    'id' => $organizationUserId,
                    'organization_id' => $organizationId,
                ]
            );
            if (is_array($fresh)) {
                $_SESSION['user'] = array_merge($_SESSION['user'] ?? [], [
                    'email' => $fresh['email'] ?? ($input['email'] ?? null),
                    'national_code' => $fresh['national_code'] ?? ($input['national_code'] ?? null),
                    'personnel_code' => $fresh['personnel_code'] ?? ($input['personnel_code'] ?? null),
                    'service_location' => $fresh['service_location'] ?? ($input['service_location'] ?? null),
                    'organization_post' => $fresh['organization_post'] ?? ($input['organization_post'] ?? null),
                    'province' => $fresh['province'] ?? ($input['province'] ?? null),
                    'city' => $fresh['city'] ?? ($input['city'] ?? null),
                ]);
            }
        } catch (Exception $exception) {
            // ignore session refresh failures
        }

        unset($_SESSION['old_input'], $_SESSION['validation_errors']);
        ResponseHelper::flashSuccess('پروفایل با موفقیت به‌روزرسانی شد.');
        UtilityHelper::redirect($redirectUrl);
    }

    public function trainingCalendar(): void
    {
        AuthHelper::startSession();

        AuthHelper::requireAuth(UtilityHelper::baseUrl('user/login'));

        $title = 'تقویم آموزشی';
        $user = AuthHelper::getUser();
        $additional_css = [];
        $additional_js = [];
        $inline_styles = '';
        $inline_scripts = '';

        $calendarItems = [];

        $organizationId = (int) ($user['organization_id'] ?? 0);
        $organizationUserId = (int) ($user['organization_user_id'] ?? 0);

    self::ensureExamStorageTables();

        if ($organizationId > 0 && $organizationUserId > 0) {
            $evaluations = [];
            try {
                $evaluations = DatabaseHelper::fetchAll(
                    'SELECT id, title, evaluation_date, general_model, specific_model, evaluatees_json, schedule_id
                     FROM organization_evaluations
                     WHERE organization_id = :organization_id
                     ORDER BY (evaluation_date IS NULL) ASC, evaluation_date ASC, id DESC',
                    ['organization_id' => $organizationId]
                );
            } catch (Exception $exception) {
                $evaluations = [];
            }

            $relevantEvaluations = [];
            $scheduleIds = [];
            $evaluationIds = [];

            foreach ($evaluations as $evaluationRow) {
                $evaluateesJson = $evaluationRow['evaluatees_json'] ?? '';
                $isAssigned = false;

                if (is_string($evaluateesJson) && $evaluateesJson !== '') {
                    $decoded = json_decode($evaluateesJson, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $value) {
                            if ((int) $value === $organizationUserId) {
                                $isAssigned = true;
                                break;
                            }
                        }
                    }
                }

                if (!$isAssigned) {
                    continue;
                }

                $relevantEvaluations[] = $evaluationRow;

                $evaluationId = (int) ($evaluationRow['id'] ?? 0);
                if ($evaluationId > 0) {
                    $evaluationIds[$evaluationId] = $evaluationId;
                }

                $scheduleId = (int) ($evaluationRow['schedule_id'] ?? 0);
                if ($scheduleId > 0) {
                    $scheduleIds[$scheduleId] = $scheduleId;
                }
            }

            $scheduleMetaMap = [];
            if (!empty($scheduleIds)) {
                try {
                    $scheduleMetaRows = DatabaseHelper::fetchAll(
                        'SELECT id, evaluation_title, evaluation_date, status, is_open
                         FROM organization_evaluation_schedules
                         WHERE organization_id = :organization_id AND id IN (' . implode(', ', array_map('intval', $scheduleIds)) . ')',
                        ['organization_id' => $organizationId]
                    );

                    foreach ($scheduleMetaRows as $scheduleRow) {
                        $scheduleMetaMap[(int) ($scheduleRow['id'] ?? 0)] = $scheduleRow;
                    }
                } catch (Exception $exception) {
                    $scheduleMetaMap = [];
                }
            }

            $participationInfo = [];
            $evaluationToolsMap = [];
            if (!empty($evaluationIds)) {
                $placeholders = [];
                $params = [
                    'organization_id' => $organizationId,
                    'evaluatee_id' => $organizationUserId,
                ];

                $index = 0;
                foreach ($evaluationIds as $evaluationId) {
                    $key = 'evaluation_' . $index;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $evaluationId;
                    $index++;
                }

                if (!empty($placeholders)) {
                                        $sql = sprintf(
                                                'SELECT scores.evaluation_id, scores.tool_id, COUNT(*) AS total_scores, tools.name AS tool_name, tools.code AS tool_code
                                                 FROM organization_evaluation_tool_scores scores
                                                 LEFT JOIN organization_evaluation_tools tools ON tools.id = scores.tool_id
                         WHERE scores.organization_id = :organization_id
                           AND scores.evaluatee_id = :evaluatee_id
                           AND scores.evaluation_id IN (%s)
                         GROUP BY scores.evaluation_id, scores.tool_id, tools.name',
                        implode(', ', $placeholders)
                    );

                    try {
                        $participationRows = DatabaseHelper::fetchAll($sql, $params);
                        foreach ($participationRows as $participationRow) {
                            $evaluationId = (int) ($participationRow['evaluation_id'] ?? 0);
                            if ($evaluationId <= 0) {
                                continue;
                            }

                            if (!isset($participationInfo[$evaluationId])) {
                                $participationInfo[$evaluationId] = [
                                    'total_scores' => 0,
                                    'tool_names' => [],
                                    'completed_tool_ids' => [],
                                ];
                            }

                            $participationInfo[$evaluationId]['total_scores'] += (int) ($participationRow['total_scores'] ?? 0);

                            $toolId = (int) ($participationRow['tool_id'] ?? 0);
                            if ($toolId > 0) {
                                $participationInfo[$evaluationId]['completed_tool_ids'][$toolId] = true;
                            }

                            $toolName = trim((string) ($participationRow['tool_name'] ?? ''));
                            if ($toolName === '') {
                                $toolName = trim((string) ($participationRow['tool_code'] ?? ''));
                            }
                            if ($toolName !== '' && !in_array($toolName, $participationInfo[$evaluationId]['tool_names'], true)) {
                                $participationInfo[$evaluationId]['tool_names'][] = $toolName;
                            }
                        }
                    } catch (Exception $exception) {
                        $participationInfo = [];
                    }
                }
            }

            foreach ($evaluationIds as $evalId) {
                if (!isset($participationInfo[$evalId])) {
                    $participationInfo[$evalId] = [
                        'total_scores' => 0,
                        'tool_names' => [],
                        'completed_tool_ids' => [],
                    ];
                }
            }

            if (!empty($evaluationIds)) {
                $toolPlaceholders = [];
                $toolParams = ['organization_id' => $organizationId];
                $toolIndex = 0;
                foreach ($evaluationIds as $evaluationId) {
                    $paramKey = 'tool_eval_' . $toolIndex;
                    $toolPlaceholders[] = ':' . $paramKey;
                    $toolParams[$paramKey] = $evaluationId;
                    $toolIndex++;
                }

                if (!empty($toolPlaceholders)) {
                    // Build exam tools list using assignment table joined with tools, only where tools.is_exam = 1
                    $toolSql = sprintf(
                        'SELECT assign.evaluation_id AS evaluation_id,
                                assign.tool_id AS id,
                                assign.sort_order AS display_order,
                                tools.name AS name,
                                tools.code AS code
                         FROM organization_evaluation_tool_assignments assign
                         INNER JOIN organization_evaluation_tools tools
                           ON tools.id = assign.tool_id AND tools.organization_id = :organization_id
                         WHERE assign.evaluation_id IN (%s)
                           AND (tools.is_exam = 1)
                         ORDER BY assign.evaluation_id ASC, assign.sort_order ASC, assign.tool_id ASC',
                        implode(', ', $toolPlaceholders)
                    );

                    try {
                        $toolRows = DatabaseHelper::fetchAll($toolSql, $toolParams);
                        foreach ($toolRows as $toolRow) {
                            $evalId = (int) ($toolRow['evaluation_id'] ?? 0);
                            $toolId = (int) ($toolRow['id'] ?? 0);
                            if ($evalId <= 0 || $toolId <= 0) {
                                continue;
                            }

                            if (!isset($evaluationToolsMap[$evalId])) {
                                $evaluationToolsMap[$evalId] = [];
                            }

                            $orderValue = isset($toolRow['display_order'])
                                ? (int) $toolRow['display_order']
                                : (count($evaluationToolsMap[$evalId]) + 1);
                            if ($orderValue <= 0) {
                                $orderValue = count($evaluationToolsMap[$evalId]) + 1;
                            }

                            $evaluationToolsMap[$evalId][$toolId] = [
                                'name' => trim((string) ($toolRow['name'] ?? '')),
                                'code' => trim((string) ($toolRow['code'] ?? '')),
                                'order' => $orderValue,
                            ];
                        }
                    } catch (Exception $exception) {
                        $evaluationToolsMap = [];
                    }
                }
            }

            if (!empty($evaluationIds)) {
                $examPlaceholders = [];
                $examParams = [
                    'organization_id' => $organizationId,
                    'evaluatee_id' => $organizationUserId,
                ];

                $counter = 0;
                foreach ($evaluationIds as $evaluationIdValue) {
                    $paramKey = 'exam_eval_' . $counter;
                    $examPlaceholders[] = ':' . $paramKey;
                    $examParams[$paramKey] = $evaluationIdValue;
                    $counter++;
                }

                if (!empty($examPlaceholders)) {
                    $examSql = sprintf(
                        'SELECT id, evaluation_id, tool_id, total_questions, answered_questions, is_completed, completed_at
                         FROM organization_evaluation_exam_participations
                         WHERE organization_id = :organization_id
                           AND evaluatee_id = :evaluatee_id
                           AND evaluation_id IN (%s)',
                        implode(', ', $examPlaceholders)
                    );

                    try {
                        $examRows = DatabaseHelper::fetchAll($examSql, $examParams);
                        foreach ($examRows as $examRow) {
                            $evalId = (int) ($examRow['evaluation_id'] ?? 0);
                            $toolId = (int) ($examRow['tool_id'] ?? 0);
                            if ($evalId <= 0 || $toolId <= 0) {
                                continue;
                            }

                            if (!isset($participationInfo[$evalId])) {
                                $participationInfo[$evalId] = [
                                    'total_scores' => 0,
                                    'tool_names' => [],
                                    'completed_tool_ids' => [],
                                ];
                            }

                            if (!isset($participationInfo[$evalId]['exam_participations'])) {
                                $participationInfo[$evalId]['exam_participations'] = [];
                            }

                            $participationInfo[$evalId]['exam_participations'][$toolId] = [
                                'participation_id' => (int) ($examRow['id'] ?? 0),
                                'total_questions' => (int) ($examRow['total_questions'] ?? 0),
                                'answered_questions' => (int) ($examRow['answered_questions'] ?? 0),
                                'is_completed' => (int) ($examRow['is_completed'] ?? 0) === 1,
                                'completed_at' => $examRow['completed_at'] ?? null,
                            ];

                            if ((int) ($examRow['is_completed'] ?? 0) === 1) {
                                $participationInfo[$evalId]['completed_tool_ids'][$toolId] = true;
                            }
                        }
                    } catch (Exception $exception) {
                        // ignore merging errors but keep existing participation info
                    }
                }

                if (!empty($examPlaceholders)) {
                    $answerSql = sprintf(
                        'SELECT evaluation_id, tool_id, COUNT(*) AS total_answers
                         FROM organization_evaluation_exam_answers
                         WHERE organization_id = :organization_id
                           AND evaluatee_id = :evaluatee_id
                           AND evaluation_id IN (%s)
                         GROUP BY evaluation_id, tool_id',
                        implode(', ', $examPlaceholders)
                    );

                    try {
                        $answerRows = DatabaseHelper::fetchAll($answerSql, $examParams);
                        foreach ($answerRows as $answerRow) {
                            $evalId = (int) ($answerRow['evaluation_id'] ?? 0);
                            $toolId = (int) ($answerRow['tool_id'] ?? 0);
                            if ($evalId <= 0 || $toolId <= 0) {
                                continue;
                            }

                            if (!isset($participationInfo[$evalId])) {
                                $participationInfo[$evalId] = [
                                    'total_scores' => 0,
                                    'tool_names' => [],
                                    'completed_tool_ids' => [],
                                ];
                            }

                            if (!isset($participationInfo[$evalId]['answer_counts'])) {
                                $participationInfo[$evalId]['answer_counts'] = [];
                            }

                            $totalAnswers = (int) ($answerRow['total_answers'] ?? 0);
                            $participationInfo[$evalId]['answer_counts'][$toolId] = $totalAnswers;

                            if ($totalAnswers > 0 && (!isset($participationInfo[$evalId]['completed_tool_ids'][$toolId]) || $participationInfo[$evalId]['completed_tool_ids'][$toolId] !== true)) {
                                $participationInfo[$evalId]['completed_tool_ids'][$toolId] = true;
                            }
                        }
                    } catch (Exception $exception) {
                        // ignore answer aggregation failures
                    }
                }
            }

            $modelLabelCache = [];
            $resolveModelLabel = static function ($value) use ($organizationId, &$modelLabelCache): string {
                $raw = trim((string) ($value ?? ''));
                if ($raw === '') {
                    return '';
                }

                if (isset($modelLabelCache[$raw])) {
                    return $modelLabelCache[$raw];
                }

                $candidate = UtilityHelper::persianToEnglish($raw);
                $label = $raw;

                if ($candidate !== '' && preg_match('/^\d+$/', $candidate)) {
                    try {
                        $modelRow = DatabaseHelper::fetchOne(
                            'SELECT title FROM organization_competency_models WHERE organization_id = :organization_id AND id = :id LIMIT 1',
                            [
                                'organization_id' => $organizationId,
                                'id' => (int) $candidate,
                            ]
                        );

                        if ($modelRow && !empty($modelRow['title'])) {
                            $label = trim((string) $modelRow['title']);
                        }
                    } catch (Exception $exception) {
                        // ignore lookup failure
                    }
                }

                if ($label !== $raw && $label !== '') {
                    $modelLabelCache[$raw] = $label;
                } else {
                    $modelLabelCache[$raw] = $raw;
                }

                return $modelLabelCache[$raw];
            };

            $formatDate = static function ($value): string {
                $dateString = trim((string) ($value ?? ''));
                if ($dateString === '') {
                    return '—';
                }

                try {
                    $date = new DateTime($dateString, new DateTimeZone('Asia/Tehran'));
                } catch (Exception $exception) {
                    try {
                        $date = new DateTime($dateString);
                        $date->setTimezone(new DateTimeZone('Asia/Tehran'));
                    } catch (Exception $exception) {
                        return '—';
                    }
                }

                if (class_exists('IntlDateFormatter')) {
                    $formatter = new IntlDateFormatter(
                        'fa_IR@calendar=persian',
                        IntlDateFormatter::FULL,
                        IntlDateFormatter::NONE,
                        'Asia/Tehran',
                        IntlDateFormatter::TRADITIONAL,
                        'yyyy/MM/dd'
                    );

                    if ($formatter !== false) {
                        $formatted = $formatter->format($date);
                        if ($formatted !== false && $formatted !== null) {
                            return UtilityHelper::englishToPersian(UtilityHelper::persianToEnglish($formatted));
                        }
                    }
                }

                return UtilityHelper::englishToPersian($date->format('Y/m/d'));
            };

            $now = new DateTime('now', new DateTimeZone('Asia/Tehran'));
            $todayMidnight = (clone $now)->setTime(0, 0, 0);

            foreach ($relevantEvaluations as $evaluationRow) {
                $evaluationId = (int) ($evaluationRow['id'] ?? 0);
                $titleValue = trim((string) ($evaluationRow['title'] ?? ''));
                $generalModel = $resolveModelLabel($evaluationRow['general_model'] ?? '') ?: '';
                $specificModel = $resolveModelLabel($evaluationRow['specific_model'] ?? '') ?: '';

                $modelParts = array_values(array_filter([$generalModel, $specificModel], static function ($value): bool {
                    return trim((string) $value) !== '';
                }));
                $modelLabel = !empty($modelParts) ? implode(' / ', $modelParts) : '—';

                $evaluationDateRaw = $evaluationRow['evaluation_date'] ?? null;
                $evaluationDateDisplay = $formatDate($evaluationDateRaw);

                $participation = $participationInfo[$evaluationId] ?? null;

                $completedToolSet = [];
                if ($participation !== null && !empty($participation['completed_tool_ids']) && is_array($participation['completed_tool_ids'])) {
                    foreach ($participation['completed_tool_ids'] as $key => $value) {
                        if (is_int($key) || ctype_digit((string) $key)) {
                            if (!empty($value)) {
                                $completedToolSet[(int) $key] = true;
                            }
                        } elseif (is_int($value) || ctype_digit((string) $value)) {
                            $completedToolSet[(int) $value] = true;
                        }
                    }
                }

                if ($participation !== null && !empty($participation['exam_participations']) && is_array($participation['exam_participations'])) {
                    foreach ($participation['exam_participations'] as $examToolId => $examMeta) {
                        $examToolId = (int) $examToolId;
                        if ($examToolId <= 0) {
                            continue;
                        }

                        if (!empty($examMeta['is_completed'])) {
                            $completedToolSet[$examToolId] = true;
                        }
                    }
                }
                $completedToolIds = array_map('intval', array_keys($completedToolSet));

                $toolsForEvaluation = $evaluationToolsMap[$evaluationId] ?? [];
                $totalToolsCount = count($toolsForEvaluation);
                $completedToolNames = [];
                $incompleteToolNames = [];
                $examToolDetails = [];

                $hasExamParticipationRecord = ($participation !== null && !empty($participation['exam_participations'] ?? []));
                $participated = !empty($completedToolIds) || $hasExamParticipationRecord || ($participation !== null && (int) ($participation['total_scores'] ?? 0) > 0);

                $completedToolsCount = 0;
                $genericIndex = 1;
                if (!empty($toolsForEvaluation)) {
                    foreach ($toolsForEvaluation as $toolId => $toolMeta) {
                        $toolId = (int) $toolId;
                        $displayName = trim((string) ($toolMeta['name'] ?? ''));
                        if ($displayName === '') {
                            $displayName = trim((string) ($toolMeta['code'] ?? ''));
                        }

                        if ($displayName === '') {
                            $orderIndex = (int) ($toolMeta['order'] ?? 0);
                            if ($orderIndex <= 0) {
                                $orderIndex = $genericIndex;
                            }
                            $displayName = 'آزمون ' . UtilityHelper::englishToPersian((string) $orderIndex);
                        }

                        if ($displayName === '') {
                            $displayName = 'آزمون ' . UtilityHelper::englishToPersian((string) $genericIndex);
                        }

                        $isToolCompleted = in_array($toolId, $completedToolIds, true);
                        if ($isToolCompleted) {
                            $completedToolsCount++;
                            $completedToolNames[] = $displayName;
                        } else {
                            $incompleteToolNames[] = $displayName;
                        }

                        $examToolDetails[] = [
                            'tool_id' => $toolId,
                            'name' => $displayName,
                            'is_completed' => $isToolCompleted,
                        ];

                        $genericIndex++;
                    }
                }

                if (empty($completedToolNames) && $participation !== null) {
                    $toolNames = $participation['tool_names'] ?? [];
                    if (!empty($toolNames)) {
                        $completedToolNames = $toolNames;
                    }
                }

                $completedToolNames = array_values(array_unique(array_filter($completedToolNames, static function ($value): bool {
                    return trim((string) $value) !== '';
                })));
                $incompleteToolNames = array_values(array_unique(array_filter($incompleteToolNames, static function ($value): bool {
                    return trim((string) $value) !== '';
                })));

                $dbAllToolsCompleted = ($totalToolsCount > 0 && $completedToolsCount >= $totalToolsCount);
                $allToolsCompleted = $dbAllToolsCompleted;

                if ($allToolsCompleted && isset($_SESSION['exam_progress'][$evaluationId])) {
                    unset($_SESSION['exam_progress'][$evaluationId]);
                }

                $participationLabel = 'خیر';
                if ($allToolsCompleted) {
                    $participationLabel = 'آزمون تکمیل شده';
                } elseif (!empty($completedToolNames)) {
                    $participationLabel = implode('، ', $completedToolNames);
                } elseif ($participated) {
                    $participationLabel = 'در آزمون شرکت کرده';
                }

                $scheduleId = (int) ($evaluationRow['schedule_id'] ?? 0);
                $scheduleStatus = null;
                $scheduleIsOpen = false;
                if ($scheduleId > 0 && isset($scheduleMetaMap[$scheduleId])) {
                    $scheduleStatus = trim((string) ($scheduleMetaMap[$scheduleId]['status'] ?? ''));
                    $scheduleIsOpen = (int) ($scheduleMetaMap[$scheduleId]['is_open'] ?? 0) === 1;
                }

                $statusLabel = $scheduleStatus !== '' ? $scheduleStatus : 'آماده شروع';
                $statusClass = 'bg-primary-subtle text-primary';
                $statusCode = 'start';

                if ($evaluationDateRaw) {
                    try {
                        $dateObject = new DateTime($evaluationDateRaw, new DateTimeZone('Asia/Tehran'));
                    } catch (Exception $exception) {
                        try {
                            $dateObject = new DateTime($evaluationDateRaw);
                        } catch (Exception $exception) {
                            $dateObject = null;
                        }
                    }

                    if ($dateObject !== null) {
                        $dateMidnight = (clone $dateObject)->setTime(0, 0, 0);

                        if ($dateMidnight < $todayMidnight) {
                            $statusLabel = ($evaluationDateDisplay !== '—' ? $evaluationDateDisplay . ' - ' : '') . 'تاریخ گذشته';
                            $statusClass = 'bg-danger-subtle text-danger';
                            $statusCode = 'past';
                        } else {
                            $statusLabel = 'آماده شروع';
                            $statusClass = 'bg-primary-subtle text-primary';
                            $statusCode = 'start';
                        }
                    }
                }

                if ($scheduleIsOpen && $statusCode === 'past') {
                    $statusCode = 'in_progress';
                    $statusLabel = $scheduleStatus !== '' ? $scheduleStatus : 'در حال انجام';
                    $statusClass = 'bg-warning-subtle text-warning';
                }

                if (!$scheduleIsOpen && $statusCode === 'start' && $scheduleStatus !== '') {
                    $statusClass = 'bg-secondary-subtle text-secondary';
                    $statusCode = 'scheduled';
                }

                if ($participated && !$allToolsCompleted && $statusCode !== 'past') {
                    $statusLabel = 'در حال انجام';
                    $statusClass = 'bg-warning-subtle text-warning';
                    $statusCode = 'in_progress';
                }

                if ($allToolsCompleted) {
                    $statusLabel = 'آزمون تکمیل شده';
                    $statusClass = 'bg-success-subtle text-success';
                    $statusCode = 'complete';
                }

                $startExamUrl = null;
                $canStartExam = ($evaluationId > 0
                    && !$allToolsCompleted
                    && $totalToolsCount > 0
                    && $statusCode !== 'past');

                if ($canStartExam) {
                    $startExamUrl = UtilityHelper::baseUrl('tests/exams?evaluation_id=' . urlencode((string) $evaluationId));
                }

                $calendarItems[] = [
                    'title' => $titleValue !== '' ? $titleValue : 'بدون عنوان',
                    'model' => $modelLabel,
                    'evaluation_date' => $evaluationDateDisplay,
                    'has_participated' => ($participated || $allToolsCompleted),
                    'participation_label' => $participationLabel,
                    'status_label' => $statusLabel,
                    'status_class' => $statusClass,
                    'status_code' => $statusCode,
                    'evaluation_id' => $evaluationId,
                    'start_exam_url' => $startExamUrl,
                    'completed_tool_names' => $completedToolNames,
                    'incomplete_tool_names' => $incompleteToolNames,
                    'exam_tools' => $examToolDetails,
                    'all_tools_completed' => $allToolsCompleted,
                    'has_exam_tools' => $totalToolsCount > 0,
                    'can_start_exam' => $canStartExam,
                    'total_tools_count' => $totalToolsCount,
                    'completed_tools_count' => $completedToolsCount,
                    'incomplete_tools_count' => count($incompleteToolNames),
                ];
            }
        }

        include __DIR__ . '/../Views/home/tests/training-calendar.php';
    }

    public function reports(): void
    {
        AuthHelper::startSession();

        AuthHelper::requireAuth(UtilityHelper::baseUrl('user/login'));

        $title = 'گزارشات';
        $user = AuthHelper::getUser();
        $additional_css = [];
        $additional_js = [];
        $inline_styles = '';
        $inline_scripts = '';

        $reports = [];
        $summaryStats = [
            'total' => 0,
            'completed' => 0,
            'certificates' => 0,
        ];

        $organizationId = (int)($user['organization_id'] ?? 0);
        $organizationUserId = (int)($user['organization_user_id'] ?? 0);

        self::ensureExamStorageTables();

        if ($organizationId > 0 && $organizationUserId > 0) {
            $evaluations = [];
            try {
                $evaluations = DatabaseHelper::fetchAll(
                    'SELECT id, title, evaluation_date, general_model, specific_model, evaluatees_json
                     FROM organization_evaluations
                     WHERE organization_id = :organization_id
                     ORDER BY (evaluation_date IS NULL) ASC, evaluation_date DESC, id DESC',
                    ['organization_id' => $organizationId]
                );
            } catch (Exception $exception) {
                $evaluations = [];
            }

            $resolveModelLabel = static function ($raw) use ($organizationId): string {
                static $modelLabelCache = [];

                $raw = trim((string)$raw);
                if ($raw === '') {
                    return '';
                }

                if (isset($modelLabelCache[$raw])) {
                    return $modelLabelCache[$raw];
                }

                $label = $raw;
                $candidate = UtilityHelper::persianToEnglish($raw);

                if ($candidate !== '' && preg_match('/^\d+$/', $candidate)) {
                    try {
                        $modelRow = DatabaseHelper::fetchOne(
                            'SELECT title FROM organization_competency_models WHERE organization_id = :organization_id AND id = :id LIMIT 1',
                            [
                                'organization_id' => $organizationId,
                                'id' => (int)$candidate,
                            ]
                        );

                        if ($modelRow && !empty($modelRow['title'])) {
                            $label = trim((string)$modelRow['title']);
                        }
                    } catch (Exception $exception) {
                        // ignore lookup failure
                    }
                }

                $modelLabelCache[$raw] = $label;

                return $modelLabelCache[$raw];
            };

            $formatDate = static function ($value): string {
                $dateString = trim((string)($value ?? ''));
                if ($dateString === '') {
                    return '—';
                }

                try {
                    $date = new DateTime($dateString, new DateTimeZone('Asia/Tehran'));
                } catch (Exception $exception) {
                    try {
                        $date = new DateTime($dateString);
                        $date->setTimezone(new DateTimeZone('Asia/Tehran'));
                    } catch (Exception $innerException) {
                        return '—';
                    }
                }

                if (class_exists('IntlDateFormatter')) {
                    $formatter = new IntlDateFormatter(
                        'fa_IR@calendar=persian',
                        IntlDateFormatter::FULL,
                        IntlDateFormatter::NONE,
                        'Asia/Tehran',
                        IntlDateFormatter::TRADITIONAL,
                        'yyyy/MM/dd'
                    );

                    if ($formatter !== false) {
                        $formatted = $formatter->format($date);
                        if ($formatted !== false && $formatted !== null) {
                            return UtilityHelper::englishToPersian(UtilityHelper::persianToEnglish($formatted));
                        }
                    }
                }

                return UtilityHelper::englishToPersian($date->format('Y/m/d'));
            };

            $relevantEvaluations = [];
            $evaluationIds = [];

            foreach ($evaluations as $evaluationRow) {
                $evaluateesJson = $evaluationRow['evaluatees_json'] ?? '';
                $isAssigned = false;

                if (is_string($evaluateesJson) && $evaluateesJson !== '') {
                    $decoded = json_decode($evaluateesJson, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $value) {
                            if ((int)$value === $organizationUserId) {
                                $isAssigned = true;
                                break;
                            }
                        }
                    }
                }

                if (!$isAssigned) {
                    continue;
                }

                $relevantEvaluations[] = $evaluationRow;

                $evaluationId = (int)($evaluationRow['id'] ?? 0);
                if ($evaluationId > 0) {
                    $evaluationIds[$evaluationId] = $evaluationId;
                }
            }

            $evaluationIds = array_values($evaluationIds);

            $participationInfo = [];
            $evaluationToolsMap = [];

            if (!empty($evaluationIds)) {
                $placeholders = [];
                $params = [
                    'organization_id' => $organizationId,
                    'evaluatee_id' => $organizationUserId,
                ];

                foreach ($evaluationIds as $index => $evaluationId) {
                    $key = 'evaluation_' . $index;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $evaluationId;
                }

                if (!empty($placeholders)) {
                    $sql = sprintf(
                        'SELECT scores.evaluation_id, scores.tool_id, COUNT(*) AS total_scores, tools.name AS tool_name, tools.code AS tool_code
                         FROM organization_evaluation_tool_scores scores
                         LEFT JOIN organization_evaluation_tools tools ON tools.id = scores.tool_id
                         WHERE scores.organization_id = :organization_id
                           AND scores.evaluatee_id = :evaluatee_id
                           AND scores.evaluation_id IN (%s)
                         GROUP BY scores.evaluation_id, scores.tool_id, tools.name',
                        implode(', ', $placeholders)
                    );

                    try {
                        $participationRows = DatabaseHelper::fetchAll($sql, $params);
                        foreach ($participationRows as $participationRow) {
                            $evaluationId = (int)($participationRow['evaluation_id'] ?? 0);
                            if ($evaluationId <= 0) {
                                continue;
                            }

                            if (!isset($participationInfo[$evaluationId])) {
                                $participationInfo[$evaluationId] = [
                                    'completed_tool_ids' => [],
                                    'tool_names' => [],
                                ];
                            }

                            $toolId = (int)($participationRow['tool_id'] ?? 0);
                            if ($toolId > 0) {
                                $participationInfo[$evaluationId]['completed_tool_ids'][$toolId] = true;
                            }

                            $toolName = trim((string)($participationRow['tool_name'] ?? ''));
                            if ($toolName === '') {
                                $toolName = trim((string)($participationRow['tool_code'] ?? ''));
                            }
                            if ($toolName !== '' && !in_array($toolName, $participationInfo[$evaluationId]['tool_names'], true)) {
                                $participationInfo[$evaluationId]['tool_names'][] = $toolName;
                            }
                        }
                    } catch (Exception $exception) {
                        $participationInfo = [];
                    }
                }

                $toolPlaceholders = [];
                $toolParams = ['organization_id' => $organizationId];
                foreach ($evaluationIds as $index => $evaluationId) {
                    $paramKey = 'tool_eval_' . $index;
                    $toolPlaceholders[] = ':' . $paramKey;
                    $toolParams[$paramKey] = $evaluationId;
                }

                if (!empty($toolPlaceholders)) {
                    $toolSql = sprintf(
                        'SELECT assign.evaluation_id AS evaluation_id,
                                assign.tool_id AS id,
                                assign.sort_order AS display_order,
                                tools.name AS name,
                                tools.code AS code
                         FROM organization_evaluation_tool_assignments assign
                         INNER JOIN organization_evaluation_tools tools
                           ON tools.id = assign.tool_id AND tools.organization_id = :organization_id
                         WHERE assign.evaluation_id IN (%s)
                           AND tools.is_exam = 1
                         ORDER BY assign.evaluation_id ASC, assign.sort_order ASC, assign.tool_id ASC',
                        implode(', ', $toolPlaceholders)
                    );

                    try {
                        $toolRows = DatabaseHelper::fetchAll($toolSql, $toolParams);
                        foreach ($toolRows as $toolRow) {
                            $evalId = (int)($toolRow['evaluation_id'] ?? 0);
                            $toolId = (int)($toolRow['id'] ?? 0);
                            if ($evalId <= 0 || $toolId <= 0) {
                                continue;
                            }

                            if (!isset($evaluationToolsMap[$evalId])) {
                                $evaluationToolsMap[$evalId] = [];
                            }

                            $orderValue = isset($toolRow['display_order'])
                                ? (int)$toolRow['display_order']
                                : (count($evaluationToolsMap[$evalId]) + 1);
                            if ($orderValue <= 0) {
                                $orderValue = count($evaluationToolsMap[$evalId]) + 1;
                            }

                            $evaluationToolsMap[$evalId][$toolId] = [
                                'name' => trim((string)($toolRow['name'] ?? '')),
                                'code' => trim((string)($toolRow['code'] ?? '')),
                                'order' => $orderValue,
                            ];
                        }
                    } catch (Exception $exception) {
                        $evaluationToolsMap = [];
                    }
                }
            }

            $toolIdsAll = [];
            foreach ($evaluationToolsMap as $toolMap) {
                foreach ($toolMap as $toolId => $meta) {
                    $toolId = (int)$toolId;
                    if ($toolId > 0) {
                        $toolIdsAll[$toolId] = true;
                    }
                }
            }
            $toolIdsAll = array_keys($toolIdsAll);

            $questionCountsByTool = [];
            if (!empty($toolIdsAll)) {
                $placeholders = implode(',', array_fill(0, count($toolIdsAll), '?'));
                try {
                    $qRows = DatabaseHelper::fetchAll(
                        "SELECT evaluation_tool_id AS tool_id, COUNT(*) AS total_q
                         FROM organization_evaluation_tool_questions
                         WHERE evaluation_tool_id IN ({$placeholders})
                         GROUP BY evaluation_tool_id",
                        $toolIdsAll
                    );
                } catch (Exception $exception) {
                    $qRows = [];
                }

                foreach ($qRows as $qr) {
                    $tid = (int)($qr['tool_id'] ?? 0);
                    if ($tid > 0) {
                        $questionCountsByTool[$tid] = (int)($qr['total_q'] ?? 0);
                    }
                }
            }

            $answerCounts = [];
            if (!empty($evaluationIds)) {
                $placeholders = implode(',', array_fill(0, count($evaluationIds), '?'));
                $params = array_merge([
                    $organizationId,
                    $organizationUserId,
                ], $evaluationIds);

                try {
                    $aRows = DatabaseHelper::fetchAll(
                        "SELECT evaluation_id, tool_id, COUNT(*) AS total_answers
                         FROM organization_evaluation_exam_answers
                         WHERE organization_id = ?
                           AND evaluatee_id = ?
                           AND evaluation_id IN ({$placeholders})
                         GROUP BY evaluation_id, tool_id",
                        $params
                    );
                } catch (Exception $exception) {
                    $aRows = [];
                }

                foreach ($aRows as $ar) {
                    $eid = (int)($ar['evaluation_id'] ?? 0);
                    $tid = (int)($ar['tool_id'] ?? 0);
                    if ($eid <= 0 || $tid <= 0) {
                        continue;
                    }
                    if (!isset($answerCounts[$eid])) {
                        $answerCounts[$eid] = [];
                    }
                    $answerCounts[$eid][$tid] = (int)($ar['total_answers'] ?? 0);
                }
            }

            $participationCompleted = [];
            if (!empty($evaluationIds)) {
                $placeholders = implode(',', array_fill(0, count($evaluationIds), '?'));
                $params = array_merge([
                    $organizationId,
                    $organizationUserId,
                ], $evaluationIds);

                try {
                    $pRows = DatabaseHelper::fetchAll(
                        "SELECT evaluation_id, tool_id, MAX(is_completed) AS is_completed
                         FROM organization_evaluation_exam_participations
                         WHERE organization_id = ?
                           AND evaluatee_id = ?
                           AND evaluation_id IN ({$placeholders})
                         GROUP BY evaluation_id, tool_id",
                        $params
                    );
                } catch (Exception $exception) {
                    $pRows = [];
                }

                foreach ($pRows as $pr) {
                    $eid = (int)($pr['evaluation_id'] ?? 0);
                    $tid = (int)($pr['tool_id'] ?? 0);
                    if ($eid <= 0 || $tid <= 0) {
                        continue;
                    }
                    if (!isset($participationCompleted[$eid])) {
                        $participationCompleted[$eid] = [];
                    }
                    $participationCompleted[$eid][$tid] = ((int)($pr['is_completed'] ?? 0) === 1);
                }
            }

            $washupCompleted = [];
            if (!empty($evaluationIds)) {
                $placeholders = implode(',', array_fill(0, count($evaluationIds), '?'));
                $params = array_merge([
                    $organizationId,
                    $organizationUserId,
                ], $evaluationIds);

                try {
                    $washupRows = DatabaseHelper::fetchAll(
                        "SELECT evaluation_id, COUNT(*) AS agreed_count
                         FROM organization_evaluation_agreed_scores
                         WHERE organization_id = ?
                           AND evaluatee_id = ?
                           AND evaluation_id IN ({$placeholders})
                           AND agreed_score IS NOT NULL
                         GROUP BY evaluation_id",
                        $params
                    );
                } catch (Exception $exception) {
                    $washupRows = [];
                }

                foreach ($washupRows as $row) {
                    $eid = (int)($row['evaluation_id'] ?? 0);
                    if ($eid > 0 && (int)($row['agreed_count'] ?? 0) > 0) {
                        $washupCompleted[$eid] = true;
                    }
                }
            }

            $visibilityMap = [];
            if (!empty($evaluationIds)) {
                $placeholders = implode(',', array_fill(0, count($evaluationIds), '?'));
                $params = array_merge([
                    $organizationId,
                    $organizationUserId,
                ], $evaluationIds);

                try {
                    $visibilityRows = DatabaseHelper::fetchAll(
                        "SELECT evaluation_id, is_visible
                         FROM organization_evaluation_user_visibility
                         WHERE organization_id = ?
                           AND evaluatee_id = ?
                           AND evaluation_id IN ({$placeholders})",
                        $params
                    );
                } catch (Exception $exception) {
                    $visibilityRows = [];
                }

                foreach ($visibilityRows as $visibilityRow) {
                    $eid = (int)($visibilityRow['evaluation_id'] ?? 0);
                    if ($eid > 0) {
                        $visibilityMap[$eid] = ((int)($visibilityRow['is_visible'] ?? 0) === 1);
                    }
                }
            }

            foreach ($relevantEvaluations as $evaluationRow) {
                $evaluationId = (int)($evaluationRow['id'] ?? 0);
                if ($evaluationId <= 0) {
                    continue;
                }

                $titleValue = trim((string)($evaluationRow['title'] ?? ''));
                if ($titleValue === '') {
                    $titleValue = 'برنامه ارزیابی #' . UtilityHelper::englishToPersian((string)$evaluationId);
                }

                $generalModel = $resolveModelLabel($evaluationRow['general_model'] ?? '') ?: '';
                $specificModel = $resolveModelLabel($evaluationRow['specific_model'] ?? '') ?: '';
                $modelParts = array_values(array_filter([$generalModel, $specificModel], static fn($value) => trim((string)$value) !== ''));
                $modelLabel = !empty($modelParts) ? implode(' / ', $modelParts) : '—';

                $evaluationDateDisplay = $formatDate($evaluationRow['evaluation_date'] ?? null);

                $toolsForEvaluation = $evaluationToolsMap[$evaluationId] ?? [];
                $completedToolNames = [];
                $incompleteToolNames = [];
                $completedToolsCount = 0;
                $totalToolsCount = count($toolsForEvaluation);

                foreach ($toolsForEvaluation as $toolId => $toolMeta) {
                    $toolId = (int)$toolId;
                    $need = (int)($questionCountsByTool[$toolId] ?? 0);
                    $have = (int)($answerCounts[$evaluationId][$toolId] ?? 0);
                    $participationFlag = !empty($participationCompleted[$evaluationId][$toolId]);

                    $displayName = trim((string)($toolMeta['name'] ?? ''));
                    if ($displayName === '') {
                        $displayName = trim((string)($toolMeta['code'] ?? ''));
                    }
                    if ($displayName === '') {
                        $displayName = 'آزمون ' . UtilityHelper::englishToPersian((string)($toolMeta['order'] ?? 0));
                    }

                    $isToolCompleted = false;
                    if ($need > 0) {
                        $isToolCompleted = $have >= $need;
                    } else {
                        $isToolCompleted = $participationFlag;
                    }

                    if ($isToolCompleted) {
                        $completedToolsCount++;
                        $completedToolNames[] = $displayName;
                    } else {
                        $incompleteToolNames[] = $displayName;
                    }
                }

                if ($completedToolsCount === 0 && isset($participationInfo[$evaluationId]['tool_names'])) {
                    $completedToolNames = $participationInfo[$evaluationId]['tool_names'];
                }

                $completedToolNames = array_values(array_unique(array_filter($completedToolNames, static fn($value) => trim((string)$value) !== '')));
                $incompleteToolNames = array_values(array_unique(array_filter($incompleteToolNames, static fn($value) => trim((string)$value) !== '')));

                $allToolsCompleted = ($totalToolsCount > 0 && $completedToolsCount >= $totalToolsCount);
                $washupDone = !empty($washupCompleted[$evaluationId]);
                $isVisible = !empty($visibilityMap[$evaluationId]);
                $hasCertificateAccess = $allToolsCompleted && $washupDone && $isVisible;

                $reports[] = [
                    'evaluation_id' => $evaluationId,
                    'title' => $titleValue,
                    'model' => $modelLabel,
                    'evaluation_date' => $evaluationDateDisplay,
                    'total_tools_count' => $totalToolsCount,
                    'completed_tools_count' => $completedToolsCount,
                    'completed_tool_names' => $completedToolNames,
                    'incomplete_tool_names' => $incompleteToolNames,
                    'all_tools_completed' => $allToolsCompleted,
                    'washup_completed' => $washupDone,
                    'is_visible' => $isVisible,
                    'has_certificate_access' => $hasCertificateAccess,
                    'certificate_url' => UtilityHelper::baseUrl('tests/reports/certificate?evaluation_id=' . urlencode((string)$evaluationId) . '&evaluatee_id=' . urlencode((string)$organizationUserId)),
                ];

                $summaryStats['total']++;
                if ($allToolsCompleted) {
                    $summaryStats['completed']++;
                }
                if ($hasCertificateAccess) {
                    $summaryStats['certificates']++;
                }
            }
        }

        include __DIR__ . '/../Views/home/tests/reports.php';
    }

    public function personalDevelopmentCourses(): void
    {
        AuthHelper::startSession();

        AuthHelper::requireAuth(UtilityHelper::baseUrl('user/login'));

        $title = 'دوره‌های توسعه فردی';
        $user = AuthHelper::getUser();
        $additional_css = [];
        $additional_js = [];
        $inline_styles = '';
        $inline_scripts = '';

        $courses = [];

        $organizationId = (int)($user['organization_id'] ?? 0);
        $organizationUserId = (int)($user['organization_user_id'] ?? 0);

        if ($organizationId > 0 && $organizationUserId > 0) {
            self::ensureCourseTables();

            try {
                $courses = DatabaseHelper::fetchAll(
                    'SELECT c.*, e.id AS enrollment_id, e.progress_percentage, e.completed_at, e.enrolled_at
                     FROM organization_course_enrollments e
                     INNER JOIN organization_courses c ON c.id = e.course_id
                     WHERE e.organization_id = :organization_id AND e.user_id = :user_id
                     ORDER BY c.sort_order ASC, c.created_at DESC',
                    [
                        'organization_id' => $organizationId,
                        'user_id' => $organizationUserId,
                    ]
                );
            } catch (Exception $exception) {
                $courses = [];
                if (class_exists('LogHelper')) {
                    LogHelper::error('personal_development_courses_fetch_failed', [
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            $courseIds = [];
            $enrollmentIds = [];

            foreach ($courses as $courseRow) {
                $courseId = (int)($courseRow['id'] ?? 0);
                $enrollmentId = (int)($courseRow['enrollment_id'] ?? 0);

                if ($courseId > 0) {
                    $courseIds[$courseId] = $courseId;
                }

                if ($enrollmentId > 0) {
                    $enrollmentIds[$enrollmentId] = $enrollmentId;
                }
            }

            $courseIds = array_values($courseIds);
            $enrollmentIds = array_values($enrollmentIds);

            $lessonsByCourse = [];

            if (!empty($courseIds)) {
                $lessonParams = [];
                $lessonPlaceholders = [];

                foreach ($courseIds as $index => $courseId) {
                    $placeholder = ':course_' . $index;
                    $lessonPlaceholders[] = $placeholder;
                    $lessonParams[$placeholder] = $courseId;
                }

                try {
                    $lessonRows = DatabaseHelper::fetchAll(
                        'SELECT id, course_id, title, description, short_description, learning_objectives, resources, text_content,
                                content_type, content_url, content_file, thumbnail_path, duration_minutes, sort_order, is_free,
                                is_published, available_at, created_at, updated_at
                         FROM organization_course_lessons
                         WHERE course_id IN (' . implode(', ', $lessonPlaceholders) . ')
                         ORDER BY course_id ASC, sort_order ASC, id ASC',
                        $lessonParams
                    );
                } catch (Exception $exception) {
                    $lessonRows = [];
                    if (class_exists('LogHelper')) {
                        LogHelper::error('personal_development_lessons_fetch_failed', [
                            'message' => $exception->getMessage(),
                        ]);
                    }
                }

                foreach ($lessonRows as $lessonRow) {
                    $courseId = (int)($lessonRow['course_id'] ?? 0);
                    if ($courseId <= 0) {
                        continue;
                    }

                    if (!isset($lessonsByCourse[$courseId])) {
                        $lessonsByCourse[$courseId] = [];
                    }

                    $lessonsByCourse[$courseId][] = $lessonRow;
                }
            }

            $progressMap = [];

            if (!empty($enrollmentIds)) {
                $progressParams = [
                    'user_id' => $organizationUserId,
                ];
                $progressPlaceholders = [];

                foreach ($enrollmentIds as $index => $enrollmentId) {
                    $placeholder = ':enrollment_' . $index;
                    $progressPlaceholders[] = $placeholder;
                    $progressParams[$placeholder] = $enrollmentId;
                }

                try {
                    $progressRows = DatabaseHelper::fetchAll(
                        'SELECT enrollment_id, lesson_id, is_completed, watch_duration_seconds, last_watched_at
                         FROM organization_course_progress
                         WHERE user_id = :user_id AND enrollment_id IN (' . implode(', ', $progressPlaceholders) . ')',
                        $progressParams
                    );
                } catch (Exception $exception) {
                    $progressRows = [];
                    if (class_exists('LogHelper')) {
                        LogHelper::error('personal_development_progress_fetch_failed', [
                            'message' => $exception->getMessage(),
                        ]);
                    }
                }

                foreach ($progressRows as $progressRow) {
                    $enrollmentId = (int)($progressRow['enrollment_id'] ?? 0);
                    $lessonId = (int)($progressRow['lesson_id'] ?? 0);

                    if ($enrollmentId <= 0 || $lessonId <= 0) {
                        continue;
                    }

                    if (!isset($progressMap[$enrollmentId])) {
                        $progressMap[$enrollmentId] = [];
                    }

                    $progressMap[$enrollmentId][$lessonId] = [
                        'is_completed' => (int)($progressRow['is_completed'] ?? 0) === 1,
                        'watch_duration_seconds' => (int)($progressRow['watch_duration_seconds'] ?? 0),
                        'last_watched_at' => $progressRow['last_watched_at'] ?? null,
                    ];
                }
            }

            $now = new DateTime('now', new DateTimeZone('Asia/Tehran'));

            foreach ($courses as &$courseRow) {
                $courseId = (int)($courseRow['id'] ?? 0);
                $enrollmentId = (int)($courseRow['enrollment_id'] ?? 0);

                $courseLessons = $lessonsByCourse[$courseId] ?? [];
                $totalLessons = count($courseLessons);
                $completedLessons = 0;
                $inProgressLessons = 0;

                $coverImage = trim((string)($courseRow['cover_image'] ?? ''));
                if ($coverImage !== '') {
                    $courseRow['cover_image_url'] = UtilityHelper::baseUrl('public/uploads/courses/' . ltrim($coverImage, '/'));
                } else {
                    $courseRow['cover_image_url'] = null;
                }

                $courseRow['enrolled_at_display'] = self::formatDateTimeForDisplay($courseRow['enrolled_at'] ?? null);
                $courseRow['completed_at_display'] = self::formatDateTimeForDisplay($courseRow['completed_at'] ?? null);

                foreach ($courseLessons as &$lessonRow) {
                    $lessonId = (int)($lessonRow['id'] ?? 0);
                    $contentType = trim((string)($lessonRow['content_type'] ?? 'video'));
                    if (!in_array($contentType, ['video', 'pdf', 'ppt', 'link', 'text'], true)) {
                        $contentType = 'video';
                    }

                    $contentFile = trim((string)($lessonRow['content_file'] ?? ''));
                    $contentUrl = trim((string)($lessonRow['content_url'] ?? ''));
                    $mediaUrl = '';

                    if ($contentFile !== '') {
                        $mediaUrl = UtilityHelper::baseUrl('public/uploads/lessons/' . ltrim($contentFile, '/'));
                    } elseif ($contentUrl !== '') {
                        $mediaUrl = preg_match('/^(?:https?:)?\/\//i', $contentUrl)
                            ? $contentUrl
                            : UtilityHelper::baseUrl(ltrim($contentUrl, '/'));
                    }

                    $viewerUrl = $mediaUrl;
                    if ($contentType === 'ppt' && $mediaUrl !== '') {
                        $viewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' . rawurlencode($mediaUrl);
                    }

                    $lessonRow['content_type'] = $contentType;
                    $lessonRow['media_url'] = $mediaUrl;
                    $lessonRow['viewer_url'] = $viewerUrl;

                    $thumbnailPath = trim((string)($lessonRow['thumbnail_path'] ?? ''));
                    $lessonRow['thumbnail_url'] = $thumbnailPath !== ''
                        ? UtilityHelper::baseUrl('public/uploads/lessons/' . ltrim($thumbnailPath, '/'))
                        : null;

                    $isPublished = (int)($lessonRow['is_published'] ?? 1) === 1;
                    $availableAt = self::parseDateTime($lessonRow['available_at'] ?? null);
                    $isAvailable = $isPublished;
                    $availableAtDisplay = null;

                    if ($availableAt instanceof DateTime) {
                        if ($availableAt > $now) {
                            $isAvailable = false;
                        }
                        $availableAtDisplay = UtilityHelper::englishToPersian($availableAt->format('Y/m/d H:i'));
                    }

                    $lessonProgress = ($enrollmentId > 0 && isset($progressMap[$enrollmentId][$lessonId]))
                        ? $progressMap[$enrollmentId][$lessonId]
                        : null;

                    $watchSeconds = (int)($lessonProgress['watch_duration_seconds'] ?? 0);
                    $isCompleted = !empty($lessonProgress['is_completed']);
                    $progressState = 'pending';

                    if (!$isAvailable) {
                        $progressState = 'scheduled';
                    }

                    if ($watchSeconds > 0) {
                        $progressState = 'in_progress';
                    }

                    if ($isCompleted) {
                        $progressState = 'completed';
                    }

                    if ($isCompleted) {
                        $completedLessons++;
                    } elseif ($watchSeconds > 0) {
                        $inProgressLessons++;
                    }

                    $lessonRow['is_available'] = $isAvailable ? 1 : 0;
                    $lessonRow['available_at_display'] = $availableAtDisplay;
                    $lessonRow['is_completed'] = $isCompleted ? 1 : 0;
                    $lessonRow['watch_duration_seconds'] = $watchSeconds;
                    $lessonRow['watch_duration_display'] = $watchSeconds > 0
                        ? UtilityHelper::englishToPersian(self::formatDuration($watchSeconds))
                        : null;
                    $lessonRow['progress_state'] = $progressState;
                    $lessonRow['last_watched_at'] = $lessonProgress['last_watched_at'] ?? null;
                    $lessonRow['last_watched_display'] = self::formatDateTimeForDisplay($lessonProgress['last_watched_at'] ?? null);
                }
                unset($lessonRow);

                $percentage = $totalLessons > 0
                    ? (int)round(($completedLessons / $totalLessons) * 100)
                    : (int)round((float)($courseRow['progress_percentage'] ?? 0));

                if ($percentage < 0) {
                    $percentage = 0;
                }
                if ($percentage > 100) {
                    $percentage = 100;
                }

                $courseRow['progress'] = [
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'in_progress_lessons' => $inProgressLessons,
                    'percentage' => $percentage,
                ];

                $courseRow['lessons'] = $courseLessons;
                $courseRow['lesson_count'] = $totalLessons;
            }
            unset($courseRow);
        }

        include __DIR__ . '/../Views/home/courses/personal-development.php';
    }

    public function exams(): void
    {
        AuthHelper::startSession();

        AuthHelper::requireAuth(UtilityHelper::baseUrl('user/login'));

    $title = 'آزمون‌ها';
    $user = AuthHelper::getUser();
        $additional_css = [];
        $additional_js = [];
        $inline_styles = '';
        $inline_scripts = '';

    $calendarUrl = UtilityHelper::baseUrl('tests/training-calendar');

    $organizationId = (int) ($user['organization_id'] ?? 0);
        $organizationUserId = (int) ($user['organization_user_id'] ?? 0);
        $evaluationId = (int) UtilityHelper::persianToEnglish((string) ($_GET['evaluation_id'] ?? 0));
        $requestedToolId = (int) UtilityHelper::persianToEnglish((string) ($_GET['tool_id'] ?? 0));
    $examBaseUrl = UtilityHelper::baseUrl('tests/exams?evaluation_id=' . urlencode((string) $evaluationId));
        $examCurrentUrl = $examBaseUrl;
        if ($requestedToolId > 0) {
            $examCurrentUrl = UtilityHelper::baseUrl('tests/exams?evaluation_id=' . urlencode((string) $evaluationId) . '&tool_id=' . urlencode((string) $requestedToolId));
        }

        if ($organizationId <= 0 || $organizationUserId <= 0 || $evaluationId <= 0) {
            ResponseHelper::flashError('دسترسی به آزمون امکان‌پذیر نیست.');
            UtilityHelper::redirect($calendarUrl);
        }

        try {
            $evaluationRow = DatabaseHelper::fetchOne(
                'SELECT id, title, evaluation_date, evaluatees_json, schedule_id
                 FROM organization_evaluations
                 WHERE id = :id AND organization_id = :organization_id
                 LIMIT 1',
                [
                    'id' => $evaluationId,
                    'organization_id' => $organizationId,
                ]
            );
        } catch (Exception $exception) {
            $evaluationRow = null;
        }

        if (!$evaluationRow) {
            ResponseHelper::flashError('ارزیابی موردنظر یافت نشد.');
            UtilityHelper::redirect($calendarUrl);
        }

        $evaluateesJson = $evaluationRow['evaluatees_json'] ?? '';
        $evaluatees = [];
        if (is_string($evaluateesJson) && $evaluateesJson !== '') {
            $decodedEvaluatees = json_decode($evaluateesJson, true);
            if (is_array($decodedEvaluatees)) {
                $evaluatees = array_map('intval', $decodedEvaluatees);
            }
        }

        if (!in_array($organizationUserId, $evaluatees, true)) {
            ResponseHelper::flashError('شما در این ارزیابی ثبت نشده‌اید.');
            UtilityHelper::redirect($calendarUrl);
        }

        if (!isset($_SESSION['exam_progress']) || !is_array($_SESSION['exam_progress'])) {
            $_SESSION['exam_progress'] = [];
        }

        if (!isset($_SESSION['exam_progress'][$evaluationId]) || !is_array($_SESSION['exam_progress'][$evaluationId])) {
            $_SESSION['exam_progress'][$evaluationId] = [
                'completed_tool_ids' => [],
            ];
        }

        if (!isset($_SESSION['exam_progress'][$evaluationId]['answers']) || !is_array($_SESSION['exam_progress'][$evaluationId]['answers'])) {
            $_SESSION['exam_progress'][$evaluationId]['answers'] = [];
        }

        if (!isset($_SESSION['exam_progress'][$evaluationId]['current_question_index']) || !is_array($_SESSION['exam_progress'][$evaluationId]['current_question_index'])) {
            $_SESSION['exam_progress'][$evaluationId]['current_question_index'] = [];
        }
        if (!isset($_SESSION['exam_progress'][$evaluationId]['start_time']) || !is_array($_SESSION['exam_progress'][$evaluationId]['start_time'])) {
            $_SESSION['exam_progress'][$evaluationId]['start_time'] = [];
        }

        self::ensureExamStorageTables();

        $toolAssignments = [];
        try {
            $toolAssignments = DatabaseHelper::fetchAll(
                'SELECT assign.tool_id, assign.sort_order, tools.code, tools.name, tools.description, tools.question_type, tools.calculation_formula, tools.guide, tools.is_exam, tools.is_optional, tools.duration_minutes
                 FROM organization_evaluation_tool_assignments assign
                 INNER JOIN organization_evaluation_tools tools ON tools.id = assign.tool_id AND tools.organization_id = :organization_id
                 WHERE assign.evaluation_id = :evaluation_id
                 ORDER BY assign.sort_order ASC, assign.id ASC',
                [
                    'organization_id' => $organizationId,
                    'evaluation_id' => $evaluationId,
                ]
            );
        } catch (Exception $exception) {
            $toolAssignments = [];
        }

        $identifyDiscTool = static function (array $toolRow): bool {
            $candidates = [
                $toolRow['code'] ?? '',
                $toolRow['name'] ?? '',
                $toolRow['description'] ?? '',
                $toolRow['question_type'] ?? '',
                $toolRow['calculation_formula'] ?? '',
                $toolRow['guide'] ?? '',
            ];

            foreach ($candidates as $value) {
                $normalized = strtolower(trim((string) $value));
                if ($normalized !== '' && strpos($normalized, 'disc') !== false) {
                    return true;
                }
            }

            return false;
        };

        $examTools = [];
        $examToolsById = [];
        foreach ($toolAssignments as $assignment) {
            $toolId = (int) ($assignment['tool_id'] ?? 0);
            if ($toolId <= 0) {
                continue;
            }

            $isExam = (int) ($assignment['is_exam'] ?? 0);
            if ($isExam !== 1) {
                continue;
            }

            $examTool = [
                'tool_id' => $toolId,
                'code' => trim((string) ($assignment['code'] ?? '')),
                'name' => trim((string) ($assignment['name'] ?? 'بدون نام')),
                'description' => trim((string) ($assignment['description'] ?? '')),
                'question_type' => trim((string) ($assignment['question_type'] ?? '')),
                'calculation_formula' => trim((string) ($assignment['calculation_formula'] ?? '')),
                'guide' => trim((string) ($assignment['guide'] ?? '')),
                'sort_order' => (int) ($assignment['sort_order'] ?? 0),
                'is_optional' => (int) ($assignment['is_optional'] ?? 0),
                'duration_minutes' => isset($assignment['duration_minutes']) && $assignment['duration_minutes'] !== '' ? (int) $assignment['duration_minutes'] : 0,
            ];

            $examTool['is_disc'] = $identifyDiscTool($examTool);

            $examTools[] = $examTool;
            $examToolsById[$toolId] = $examTool;
        }

        usort($examTools, static function (array $a, array $b): int {
            $orderA = $a['sort_order'] ?? 0;
            $orderB = $b['sort_order'] ?? 0;
            if ($orderA === $orderB) {
                return ($a['tool_id'] ?? 0) <=> ($b['tool_id'] ?? 0);
            }

            return $orderA <=> $orderB;
        });

        if (empty($examTools)) {
            ResponseHelper::flashError('برای این ارزیابی آزمونی تعریف نشده است.');
            UtilityHelper::redirect($calendarUrl);
        }

        $toolIds = array_column($examTools, 'tool_id');

        $participationMap = [];
        if (!empty($toolIds)) {
            $placeholders = [];
            $params = [
                'organization_id' => $organizationId,
                'evaluation_id' => $evaluationId,
                'evaluatee_id' => $organizationUserId,
            ];

            foreach ($toolIds as $index => $toolId) {
                $placeholder = ':tool_' . $index;
                $placeholders[] = $placeholder;
                $params['tool_' . $index] = $toolId;
            }

            $sql = sprintf(
                'SELECT tool_id, COUNT(*) AS total_scores, MAX(updated_at) AS last_updated
                 FROM organization_evaluation_tool_scores
                 WHERE organization_id = :organization_id
                   AND evaluation_id = :evaluation_id
                   AND evaluatee_id = :evaluatee_id
                   AND tool_id IN (%s)
                 GROUP BY tool_id',
                implode(', ', $placeholders)
            );

            try {
                $participationRows = DatabaseHelper::fetchAll($sql, $params);
                foreach ($participationRows as $row) {
                    $toolId = (int) ($row['tool_id'] ?? 0);
                    if ($toolId <= 0) {
                        continue;
                    }

                    $participationMap[$toolId] = [
                        'total_scores' => (int) ($row['total_scores'] ?? 0),
                        'last_updated' => $row['last_updated'] ?? null,
                        'is_completed' => (int) ($row['total_scores'] ?? 0) > 0,
                    ];
                }
            } catch (Exception $exception) {
                $participationMap = [];
            }

            try {
                $examParticipationSql = sprintf(
                    'SELECT id, tool_id, total_questions, answered_questions, is_completed, completed_at
                     FROM organization_evaluation_exam_participations
                     WHERE organization_id = :organization_id
                       AND evaluation_id = :evaluation_id
                       AND evaluatee_id = :evaluatee_id
                       AND tool_id IN (%s)',
                    implode(', ', $placeholders)
                );

                $examParticipationRows = DatabaseHelper::fetchAll($examParticipationSql, $params);
                foreach ($examParticipationRows as $examParticipationRow) {
                    $toolId = (int) ($examParticipationRow['tool_id'] ?? 0);
                    if ($toolId <= 0) {
                        continue;
                    }

                    if (!isset($participationMap[$toolId])) {
                        $participationMap[$toolId] = [
                            'total_scores' => 0,
                            'last_updated' => null,
                        ];
                    }

                    $isCompleted = (int) ($examParticipationRow['is_completed'] ?? 0) === 1;
                    $participationMap[$toolId]['is_completed'] = $isCompleted || !empty($participationMap[$toolId]['is_completed']);
                    $participationMap[$toolId]['exam_participation'] = [
                        'id' => (int) ($examParticipationRow['id'] ?? 0),
                        'total_questions' => (int) ($examParticipationRow['total_questions'] ?? 0),
                        'answered_questions' => (int) ($examParticipationRow['answered_questions'] ?? 0),
                        'completed_at' => $examParticipationRow['completed_at'] ?? null,
                    ];

                    if (!empty($participationMap[$toolId]['exam_participation']['completed_at'])) {
                        $participationMap[$toolId]['last_updated'] = $participationMap[$toolId]['exam_participation']['completed_at'];
                    }
                }
            } catch (Exception $exception) {
                // ignore exam participation merge failures
            }
        }

        $sessionCompletedToolIds = $_SESSION['exam_progress'][$evaluationId]['completed_tool_ids'] ?? [];
        if (!is_array($sessionCompletedToolIds)) {
            $sessionCompletedToolIds = [];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!AuthHelper::verifyCsrfToken((string) $csrfToken)) {
                ResponseHelper::flashError('درخواست نامعتبر است. لطفاً دوباره تلاش کنید.');
                UtilityHelper::redirect($examCurrentUrl);
            }

            $action = trim((string) ($_POST['action'] ?? ''));

            if (in_array($action, ['navigate_question', 'navigate_next', 'navigate_previous'], true)) {
                $submittedToolId = (int) UtilityHelper::persianToEnglish((string) ($_POST['tool_id'] ?? 0));
                if ($submittedToolId <= 0 || !in_array($submittedToolId, $toolIds, true)) {
                    ResponseHelper::flashError('آزمون انتخاب شده معتبر نیست.');
                    UtilityHelper::redirect($examCurrentUrl);
                }

                // Persist start time on first interaction for timed exams
                $submittedToolMeta = $examToolsById[$submittedToolId] ?? [];
                $toolMinutes = isset($submittedToolMeta['duration_minutes']) ? (int) $submittedToolMeta['duration_minutes'] : 0;
                if ($toolMinutes > 0 && empty($_SESSION['exam_progress'][$evaluationId]['start_time'][$submittedToolId])) {
                    $_SESSION['exam_progress'][$evaluationId]['start_time'][$submittedToolId] = time();
                }
                $isDiscSubmission = $identifyDiscTool($submittedToolMeta);

                $existingAnswers = $_SESSION['exam_progress'][$evaluationId]['answers'][$submittedToolId] ?? [];
                if (!is_array($existingAnswers)) {
                    $existingAnswers = [];
                }

                $submittedAnswersRaw = $_POST['answers'] ?? [];
                if (is_array($submittedAnswersRaw)) {
                    foreach ($submittedAnswersRaw as $questionKey => $answerValue) {
                        $questionId = (int) UtilityHelper::persianToEnglish((string) $questionKey);
                        if ($questionId <= 0) {
                            continue;
                        }

                        if ($isDiscSubmission) {
                            $bestRaw = $answerValue['best'] ?? null;
                            $leastRaw = $answerValue['least'] ?? null;

                            if (is_array($bestRaw)) {
                                $bestRaw = reset($bestRaw);
                            }
                            if (is_array($leastRaw)) {
                                $leastRaw = reset($leastRaw);
                            }

                            $bestId = (int) UtilityHelper::persianToEnglish((string) ($bestRaw ?? 0));
                            $leastId = (int) UtilityHelper::persianToEnglish((string) ($leastRaw ?? 0));

                            $existingAnswers[$questionId] = [
                                'best' => $bestId,
                                'least' => $leastId,
                            ];
                        } else {
                            if (is_array($answerValue)) {
                                $answerValue = reset($answerValue);
                            }
                            $answerId = (int) UtilityHelper::persianToEnglish((string) ($answerValue ?? 0));
                            $existingAnswers[$questionId] = $answerId;
                        }
                    }
                }

                $_SESSION['exam_progress'][$evaluationId]['answers'][$submittedToolId] = $existingAnswers;

                $questionRows = [];
                try {
                    $questionRows = DatabaseHelper::fetchAll(
                        'SELECT id
                         FROM organization_evaluation_tool_questions
                         WHERE organization_id = :organization_id AND evaluation_tool_id = :tool_id
                         ORDER BY display_order ASC, id ASC',
                        [
                            'organization_id' => $organizationId,
                            'tool_id' => $submittedToolId,
                        ]
                    );
                } catch (Exception $exception) {
                    $questionRows = [];
                }

                $questionIdsOrdered = [];
                foreach ($questionRows as $questionRow) {
                    $extractedId = (int) ($questionRow['id'] ?? 0);
                    if ($extractedId > 0) {
                        $questionIdsOrdered[] = $extractedId;
                    }
                }

                $direction = trim((string) ($_POST['direction'] ?? ''));
                if ($direction === '' && $action === 'navigate_next') {
                    $direction = 'next';
                } elseif ($direction === '' && $action === 'navigate_previous') {
                    $direction = 'previous';
                }
                $submittedQuestionId = (int) UtilityHelper::persianToEnglish((string) ($_POST['question_id'] ?? 0));
                $targetIndexRaw = $_POST['target_index'] ?? null;

                $currentIndex = (int) ($_SESSION['exam_progress'][$evaluationId]['current_question_index'][$submittedToolId] ?? 0);

                if (!empty($questionIdsOrdered)) {
                    $currentIndex = max(0, min($currentIndex, count($questionIdsOrdered) - 1));

                    if ($submittedQuestionId > 0) {
                        $locatedIndex = array_search($submittedQuestionId, $questionIdsOrdered, true);
                        if ($locatedIndex !== false) {
                            $currentIndex = (int) $locatedIndex;
                        }
                    }

                    if ($targetIndexRaw !== null) {
                        $targetIndex = (int) UtilityHelper::persianToEnglish((string) $targetIndexRaw);
                        if ($targetIndex >= 0 && $targetIndex < count($questionIdsOrdered)) {
                            $currentIndex = $targetIndex;
                        }
                    } elseif ($direction === 'next') {
                        $currentIndex = min($currentIndex + 1, count($questionIdsOrdered) - 1);
                    } elseif ($direction === 'previous') {
                        $currentIndex = max($currentIndex - 1, 0);
                    }
                } else {
                    $currentIndex = 0;
                }

                $_SESSION['exam_progress'][$evaluationId]['current_question_index'][$submittedToolId] = $currentIndex;
                UtilityHelper::redirect($examCurrentUrl);
            }

            if ($action === 'finish_exam') {
                $submittedToolId = (int) UtilityHelper::persianToEnglish((string) ($_POST['tool_id'] ?? 0));
                $expiredFlag = (int) UtilityHelper::persianToEnglish((string) ($_POST['expired'] ?? 0)) === 1;

                if ($submittedToolId <= 0 || !in_array($submittedToolId, $toolIds, true)) {
                    ResponseHelper::flashError('آزمون انتخاب شده معتبر نیست.');
                    UtilityHelper::redirect($examCurrentUrl);
                }

                $combinedCompletedBefore = [];
                foreach ($participationMap as $toolId => $info) {
                    $toolId = (int) $toolId;
                    if ($toolId <= 0) {
                        continue;
                    }

                    $hasScores = (int) ($info['total_scores'] ?? 0) > 0;
                    $hasCompletionFlag = !empty($info['is_completed']);
                    if ($hasScores || $hasCompletionFlag) {
                        $combinedCompletedBefore[$toolId] = true;
                    }
                }
                foreach ($sessionCompletedToolIds as $toolId) {
                    $toolId = (int) $toolId;
                    if ($toolId > 0) {
                        $combinedCompletedBefore[$toolId] = true;
                    }
                }

                $currentAccessibleToolId = 0;
                foreach ($examTools as $examTool) {
                    $toolId = (int) ($examTool['tool_id'] ?? 0);
                    if ($toolId <= 0) {
                        continue;
                    }

                    if (!isset($combinedCompletedBefore[$toolId])) {
                        $currentAccessibleToolId = $toolId;
                        break;
                    }
                }

                if ($currentAccessibleToolId === 0) {
                    ResponseHelper::flashInfo('تمام آزمون‌ها قبلاً تکمیل شده‌اند.');
                    UtilityHelper::redirect($examBaseUrl);
                }

                if ($submittedToolId !== $currentAccessibleToolId) {
                    ResponseHelper::flashError('امکان پایان این آزمون در حال حاضر وجود ندارد.');
                    UtilityHelper::redirect($examCurrentUrl);
                }

                $submittedToolMeta = $examToolsById[$submittedToolId] ?? [];
                $isDiscSubmission = $identifyDiscTool($submittedToolMeta);
                $isOptionalSubmission = !empty($submittedToolMeta['is_optional']);

                // Enforce timeout if server-side window elapsed
                $toolMinutes = isset($submittedToolMeta['duration_minutes']) ? (int) $submittedToolMeta['duration_minutes'] : 0;
                if ($toolMinutes > 0) {
                    $startTs = (int) ($_SESSION['exam_progress'][$evaluationId]['start_time'][$submittedToolId] ?? 0);
                    if ($startTs > 0) {
                        $elapsed = max(0, time() - $startTs);
                        if ($elapsed >= ($toolMinutes * 60)) {
                            $expiredFlag = true;
                        }
                    }
                }

                $submittedAnswersRaw = $_POST['answers'] ?? [];
                if ($submittedAnswersRaw !== null && !is_array($submittedAnswersRaw)) {
                    ResponseHelper::flashError('ساختار پاسخ‌های ارسالی نامعتبر است.');
                    UtilityHelper::redirect($examCurrentUrl);
                }

                $normalizedAnswers = [];
                if (is_array($submittedAnswersRaw)) {
                    foreach ($submittedAnswersRaw as $questionKey => $answerValue) {
                        $questionId = (int) UtilityHelper::persianToEnglish((string) $questionKey);
                        if ($questionId <= 0) {
                            continue;
                        }

                        if ($isDiscSubmission) {
                            $bestRaw = $answerValue['best'] ?? null;
                            $leastRaw = $answerValue['least'] ?? null;

                            if (is_array($bestRaw)) {
                                $bestRaw = reset($bestRaw);
                            }
                            if (is_array($leastRaw)) {
                                $leastRaw = reset($leastRaw);
                            }

                            $bestId = (int) UtilityHelper::persianToEnglish((string) ($bestRaw ?? 0));
                            $leastId = (int) UtilityHelper::persianToEnglish((string) ($leastRaw ?? 0));

                            if ($bestId > 0 || $leastId > 0) {
                                $normalizedAnswers[$questionId] = [
                                    'best' => $bestId,
                                    'least' => $leastId,
                                ];
                            }
                        } else {
                            if (is_array($answerValue)) {
                                $answerValue = reset($answerValue);
                            }
                            $answerId = (int) UtilityHelper::persianToEnglish((string) ($answerValue ?? 0));
                            if ($answerId > 0) {
                                $normalizedAnswers[$questionId] = $answerId;
                            }
                        }
                    }
                }

                try {
                    $questionRows = DatabaseHelper::fetchAll(
                        'SELECT id, title, question_text, description, is_description_only
                         FROM organization_evaluation_tool_questions
                         WHERE organization_id = :organization_id AND evaluation_tool_id = :tool_id',
                        [
                            'organization_id' => $organizationId,
                            'tool_id' => $submittedToolId,
                        ]
                    );
                } catch (Exception $exception) {
                    $questionRows = [];
                }

                $questionIds = [];
                $questionsRequiringAnswers = [];
                $questionDetailsForStorage = [];
                foreach ($questionRows as $questionRow) {
                    $questionId = (int) ($questionRow['id'] ?? 0);
                    if ($questionId <= 0) {
                        continue;
                    }

                    $questionIds[] = $questionId;
                    $questionsRequiringAnswers[$questionId] = !$isOptionalSubmission && ((int) ($questionRow['is_description_only'] ?? 0) !== 1);

                    $questionDetailsForStorage[$questionId] = [
                        'title' => trim((string) ($questionRow['title'] ?? '')),
                        'text' => trim((string) ($questionRow['question_text'] ?? '')),
                        'description' => trim((string) ($questionRow['description'] ?? '')),
                        'is_description_only' => ((int) ($questionRow['is_description_only'] ?? 0) === 1),
                        'requires_answer' => false,
                        'answers' => [],
                    ];
                }

                $validAnswerMap = [];
                $answerDetailsByQuestion = [];
                if (!empty($questionIds)) {
                    $answerPlaceholders = [];
                    $answerParams = [
                        'organization_id' => $organizationId,
                        'tool_id' => $submittedToolId,
                    ];

                    foreach ($questionIds as $index => $questionId) {
                        $placeholder = ':question_' . $index;
                        $answerPlaceholders[] = $placeholder;
                        $answerParams['question_' . $index] = $questionId;
                    }

                    $answerSql = sprintf(
                        'SELECT id, question_id, answer_code, option_text
                         FROM organization_evaluation_tool_answers
                         WHERE organization_id = :organization_id
                           AND evaluation_tool_id = :tool_id
                           AND question_id IN (%s)',
                        implode(', ', $answerPlaceholders)
                    );

                    try {
                        $answerRows = DatabaseHelper::fetchAll($answerSql, $answerParams);
                    } catch (Exception $exception) {
                        $answerRows = [];
                    }

                    foreach ($answerRows as $answerRow) {
                        $questionId = (int) ($answerRow['question_id'] ?? 0);
                        $answerId = (int) ($answerRow['id'] ?? 0);
                        if ($questionId <= 0 || $answerId <= 0) {
                            continue;
                        }

                        if (!isset($validAnswerMap[$questionId])) {
                            $validAnswerMap[$questionId] = [];
                        }

                        $validAnswerMap[$questionId][$answerId] = true;

                        if (!isset($answerDetailsByQuestion[$questionId])) {
                            $answerDetailsByQuestion[$questionId] = [];
                        }

                        $answerDetailsByQuestion[$questionId][$answerId] = [
                            'code' => ($answerRow['answer_code'] ?? null) !== null ? (string) $answerRow['answer_code'] : null,
                            'text' => trim((string) ($answerRow['option_text'] ?? '')),
                        ];
                    }
                }

                foreach ($questionDetailsForStorage as $questionId => &$questionDetail) {
                    $questionDetail['requires_answer'] = (bool) ($questionsRequiringAnswers[$questionId] ?? false);
                    $questionDetail['answers'] = $answerDetailsByQuestion[$questionId] ?? [];
                }
                unset($questionDetail);

                $missingAnswers = [];
                $invalidAnswerDetected = false;
                $duplicateSelectionDetected = false;

                foreach ($questionsRequiringAnswers as $questionId => $isRequired) {
                    if (!$isRequired) {
                        continue;
                    }

                    $questionHasOptions = !empty($validAnswerMap[$questionId] ?? []);
                    if (!$questionHasOptions) {
                        continue;
                    }

                    if (!isset($normalizedAnswers[$questionId])) {
                        $missingAnswers[] = $questionId;
                        continue;
                    }

                    if ($isDiscSubmission) {
                        $answerPayload = $normalizedAnswers[$questionId];
                        $bestId = (int) ($answerPayload['best'] ?? 0);
                        $leastId = (int) ($answerPayload['least'] ?? 0);

                        if ($bestId <= 0 || $leastId <= 0) {
                            $missingAnswers[] = $questionId;
                            continue;
                        }

                        if ($bestId === $leastId) {
                            $duplicateSelectionDetected = true;
                            $invalidAnswerDetected = true;
                            break;
                        }

                        if (!isset($validAnswerMap[$questionId][$bestId]) || !isset($validAnswerMap[$questionId][$leastId])) {
                            $invalidAnswerDetected = true;
                            break;
                        }
                    } else {
                        $answerId = (int) $normalizedAnswers[$questionId];
                        if (!isset($validAnswerMap[$questionId][$answerId])) {
                            $invalidAnswerDetected = true;
                            break;
                        }
                    }
                }

                if (!$invalidAnswerDetected) {
                    foreach ($normalizedAnswers as $questionId => $answerValue) {
                        if ($isDiscSubmission) {
                            if (!is_array($answerValue)) {
                                $invalidAnswerDetected = true;
                                break;
                            }

                            $bestId = (int) ($answerValue['best'] ?? 0);
                            $leastId = (int) ($answerValue['least'] ?? 0);

                            if (($bestId > 0 && !isset($validAnswerMap[$questionId][$bestId]))
                                || ($leastId > 0 && !isset($validAnswerMap[$questionId][$leastId]))) {
                                $invalidAnswerDetected = true;
                                break;
                            }
                        } else {
                            $answerId = (int) $answerValue;
                            if (!isset($validAnswerMap[$questionId][$answerId])) {
                                $invalidAnswerDetected = true;
                                break;
                            }
                        }
                    }
                }

                $_SESSION['exam_progress'][$evaluationId]['answers'][$submittedToolId] = $normalizedAnswers;

                if (!$expiredFlag && $duplicateSelectionDetected) {
                    ResponseHelper::flashError('برای هر سوال باید گزینه‌های «بهترین» و «ضعیف‌ترین» متفاوت باشند.');
                    UtilityHelper::redirect($examCurrentUrl);
                }

                if (!$expiredFlag && $invalidAnswerDetected) {
                    ResponseHelper::flashError('گزینه انتخاب شده معتبر نیست.');
                    UtilityHelper::redirect($examCurrentUrl);
                }

                if (!$expiredFlag && !$isOptionalSubmission && !empty($missingAnswers)) {
                    $missingMessage = $isDiscSubmission
                        ? 'لطفاً برای هر سوال گزینه‌های «بهترین توصیف» و «ضعیف‌ترین توصیف» را انتخاب کنید.'
                        : 'لطفاً برای تمام سوال‌ها گزینه‌ای انتخاب کنید.';
                    ResponseHelper::flashError($missingMessage);
                    UtilityHelper::redirect($examCurrentUrl);
                }

                $storagePersisted = $this->recordExamSubmission(
                    $organizationId,
                    $evaluationId,
                    $organizationUserId,
                    $submittedToolId,
                    $submittedToolMeta,
                    $questionDetailsForStorage,
                    $normalizedAnswers,
                    $isDiscSubmission,
                    $isOptionalSubmission
                );

                if (!$storagePersisted) {
                    ResponseHelper::flashError('ذخیره‌سازی پاسخ‌ها با خطا مواجه شد. لطفاً دوباره تلاش کنید.');
                    UtilityHelper::redirect($examCurrentUrl);
                }

                if (!in_array($submittedToolId, $sessionCompletedToolIds, true)) {
                    $sessionCompletedToolIds[] = $submittedToolId;
                }

                $_SESSION['exam_progress'][$evaluationId]['completed_tool_ids'] = array_values(array_unique(array_map('intval', $sessionCompletedToolIds)));
                $sessionCompletedToolIds = $_SESSION['exam_progress'][$evaluationId]['completed_tool_ids'];

                $combinedCompletedAfter = $combinedCompletedBefore;
                $combinedCompletedAfter[$submittedToolId] = true;

                $nextToolId = 0;
                foreach ($examTools as $examTool) {
                    $toolId = (int) ($examTool['tool_id'] ?? 0);
                    if ($toolId <= 0) {
                        continue;
                    }

                    if (!isset($combinedCompletedAfter[$toolId])) {
                        $nextToolId = $toolId;
                        break;
                    }
                }

                // clear timer state for this tool
                if (isset($_SESSION['exam_progress'][$evaluationId]['start_time'][$submittedToolId])) {
                    unset($_SESSION['exam_progress'][$evaluationId]['start_time'][$submittedToolId]);
                }

                if ($nextToolId > 0) {
                    ResponseHelper::flashSuccess('آزمون جاری با موفقیت پایان یافت. اکنون آزمون بعدی آغاز می‌شود.');
                    UtilityHelper::redirect(UtilityHelper::baseUrl('tests/exams?evaluation_id=' . urlencode((string) $evaluationId) . '&tool_id=' . urlencode((string) $nextToolId)));
                }

                unset($_SESSION['exam_progress'][$evaluationId]);

                ResponseHelper::flashSuccess('تمام آزمون‌های این ارزیابی پایان یافت.');
                UtilityHelper::redirect($examBaseUrl);
            }

            ResponseHelper::flashWarning('درخواست ارسال شده ناشناخته بود.');
            UtilityHelper::redirect($examCurrentUrl);
        }

        $completedLookup = [];
        foreach ($participationMap as $toolId => $info) {
            $toolId = (int) $toolId;
            if ($toolId <= 0) {
                continue;
            }

            $hasScores = (int) ($info['total_scores'] ?? 0) > 0;
            $hasCompletionFlag = !empty($info['is_completed']);
            if ($hasScores || $hasCompletionFlag) {
                $completedLookup[$toolId] = true;
            }
        }

        foreach ($sessionCompletedToolIds as $completedToolId) {
            $toolId = (int) $completedToolId;
            if ($toolId > 0) {
                $completedLookup[$toolId] = true;
            }
        }

        $firstIncompleteToolId = 0;
        foreach ($examTools as $examTool) {
            $toolId = (int) ($examTool['tool_id'] ?? 0);
            if ($toolId <= 0) {
                continue;
            }

            if (!isset($completedLookup[$toolId]) && $firstIncompleteToolId === 0) {
                $firstIncompleteToolId = $toolId;
            }
        }

        $allExamsCompleted = ($firstIncompleteToolId === 0);
        $_SESSION['exam_progress'][$evaluationId]['all_exams_completed'] = $allExamsCompleted;

        if ($allExamsCompleted) {
            ResponseHelper::flashSuccess('تمام آزمون‌های این ارزیابی تکمیل شده است.');
            UtilityHelper::redirect($calendarUrl);
        }

        $defaultToolId = $firstIncompleteToolId > 0 ? $firstIncompleteToolId : $toolIds[0];

        $currentToolId = $allExamsCompleted ? 0 : $defaultToolId;

        $firstIncompleteUrl = $firstIncompleteToolId > 0
            ? UtilityHelper::baseUrl('tests/exams?evaluation_id=' . urlencode((string) $evaluationId) . '&tool_id=' . urlencode((string) $firstIncompleteToolId))
            : $examBaseUrl;

        if ($requestedToolId > 0 && in_array($requestedToolId, $toolIds, true)) {
            if (isset($completedLookup[$requestedToolId])) {
                ResponseHelper::flashError('این آزمون قبلاً تکمیل شده است و امکان بازگشایی مجدد آن وجود ندارد.');
                UtilityHelper::redirect($firstIncompleteUrl);
            }

            if (!$allExamsCompleted && $requestedToolId === $firstIncompleteToolId) {
                $currentToolId = $requestedToolId;
            } elseif (!$allExamsCompleted) {
                ResponseHelper::flashError('ابتدا آزمون‌های قبلی را تکمیل کنید.');
                UtilityHelper::redirect($firstIncompleteUrl);
            }
        }

        $selectedAnswers = $currentToolId > 0
            ? ($_SESSION['exam_progress'][$evaluationId]['answers'][$currentToolId] ?? [])
            : [];
        if (!is_array($selectedAnswers)) {
            $selectedAnswers = [];
        }

        $currentQuestionIndex = $currentToolId > 0
            ? (int) ($_SESSION['exam_progress'][$evaluationId]['current_question_index'][$currentToolId] ?? 0)
            : 0;
        if ($currentQuestionIndex < 0) {
            $currentQuestionIndex = 0;
        }

        $examSteps = [];
        $currentStepIndex = 0;
        foreach ($examTools as $index => $examTool) {
            $toolId = (int) ($examTool['tool_id'] ?? 0);
            $status = 'upcoming';

            if (isset($completedLookup[$toolId])) {
                $status = 'complete';
            } elseif (!$allExamsCompleted && $toolId === $firstIncompleteToolId) {
                $status = 'current';
            }

            $isCurrentView = ($toolId === $currentToolId);
            if ($isCurrentView) {
                $currentStepIndex = $index;
            }

            $canAccessStep = (!$allExamsCompleted && $isCurrentView);
            $stepLink = $canAccessStep
                ? UtilityHelper::baseUrl('tests/exams?evaluation_id=' . urlencode((string) $evaluationId) . '&tool_id=' . urlencode((string) $toolId))
                : null;

            $examSteps[] = [
                'tool_id' => $toolId,
                'code' => $examTool['code'] ?? '',
                'name' => $examTool['name'] !== '' ? $examTool['name'] : 'آزمون ' . UtilityHelper::englishToPersian((string) ($index + 1)),
                'description' => $examTool['description'] ?? '',
                'question_type' => $examTool['question_type'] ?? '',
                'is_disc' => !empty($examTool['is_disc']),
                'is_optional' => !empty($examTool['is_optional']),
                'status' => $status,
                'order' => $index + 1,
                'link' => $stepLink,
                'is_clickable' => $canAccessStep,
                'is_current_view' => $isCurrentView,
                'completed_text' => 'آزمون ' . UtilityHelper::englishToPersian((string) ($index + 1)) . ' تمام شد',
            ];
        }

        $totalExams = count($examSteps);
        $completedCount = 0;
        foreach ($examSteps as $step) {
            if (($step['status'] ?? '') === 'complete') {
                $completedCount++;
            }
        }

        if ($allExamsCompleted) {
            $progressHeadline = 'تمام آزمون‌ها تکمیل شده است';
            $progressSummary = 'تبریک! تمامی آزمون‌های این ارزیابی را به پایان رسانده‌اید.';
        } else {
            $currentExamPosition = $currentStepIndex + 1;
            $progressHeadline = 'آزمون ' . UtilityHelper::englishToPersian((string) $currentExamPosition) . ' از ' . UtilityHelper::englishToPersian((string) $totalExams);

            if ($currentStepIndex > 0 && ($examSteps[$currentStepIndex - 1]['status'] ?? '') === 'complete') {
                $progressSummary = $examSteps[$currentStepIndex - 1]['completed_text'] ?? '';
            } else {
                $progressSummary = 'آماده شروع آزمون';
            }
        }

        // Use the full tool record (includes duration_minutes) for the current tool
        $currentTool = $examToolsById[$currentToolId] ?? null;

    $currentToolIsDisc = ($currentTool !== null && !empty($currentTool['is_disc']));
        $currentToolIsOptional = ($currentTool !== null && !empty($currentTool['is_optional']));

        if ($currentToolIsDisc) {
            if (!is_array($selectedAnswers)) {
                $selectedAnswers = [];
            }

            foreach ($selectedAnswers as $questionId => $answerValue) {
                if (!is_array($answerValue)) {
                    $selectedAnswers[$questionId] = [
                        'best' => (int) $answerValue,
                        'least' => 0,
                    ];
                    continue;
                }

                $selectedAnswers[$questionId] = [
                    'best' => isset($answerValue['best']) ? (int) $answerValue['best'] : 0,
                    'least' => isset($answerValue['least']) ? (int) $answerValue['least'] : 0,
                ];
            }
        }

        $currentToolIsCompleted = ($currentToolId > 0 && isset($completedLookup[$currentToolId]));
        $allowFinish = !$allExamsCompleted && !$currentToolIsCompleted;
        $questionsLocked = ($currentToolId <= 0) ? true : !$currentToolIsCompleted;
        $examIntroText = '';
        if ($currentTool) {
            $examIntroText = trim((string) ($currentTool['description'] ?? ''));
        }
        if ($examIntroText === '') {
            $examIntroText = 'پیش از شروع، لطفاً دستورالعمل آزمون را با دقت مطالعه کنید.';
        }

        // Compute remaining duration from persisted start time (prevents reset on refresh)
        $examDurationSeconds = 0;
        if ($currentTool) {
            $minutes = isset($currentTool['duration_minutes']) ? (int) $currentTool['duration_minutes'] : 0;
            $totalSeconds = $minutes > 0 ? $minutes * 60 : 0;
            if ($totalSeconds > 0) {
                $startTs = (int) ($_SESSION['exam_progress'][$evaluationId]['start_time'][$currentToolId] ?? 0);
                if ($startTs > 0) {
                    $elapsed = max(0, time() - $startTs);
                    $examDurationSeconds = max(0, $totalSeconds - $elapsed);
                } else {
                    $examDurationSeconds = $totalSeconds;
                }
            }
        }

        $questions = [];
        if ($currentTool) {
            try {
                $questionRows = DatabaseHelper::fetchAll(
                    'SELECT id, title, question_text, description, image_path, is_description_only
                     FROM organization_evaluation_tool_questions
                     WHERE organization_id = :organization_id AND evaluation_tool_id = :tool_id
                     ORDER BY display_order ASC, id ASC',
                    [
                        'organization_id' => $organizationId,
                        'tool_id' => $currentToolId,
                    ]
                );
            } catch (Exception $exception) {
                $questionRows = [];
            }

            $questions = [];
            if (!empty($questionRows)) {
                $questionIds = array_column($questionRows, 'id');
                $answersByQuestion = [];

                if (!empty($questionIds)) {
                    $answerPlaceholders = [];
                    $answerParams = [
                        'organization_id' => $organizationId,
                        'tool_id' => $currentToolId,
                    ];

                    foreach ($questionIds as $index => $questionId) {
                        $placeholder = ':question_' . $index;
                        $answerPlaceholders[] = $placeholder;
                        $answerParams['question_' . $index] = $questionId;
                    }

                                        $answerSql = sprintf(
                                                'SELECT id, question_id, answer_code, option_text, numeric_score, character_score
                         FROM organization_evaluation_tool_answers
                         WHERE organization_id = :organization_id
                           AND evaluation_tool_id = :tool_id
                           AND question_id IN (%s)
                         ORDER BY question_id ASC, display_order ASC, id ASC',
                        implode(', ', $answerPlaceholders)
                    );

                    try {
                        $answerRows = DatabaseHelper::fetchAll($answerSql, $answerParams);
                        foreach ($answerRows as $answerRow) {
                            $questionId = (int) ($answerRow['question_id'] ?? 0);
                            if ($questionId <= 0) {
                                continue;
                            }

                            if (!isset($answersByQuestion[$questionId])) {
                                $answersByQuestion[$questionId] = [];
                            }

                            $answersByQuestion[$questionId][] = [
                                'id' => (int) ($answerRow['id'] ?? 0),
                                'code' => $answerRow['answer_code'] ?? null,
                                'text' => trim((string) ($answerRow['option_text'] ?? '')),
                                'numeric_score' => $answerRow['numeric_score'] ?? null,
                                'character_score' => $answerRow['character_score'] ?? null,
                            ];
                        }
                    } catch (Exception $exception) {
                        $answersByQuestion = [];
                    }
                }

                foreach ($questionRows as $index => $questionRow) {
                    $questionId = (int) ($questionRow['id'] ?? 0);
                    $questions[] = [
                        'id' => $questionId,
                        'title' => trim((string) ($questionRow['title'] ?? 'سوال ' . UtilityHelper::englishToPersian((string) ($index + 1)))) ,
                        'text' => trim((string) ($questionRow['question_text'] ?? '')),
                        'description' => trim((string) ($questionRow['description'] ?? '')),
                        'image_path' => $questionRow['image_path'] ?? null,
                        'is_description_only' => (int) ($questionRow['is_description_only'] ?? 0) === 1,
                        'answers' => $answersByQuestion[$questionId] ?? [],
                        'display_index' => $index + 1,
                    ];
                }
            }
        }

        $totalQuestions = count($questions);
        if ($totalQuestions > 0) {
            if ($currentQuestionIndex >= $totalQuestions) {
                $currentQuestionIndex = $totalQuestions - 1;
            }
        } else {
            $currentQuestionIndex = 0;
        }

        if ($currentToolId > 0) {
            $_SESSION['exam_progress'][$evaluationId]['current_question_index'][$currentToolId] = $currentQuestionIndex;
        }
        $activeQuestion = $totalQuestions > 0 ? $questions[$currentQuestionIndex] : null;

        $evaluationTitle = trim((string) ($evaluationRow['title'] ?? ''));

        include __DIR__ . '/../Views/home/tests/exams.php';
    }

    private static function ensureExamStorageTables(): void
    {
        if (self::$examStorageTablesEnsured) {
            return;
        }

        try {
            $pdo = DatabaseHelper::getConnection();

            $participationTableExists = $pdo->query("SHOW TABLES LIKE 'organization_evaluation_exam_participations'")->fetch();
            if (!$participationTableExists) {
                $pdo->exec(
                    "CREATE TABLE IF NOT EXISTS `organization_evaluation_exam_participations` (
                        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        `organization_id` BIGINT UNSIGNED NOT NULL,
                        `evaluation_id` BIGINT UNSIGNED NOT NULL,
                        `tool_id` BIGINT UNSIGNED NOT NULL,
                        `evaluatee_id` BIGINT UNSIGNED NOT NULL,
                        `tool_code` VARCHAR(100) NULL,
                        `tool_name` VARCHAR(255) NULL,
                        `question_type` VARCHAR(100) NULL,
                        `is_disc` TINYINT(1) NOT NULL DEFAULT 0,
                        `is_optional` TINYINT(1) NOT NULL DEFAULT 0,
                        `total_questions` INT UNSIGNED NOT NULL DEFAULT 0,
                        `answered_questions` INT UNSIGNED NOT NULL DEFAULT 0,
                        `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
                        `completed_at` DATETIME NULL,
                        `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
                        `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        UNIQUE KEY `uniq_exam_participation` (`organization_id`, `evaluation_id`, `tool_id`, `evaluatee_id`),
                        KEY `idx_exam_participations_lookup` (`organization_id`, `evaluation_id`, `tool_id`),
                        KEY `idx_exam_participations_evaluatee` (`organization_id`, `evaluatee_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                );
            } else {
                $participationColumns = $pdo->query("SHOW COLUMNS FROM `organization_evaluation_exam_participations`")->fetchAll(PDO::FETCH_ASSOC);
                $participationColumnNames = array_column($participationColumns, 'Field');

                $participationAlterStatements = [
                    'tool_code' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `tool_code` VARCHAR(100) NULL AFTER `evaluatee_id`",
                    'tool_name' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `tool_name` VARCHAR(255) NULL AFTER `tool_code`",
                    'question_type' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `question_type` VARCHAR(100) NULL AFTER `tool_name`",
                    'is_disc' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `is_disc` TINYINT(1) NOT NULL DEFAULT 0 AFTER `question_type`",
                    'is_optional' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `is_optional` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_disc`",
                    'total_questions' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `total_questions` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `is_optional`",
                    'answered_questions' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `answered_questions` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `total_questions`",
                    'is_completed' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `is_completed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `answered_questions`",
                    'completed_at' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `completed_at` DATETIME NULL AFTER `is_completed`",
                    'created_at' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `completed_at`",
                    'updated_at' => "ALTER TABLE `organization_evaluation_exam_participations` ADD COLUMN `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
                ];

                foreach ($participationAlterStatements as $column => $statement) {
                    if (!in_array($column, $participationColumnNames, true)) {
                        try {
                            $pdo->exec($statement);
                        } catch (Exception $exception) {
                            // ignore if alteration fails (e.g., concurrent addition)
                        }
                    }
                }

                $indexes = $pdo->query("SHOW INDEX FROM `organization_evaluation_exam_participations`")->fetchAll(PDO::FETCH_ASSOC);
                $indexNames = array_column($indexes, 'Key_name');

                if (!in_array('uniq_exam_participation', $indexNames, true)) {
                    try {
                        $pdo->exec("ALTER TABLE `organization_evaluation_exam_participations` ADD UNIQUE KEY `uniq_exam_participation` (`organization_id`, `evaluation_id`, `tool_id`, `evaluatee_id`)");
                    } catch (Exception $exception) {
                        // ignore
                    }
                }

                if (!in_array('idx_exam_participations_lookup', $indexNames, true)) {
                    try {
                        $pdo->exec("ALTER TABLE `organization_evaluation_exam_participations` ADD KEY `idx_exam_participations_lookup` (`organization_id`, `evaluation_id`, `tool_id`)");
                    } catch (Exception $exception) {
                        // ignore
                    }
                }

                if (!in_array('idx_exam_participations_evaluatee', $indexNames, true)) {
                    try {
                        $pdo->exec("ALTER TABLE `organization_evaluation_exam_participations` ADD KEY `idx_exam_participations_evaluatee` (`organization_id`, `evaluatee_id`)");
                    } catch (Exception $exception) {
                        // ignore
                    }
                }
            }

            $answersTableExists = $pdo->query("SHOW TABLES LIKE 'organization_evaluation_exam_answers'")->fetch();
            if (!$answersTableExists) {
                $pdo->exec(
                    "CREATE TABLE IF NOT EXISTS `organization_evaluation_exam_answers` (
                        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        `participation_id` BIGINT UNSIGNED NOT NULL,
                        `organization_id` BIGINT UNSIGNED NOT NULL,
                        `evaluation_id` BIGINT UNSIGNED NOT NULL,
                        `tool_id` BIGINT UNSIGNED NOT NULL,
                        `evaluatee_id` BIGINT UNSIGNED NOT NULL,
                        `question_id` BIGINT UNSIGNED NOT NULL,
                        `question_title` VARCHAR(255) NULL,
                        `question_text` TEXT NULL,
                        `question_description` TEXT NULL,
                        `is_description_only` TINYINT(1) NOT NULL DEFAULT 0,
                        `requires_answer` TINYINT(1) NOT NULL DEFAULT 0,
                        `answer_id` BIGINT UNSIGNED NULL,
                        `answer_code` VARCHAR(100) NULL,
                        `answer_text` TEXT NULL,
                        `disc_best_answer_id` BIGINT UNSIGNED NULL,
                        `disc_best_answer_code` VARCHAR(100) NULL,
                        `disc_best_answer_text` TEXT NULL,
                        `disc_least_answer_id` BIGINT UNSIGNED NULL,
                        `disc_least_answer_code` VARCHAR(100) NULL,
                        `disc_least_answer_text` TEXT NULL,
                        `answer_payload` LONGTEXT NULL,
                        `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
                        `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        KEY `idx_exam_answers_participation` (`participation_id`),
                        KEY `idx_exam_answers_question` (`organization_id`, `evaluation_id`, `tool_id`, `question_id`),
                        CONSTRAINT `fk_exam_answers_participation` FOREIGN KEY (`participation_id`) REFERENCES `organization_evaluation_exam_participations` (`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                );
            } else {
                $answerColumns = $pdo->query("SHOW COLUMNS FROM `organization_evaluation_exam_answers`")->fetchAll(PDO::FETCH_ASSOC);
                $answerColumnNames = array_column($answerColumns, 'Field');

                $answerAlterStatements = [
                    'question_title' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `question_title` VARCHAR(255) NULL AFTER `question_id`",
                    'question_text' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `question_text` TEXT NULL AFTER `question_title`",
                    'question_description' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `question_description` TEXT NULL AFTER `question_text`",
                    'is_description_only' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `is_description_only` TINYINT(1) NOT NULL DEFAULT 0 AFTER `question_description`",
                    'requires_answer' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `requires_answer` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_description_only`",
                    'answer_id' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `answer_id` BIGINT UNSIGNED NULL AFTER `requires_answer`",
                    'answer_code' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `answer_code` VARCHAR(100) NULL AFTER `answer_id`",
                    'answer_text' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `answer_text` TEXT NULL AFTER `answer_code`",
                    'disc_best_answer_id' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `disc_best_answer_id` BIGINT UNSIGNED NULL AFTER `answer_text`",
                    'disc_best_answer_code' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `disc_best_answer_code` VARCHAR(100) NULL AFTER `disc_best_answer_id`",
                    'disc_best_answer_text' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `disc_best_answer_text` TEXT NULL AFTER `disc_best_answer_code`",
                    'disc_least_answer_id' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `disc_least_answer_id` BIGINT UNSIGNED NULL AFTER `disc_best_answer_text`",
                    'disc_least_answer_code' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `disc_least_answer_code` VARCHAR(100) NULL AFTER `disc_least_answer_id`",
                    'disc_least_answer_text' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `disc_least_answer_text` TEXT NULL AFTER `disc_least_answer_code`",
                    'answer_payload' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `answer_payload` LONGTEXT NULL AFTER `disc_least_answer_text`",
                    'created_at' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `answer_payload`",
                    'updated_at' => "ALTER TABLE `organization_evaluation_exam_answers` ADD COLUMN `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
                ];

                foreach ($answerAlterStatements as $column => $statement) {
                    if (!in_array($column, $answerColumnNames, true)) {
                        try {
                            $pdo->exec($statement);
                        } catch (Exception $exception) {
                            // ignore
                        }
                    }
                }

                $answerIndexes = $pdo->query("SHOW INDEX FROM `organization_evaluation_exam_answers`")->fetchAll(PDO::FETCH_ASSOC);
                $answerIndexNames = array_column($answerIndexes, 'Key_name');

                if (!in_array('idx_exam_answers_participation', $answerIndexNames, true)) {
                    try {
                        $pdo->exec("ALTER TABLE `organization_evaluation_exam_answers` ADD KEY `idx_exam_answers_participation` (`participation_id`)");
                    } catch (Exception $exception) {
                        // ignore
                    }
                }

                if (!in_array('idx_exam_answers_question', $answerIndexNames, true)) {
                    try {
                        $pdo->exec("ALTER TABLE `organization_evaluation_exam_answers` ADD KEY `idx_exam_answers_question` (`organization_id`, `evaluation_id`, `tool_id`, `question_id`)");
                    } catch (Exception $exception) {
                        // ignore
                    }
                }

                try {
                    $pdo->exec("ALTER TABLE `organization_evaluation_exam_answers` ADD CONSTRAINT `fk_exam_answers_participation` FOREIGN KEY (`participation_id`) REFERENCES `organization_evaluation_exam_participations` (`id`) ON DELETE CASCADE");
                } catch (Exception $exception) {
                    // ignore in case FK already exists or cannot be added
                }
            }

            self::$examStorageTablesEnsured = true;
        } catch (Exception $exception) {
            LogHelper::error('ensure_exam_storage_tables_failed', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private static function ensureCourseTables(): void
    {
        if (self::$courseTablesEnsured) {
            return;
        }

        try {
            DatabaseHelper::query(
                'CREATE TABLE IF NOT EXISTS organization_courses (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    organization_id BIGINT UNSIGNED NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT NULL,
                    category VARCHAR(100) NULL,
                    instructor_name VARCHAR(255) NULL,
                    price DECIMAL(10,2) DEFAULT 0.00,
                    duration_hours INT DEFAULT 0,
                    cover_image VARCHAR(255) NULL,
                    status ENUM(\'draft\', \'published\', \'archived\', \'presale\') DEFAULT \'draft\',
                    sort_order INT DEFAULT 0,
                    published_at DATE NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_organization (organization_id),
                    INDEX idx_status (status),
                    INDEX idx_sort (sort_order),
                    INDEX idx_published_at (published_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
            );
        } catch (Exception $exception) {
            if (class_exists('LogHelper')) {
                LogHelper::error('ensure_courses_table_failed', [
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        try {
            DatabaseHelper::query(
                'CREATE TABLE IF NOT EXISTS organization_course_lessons (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    course_id BIGINT UNSIGNED NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT NULL,
                    short_description TEXT NULL,
                    learning_objectives TEXT NULL,
                    resources TEXT NULL,
                    text_content LONGTEXT NULL,
                    content_type ENUM(\'video\', \'pdf\', \'ppt\', \'link\', \'text\') DEFAULT \'video\',
                    content_url VARCHAR(500) NULL,
                    content_file VARCHAR(255) NULL,
                    thumbnail_path VARCHAR(255) NULL,
                    duration_minutes INT DEFAULT 0,
                    sort_order INT DEFAULT 0,
                    is_free TINYINT(1) DEFAULT 0,
                    is_published TINYINT(1) DEFAULT 1,
                    available_at DATETIME NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_course (course_id),
                    INDEX idx_sort (sort_order)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
            );

            $columns = DatabaseHelper::fetchAll('SHOW COLUMNS FROM organization_course_lessons');
            $columnNames = array_map(static fn($column) => $column['Field'] ?? '', $columns);

            self::ensureLessonColumn($columnNames, 'short_description', "ALTER TABLE organization_course_lessons ADD COLUMN short_description TEXT NULL AFTER description");
            self::ensureLessonColumn($columnNames, 'learning_objectives', "ALTER TABLE organization_course_lessons ADD COLUMN learning_objectives TEXT NULL AFTER short_description");
            self::ensureLessonColumn($columnNames, 'resources', "ALTER TABLE organization_course_lessons ADD COLUMN resources TEXT NULL AFTER learning_objectives");
            self::ensureLessonColumn($columnNames, 'text_content', "ALTER TABLE organization_course_lessons ADD COLUMN text_content LONGTEXT NULL AFTER resources");
            self::ensureLessonColumn($columnNames, 'thumbnail_path', "ALTER TABLE organization_course_lessons ADD COLUMN thumbnail_path VARCHAR(255) NULL AFTER content_file");
            self::ensureLessonColumn($columnNames, 'is_published', "ALTER TABLE organization_course_lessons ADD COLUMN is_published TINYINT(1) DEFAULT 1 AFTER is_free");
            self::ensureLessonColumn($columnNames, 'available_at', "ALTER TABLE organization_course_lessons ADD COLUMN available_at DATETIME NULL AFTER is_published");
        } catch (Exception $exception) {
            if (class_exists('LogHelper')) {
                LogHelper::error('ensure_course_lessons_table_failed', [
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        try {
            DatabaseHelper::query(
                'CREATE TABLE IF NOT EXISTS organization_course_enrollments (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    organization_id BIGINT UNSIGNED NOT NULL,
                    course_id BIGINT UNSIGNED NOT NULL,
                    user_id BIGINT UNSIGNED NOT NULL,
                    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    completed_at TIMESTAMP NULL,
                    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
                    UNIQUE KEY unique_enrollment (organization_id, course_id, user_id),
                    INDEX idx_organization (organization_id),
                    INDEX idx_course (course_id),
                    INDEX idx_user (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
            );
        } catch (Exception $exception) {
            if (class_exists('LogHelper')) {
                LogHelper::error('ensure_course_enrollments_table_failed', [
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        try {
            DatabaseHelper::query(
                'CREATE TABLE IF NOT EXISTS organization_course_progress (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    enrollment_id BIGINT UNSIGNED NOT NULL,
                    lesson_id BIGINT UNSIGNED NOT NULL,
                    user_id BIGINT UNSIGNED NOT NULL,
                    is_completed TINYINT(1) DEFAULT 0,
                    completed_at TIMESTAMP NULL,
                    watch_duration_seconds INT DEFAULT 0,
                    last_watched_at TIMESTAMP NULL,
                    UNIQUE KEY unique_progress (enrollment_id, lesson_id),
                    INDEX idx_user (user_id),
                    INDEX idx_lesson (lesson_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
            );
        } catch (Exception $exception) {
            if (class_exists('LogHelper')) {
                LogHelper::error('ensure_course_progress_table_failed', [
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        self::$courseTablesEnsured = true;
    }

    private static function ensureLessonColumn(array $columnNames, string $column, string $alterSql): void
    {
        if (in_array($column, $columnNames, true)) {
            return;
        }

        try {
            DatabaseHelper::query($alterSql);
        } catch (Exception $exception) {
            if (class_exists('LogHelper')) {
                LogHelper::warning('ensure_lesson_column_failed', [
                    'column' => $column,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    private static function parseDateTime(?string $value): ?DateTime
    {
        $raw = trim((string)($value ?? ''));
        if ($raw === '') {
            return null;
        }

        try {
            return new DateTime($raw, new DateTimeZone('Asia/Tehran'));
        } catch (Exception $exception) {
            try {
                $date = new DateTime($raw);
                $date->setTimezone(new DateTimeZone('Asia/Tehran'));
                return $date;
            } catch (Exception $innerException) {
                return null;
            }
        }
    }

    private static function formatDateTimeForDisplay(?string $value): ?string
    {
        $date = self::parseDateTime($value);
        if (!$date instanceof DateTime) {
            return null;
        }

        return UtilityHelper::englishToPersian($date->format('Y/m/d H:i'));
    }

    private static function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remaining = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $remaining);
        }

        return sprintf('%02d:%02d', $minutes, $remaining);
    }

    private function recordExamSubmission(
        int $organizationId,
        int $evaluationId,
        int $evaluateeId,
        int $toolId,
        array $toolMeta,
        array $questionDetails,
        array $normalizedAnswers,
        bool $isDiscSubmission,
        bool $isOptionalSubmission
    ): bool {
        if ($organizationId <= 0 || $evaluationId <= 0 || $evaluateeId <= 0 || $toolId <= 0) {
            return false;
        }

        self::ensureExamStorageTables();

        $truncate = static function (?string $value, int $limit): ?string {
            if ($value === null) {
                return null;
            }

            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            if (mb_strlen($trimmed, 'UTF-8') > $limit) {
                return mb_substr($trimmed, 0, $limit, 'UTF-8');
            }

            return $trimmed;
        };

        $totalQuestions = count($questionDetails);
        $answeredQuestions = 0;

        foreach ($questionDetails as $questionId => $questionDetail) {
            if (!isset($normalizedAnswers[$questionId])) {
                continue;
            }

            if ($isDiscSubmission) {
                $answerPayload = $normalizedAnswers[$questionId];
                $bestId = (int) ($answerPayload['best'] ?? 0);
                $leastId = (int) ($answerPayload['least'] ?? 0);
                if ($bestId > 0 && $leastId > 0) {
                    $answeredQuestions++;
                }
            } else {
                $answerId = (int) $normalizedAnswers[$questionId];
                if ($answerId > 0) {
                    $answeredQuestions++;
                }
            }
        }

        try {
            $pdo = DatabaseHelper::getConnection();
            $startedTransaction = false;

            if (!$pdo->inTransaction()) {
                $pdo->beginTransaction();
                $startedTransaction = true;
            }

            $now = (new DateTime('now', new DateTimeZone('Asia/Tehran')))->format('Y-m-d H:i:s');

            $existingParticipation = DatabaseHelper::fetchOne(
                'SELECT id FROM organization_evaluation_exam_participations
                 WHERE organization_id = :organization_id
                   AND evaluation_id = :evaluation_id
                   AND tool_id = :tool_id
                   AND evaluatee_id = :evaluatee_id
                 LIMIT 1',
                [
                    'organization_id' => $organizationId,
                    'evaluation_id' => $evaluationId,
                    'tool_id' => $toolId,
                    'evaluatee_id' => $evaluateeId,
                ]
            );

            $participationData = [
                'organization_id' => $organizationId,
                'evaluation_id' => $evaluationId,
                'tool_id' => $toolId,
                'evaluatee_id' => $evaluateeId,
                'tool_code' => $truncate((string) ($toolMeta['code'] ?? ''), 100),
                'tool_name' => $truncate((string) ($toolMeta['name'] ?? ''), 255),
                'question_type' => $truncate((string) ($toolMeta['question_type'] ?? ''), 100),
                'is_disc' => $isDiscSubmission ? 1 : 0,
                'is_optional' => $isOptionalSubmission ? 1 : 0,
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'is_completed' => 1,
                'completed_at' => $now,
            ];

            if ($participationData['tool_code'] === '') {
                $participationData['tool_code'] = null;
            }
            if ($participationData['tool_name'] === '') {
                $participationData['tool_name'] = null;
            }
            if ($participationData['question_type'] === '') {
                $participationData['question_type'] = null;
            }

            if ($existingParticipation && isset($existingParticipation['id'])) {
                $participationId = (int) $existingParticipation['id'];
                DatabaseHelper::update(
                    'organization_evaluation_exam_participations',
                    $participationData,
                    'id = :id',
                    ['id' => $participationId]
                );

                DatabaseHelper::delete(
                    'organization_evaluation_exam_answers',
                    'participation_id = :id',
                    ['id' => $participationId]
                );
            } else {
                $participationId = (int) DatabaseHelper::insert(
                    'organization_evaluation_exam_participations',
                    $participationData
                );
            }

            foreach ($questionDetails as $questionId => $questionDetail) {
                $questionTitle = $truncate((string) ($questionDetail['title'] ?? ''), 255);
                $questionText = trim((string) ($questionDetail['text'] ?? ''));
                $questionDescription = trim((string) ($questionDetail['description'] ?? ''));
                $questionRequiresAnswer = !empty($questionDetail['requires_answer']);
                $questionIsDescriptionOnly = !empty($questionDetail['is_description_only']);
                $questionAnswers = is_array($questionDetail['answers'] ?? null) ? $questionDetail['answers'] : [];

                $selectedAnswer = $normalizedAnswers[$questionId] ?? null;
                $answerRecord = [
                    'participation_id' => $participationId,
                    'organization_id' => $organizationId,
                    'evaluation_id' => $evaluationId,
                    'tool_id' => $toolId,
                    'evaluatee_id' => $evaluateeId,
                    'question_id' => (int) $questionId,
                    'question_title' => $questionTitle,
                    'question_text' => $questionText !== '' ? $questionText : null,
                    'question_description' => $questionDescription !== '' ? $questionDescription : null,
                    'is_description_only' => $questionIsDescriptionOnly ? 1 : 0,
                    'requires_answer' => $questionRequiresAnswer ? 1 : 0,
                    'answer_id' => null,
                    'answer_code' => null,
                    'answer_text' => null,
                    'disc_best_answer_id' => null,
                    'disc_best_answer_code' => null,
                    'disc_best_answer_text' => null,
                    'disc_least_answer_id' => null,
                    'disc_least_answer_code' => null,
                    'disc_least_answer_text' => null,
                    'answer_payload' => null,
                ];

                if ($selectedAnswer !== null) {
                    if ($isDiscSubmission) {
                        $bestId = (int) ($selectedAnswer['best'] ?? 0);
                        $leastId = (int) ($selectedAnswer['least'] ?? 0);

                        $bestMeta = ($bestId > 0 && isset($questionAnswers[$bestId])) ? $questionAnswers[$bestId] : null;
                        $leastMeta = ($leastId > 0 && isset($questionAnswers[$leastId])) ? $questionAnswers[$leastId] : null;

                        if ($bestId > 0) {
                            $answerRecord['disc_best_answer_id'] = $bestId;
                            $answerRecord['disc_best_answer_code'] = $bestMeta && isset($bestMeta['code']) ? $truncate((string) $bestMeta['code'], 100) : null;
                            $answerRecord['disc_best_answer_text'] = $bestMeta && isset($bestMeta['text']) ? trim((string) $bestMeta['text']) : null;
                        }

                        if ($leastId > 0) {
                            $answerRecord['disc_least_answer_id'] = $leastId;
                            $answerRecord['disc_least_answer_code'] = $leastMeta && isset($leastMeta['code']) ? $truncate((string) $leastMeta['code'], 100) : null;
                            $answerRecord['disc_least_answer_text'] = $leastMeta && isset($leastMeta['text']) ? trim((string) $leastMeta['text']) : null;
                        }

                        $answerRecord['answer_payload'] = json_encode(
                            [
                                'type' => 'disc',
                                'best' => [
                                    'answer_id' => $bestId > 0 ? $bestId : null,
                                    'code' => $answerRecord['disc_best_answer_code'],
                                    'text' => $answerRecord['disc_best_answer_text'],
                                ],
                                'least' => [
                                    'answer_id' => $leastId > 0 ? $leastId : null,
                                    'code' => $answerRecord['disc_least_answer_code'],
                                    'text' => $answerRecord['disc_least_answer_text'],
                                ],
                            ],
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        );
                    } else {
                        $answerId = (int) $selectedAnswer;
                        $answerMeta = ($answerId > 0 && isset($questionAnswers[$answerId])) ? $questionAnswers[$answerId] : null;

                        if ($answerId > 0) {
                            $answerRecord['answer_id'] = $answerId;
                            $answerRecord['answer_code'] = $answerMeta && isset($answerMeta['code']) ? $truncate((string) $answerMeta['code'], 100) : null;
                            $answerRecord['answer_text'] = $answerMeta && isset($answerMeta['text']) ? trim((string) $answerMeta['text']) : null;
                        }

                        $answerRecord['answer_payload'] = json_encode(
                            [
                                'type' => 'single_choice',
                                'answer' => [
                                    'answer_id' => $answerId > 0 ? $answerId : null,
                                    'code' => $answerRecord['answer_code'],
                                    'text' => $answerRecord['answer_text'],
                                ],
                            ],
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        );
                    }
                }

                DatabaseHelper::insert('organization_evaluation_exam_answers', $answerRecord);
            }

            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->commit();
            }
            return true;
        } catch (Exception $exception) {
            try {
                $pdo = DatabaseHelper::getConnection();
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            } catch (Exception $rollbackException) {
                // ignore rollback failures
            }

            LogHelper::error('exam_submission_persist_failed', [
                'message' => $exception->getMessage(),
                'organization_id' => $organizationId,
                'evaluation_id' => $evaluationId,
                'tool_id' => $toolId,
                'evaluatee_id' => $evaluateeId,
            ]);
            return false;
        }
    }

}
