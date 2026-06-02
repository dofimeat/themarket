<?php

namespace app\models\traits;

trait ProductFormTrait
{
    public function validatePrice($attribute): void
    {
        $n = $this->parsePriceValue();
        if ($n === null || $n <= 0) {
            $this->addError($attribute, 'Укажите корректную цену.');
        }
    }

    public function validateFeatures($attribute): void
    {
        $rows = $this->normalizeFeaturesInput();
        foreach ($rows as $i => $row) {
            if ($row['name'] === '' && $row['value'] !== '') {
                $this->addError($attribute, 'Укажите название характеристики в строке ' . ($i + 1) . '.');
                return;
            }
            if ($row['value'] === '' && $row['name'] !== '') {
                $this->addError($attribute, 'Укажите значение характеристики в строке ' . ($i + 1) . '.');
                return;
            }
        }
    }

    /**
     * @return array<int, array{name: string, value: string, id: ?int}>
     */
    public function normalizeFeaturesInput(): array
    {
        $out = [];
        foreach ($this->features as $row) {
            if (!is_array($row)) {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            $id = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null;
            if ($name === '' && $value === '' && $id === null) {
                continue;
            }
            $out[] = [
                'id' => $id > 0 ? $id : null,
                'name' => $name,
                'value' => $value,
            ];
        }
        return $out;
    }

    public function validateSizes($attribute): void
    {
        $rows = $this->normalizeSizesInput();
        if ($rows === []) {
            $this->addError($attribute, 'Добавьте хотя бы один размер.');
            return;
        }
        foreach ($rows as $i => $row) {
            if ($row['size'] === '') {
                $this->addError($attribute, 'Заполните название размера в строке ' . ($i + 1) . '.');
                return;
            }
            if ($row['quantity'] < 0) {
                $this->addError($attribute, 'Количество не может быть отрицательным.');
                return;
            }
        }
    }

    public function parsePriceValue(): ?float
    {
        $raw = trim((string) $this->price);
        if ($raw === '') {
            return null;
        }
        $normalized = str_replace([' ', "\xc2\xa0"], '', $raw);
        $normalized = str_replace(',', '.', $normalized);
        if (!is_numeric($normalized)) {
            return null;
        }
        return round((float) $normalized, 2);
    }

    /**
     * @return array<int, array{size: string, quantity: int, id: ?int}>
     */
    public function normalizeSizesInput(): array
    {
        $out = [];
        foreach ($this->sizes as $row) {
            if (!is_array($row)) {
                continue;
            }
            $size = trim((string) ($row['size'] ?? ''));
            $qtyRaw = $row['quantity'] ?? '';
            if ($qtyRaw === '' || $qtyRaw === null) {
                $qty = 1;
            } else {
                $qty = is_numeric($qtyRaw) ? (int) $qtyRaw : 0;
            }
            $id = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null;
            if ($size === '' && $id === null) {
                continue;
            }
            $out[] = [
                'id' => $id > 0 ? $id : null,
                'size' => $size,
                'quantity' => max(0, $qty),
            ];
        }
        return $out;
    }
}
