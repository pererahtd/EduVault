<?php
// Admin batches server-side storage (JSON)
const BATCH_FILE = __DIR__ . '/batches.json';

function batch_defaults(): array {
    return ['26.1 IFSLS', '26.1 FOC', '25.1 IFSLS'];
}

function batch_load(): array {
    if (!is_file(BATCH_FILE)) {
        batch_save(batch_defaults());
        return batch_defaults();
    }
    $data = json_decode((string)file_get_contents(BATCH_FILE), true);
    if (!is_array($data)) return [];
    $clean = [];
    foreach ($data as $b) {
        if (is_string($b) && trim($b) !== '' && mb_strlen($b) <= 75) $clean[] = $b;
    }
    return array_values(array_unique($clean));
}

function batch_save(array $batches): bool {
    $json = json_encode(array_values($batches), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return file_put_contents(BATCH_FILE, $json, LOCK_EX) !== false;
}

function batch_add(string $name): string {
    $name = trim(preg_replace('/\s+/', ' ', $name));
    if ($name === '') return 'empty';
    if (mb_strlen($name) > 75) return 'toolong';
    $batches = batch_load();
    if (in_array($name, $batches, true)) return 'exists';
    $batches[] = $name;
    return batch_save($batches) ? 'ok' : 'error';
}

function batch_delete(string $name): bool {
    $batches = array_values(array_filter(batch_load(), fn($b) => $b !== $name));
    return batch_save($batches);
}