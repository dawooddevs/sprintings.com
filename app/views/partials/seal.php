<?php /** Rotating "100% satisfaction" seal. */ ?>
<div class="seal" aria-hidden="true">
  <svg class="seal__ring" viewBox="0 0 100 100"><defs><path id="seal-path-<?= $u = substr(md5((string)mt_rand()), 0, 5) ?>" d="M50 50 m-38 0 a38 38 0 1 1 76 0 a38 38 0 1 1 -76 0"/></defs><text font-size="8.6" font-weight="700" letter-spacing="2.2" fill="#fff" font-family="Montserrat, sans-serif"><textPath href="#seal-path-<?= $u ?>">EVERY TIME · ANY REASON · GUARANTEED ·</textPath></text></svg>
  <span class="seal__core">100%<small>Satisfaction</small></span>
</div>
