<?php

require_once __DIR__ . '/config.php';

if (!function_exists('hh_terms_items')) {
    function hh_terms_items(): array
    {
        global $competition, $signup_fee_formatted, $signup_close_date;

        return [
            'your involvement in this game, and the game itself, is intended only for entertainment',
            'the game is based on ' . (string) $competition,
            'only one registration per person is permitted although family and friends are welcome to participate',
            'an entry fee of £' . (string) $signup_fee_formatted . ' is to be paid prior to ' . (string) $signup_close_date . '; split for charity donation and prize funds',
            'an unpaid entry fee results in removal from the game',
            'the number of prize funds, and their amounts, are revealed in due course, awarded to winners after the final tournament fixture and, in the event of a shared winning spot, divided accordingly.',
        ];
    }
}

if (!function_exists('hh_render_terms_content')) {
    function hh_render_terms_content(string $assetPrefix = '', bool $showLogo = true): void
    {
        $assetPrefix = trim($assetPrefix);
        if ($assetPrefix !== '' && !str_ends_with($assetPrefix, '/')) {
            $assetPrefix .= '/';
        }

        if ($showLogo) {
            ?>
            <img src="<?= htmlspecialchars($assetPrefix . 'img/hh-logo-2026-main.png', ENT_QUOTES) ?>" class="img-fluid d-block mx-auto mb-3" alt="Hendy's Hunches logo" style="max-width: 180px;">
            <?php
        }
        ?>
        <p>By registering to play Hendy's Hunches, you acknowledge that:</p>
        <ul>
            <?php foreach (hh_terms_items() as $item) : ?>
                <li><?= htmlspecialchars($item, ENT_QUOTES) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php
    }
}

if (!function_exists('hh_render_terms_modal')) {
    function hh_render_terms_modal(string $assetPrefix = ''): void
    {
        ?>
        <div class="modal fade" id="terms" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="termsTitle" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="termsTitle">Hendy's Hunches: Terms &amp; Conditions</h1>
              </div>
              <div class="modal-body">
                <?php hh_render_terms_content($assetPrefix, true); ?>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Understood</button>
              </div>
            </div>
          </div>
        </div>
        <?php
    }
}

if (!function_exists('hh_render_terms_inline_panel')) {
    function hh_render_terms_inline_panel(): void
    {
        ?>
        <div class="terms-panel text-start" id="terms-panel">
          <h5 class="text-center">Hendy's Hunches: Terms &amp; Conditions</h5>
          <?php hh_render_terms_content('', false); ?>
        </div>
        <?php
    }
}
