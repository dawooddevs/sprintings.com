<?php
/**
 * Renders any form defined in QuoteForms.php. Posts to /api/form (JSON with JavaScript,
 * redirect to /thank-you/ without). The main quote form becomes a 3-step wizard when
 * $wizard is true and JavaScript is available; without JavaScript every step shows.
 *
 * @var string     $type     form key (quote, contact, product_general, …, newsletter)
 * @var array|null $product  product row (adds the product name and spec suggestions)
 * @var bool       $wizard   render the main quote form as a wizard
 * @var bool       $compact  single-row layout (newsletter)
 * @var string     $idp      id prefix when the same form appears twice on a page
 */
$def = QuoteForms::get($type);
if (!$def) {
    return;
}
$product = $product ?? null;
$wizard = !empty($wizard);
$compact = !empty($compact);
$idp = ($idp ?? $type) . '-';
$hasFile = (bool)array_filter($def['fields'], fn($f) => $f['type'] === 'file');
$steps = [];
foreach ($def['fields'] as $f) {
    $steps[$wizard ? (int)($f['step'] ?? 1) : 1][] = $f;
}
ksort($steps);
$stepInfo = [
    1 => ['Contact details', 'Who should we send the quote to?'],
    2 => ['Project specs', 'Tell us what you’d like printed. Not sure yet? Leave it blank and we’ll help.'],
    3 => ['Artwork & comments', 'Upload your files if they’re ready, or tell us about your idea.'],
];
$accept = '.' . implode(',.', array_keys(QuoteForms::UPLOAD_TYPES));
$renderField = function (array $f) use ($idp, $product, $accept) {
    $id = $idp . $f['name'];
    $req = !empty($f['required']);
    $cls = 'field' . (!empty($f['half']) ? ' field--half' : '');
    $help = !empty($f['help']) ? '<p class="help" id="' . e($id) . '-help">' . e($f['help']) . '</p>' : '';
    $described = !empty($f['help']) ? ' aria-describedby="' . e($id) . '-help"' : '';
    $label = '<label for="' . e($id) . '">' . e($f['label']) . ($req ? ' <span class="req" aria-hidden="true">*</span>' : '') . '</label>';
    if ($f['type'] === 'file') {
        $max = QuoteForms::UPLOAD_MAX_FILES;
        return '<div class="field" data-files-field><span class="label" id="' . e($id) . '-label">' . e($f['label']) . '</span>'
            . '<div class="drop" data-drop>' . icon('upload') . '<strong>Drop your artwork here or browse</strong>'
            . '<small>PDF, AI, EPS, PSD, PNG, JPG, TIFF or ZIP · up to ' . $max . ' files · 50 MB each</small>'
            . '<input type="file" id="' . e($id) . '" name="files[]" multiple accept="' . e($accept) . '" aria-labelledby="' . e($id) . '-label" data-max="' . $max . '" data-max-bytes="' . QuoteForms::UPLOAD_MAX_BYTES . '"></div>'
            . '<ul class="files" data-file-list aria-live="polite"></ul>'
            . '<p class="help">No artwork yet? No problem — we can design it for you.</p></div>';
    }
    $list = '';
    $listAttr = '';
    if (!empty($f['suggest']) && $product) {
        $opts = Content::specItems($product, $f['suggest']);
        if ($opts) {
            $list = '<datalist id="' . e($id) . '-list">' . implode('', array_map(fn($o) => '<option value="' . e($o) . '">', $opts)) . '</datalist>';
            $listAttr = ' list="' . e($id) . '-list" placeholder="Choose or type…"';
        }
    }
    if ($f['type'] === 'textarea') {
        $input = '<textarea id="' . e($id) . '" name="' . e($f['name']) . '" rows="4"' . ($req ? ' required' : '') . $described . '></textarea>';
    } else {
        $type = in_array($f['type'], ['email', 'tel', 'number'], true) ? $f['type'] : 'text';
        $input = '<input id="' . e($id) . '" name="' . e($f['name']) . '" type="' . $type . '"' . ($req ? ' required' : '')
            . (!empty($f['autocomplete']) ? ' autocomplete="' . e($f['autocomplete']) . '"' : '')
            . ($type === 'tel' ? ' inputmode="tel"' : '') . ($type === 'number' ? ' inputmode="numeric" min="0"' : '')
            . (!empty($f['placeholder']) ? ' placeholder="' . e($f['placeholder']) . '"' : '') . $listAttr . $described . '>';
    }
    return '<div class="' . $cls . '">' . $label . $input . $list . $help . '</div>';
};
$page = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
?>
<form class="form<?= $compact ? ' form--compact' : '' ?><?= $wizard ? ' wizard-form' : '' ?>" method="post" action="<?= e(url('api/form')) ?>"<?= $hasFile ? ' enctype="multipart/form-data"' : '' ?> data-form data-success="<?= e($def['success']) ?>" novalidate>
  <input type="hidden" name="_form" value="<?= e($type) ?>">
  <input type="hidden" name="_token" value="<?= e(Forms::token()) ?>">
  <input type="hidden" name="_page" value="<?= e($page) ?>">
  <?php if ($product): ?><input type="hidden" name="_product" value="<?= e($product['slug']) ?>"><?php endif; ?>
  <div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

  <?php if ($wizard): ?>
  <div class="wizard__bar" aria-hidden="true"><span data-wizard-bar></span></div>
  <?php foreach ($steps as $n => $fields): ?>
  <fieldset class="wstep<?= $n === 1 ? ' is-current' : '' ?>" data-step="<?= $n ?>" style="border:0;margin:0;padding:0;min-width:0">
    <legend class="wstep__title h3" style="font-family:var(--font-display);font-weight:800;color:var(--ink);padding:0"><span class="sr-only">Step <?= $n ?> of <?= count($steps) ?>: </span><?= e($stepInfo[$n][0] ?? 'Details') ?></legend>
    <p class="wstep__intro"><?= e($stepInfo[$n][1] ?? '') ?></p>
    <div class="form__grid">
      <?php $group = null; foreach ($fields as $f):
          if (($f['group'] ?? null) !== $group) { $group = $f['group'] ?? null; if ($group) echo '<div class="field field--group-start"><span class="group-title">' . e($group) . ' <span style="font-weight:500;color:var(--muted);font-family:var(--font-body)">(optional)</span></span></div>'; }
          echo $renderField($f);
      endforeach; ?>
    </div>
    <div class="wizard__nav">
      <?php if ($n > 1): ?><button class="btn btn--ghost js-only" type="button" data-wizard-prev><?= icon('arrow-left') ?>Back</button><?php else: ?><span></span><?php endif; ?>
      <?php if ($n < count($steps)): ?>
      <button class="btn btn--teal js-only" type="button" data-wizard-next>Next: <?= e($stepInfo[$n + 1][0] ?? 'Continue') ?><?= icon('arrow-right', 'icon icon--arrow') ?></button>
      <?php else: ?>
      <button class="btn btn--accent btn--lg btn--magnetic" type="submit"><span><?= e($def['submit']) ?></span><?= icon('arrow-right', 'icon icon--arrow') ?></button>
      <?php endif; ?>
    </div>
  </fieldset>
  <?php endforeach; ?>
  <?php else: ?>
  <div class="form__grid">
    <?php foreach ($def['fields'] as $f): ?><?= $compact ? str_replace('<label for', '<label class="sr-only" for', $renderField($f + ['placeholder' => 'Your email address'])) : $renderField($f) ?><?php endforeach; ?>
    <?php if ($compact): ?><div class="form__actions"><button class="btn btn--accent" type="submit"><?= e($def['submit']) ?></button></div><?php endif; ?>
  </div>
  <?php if (!empty($def['consent'])): ?>
  <div class="field field--check"><input type="checkbox" id="<?= e($idp) ?>consent" name="consent" value="1" required><label for="<?= e($idp) ?>consent">I agree to be contacted about this request.</label></div>
  <?php endif; ?>
  <?php if (!$compact): ?>
  <div class="form__actions">
    <button class="btn btn--accent btn--lg btn--block btn--magnetic" type="submit"><span><?= e($def['submit']) ?></span><?= icon('arrow-right', 'icon icon--arrow') ?></button>
  </div>
  <?php endif; ?>
  <?php endif; ?>
  <div class="upload-progress" data-upload-progress hidden><span></span></div>
  <p class="form__privacy"><?= $def['kind'] === 'newsletter' ? 'We’ll only use your email for Sprintings offers.' : 'We’ll only use your details to answer this request.' ?> See our <a href="<?= e(url('privacy-policy/')) ?>">privacy policy</a>.</p>
  <div class="form__status" role="status" aria-live="polite" data-form-status></div>
</form>
