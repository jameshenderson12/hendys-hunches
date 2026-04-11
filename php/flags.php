<?php

function hh_get_team_flag_specs(): array {
    return [
        'Algeria' => ['code' => 'dz'],
        'Argentina' => ['code' => 'ar'],
        'Australia' => ['code' => 'au'],
        'Austria' => ['code' => 'at'],
        'Belgium' => ['code' => 'be'],
        'Bosnia-Herzegovina' => ['code' => 'ba'],
        'Bosnia and Herzegovina' => ['code' => 'ba'],
        'Brazil' => ['code' => 'br'],
        'Cabo Verde' => ['code' => 'cv'],
        'Canada' => ['code' => 'ca'],
        'Colombia' => ['code' => 'co'],
        'Congo DR' => ['code' => 'cd'],
        'Croatia' => ['code' => 'hr'],
        'Curaçao' => ['code' => 'cw'],
        'Curacao' => ['code' => 'cw'],
        'Czech Republic' => ['code' => 'cz'],
        'Czechia' => ['code' => 'cz'],
        'Côte d\'Ivoire' => ['code' => 'ci'],
        'Ivory Coast' => ['code' => 'ci'],
        'Ecuador' => ['code' => 'ec'],
        'Egypt' => ['code' => 'eg'],
        'England' => ['code' => 'gb-eng'],
        'France' => ['code' => 'fr'],
        'Germany' => ['code' => 'de'],
        'Ghana' => ['code' => 'gh'],
        'Haiti' => ['code' => 'ht'],
        'IR Iran' => ['code' => 'ir'],
        'Iran' => ['code' => 'ir'],
        'Iraq' => ['code' => 'iq'],
        'Japan' => ['code' => 'jp'],
        'Jordan' => ['code' => 'jo'],
        'Korea Republic' => ['code' => 'kr'],
        'South Korea' => ['code' => 'kr'],
        'Mexico' => ['code' => 'mx'],
        'Morocco' => ['code' => 'ma'],
        'Netherlands' => ['code' => 'nl'],
        'New Zealand' => ['code' => 'nz'],
        'Norway' => ['code' => 'no'],
        'Panama' => ['code' => 'pa'],
        'Paraguay' => ['code' => 'py'],
        'Portugal' => ['code' => 'pt'],
        'Qatar' => ['code' => 'qa'],
        'Saudi Arabia' => ['code' => 'sa'],
        'Scotland' => ['code' => 'gb-sct'],
        'Senegal' => ['code' => 'sn'],
        'South Africa' => ['code' => 'za'],
        'Spain' => ['code' => 'es'],
        'Sweden' => ['code' => 'se'],
        'Switzerland' => ['code' => 'ch'],
        'To be announced' => ['custom' => 'tbd'],
        'Tunisia' => ['code' => 'tn'],
        'Türkiye' => ['code' => 'tr'],
        'Turkey' => ['code' => 'tr'],
        'USA' => ['code' => 'us'],
        'United States' => ['code' => 'us'],
        'Uruguay' => ['code' => 'uy'],
        'Uzbekistan' => ['code' => 'uz'],
    ];
}

function hh_is_placeholder_team(string $team): bool {
    $team = trim($team);

    if ($team === '') {
        return true;
    }

    if (strcasecmp($team, 'To be announced') === 0) {
        return true;
    }

    return (bool)preg_match('/^\d[A-Z]+$/', $team);
}

function hh_get_local_flag_asset_candidates(array $spec): array {
    $candidates = [];

    if (!empty($spec['custom'])) {
        $candidates[] = 'img/flags/' . $spec['custom'] . '.svg';
        $candidates[] = 'img/flags/' . $spec['custom'] . '.png';
    }

    if (!empty($spec['code'])) {
        $candidates[] = 'img/flags/' . $spec['code'] . '.svg';
        $candidates[] = 'img/flags/' . $spec['code'] . '.png';
    }

    return $candidates;
}

function hh_resolve_team_flag_asset(string $team): array {
    $team = trim($team);

    if ($team === '') {
        return ['path' => ''];
    }

    $specs = hh_get_team_flag_specs();
    $spec = $specs[$team] ?? [];

    if (hh_is_placeholder_team($team) && empty($spec)) {
        $spec = ['custom' => 'tbd'];
    }

    $projectRoot = dirname(__DIR__);
    foreach (hh_get_local_flag_asset_candidates($spec) as $candidate) {
        if (file_exists($projectRoot . '/' . $candidate)) {
            return ['path' => $candidate];
        }
    }

    return ['path' => ''];
}

function hh_get_team_flag_path(string $team): string {
    return hh_resolve_team_flag_asset($team)['path'];
}

function hh_get_known_team_flag_paths(string $relativePrefix = ''): array {
    $paths = [];

    foreach (array_keys(hh_get_team_flag_specs()) as $team) {
        $path = hh_get_team_flag_path($team);
        if ($path !== '') {
            $paths[$team] = hh_normalize_flag_src($path, $relativePrefix);
        }
    }

    return $paths;
}

function hh_normalize_flag_src(string $path, string $relativePrefix = ''): string {
    if ($path === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return $relativePrefix . ltrim($path, '/');
}
