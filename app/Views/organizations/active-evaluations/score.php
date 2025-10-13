<?php
if (!class_exists('UtilityHelper')) {
	require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'امتیازدهی ارزیابی فعال';
$evaluation = $evaluationSummary ?? [];
$generalModel = $generalModelSection ?? ['label' => '', 'model' => null, 'competencies' => []];
$specificModel = $specificModelSection ?? ['label' => '', 'model' => null, 'competencies' => []];
$evaluateeTabs = $evaluateeTabs ?? [];
$selectedEvaluatee = $selectedEvaluatee ?? null;
$toolEntries = $toolEntries ?? [];
$stats = $stats ?? [];
$pageMessages = $pageMessages ?? [];
$hasMatrixVisibility = isset($hasMatrixVisibility) ? (bool) $hasMatrixVisibility : false;
$visibilityContext = $visibilityContext ?? [
	'role_label' => 'کاربر سازمان',
	'user_display' => 'کاربر سازمان',
	'message' => '',
	'can_view_all' => false,
	'is_evaluator' => false,
	'is_evaluatee' => false,
];
$scoreBaseLink = $scoreBaseLink ?? UtilityHelper::baseUrl('organizations/active-evaluations');
$backLink = $backLink ?? UtilityHelper::baseUrl('organizations/active-evaluations');

$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$inline_styles .= <<<'CSS'
	body {
		background: #f5f7fb;
	}
	.evaluation-score-card {
		border-radius: 24px;
		border: 1px solid #e4e9f2;
		background: #ffffff;
	}
	.evaluation-score-hero {
		position: relative;
		overflow: hidden;
	}
	.evaluation-score-hero::before {
		content: '';
		position: absolute;
		inset-inline-start: -160px;
		inset-block-start: -160px;
		width: 300px;
		height: 300px;
		background: radial-gradient(circle at center, rgba(79, 70, 229, 0.18), transparent 70%);
		z-index: 0;
	}
	.evaluation-score-hero > * {
		position: relative;
		z-index: 1;
	}
	.score-stat-card {
		border: 1px solid rgba(226, 232, 240, 0.6);
		border-radius: 18px;
		background: #f8fafc;
		padding: 18px 20px;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
	}
	.score-stat-card .stat-label {
		font-size: 13px;
		color: #64748b;
		margin-bottom: 6px;
	}
	.score-stat-card .stat-value {
		font-size: 22px;
		font-weight: 700;
		color: #0f172a;
	}
	.score-stat-card .stat-icon {
		width: 48px;
		height: 48px;
		border-radius: 16px;
		background: rgba(79, 70, 229, 0.12);
		color: #4f46e5;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 22px;
	}
	.evaluatee-tab {
		border-radius: 999px;
		padding: 8px 18px;
		border: 1px solid transparent;
		background: rgba(99, 102, 241, 0.06);
		color: #312e81;
		font-size: 13px;
		font-weight: 600;
		display: inline-flex;
		align-items: center;
		gap: 8px;
		text-decoration: none;
		transition: all 0.2s ease;
	}
	.evaluatee-tab:hover {
		background: rgba(99, 102, 241, 0.12);
		color: #1d1b5d;
	}
	.evaluatee-tab.active {
		background: #4f46e5;
		color: #ffffff;
		box-shadow: 0 8px 20px rgba(79, 70, 229, 0.22);
	}
	.competency-list {
		list-style: none;
		margin: 0;
		padding: 0;
		display: flex;
		flex-direction: column;
		gap: 10px;
	}
	.competency-item {
		display: flex;
		align-items: baseline;
		gap: 10px;
		background: rgba(248, 250, 252, 0.9);
		border: 1px solid rgba(226, 232, 240, 0.7);
		border-radius: 16px;
		padding: 12px 14px;
	}
	.competency-dot {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		background: #4f46e5;
		flex-shrink: 0;
		margin-top: 6px;
	}
	.competency-title {
		font-size: 14px;
		color: #0f172a;
		font-weight: 600;
	}
	.competency-meta {
		font-size: 12px;
		color: #64748b;
	}
	.tool-card {
		border-radius: 20px;
		border: 1px solid rgba(226, 232, 240, 0.7);
		padding: 20px 22px;
		background: #ffffff;
		transition: box-shadow 0.2s ease;
	}
	.tool-card:hover {
		box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
	}
	.tool-card .score-button {
		margin-top: 18px;
	}
	.pill-badge {
		border-radius: 999px;
		padding: 6px 14px;
		display: inline-flex;
		align-items: center;
		gap: 6px;
		font-size: 12px;
		font-weight: 600;
	}
	.pill-badge-evaluator {
		background: rgba(14, 116, 144, 0.12);
		color: #0e7490;
	}
	.pill-badge-competency {
		background: rgba(99, 102, 241, 0.12);
		color: #3730a3;
	}
	.pill-badge-competency.small {
		padding: 4px 12px;
		font-size: 11px;
	}
	.empty-state {
		border-radius: 18px;
		border: 1px dashed rgba(148, 163, 184, 0.4);
		padding: 32px;
		background: #fbfdff;
		text-align: center;
		color: #64748b;
	}
	@media (max-width: 768px) {
		.score-stat-card {
			flex-direction: row;
			align-items: flex-start;
		}
		.score-stat-card .stat-value {
			font-size: 20px;
		}
		.tool-card {
			padding: 18px;
		}
		.evaluatee-tab {
			width: 100%;
			justify-content: center;
		}
	}
CSS;

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user ?? null;
include __DIR__ . '/../../layouts/organization-navbar.php';

$evaluationTitle = $evaluation['title'] ?? 'بدون عنوان';
$evaluationDateDisplay = $evaluation['date_display'] ?? 'تاریخ ثبت نشده';
$generalCount = UtilityHelper::englishToPersian((string) ($stats['general_competencies'] ?? count($generalModel['competencies'] ?? [])));
$specificCount = UtilityHelper::englishToPersian((string) ($stats['specific_competencies'] ?? count($specificModel['competencies'] ?? [])));
$toolsCount = UtilityHelper::englishToPersian((string) ($stats['tools'] ?? count($toolEntries)));
$evaluateesCount = UtilityHelper::englishToPersian((string) ($stats['evaluatees'] ?? count($evaluateeTabs)));
$selectedEvaluateeLabel = $selectedEvaluatee['label'] ?? 'انتخاب نشده';

$generalModelDisplay = '';
$generalModelCode = '';
if (!empty($generalModel['model']['title'])) {
	$generalModelDisplay = $generalModel['model']['title'];
}
if ($generalModelDisplay === '' && !empty($generalModel['label'])) {
	$generalModelDisplay = $generalModel['label'];
}
if (!empty($generalModel['model']['code'])) {
	$generalModelCode = $generalModel['model']['code'];
	if ($generalModelDisplay === '') {
		$generalModelDisplay = $generalModelCode;
	}
}

$specificModelDisplay = '';
$specificModelCode = '';
if (!empty($specificModel['model']['title'])) {
	$specificModelDisplay = $specificModel['model']['title'];
}
if ($specificModelDisplay === '' && !empty($specificModel['label'])) {
	$specificModelDisplay = $specificModel['label'];
}
if (!empty($specificModel['model']['code'])) {
	$specificModelCode = $specificModel['model']['code'];
	if ($specificModelDisplay === '') {
		$specificModelDisplay = $specificModelCode;
	}
}

?>

<div class="page-content-wrapper">
	<div class="page-content">
		<div class="row g-4">
			<div class="col-12">
				<div class="card evaluation-score-card evaluation-score-hero shadow-sm">
					<div class="card-body p-24">
						<div class="d-flex flex-wrap justify-content-between align-items-start gap-20">
							<div>
								<div class="d-flex flex-wrap align-items-center gap-10 mb-12">
									<a href="<?= htmlspecialchars($backLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-secondary rounded-pill d-inline-flex align-items-center gap-6">
										<ion-icon name="arrow-back-outline"></ion-icon>
										بازگشت
									</a>
									<span class="badge bg-main-50 text-main-600 rounded-pill px-16 py-8">
										نقش: <?= htmlspecialchars($visibilityContext['role_label'] ?? 'کاربر سازمان', ENT_QUOTES, 'UTF-8'); ?>
									</span>
									<?php if (!empty($visibilityContext['user_display'])): ?>
										<span class="badge bg-secondary-50 text-secondary-600 rounded-pill px-16 py-8">
											<?= htmlspecialchars($visibilityContext['user_display'], ENT_QUOTES, 'UTF-8'); ?>
										</span>
									<?php endif; ?>
									<?php if (!empty($visibilityContext['can_view_all'])): ?>
										<span class="badge bg-success-50 text-success-700 rounded-pill px-16 py-8">دسترسی کامل</span>
									<?php endif; ?>
								</div>
								<h2 class="mb-10 text-gray-900 fw-bold">امتیازدهی: <?= htmlspecialchars($evaluationTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
								<p class="mb-0 text-gray-600">
									<?= htmlspecialchars($visibilityContext['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
								</p>
							</div>
							<div class="d-flex flex-column align-items-end gap-10 text-gray-500">
								<div class="d-flex align-items-center gap-8">
									<ion-icon name="calendar-outline" class="text-main-500"></ion-icon>
									<span>تاریخ ارزیابی: <?= htmlspecialchars($evaluationDateDisplay, ENT_QUOTES, 'UTF-8'); ?></span>
								</div>
								<div class="d-flex align-items-center gap-8">
									<ion-icon name="person-outline" class="text-main-500"></ion-icon>
									<span>ارزیابی‌شونده انتخاب‌شده: <?= htmlspecialchars($selectedEvaluateeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
								</div>
								<?php if (!empty($evaluation['schedule_title'])): ?>
									<div class="d-flex align-items-center gap-8 text-gray-500">
										<ion-icon name="calendar-number-outline" class="text-main-500"></ion-icon>
										<span>برنامه مرتبط: <?= htmlspecialchars($evaluation['schedule_title'], ENT_QUOTES, 'UTF-8'); ?></span>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="row g-3 mt-1">
							<div class="col-12 col-sm-6 col-lg-3">
								<div class="score-stat-card">
									<div>
										<div class="stat-label">تعداد ابزارهای فعال</div>
										<div class="stat-value"><?= $toolsCount; ?></div>
									</div>
									<div class="stat-icon">
										<ion-icon name="clipboard-outline"></ion-icon>
									</div>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-lg-3">
								<div class="score-stat-card">
									<div>
										<div class="stat-label">شایستگی‌های مدل عمومی</div>
										<div class="stat-value"><?= $generalCount; ?></div>
									</div>
									<div class="stat-icon">
										<ion-icon name="layers-outline"></ion-icon>
									</div>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-lg-3">
								<div class="score-stat-card">
									<div>
										<div class="stat-label">شایستگی‌های مدل اختصاصی</div>
										<div class="stat-value"><?= $specificCount; ?></div>
									</div>
									<div class="stat-icon">
										<ion-icon name="sparkles-outline"></ion-icon>
									</div>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-lg-3">
								<div class="score-stat-card">
									<div>
										<div class="stat-label">تعداد ارزیابی‌شوندگان</div>
										<div class="stat-value"><?= $evaluateesCount; ?></div>
									</div>
									<div class="stat-icon">
										<ion-icon name="people-outline"></ion-icon>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php if (!empty($pageMessages)): ?>
				<div class="col-12">
					<?php foreach ($pageMessages as $message): ?>
						<?php
							$type = $message['type'] ?? 'info';
							$text = $message['text'] ?? '';
							$alertClass = 'alert-info';
							if ($type === 'warning') {
								$alertClass = 'alert-warning';
							} elseif ($type === 'success') {
								$alertClass = 'alert-success';
							} elseif ($type === 'danger' || $type === 'error') {
								$alertClass = 'alert-danger';
							}
						?>
						<div class="alert <?= htmlspecialchars($alertClass, ENT_QUOTES, 'UTF-8'); ?> rounded-16 d-flex align-items-center gap-12" role="alert">
							<ion-icon name="information-circle-outline"></ion-icon>
							<span><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if (!empty($evaluateeTabs)): ?>
				<div class="col-12">
					<div class="card evaluation-score-card shadow-sm">
						<div class="card-body p-24">
							<h5 class="mb-16 text-gray-900 fw-semibold">انتخاب ارزیابی‌شونده</h5>
							<div class="d-flex flex-wrap gap-10">
								<?php foreach ($evaluateeTabs as $tab): ?>
									<?php $isActive = !empty($tab['selected']); ?>
									<a href="<?= htmlspecialchars($tab['link'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>" class="evaluatee-tab <?= $isActive ? 'active' : ''; ?>">
										<ion-icon name="person-circle-outline"></ion-icon>
										<?= htmlspecialchars($tab['label'] ?? 'ارزیابی‌شونده', ENT_QUOTES, 'UTF-8'); ?>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<div class="col-12">
				<div class="card evaluation-score-card shadow-sm">
					<div class="card-body p-24">
						<div class="d-flex flex-wrap justify-content-between align-items-center mb-20 gap-12">
							<h5 class="mb-0 fw-semibold text-gray-900">مدل‌های شایستگی مرتبط</h5>
							<?php if ($hasMatrixVisibility): ?>
								<span class="badge bg-main-50 text-main-600 rounded-pill px-16 py-8">ماتریس ابزار برای این ارزیابی فعال است</span>
							<?php else: ?>
								<span class="badge bg-secondary-50 text-secondary-600 rounded-pill px-16 py-8">ماتریس اختصاص ابزار ثبت نشده است</span>
							<?php endif; ?>
						</div>
						<div class="row g-4">
							<div class="col-12 col-xl-6">
								<div class="p-20 border rounded-20 h-100">
									<div class="d-flex align-items-center gap-8 mb-12">
										<ion-icon name="layers-outline" class="text-main-500"></ion-icon>
										<h6 class="mb-0 text-gray-900 fw-semibold">مدل عمومی</h6>
									</div>
									<?php if ($generalModelDisplay !== ''): ?>
										<p class="text-gray-600 <?= ($generalModelCode !== '' && $generalModelCode !== $generalModelDisplay) ? 'mb-8' : 'mb-16'; ?>">
											<?= htmlspecialchars($generalModelDisplay, ENT_QUOTES, 'UTF-8'); ?>
										</p>
										<?php if ($generalModelCode !== '' && $generalModelCode !== $generalModelDisplay): ?>
											<p class="text-gray-500 mb-16 text-sm">کد مدل: <?= htmlspecialchars($generalModelCode, ENT_QUOTES, 'UTF-8'); ?></p>
										<?php endif; ?>
									<?php else: ?>
										<p class="text-gray-500 mb-16">مدل عمومی برای این ارزیابی ثبت نشده است.</p>
									<?php endif; ?>
									<?php if (!empty($generalModel['competencies'])): ?>
										<ul class="competency-list">
											<?php foreach ($generalModel['competencies'] as $competency): ?>
												<li class="competency-item">
													<span class="competency-dot"></span>
													<div>
														<div class="competency-title">
															<?= htmlspecialchars($competency['title'] ?? 'شایستگی بدون عنوان', ENT_QUOTES, 'UTF-8'); ?>
															<?php if (!empty($competency['code'])): ?>
																<span class="text-gray-400 fw-normal">(<?= htmlspecialchars($competency['code'], ENT_QUOTES, 'UTF-8'); ?>)</span>
															<?php endif; ?>
														</div>
														<?php if (!empty($competency['dimension'])): ?>
															<div class="competency-meta">بعد: <?= htmlspecialchars($competency['dimension'], ENT_QUOTES, 'UTF-8'); ?></div>
														<?php endif; ?>
													</div>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php else: ?>
										<div class="text-gray-500">هیچ شایستگی‌ای برای مدل عمومی مرتبط ثبت نشده است.</div>
									<?php endif; ?>
								</div>
							</div>
							<div class="col-12 col-xl-6">
								<div class="p-20 border rounded-20 h-100">
									<div class="d-flex align-items-center gap-8 mb-12">
										<ion-icon name="sparkles-outline" class="text-main-500"></ion-icon>
										<h6 class="mb-0 text-gray-900 fw-semibold">مدل اختصاصی</h6>
									</div>
									<?php if ($specificModelDisplay !== ''): ?>
										<p class="text-gray-600 <?= ($specificModelCode !== '' && $specificModelCode !== $specificModelDisplay) ? 'mb-8' : 'mb-16'; ?>">
											<?= htmlspecialchars($specificModelDisplay, ENT_QUOTES, 'UTF-8'); ?>
										</p>
										<?php if ($specificModelCode !== '' && $specificModelCode !== $specificModelDisplay): ?>
											<p class="text-gray-500 mb-16 text-sm">کد مدل: <?= htmlspecialchars($specificModelCode, ENT_QUOTES, 'UTF-8'); ?></p>
										<?php endif; ?>
									<?php else: ?>
										<p class="text-gray-500 mb-16">مدل اختصاصی برای این ارزیابی ثبت نشده است.</p>
									<?php endif; ?>
									<?php if (!empty($specificModel['competencies'])): ?>
										<ul class="competency-list">
											<?php foreach ($specificModel['competencies'] as $competency): ?>
												<li class="competency-item">
													<span class="competency-dot"></span>
													<div>
														<div class="competency-title">
															<?= htmlspecialchars($competency['title'] ?? 'شایستگی بدون عنوان', ENT_QUOTES, 'UTF-8'); ?>
															<?php if (!empty($competency['code'])): ?>
																<span class="text-gray-400 fw-normal">(<?= htmlspecialchars($competency['code'], ENT_QUOTES, 'UTF-8'); ?>)</span>
															<?php endif; ?>
														</div>
														<?php if (!empty($competency['dimension'])): ?>
															<div class="competency-meta">بعد: <?= htmlspecialchars($competency['dimension'], ENT_QUOTES, 'UTF-8'); ?></div>
														<?php endif; ?>
													</div>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php else: ?>
										<div class="text-gray-500">هیچ شایستگی‌ای برای مدل اختصاصی مرتبط ثبت نشده است.</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12">
				<div class="card evaluation-score-card shadow-sm">
					<div class="card-body p-24">
						<div class="d-flex flex-wrap justify-content-between align-items-center mb-20 gap-12">
							<h5 class="mb-0 text-gray-900 fw-semibold">ابزارهای اختصاص داده‌شده به <?= htmlspecialchars($selectedEvaluateeLabel, ENT_QUOTES, 'UTF-8'); ?></h5>
							<span class="badge bg-main-50 text-main-600 rounded-pill px-16 py-8">
								تعداد ابزار: <?= htmlspecialchars($toolsCount, ENT_QUOTES, 'UTF-8'); ?>
							</span>
						</div>

						<?php if (!empty($toolEntries)): ?>
							<div class="row g-4">
								<?php foreach ($toolEntries as $tool): ?>
									<div class="col-12 col-lg-6">
										<div class="tool-card h-100">
											<div class="d-flex justify-content-between align-items-start gap-12 mb-12">
												<div>
													<h6 class="mb-6 text-gray-900 fw-semibold">
														<?= htmlspecialchars($tool['name'] ?? 'ابزار بدون عنوان', ENT_QUOTES, 'UTF-8'); ?>
													</h6>
													<div class="text-gray-500 text-sm">
														ترتیب اجرا: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($tool['order'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
													</div>
												</div>
												<ion-icon name="clipboard-check" class="fs-4 text-main-500"></ion-icon>
											</div>
											<div class="mb-12">
												<div class="fw-semibold text-gray-700 mb-8">ارزیابان مرتبط</div>
												<div class="d-flex flex-wrap gap-8">
													<?php foreach (($tool['evaluators'] ?? []) as $evaluatorLabel): ?>
														<span class="pill-badge pill-badge-evaluator">
															<ion-icon name="person-outline"></ion-icon>
															<?= htmlspecialchars($evaluatorLabel, ENT_QUOTES, 'UTF-8'); ?>
														</span>
													<?php endforeach; ?>
												</div>
											</div>
											<div>
												<div class="fw-semibold text-gray-700 mb-8">شایستگی‌های پوشش داده‌شده</div>
												<?php if (!empty($tool['competencies'])): ?>
													<div class="d-flex flex-wrap gap-8">
														<?php foreach ($tool['competencies'] as $competency): ?>
															<span class="pill-badge pill-badge-competency pill-badge-competency small">
																<?= htmlspecialchars($competency['title'] ?? 'شایستگی', ENT_QUOTES, 'UTF-8'); ?>
															</span>
														<?php endforeach; ?>
													</div>
												<?php else: ?>
													<span class="text-gray-500 text-sm">شایستگی مشخصی برای این ابزار ثبت نشده است.</span>
												<?php endif; ?>
											</div>
											<?php if (!empty($tool['score_link'])): ?>
												<div class="score-button text-end">
													<a class="btn btn-sm btn-main rounded-pill d-inline-flex align-items-center gap-6" href="<?= htmlspecialchars($tool['score_link'], ENT_QUOTES, 'UTF-8'); ?>">
														<ion-icon name="create-outline"></ion-icon>
														<span>ثبت امتیاز برای این ابزار</span>
													</a>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="empty-state">
								<ion-icon name="construct-outline" class="fs-3 mb-8"></ion-icon>
								<p class="mb-2">هیچ ابزاری برای ارزیابی‌شونده انتخاب‌شده فعال نشده است.</p>
								<p class="mb-0 text-sm">در صورت نیاز، از بخش ماتریس ارزیابی نسبت به اختصاص ابزارها اقدام کنید.</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
	</div>
</div>
