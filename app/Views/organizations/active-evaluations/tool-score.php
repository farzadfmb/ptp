<?php
if (!class_exists('UtilityHelper')) {
	require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ثبت امتیاز ابزار ارزیابی';
$evaluationSummary = $evaluationSummary ?? [];
$toolMeta = $toolMeta ?? [];
$competencies = $competencies ?? [];
$existingScores = $existingScores ?? [];
$oldInput = $oldInput ?? [];
$validationErrors = $validationErrors ?? [];
$scoreFormAction = $scoreFormAction ?? UtilityHelper::baseUrl('organizations/active-evaluations/tool-score');
$selectedEvaluatee = $selectedEvaluatee ?? null;
$evaluateeTabs = $evaluateeTabs ?? [];
$pageMessages = $pageMessages ?? [];
$visibilityContext = $visibilityContext ?? [
	'role_label' => 'کاربر سازمان',
	'user_display' => 'کاربر سازمان',
	'message' => '',
	'can_view_all' => false,
	'is_evaluator' => false,
	'is_evaluatee' => false,
];
$backLink = $backLink ?? UtilityHelper::baseUrl('organizations/active-evaluations');
$toolScoreLink = $toolScoreLink ?? UtilityHelper::baseUrl('organizations/active-evaluations/tool-score');
$toolScoreBaseLink = $toolScoreBaseLink ?? ($toolScoreBaseLinkForView ?? $toolScoreLink);
$selectedEvaluateeId = (int) ($selectedEvaluatee['id'] ?? 0);
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];

$inline_scripts .= <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
	var scorerSelect = document.querySelector('[data-role="scorer-select"]');
	if (!scorerSelect) {
		return;
	}

	scorerSelect.addEventListener('change', function () {
		var baseUrl = scorerSelect.getAttribute('data-base-url');
		if (!baseUrl) {
			return;
		}

		var evaluateeId = scorerSelect.getAttribute('data-evaluatee-id');
		var selectedScorer = scorerSelect.value || '';

		var hasQuestion = baseUrl.indexOf('?') !== -1;
		var url = baseUrl;
		var params = [];
		if (evaluateeId) {
			params.push('evaluatee_id=' + encodeURIComponent(evaluateeId));
		}
		if (selectedScorer) {
			params.push('scorer_id=' + encodeURIComponent(selectedScorer));
		}

		if (params.length > 0) {
			url += (hasQuestion ? '&' : '?') + params.join('&');
		}

		window.location.href = url;
	});
});
JS;

$inline_styles .= <<<'CSS'
	body {
		background: #f5f7fb;
	}
	.tool-score-card {
		border-radius: 24px;
		border: 1px solid #e4e9f2;
		background: #ffffff;
	}
	.tool-score-hero {
		position: relative;
		overflow: hidden;
	}
	.tool-score-hero::before {
		content: '';
		position: absolute;
		inset-inline-start: -160px;
		inset-block-start: -160px;
		width: 300px;
		height: 300px;
		background: radial-gradient(circle at center, rgba(14, 116, 144, 0.18), transparent 70%);
		z-index: 0;
	}
	.tool-score-hero > * {
		position: relative;
		z-index: 1;
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
	.evaluatee-tab {
		border-radius: 999px;
		padding: 8px 18px;
		border: 1px solid transparent;
		background: rgba(14, 116, 144, 0.08);
		color: #0f172a;
		font-size: 13px;
		font-weight: 600;
		display: inline-flex;
		align-items: center;
		gap: 8px;
		text-decoration: none;
		transition: all 0.2s ease;
	}
	.evaluatee-tab:hover {
		background: rgba(14, 116, 144, 0.14);
		color: #0f172a;
	}
	.evaluatee-tab.active {
		background: #0e7490;
		color: #ffffff;
		box-shadow: 0 8px 20px rgba(14, 116, 144, 0.22);
	}
	.scorer-selection {
		border: 1px solid rgba(226, 232, 240, 0.8);
		border-radius: 18px;
		background: #f8fafc;
		padding: 16px 18px;
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
	}
	.scorer-selection h6 {
		margin: 0;
		font-weight: 600;
		color: #0f172a;
		font-size: 14px;
	}
	.scorer-selection .scorer-meta {
		font-size: 13px;
		color: #475569;
	}
	.competency-card {
		border: 1px solid rgba(226, 232, 240, 0.8);
		border-radius: 20px;
		padding: 20px 22px;
		background: #ffffff;
	}
	.score-choice-wrapper {
		direction: rtl;
		width: 100%;
		overflow-x: auto;
		padding-bottom: 4px;
	}
	.score-choice-grid {
		display: inline-flex;
		flex-wrap: nowrap;
		align-items: stretch;
		gap: 6px;
	}
	.score-choice {
		position: relative;
		display: flex;
		align-items: center;
		justify-content: center;
		min-width: 42px;
	}
	.score-choice input[type="radio"] {
		position: absolute;
		opacity: 0;
		pointer-events: none;
	}
	.score-choice span {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 40px;
		min-width: 42px;
		width: 100%;
		white-space: nowrap;
		border-radius: 12px;
		border: 1px solid rgba(226, 232, 240, 0.9);
		background: #f8fafc;
		color: #0f172a;
		font-weight: 600;
		font-size: 13px;
		cursor: pointer;
		transition: all 0.2s ease;
	}
	.score-choice span:hover {
		background: rgba(14, 116, 144, 0.12);
		border-color: rgba(14, 116, 144, 0.35);
	}
	.score-choice input[type="radio"]:checked + span {
		background: #0e7490;
		color: #ffffff;
		border-color: #0e7490;
		box-shadow: 0 8px 20px rgba(14, 116, 144, 0.25);
	}
	.score-choice-empty span {
		font-size: 12px;
		font-weight: 500;
		color: #475569;
		background: rgba(148, 163, 184, 0.12);
	}
	.score-choice input[type="radio"]:checked + span.score-choice-empty-label {
		background: #e2e8f0;
		color: #334155;
		border-color: rgba(148, 163, 184, 0.6);
		box-shadow: none;
	}
	.textarea-note {
		min-height: 90px;
	}
	.example-row {
		border: 1px solid rgba(226, 232, 240, 0.6);
		border-radius: 16px;
		padding: 16px;
		background: #f8fafc;
	}
	.example-list {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}
	.form-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
		justify-content: flex-end;
	}
	@media (max-width: 768px) {
		.score-choice-grid {
			gap: 6px;
		}
		.form-actions {
			justify-content: stretch;
		}
		.form-actions .btn {
			flex: 1 1 auto;
		}
	}
CSS;

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user ?? null;
include __DIR__ . '/../../layouts/organization-navbar.php';

$evaluationTitle = $evaluationSummary['title'] ?? 'بدون عنوان';
$evaluationDateDisplay = $evaluationSummary['date_display'] ?? 'تاریخ ثبت نشده';
$generalModelLabel = $evaluationSummary['general_model_label'] ?? '';
$specificModelLabel = $evaluationSummary['specific_model_label'] ?? '';
$selectedEvaluateeLabel = $selectedEvaluatee['label'] ?? 'انتخاب نشده';

$toolName = $toolMeta['name'] ?? 'ابزار بدون عنوان';
$toolOrder = UtilityHelper::englishToPersian((string) ($toolMeta['order'] ?? 0));
$toolQuestionType = $toolMeta['question_type'] ?? '';
$toolEvaluators = $toolMeta['evaluators'] ?? [];

$scorerSelection = $scorerSelection ?? [
	'options' => [],
	'can_select' => false,
	'selected_id' => 0,
	'acting_as_own' => false,
];
$scorerOptions = $scorerSelection['options'] ?? [];
$canSelectScorer = !empty($scorerSelection['can_select']);
$selectedScorerId = (int) ($scorerSelection['selected_id'] ?? 0);
$selectedScorerLabel = 'ارزیاب تعیین نشده';
foreach ($scorerOptions as $option) {
	if (!empty($option['selected'])) {
		$selectedScorerLabel = $option['label'] ?? $selectedScorerLabel;
		break;
	}
}

if ($selectedScorerLabel === 'ارزیاب تعیین نشده' && !empty($scorerOptions)) {
	$selectedScorerLabel = $scorerOptions[0]['label'] ?? $selectedScorerLabel;
}

?>

<div class="page-content-wrapper">
	<div class="page-content">
		<div class="row g-4">
			<div class="col-12">
				<div class="card tool-score-card tool-score-hero shadow-sm">
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
								<h2 class="mb-10 text-gray-900 fw-bold">ثبت امتیاز ابزار: <?= htmlspecialchars($toolName, ENT_QUOTES, 'UTF-8'); ?></h2>
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
								<?php if ($generalModelLabel !== ''): ?>
									<div class="d-flex align-items-center gap-8 text-gray-500">
										<ion-icon name="layers-outline" class="text-main-500"></ion-icon>
										<span>مدل عمومی: <?= htmlspecialchars($generalModelLabel, ENT_QUOTES, 'UTF-8'); ?></span>
									</div>
								<?php endif; ?>
								<?php if ($specificModelLabel !== ''): ?>
									<div class="d-flex align-items-center gap-8 text-gray-500">
										<ion-icon name="sparkles-outline" class="text-main-500"></ion-icon>
										<span>مدل اختصاصی: <?= htmlspecialchars($specificModelLabel, ENT_QUOTES, 'UTF-8'); ?></span>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="d-flex flex-wrap gap-12 mt-18">
							<span class="pill-badge pill-badge-evaluator">
								<ion-icon name="list-outline"></ion-icon>
								ترتیب اجرا: <?= htmlspecialchars($toolOrder, ENT_QUOTES, 'UTF-8'); ?>
							</span>
							<?php if ($toolQuestionType !== ''): ?>
								<span class="pill-badge" style="background: rgba(245, 158, 11, 0.12); color: #b45309;">
									<ion-icon name="help-circle-outline"></ion-icon>
									نوع سوال: <?= htmlspecialchars($toolQuestionType, ENT_QUOTES, 'UTF-8'); ?>
								</span>
							<?php endif; ?>
							<?php if ($selectedScorerLabel !== ''): ?>
								<span class="pill-badge pill-badge-evaluator" style="background: rgba(14, 116, 144, 0.18); color: #0e7490;">
									<ion-icon name="person-circle-outline"></ion-icon>
									ارزیاب امتیازدهنده: <?= htmlspecialchars($selectedScorerLabel, ENT_QUOTES, 'UTF-8'); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<?php if ($successMessage): ?>
				<div class="col-12">
					<div class="alert alert-success rounded-16" role="alert">
						<?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($errorMessage): ?>
				<div class="col-12">
					<div class="alert alert-danger rounded-16" role="alert">
						<?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
					</div>
				</div>
			<?php endif; ?>

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
					<div class="card tool-score-card shadow-sm">
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
				<div class="card tool-score-card shadow-sm">
					<div class="card-body p-24">
						<form action="<?= htmlspecialchars($scoreFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="POST" class="d-flex flex-column gap-24">
							<?= csrf_field(); ?>
							<input type="hidden" name="evaluation_id" value="<?= htmlspecialchars((string) $evaluationSummary['id'], ENT_QUOTES, 'UTF-8'); ?>">
							<input type="hidden" name="tool_id" value="<?= htmlspecialchars((string) ($toolMeta['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
							<input type="hidden" name="evaluatee_id" value="<?= htmlspecialchars((string) ($selectedEvaluatee['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

							<div class="scorer-selection">
								<div>
									<h6>ارزیاب انتخاب‌شده برای ثبت امتیاز</h6>
									<div class="scorer-meta">
										امتیازها به نام این ارزیاب ذخیره می‌شوند تا در گزارش‌های شخصی او نمایش داده شود.
									</div>
								</div>
								<?php if ($canSelectScorer && !empty($scorerOptions)): ?>
									<div class="flex-grow-1" style="min-width: 220px;">
										<select
											name="scorer_id"
											class="form-select rounded-pill"
											data-role="scorer-select"
											data-base-url="<?= htmlspecialchars($toolScoreBaseLink, ENT_QUOTES, 'UTF-8'); ?>"
											data-evaluatee-id="<?= htmlspecialchars((string) $selectedEvaluateeId, ENT_QUOTES, 'UTF-8'); ?>"
										>
											<?php foreach ($scorerOptions as $option): ?>
												<?php $isSelected = !empty($option['selected']) || ((int) ($option['id'] ?? 0) === $selectedScorerId); ?>
												<option value="<?= htmlspecialchars((string) ($option['id'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>" <?php if ($isSelected): ?>selected<?php endif; ?>>
													<?= htmlspecialchars($option['label'] ?? 'ارزیاب', ENT_QUOTES, 'UTF-8'); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
								<?php else: ?>
									<span class="pill-badge pill-badge-evaluator" style="background: rgba(14, 116, 144, 0.18);">
										<ion-icon name="person-outline"></ion-icon>
										<?= htmlspecialchars($selectedScorerLabel, ENT_QUOTES, 'UTF-8'); ?>
									</span>
									<input type="hidden" name="scorer_id" value="<?= htmlspecialchars((string) $selectedScorerId, ENT_QUOTES, 'UTF-8'); ?>">
								<?php endif; ?>
							</div>

							<?php foreach ($competencies as $competency): ?>
								<?php
									$compId = (int) ($competency['id'] ?? 0);
									$range = $competency['scoring_range'] ?? ['min' => 0, 'max' => 5, 'step' => 1];
									$rangeLabel = sprintf(
										'دامنه امتیاز: %s تا %s',
										UtilityHelper::englishToPersian((string) ($range['min'] ?? 0)),
										UtilityHelper::englishToPersian((string) ($range['max'] ?? 5))
									);
									$minScore = (int) ($range['min'] ?? 0);
									$maxScore = (int) ($range['max'] ?? 5);
									if ($maxScore < $minScore) {
										[$minScore, $maxScore] = [$maxScore, $minScore];
									}
									$step = (int) ($range['step'] ?? 1);
									if ($step <= 0) {
										$step = 1;
									}
									$scoreOptions = [];
									for ($scoreValue = $minScore; $scoreValue <= $maxScore; $scoreValue += $step) {
										$scoreOptions[] = $scoreValue;
									}
									if (end($scoreOptions) !== $maxScore) {
										$scoreOptions[] = $maxScore;
									}
								?>
								<div class="competency-card">
									<div class="d-flex flex-column flex-md-row justify-content-between gap-12 mb-16">
										<div>
											<h5 class="mb-6 text-gray-900 fw-semibold">
												<?= htmlspecialchars($competency['title'] ?? 'شایستگی بدون عنوان', ENT_QUOTES, 'UTF-8'); ?>
												<?php if (!empty($competency['code'])): ?>
													<span class="text-gray-400 fw-normal">(<?= htmlspecialchars($competency['code'], ENT_QUOTES, 'UTF-8'); ?>)</span>
												<?php endif; ?>
											</h5>
											<?php if (!empty($competency['dimension'])): ?>
												<div class="text-gray-600 text-sm mb-2">بعد: <?= htmlspecialchars($competency['dimension'], ENT_QUOTES, 'UTF-8'); ?></div>
											<?php endif; ?>
											<div class="text-gray-500 text-sm">گروه: <?= htmlspecialchars($competency['group_label'] ?? 'سایر شایستگی‌ها', ENT_QUOTES, 'UTF-8'); ?></div>
										</div>
										<div class="text-gray-500 text-sm d-flex flex-column gap-4 align-items-md-end">
											<div>
												<ion-icon name="speedometer-outline" class="text-main-500"></ion-icon>
												<span><?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
											</div>
											<div>
												<ion-icon name="stats-chart-outline" class="text-main-500"></ion-icon>
												<span>نوع امتیازدهی: <?= htmlspecialchars($competency['scoring_type_label'] ?? 'امتیاز دهی ۰ تا ۵', ENT_QUOTES, 'UTF-8'); ?></span>
											</div>
										</div>
									</div>

									<?php if (!empty($competency['examples'])): ?>
										<div class="example-list">
											<?php foreach ($competency['examples'] as $example): ?>
												<?php
													$exampleId = (int) ($example['id'] ?? 0);
													$exampleValue = '';
													if (isset($oldInput['scores'][$compId]['examples'][$exampleId])) {
														$exampleValue = (string) $oldInput['scores'][$compId]['examples'][$exampleId];
													} elseif (isset($existingScores[$compId]['examples'][$exampleId]['score'])) {
														$exampleValue = UtilityHelper::englishToPersian((string) $existingScores[$compId]['examples'][$exampleId]['score']);
													}

													$exampleNote = '';
													if (isset($oldInput['notes'][$compId]['examples'][$exampleId])) {
														$exampleNote = (string) $oldInput['notes'][$compId]['examples'][$exampleId];
													} elseif (!empty($existingScores[$compId]['examples'][$exampleId]['note'])) {
														$exampleNote = (string) $existingScores[$compId]['examples'][$exampleId]['note'];
													}
												?>
												<div class="example-row">
													<div class="d-flex flex-column flex-md-row justify-content-between gap-12 mb-12">
														<div class="text-gray-800 fw-semibold">
															<?= htmlspecialchars($example['text'] ?? 'مصداق رفتاری', ENT_QUOTES, 'UTF-8'); ?>
														</div>
														<div class="text-gray-500 text-sm d-flex align-items-center gap-6">
															<ion-icon name="pricetag-outline" class="text-main-500"></ion-icon>
															<span><?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
														</div>
													</div>
													<div class="row g-3 mb-8">
														<?php
															$exampleValueEnglish = $exampleValue !== '' ? UtilityHelper::persianToEnglish((string) $exampleValue) : '';
															$exampleColumns = count($scoreOptions) + 1;
															if ($exampleColumns > 10) {
																$exampleColumns = 10;
															}
															if ($exampleColumns < 3) {
																$exampleColumns = 3;
															}
														?>
														<div class="col-12">
															<div class="score-choice-wrapper">
																<div class="score-choice-grid" style="--score-columns: <?= (int) $exampleColumns; ?>;">
																	<label class="score-choice score-choice-empty">
																		<input type="radio" name="scores[<?= $compId; ?>][examples][<?= $exampleId; ?>]" value="" <?php if ($exampleValueEnglish === ''): ?>checked<?php endif; ?>>
																		<span class="score-choice-empty-label">بدون امتیاز</span>
																	</label>
																	<?php foreach ($scoreOptions as $scoreOption): ?>
																		<?php
																			$scoreOptionValue = (string) $scoreOption;
																			$scoreOptionPersian = UtilityHelper::englishToPersian($scoreOptionValue);
																			$isChecked = $exampleValueEnglish !== '' && (string) $scoreOption === (string) $exampleValueEnglish;
																		?>
																		<label class="score-choice">
																			<input type="radio" name="scores[<?= $compId; ?>][examples][<?= $exampleId; ?>]" value="<?= htmlspecialchars($scoreOptionValue, ENT_QUOTES, 'UTF-8'); ?>" <?php if ($isChecked): ?>checked<?php endif; ?>>
																			<span><?= htmlspecialchars($scoreOptionPersian, ENT_QUOTES, 'UTF-8'); ?></span>
																		</label>
																	<?php endforeach; ?>
																</div>
															</div>
															<?php if (!empty($validationErrors[$compId]['examples'][$exampleId])): ?>
																<div class="text-danger small mt-1"><?= htmlspecialchars($validationErrors[$compId]['examples'][$exampleId], ENT_QUOTES, 'UTF-8'); ?></div>
															<?php endif; ?>
														</div>
													</div>
													<div class="row g-3 align-items-start">
														<div class="col-12">
															<textarea name="notes[<?= $compId; ?>][examples][<?= $exampleId; ?>]" class="form-control textarea-note" placeholder="توضیح مرتبط با این مصداق (اختیاری)"><?= htmlspecialchars($exampleNote, ENT_QUOTES, 'UTF-8'); ?></textarea>
															<?php if (!empty($validationErrors[$compId]['examples_notes'][$exampleId])): ?>
																<div class="text-danger small mt-1"><?= htmlspecialchars($validationErrors[$compId]['examples_notes'][$exampleId], ENT_QUOTES, 'UTF-8'); ?></div>
															<?php endif; ?>
														</div>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php else: ?>
										<div class="text-gray-500">مصداق رفتاری برای این شایستگی ثبت نشده است.</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>

							<?php if (empty($competencies)): ?>
								<div class="alert alert-warning rounded-16" role="alert">
									برای این ابزار شایستگی‌ای ثبت نشده است. لطفاً از بخش ماتریس شایستگی ابزار، شایستگی‌های مرتبط را تعریف کنید.
								</div>
							<?php endif; ?>

							<div class="form-actions">
								<button type="submit" class="btn btn-main rounded-pill d-inline-flex align-items-center gap-6">
									<ion-icon name="save-outline"></ion-icon>
									<span>ثبت امتیاز ابزار</span>
								</button>
								<a href="<?= htmlspecialchars($backLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary rounded-pill">
									بازگشت به ارزیابی
								</a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
	</div>
</div>
